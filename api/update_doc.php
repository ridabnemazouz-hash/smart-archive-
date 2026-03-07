<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $is_important = isset($_POST['important']) ? 1 : 0;
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $tags = $_POST['tags'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($id) || empty($title)) {
        echo json_encode(['success' => false, 'message' => 'ID and Title are required.']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE documents SET title = ?, category = ?, description = ?, is_important = ?, expiry_date = ?, tags = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$title, $category, $description, $is_important, $expiry_date, $tags, $id, $user_id])) {
        echo json_encode(['success' => true, 'message' => 'Document updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update document.']);
    }
}
?>
