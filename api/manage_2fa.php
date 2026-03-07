<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';

    if ($action === 'verify') {
        $code = $_POST['code'] ?? '';
        
        // Mocking 2FA verification for SaaS Demo
        // In a real app, use Google2FA library to verify $code against $user['two_factor_secret']
        if ($code === '123456' || strlen($code) === 6) {
            $stmt = $pdo->prepare("UPDATE users SET two_factor_enabled = 1 WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                echo json_encode(['success' => true, 'message' => '2FA enabled successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid verification code. Use 123456 for demo.']);
        }
        exit();
    }
}
?>
