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
    echo json_encode(['success' => false, 'message' => 'Document ID required']);
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

$filePath = '../uploads/' . $doc['file_path'];
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

if (!file_exists($filePath)) {
    echo json_encode(['success' => false, 'message' => 'File not found on server']);
    exit;
}

$extractedText = '';
$method = 'none';

// Image OCR simulation using built-in PHP (no external libs needed)
$imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

if (in_array($ext, $imageExts)) {
    // Basic OCR approach: Extract any embedded text metadata from image
    $method = 'image_metadata';
    
    // Read EXIF data if available
    if (function_exists('exif_read_data') && in_array($ext, ['jpg', 'jpeg'])) {
        $exif = @exif_read_data($filePath, 'ANY_TAG', true);
        if ($exif) {
            if (isset($exif['IFD0']['ImageDescription'])) {
                $extractedText .= $exif['IFD0']['ImageDescription'] . "\n";
            }
            if (isset($exif['COMMENT'])) {
                if (is_array($exif['COMMENT'])) {
                    $extractedText .= implode("\n", $exif['COMMENT']) . "\n";
                } else {
                    $extractedText .= $exif['COMMENT'] . "\n";
                }
            }
            // Add image metadata
            if (isset($exif['COMPUTED'])) {
                $extractedText .= "Resolution: " . ($exif['COMPUTED']['Width'] ?? '?') . "x" . ($exif['COMPUTED']['Height'] ?? '?') . "\n";
            }
            if (isset($exif['IFD0']['Make'])) {
                $extractedText .= "Camera: " . $exif['IFD0']['Make'] . " " . ($exif['IFD0']['Model'] ?? '') . "\n";
            }
            if (isset($exif['EXIF']['DateTimeOriginal'])) {
                $extractedText .= "Taken: " . $exif['EXIF']['DateTimeOriginal'] . "\n";
            }
        }
    }

    // Get image dimensions
    $imageInfo = @getimagesize($filePath);
    if ($imageInfo) {
        $extractedText .= "Format: " . ($imageInfo['mime'] ?? 'unknown') . "\n";
        $extractedText .= "Dimensions: {$imageInfo[0]}x{$imageInfo[1]} pixels\n";
        $method = 'image_analysis';
    }

    // Build an analysis summary even without full OCR
    if (empty(trim($extractedText))) {
        $extractedText = "Image file detected ({$ext}). ";
        if ($imageInfo) {
            $megapixels = round(($imageInfo[0] * $imageInfo[1]) / 1000000, 1);
            $extractedText .= "Resolution: {$imageInfo[0]}x{$imageInfo[1]} ({$megapixels}MP). ";
        }
        $extractedText .= "Full OCR requires Tesseract installation.";
        $method = 'basic_analysis';
    }
} elseif ($ext === 'pdf') {
    $method = 'pdf_extraction';
    $content = file_get_contents($filePath);
    
    // Extract text from PDF streams
    preg_match_all('/stream\s*\n(.*?)\nendstream/s', $content, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $stream) {
            $decoded = @gzuncompress($stream);
            if ($decoded) {
                preg_match_all('/\((.*?)\)/s', $decoded, $textMatches);
                if (!empty($textMatches[1])) {
                    $extractedText .= implode(' ', $textMatches[1]) . ' ';
                }
            }
        }
    }
    
    if (empty(trim($extractedText))) {
        // Try direct text extraction
        preg_match_all('/\((.*?)\)/s', $content, $directMatches);
        if (!empty($directMatches[1])) {
            foreach ($directMatches[1] as $txt) {
                if (mb_strlen($txt) > 3 && preg_match('/[a-zA-Zàâçéèêëîïôùûüÿ]/u', $txt)) {
                    $extractedText .= $txt . ' ';
                }
            }
        }
        if (!empty(trim($extractedText))) {
            $method = 'pdf_direct';
        }
    }
    
    $extractedText = mb_substr(trim($extractedText), 0, 5000);
} elseif (in_array($ext, ['txt', 'csv', 'log', 'md', 'json', 'html', 'xml'])) {
    $method = 'text_file';
    $extractedText = file_get_contents($filePath);
    $extractedText = mb_substr($extractedText, 0, 5000);
}

// Save extracted text
if (!empty(trim($extractedText))) {
    try {
        $updateStmt = $pdo->prepare("UPDATE documents SET ocr_text = ? WHERE id = ?");
        $updateStmt->execute([trim($extractedText), $document_id]);
    } catch (PDOException $e) {
        // Column might not exist, continue
    }
}

logActivity($pdo, $user_id, 'OCR', "Text extracted from: {$doc['title']} ({$method})");

echo json_encode([
    'success' => true,
    'text' => trim($extractedText) ?: 'No text could be extracted from this file.',
    'method' => $method,
    'word_count' => str_word_count($extractedText),
    'document_id' => $document_id
]);
?>
