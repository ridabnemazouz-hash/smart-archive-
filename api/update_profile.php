<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'] ?? null;
$email = $_POST['email'] ?? null;
$phone = $_POST['phone'] ?? null;
$new_password = $_POST['new_password'] ?? null;
$current_password = $_POST['current_password'] ?? null;

try {
    if ($name && $email) {
        // Update basic info
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $user_id]);
        $_SESSION['user_name'] = $name;
        echo json_encode(['success' => true, 'message' => 'Profile updated']);
    } elseif ($new_password && $current_password) {
        // Verify current password first
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password'])) {
            // Update password
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
        }
    } elseif ($new_password && !$current_password) {
        echo json_encode(['success' => false, 'message' => 'Current password is required to set a new password.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
