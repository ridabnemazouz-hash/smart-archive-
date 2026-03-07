<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Document ID is required.']);
        exit();
    }

    // Get document status
    $stmt = $pdo->prepare("SELECT id, file_path, deleted_at FROM documents WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $doc = $stmt->fetch();

    if ($doc) {
        if ($doc['deleted_at'] === null && !isset($_POST['force'])) {
            // Soft Delete
            $stmt = $pdo->prepare("UPDATE documents SET deleted_at = NOW() WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$id, $user_id])) {
                logActivity($pdo, $user_id, 'Move to Trash', "Moved document #$id to trash");
                echo json_encode(['success' => true, 'message' => 'Document moved to trash.']);
            }
        } else {
            // Permanent Delete
            $filePath = "../uploads/" . $doc['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$id, $user_id])) {
                logActivity($pdo, $user_id, 'Delete Permanently', "Permanently deleted document #$id");
                echo json_encode(['success' => true, 'message' => 'Document deleted permanently!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete record.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Document not found or unauthorized.']);
    }
}
?>
