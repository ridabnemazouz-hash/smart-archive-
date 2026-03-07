<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'create';

switch ($action) {
    case 'create':
        // Create a share link
        $document_id = $_POST['document_id'] ?? null;
        $password = $_POST['password'] ?? null;
        $expires_hours = $_POST['expires_hours'] ?? 72; // Default 3 days

        if (!$document_id) {
            echo json_encode(['success' => false, 'message' => 'Document ID required']);
            exit;
        }

        // Verify document ownership
        $stmt = $pdo->prepare("SELECT id, title FROM documents WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
        $stmt->execute([$document_id, $user_id]);
        $doc = $stmt->fetch();

        if (!$doc) {
            echo json_encode(['success' => false, 'message' => 'Document not found']);
            exit;
        }

        // Generate unique token
        $token = bin2hex(random_bytes(32));
        $password_hash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
        $expires_at = date('Y-m-d H:i:s', strtotime("+{$expires_hours} hours"));

        $stmt = $pdo->prepare("INSERT INTO shared_links (document_id, user_id, share_token, password_hash, expires_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$document_id, $user_id, $token, $password_hash, $expires_at]);

        // Build share URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = dirname(dirname($_SERVER['SCRIPT_NAME']));
        $shareUrl = "{$protocol}://{$host}{$baseUrl}/shared_view.php?token={$token}";

        logActivity($pdo, $user_id, 'Share', "Shared: {$doc['title']}");

        echo json_encode([
            'success' => true,
            'share_url' => $shareUrl,
            'token' => $token,
            'expires_at' => $expires_at,
            'has_password' => !empty($password)
        ]);
        break;

    case 'list':
        // List all shares for user
        $stmt = $pdo->prepare("
            SELECT sl.*, d.title as doc_title 
            FROM shared_links sl 
            JOIN documents d ON sl.document_id = d.id 
            WHERE sl.user_id = ? 
            ORDER BY sl.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $shares = $stmt->fetchAll();

        echo json_encode(['success' => true, 'shares' => $shares]);
        break;

    case 'delete':
        $link_id = $_POST['link_id'] ?? $_GET['id'] ?? null;
        if (!$link_id) {
            echo json_encode(['success' => false, 'message' => 'Link ID required']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM shared_links WHERE id = ? AND user_id = ?");
        $stmt->execute([$link_id, $user_id]);

        echo json_encode(['success' => true, 'message' => 'Share link deleted']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
