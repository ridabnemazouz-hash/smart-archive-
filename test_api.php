<?php
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Bnemazouz Rida';
$_SESSION['lang'] = 'fr';

// Mock get_docs.php logic without session_start()
require_once 'includes/config.php';
// We need to bypass session_start() in config.php if it's there, but we'll just ignore it for CLI
// Actually, config.php HAS session_start(). It might fail in CLI if headers already sent.

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM documents WHERE user_id = ?";
$params = [$user_id];

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$documents = $stmt->fetchAll();

echo "JSON RESULT:\n";
echo json_encode(['success' => true, 'documents' => $documents]);
?>
