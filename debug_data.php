<?php
require_once 'includes/config.php';
session_start();
echo "<h3>Debug Info</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM documents WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id'] ?? 0]);
$row = $stmt->fetch();
echo "Document count for this user: " . $row['count'] . "<br>";

$stmt = $pdo->query("SELECT * FROM documents");
$all = $stmt->fetchAll();
echo "Total documents in DB: " . count($all) . "<br>";
foreach($all as $doc) {
    echo "- ID: {$doc['id']}, Title: {$doc['title']}, UserID: {$doc['user_id']}<br>";
}
?>
