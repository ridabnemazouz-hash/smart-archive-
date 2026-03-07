<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $category = $_POST['category'] ?? 'General';
    $tags = $_POST['tags'] ?? '';
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $folder_id = !empty($_POST['folder_id']) ? (int)$_POST['folder_id'] : null;
    
    if (!isset($_FILES['documents'])) {
        echo json_encode(['success' => false, 'message' => 'No files uploaded.']);
        exit();
    }

    $files = $_FILES['documents'];
    $success_count = 0;
    $errors = [];

    foreach ($files['name'] as $key => $name) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $title = pathinfo($name, PATHINFO_FILENAME);
            
            // Phase 15: Auto Deadline Detection (AI-lite)
            // Scan title for date patterns if expiry_date is null
            $auto_expiry = $expiry_date;
            if (!$auto_expiry) {
                // Regex for YYYY-MM-DD or DD-MM-YYYY or DD/MM/YYYY
                if (preg_match('/(\d{4}[-\/]\d{2}[-\/]\d{2})|(\d{2}[-\/]\d{2}[-\/]\d{4})/', $title, $matches)) {
                    $detected = $matches[0];
                    // Normalize to YYYY-MM-DD for MySQL
                    $normalized = date('Y-m-d', strtotime(str_replace('/', '-', $detected)));
                    if ($normalized && $normalized !== '1970-01-01') {
                        $auto_expiry = $normalized;
                    }
                }
            }

            $fileName = time() . '_' . $key . '_' . basename($name);
            $targetDir = "../uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            
            $targetFilePath = $targetDir . $fileName;
            
            if (move_uploaded_file($files["tmp_name"][$key], $targetFilePath)) {
                $fileSize = $files['size'][$key];
                $stmt = $pdo->prepare("INSERT INTO documents (title, file_path, category, description, is_important, user_id, folder_id, file_size, expiry_date, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$title, $fileName, $category, '', 0, $user_id, $folder_id, $fileSize, $auto_expiry, $tags])) {
                    $success_count++;
                } else {
                    $errors[] = "Database error for file $name";
                }
            } else {
                $errors[] = "Failed to move file $name";
            }
        } else {
            $errors[] = "Upload error for file $name: " . $files['error'][$key];
        }
    }

    if ($success_count > 0) {
        logActivity($pdo, $user_id, 'Upload', "Bulk Uploaded $success_count files");
        echo json_encode(['success' => true, 'message' => "Successfully uploaded $success_count files.", 'errors' => $errors]);
    } else {
        echo json_encode(['success' => false, 'message' => 'All uploads failed.', 'errors' => $errors]);
    }
}
?>
