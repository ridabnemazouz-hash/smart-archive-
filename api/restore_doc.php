<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("UPDATE documents SET deleted_at = NULL WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$id, $user_id])) {
        logActivity($pdo, $user_id, 'Restore', "Restored document #$id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Restore failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
