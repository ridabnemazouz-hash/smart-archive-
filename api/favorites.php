<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'toggle';

switch ($action) {
    // ==================== TOGGLE FAVORITE ====================
    case 'toggle':
        $doc_id = $_POST['document_id'] ?? $_GET['id'] ?? null;
        
        if (!$doc_id) {
            echo json_encode(['success' => false, 'message' => 'Document ID required']);
            exit;
        }
        
        // Get current state
        $stmt = $pdo->prepare("SELECT id, title, is_favorite FROM documents WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
        $stmt->execute([$doc_id, $user_id]);
        $doc = $stmt->fetch();
        
        if (!$doc) {
            echo json_encode(['success' => false, 'message' => 'Document not found']);
            exit;
        }
        
        $newState = $doc['is_favorite'] ? 0 : 1;
        $pdo->prepare("UPDATE documents SET is_favorite = ? WHERE id = ?")->execute([$newState, $doc_id]);
        
        logActivity($pdo, $user_id, $newState ? 'Favorite' : 'Unfavorite', ($newState ? 'Added' : 'Removed') . " favorite: {$doc['title']}");
        
        echo json_encode([
            'success' => true, 
            'is_favorite' => $newState,
            'message' => $newState ? '⭐ Added to favorites' : 'Removed from favorites'
        ]);
        break;

    // ==================== LIST FAVORITES ====================
    case 'list':
        $stmt = $pdo->prepare("
            SELECT id, title, file_path, category, file_size, created_at, tags, is_important
            FROM documents 
            WHERE user_id = ? AND is_favorite = 1 AND deleted_at IS NULL 
            ORDER BY updated_at DESC
        ");
        $stmt->execute([$user_id]);
        $favorites = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'favorites' => $favorites, 'count' => count($favorites)]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
