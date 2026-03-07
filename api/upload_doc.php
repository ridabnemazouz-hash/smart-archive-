<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $user_id = $_SESSION['user_id'] ?? 'NULL';
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $tags = $_POST['tags'] ?? '';

    // AI Brain - Enhanced Auto-Categorization & Importance Engine
    $is_important = isset($_POST['important']) ? 1 : 0;
    $category = $_POST['category'] ?? 'General';

    // Smart AI Categorization: Analyze title + description + filename
    $fileName_raw = isset($_FILES['document']) ? mb_strtolower($_FILES['document']['name']) : '';
    $fileExt = strtolower(pathinfo($fileName_raw, PATHINFO_EXTENSION));
    $analysis_text = mb_strtolower($title . ' ' . $description . ' ' . $fileName_raw);

    if ($category === 'General' || empty($category)) {
        // Priority-ordered detection rules
        $categoryRules = [
            'Facture'  => ['facture', 'invoice', 'فاتورة', 'bill', 'receipt', 'reçu', 'paiement', 'payment'],
            'Contrat'  => ['contrat', 'contract', 'عقد', 'agreement', 'convention', 'accord'],
            'CIN'      => ['cin', 'carte nationale', 'identité', 'identity', 'بطاقة', 'national id', 'carte d\'identité'],
            'Reçu'     => ['reçu', 'recu', 'récépissé', 'quittance', 'وصل'],
            'Rapport'  => ['rapport', 'report', 'تقرير', 'analyse', 'analysis', 'bilan', 'compte rendu'],
            'CV'       => ['cv', 'curriculum', 'resume', 'سيرة ذاتية', 'parcours', 'profil professionnel'],
            'Lettre'   => ['lettre', 'letter', 'رسالة', 'courrier', 'correspondance', 'demande'],
            'Projets'  => ['projet', 'project', 'مشروع', 'plan', 'proposal', 'cahier des charges'],
            'Image'    => ['image', 'photo', 'صورة', 'screenshot', 'capture'],
            'Personnel'=> ['personnel', 'personal', 'شخصي', 'privé', 'private'],
        ];

        // Check by file extension first (images)
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        if (in_array($fileExt, $imageExts)) {
            $category = 'Image';
        }

        // Then check by text content
        if ($category === 'General') {
            foreach ($categoryRules as $cat => $keywords) {
                foreach ($keywords as $keyword) {
                    if (mb_strpos($analysis_text, $keyword) !== false) {
                        $category = $cat;
                        break 2;
                    }
                }
            }
        }

        // Auto-importance for critical document types
        $importantCategories = ['Facture', 'Contrat', 'CIN'];
        if (in_array($category, $importantCategories)) {
            $is_important = 1;
        }
    }

    if (empty($title) || !isset($_FILES['document'])) {
        echo json_encode(['success' => false, 'message' => 'Title and document are required.']);
        exit();
    }

    $file = $_FILES['document'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
        exit();
    }

    $fileName = time() . '_' . basename($file['name']);
    $targetDir = "../uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
        $fileSize = $file['size'];
        $stmt = $pdo->prepare("INSERT INTO documents (title, file_path, category, description, is_important, user_id, file_size, expiry_date, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $fileName, $category, $description, $is_important, $user_id, $fileSize, $expiry_date, $tags])) {
            logActivity($pdo, $user_id, 'Upload', "Uploaded: $title ($category)");
            echo json_encode(['success' => true, 'message' => 'The file has been uploaded.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving file.']);
    }
}
?>
