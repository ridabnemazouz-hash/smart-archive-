<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$document_id = $_POST['document_id'] ?? null;

if (!$document_id) {
    echo json_encode(['success' => false, 'message' => 'Document ID is required']);
    exit;
}

// Fetch document
$stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
$stmt->execute([$document_id, $user_id]);
$doc = $stmt->fetch();

if (!$doc) {
    echo json_encode(['success' => false, 'message' => 'Document not found']);
    exit;
}

// Check if summary already exists
if (!empty($doc['ai_summary'])) {
    echo json_encode(['success' => true, 'summary' => $doc['ai_summary'], 'cached' => true]);
    exit;
}

// Generate AI Summary based on document metadata + content analysis
$filePath = '../uploads/' . $doc['file_path'];
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$extractedText = '';

// Try to extract text from file
if (in_array($ext, ['txt', 'csv', 'log', 'md'])) {
    if (file_exists($filePath)) {
        $extractedText = file_get_contents($filePath);
        $extractedText = mb_substr($extractedText, 0, 5000); // Limit
    }
} elseif ($ext === 'pdf') {
    // Basic PDF text extraction (reads raw text from PDF)
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        // Extract text between stream markers (basic PDF parsing)
        preg_match_all('/stream\s*\n(.*?)\nendstream/s', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $stream) {
                // Try to decode if it's plain text
                $decoded = @gzuncompress($stream);
                if ($decoded) {
                    // Extract text operators
                    preg_match_all('/\((.*?)\)/s', $decoded, $textMatches);
                    if (!empty($textMatches[1])) {
                        $extractedText .= implode(' ', $textMatches[1]) . ' ';
                    }
                }
            }
        }
        $extractedText = mb_substr(trim($extractedText), 0, 3000);
    }
}

// Build intelligent summary
$title = $doc['title'];
$category = $doc['category'];
$description = $doc['description'] ?? '';
$tags = $doc['tags'] ?? '';
$fileSize = $doc['file_size'];
$createdAt = $doc['created_at'];

// AI Summary engine
$summaryParts = [];

// Document type analysis
$categoryDescriptions = [
    'Facture'   => '📄 Ce document est une facture/invoice.',
    'Contrat'   => '📋 Ce document est un contrat/accord officiel.',
    'CIN'       => '🪪 Ce document est une pièce d\'identité (CIN).',
    'Reçu'      => '🧾 Ce document est un reçu/récépissé.',
    'Rapport'   => '📊 Ce document est un rapport/analyse.',
    'CV'        => '👤 Ce document est un CV/curriculum vitae.',
    'Lettre'    => '✉️ Ce document est une lettre/correspondance.',
    'Projets'   => '🏗️ Ce document est lié à un projet.',
    'Image'     => '🖼️ Ce document est une image.',
    'Personnel' => '🔒 Ce document est un fichier personnel.',
    'General'   => '📁 Document général.',
];

$summaryParts[] = $categoryDescriptions[$category] ?? $categoryDescriptions['General'];
$summaryParts[] = "**Titre**: {$title}";

if (!empty($description)) {
    $summaryParts[] = "**Description**: {$description}";
}

if (!empty($tags)) {
    $summaryParts[] = "**Tags**: {$tags}";
}

// File info
$sizeFormatted = round($fileSize / 1024, 1);
$unit = 'KB';
if ($sizeFormatted > 1024) {
    $sizeFormatted = round($sizeFormatted / 1024, 1);
    $unit = 'MB';
}
$summaryParts[] = "**Taille**: {$sizeFormatted} {$unit}";
$summaryParts[] = "**Créé le**: " . date('d/m/Y à H:i', strtotime($createdAt));

// Important flag
if ($doc['is_important']) {
    $summaryParts[] = "⭐ Ce document est marqué comme **important**.";
}

// Expiry warning
if (!empty($doc['expiry_date'])) {
    $expiry = new DateTime($doc['expiry_date']);
    $now = new DateTime();
    $diff = $now->diff($expiry);
    if ($expiry < $now) {
        $summaryParts[] = "⚠️ **EXPIRÉ** depuis {$diff->days} jours.";
    } elseif ($diff->days <= 7) {
        $summaryParts[] = "🔔 **Expire dans {$diff->days} jours** — action requise.";
    } else {
        $summaryParts[] = "📅 Expire le " . $expiry->format('d/m/Y');
    }
}

// Content analysis
if (!empty($extractedText)) {
    // Get first 200 chars as preview
    $preview = mb_substr(strip_tags($extractedText), 0, 200);
    $summaryParts[] = "\n**Aperçu du contenu**:\n> " . trim($preview) . "...";

    // Word count
    $wordCount = str_word_count($extractedText);
    $summaryParts[] = "📝 Environ **{$wordCount} mots** détectés.";
}

// Smart suggestions based on category
$suggestions = [
    'Facture'  => "💡 **Suggestion**: Vérifiez la date d'échéance et conservez ce document pour la comptabilité.",
    'Contrat'  => "💡 **Suggestion**: Vérifiez les clauses importantes et la date de validité du contrat.",
    'CIN'      => "💡 **Suggestion**: Gardez ce document en lieu sûr. Vérifiez la date d'expiration.",
    'Rapport'  => "💡 **Suggestion**: Partagez ce rapport avec votre équipe pour analyse.",
];

if (isset($suggestions[$category])) {
    $summaryParts[] = $suggestions[$category];
}

$summary = implode("\n", $summaryParts);

// Cache the summary in database
try {
    $updateStmt = $pdo->prepare("UPDATE documents SET ai_summary = ? WHERE id = ?");
    $updateStmt->execute([$summary, $document_id]);
} catch (PDOException $e) {
    // Column might not exist yet, continue anyway
}

logActivity($pdo, $user_id, 'AI Summary', "Generated summary for: {$title}");

echo json_encode(['success' => true, 'summary' => $summary, 'cached' => false]);
?>
