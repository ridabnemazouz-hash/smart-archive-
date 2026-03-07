<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

if ($method === 'GET') {
    // List credits for a specific client or all
    $client_id = $_GET['client_id'] ?? null;
    if ($client_id) {
        $stmt = $pdo->prepare("SELECT * FROM credits WHERE client_id = ? AND user_id = ? ORDER BY date DESC, created_at DESC");
        $stmt->execute([$client_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT c.*, cl.name as client_name FROM credits c JOIN clients cl ON c.client_id = cl.id WHERE c.user_id = ? ORDER BY c.date DESC, c.created_at DESC");
        $stmt->execute([$user_id]);
    }
    echo json_encode(['success' => true, 'credits' => $stmt->fetchAll()]);
} 
elseif ($method === 'POST') {
    // Add credit or payment
    $client_id = $_POST['client_id'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $type = $_POST['type'] ?? 'gave'; // 'gave' = Credit, 'received' = Payment
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');

    if (empty($client_id) || empty($amount)) {
        echo json_encode(['success' => false, 'message' => 'Client and amount are required.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Insert credit record
        $stmt = $pdo->prepare("INSERT INTO credits (client_id, user_id, amount, type, description, date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$client_id, $user_id, $amount, $type, $description, $date]);

        // 2. Update client total_debt
        // If 'gave', debt increases. If 'received', debt decreases.
        $adjustment = ($type === 'gave') ? $amount : -$amount;
        $stmt = $pdo->prepare("UPDATE clients SET total_debt = total_debt + ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$adjustment, $client_id, $user_id]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
