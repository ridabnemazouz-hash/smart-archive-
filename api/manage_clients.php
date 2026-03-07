<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

if ($method === 'GET') {
    // List clients
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true, 'clients' => $stmt->fetchAll()]);
} 
elseif ($method === 'POST') {
    // Add or Edit client
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Client name is required.']);
        exit;
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE clients SET name = ?, phone = ?, email = ? WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$name, $phone, $email, $id, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO clients (user_id, name, phone, email) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$user_id, $name, $phone, $email]);
    }

    echo json_encode(['success' => $result]);
} 
elseif ($method === 'DELETE') {
    // Note: In PHP, DELETE params are usually in the URL
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$id, $user_id]);
        echo json_encode(['success' => $result]);
    }
}
?>
