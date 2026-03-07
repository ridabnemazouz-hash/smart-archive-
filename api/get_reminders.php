<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// Initial version: Fetch real reminders + Generate smart AI tasks
$stmt = $pdo->prepare("SELECT r.*, d.title as doc_title FROM reminders r LEFT JOIN documents d ON r.doc_id = d.id WHERE r.user_id = ? AND r.is_completed = 0 ORDER BY r.remind_at ASC");
$stmt->execute([$user_id]);
$reminders = $stmt->fetchAll();

// If no manual reminders, AI generates proactive tasks based on latest uploads
if (empty($reminders)) {
    $stmt = $pdo->prepare("SELECT id, title FROM documents WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $last_doc = $stmt->fetch();
    
    if ($last_doc) {
        $reminders[] = [
            'id' => 'ai-task-1',
            'reminder_text' => "Check if '" . $last_doc['title'] . "' needs urgent review. 🤖",
            'remind_at' => date('Y-m-d H:i:s'),
            'doc_id' => $last_doc['id'],
            'is_completed' => 0,
            'doc_title' => $last_doc['title']
        ];
    }
}

echo json_encode(['success' => true, 'reminders' => $reminders]);
?>
