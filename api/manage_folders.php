<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    // ==================== LIST FOLDERS ====================
    case 'list':
        $parent_id = $_GET['parent_id'] ?? null;
        
        if ($parent_id) {
            $stmt = $pdo->prepare("SELECT * FROM folders WHERE user_id = ? AND parent_id = ? ORDER BY name ASC");
            $stmt->execute([$user_id, $parent_id]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM folders WHERE user_id = ? AND parent_id IS NULL ORDER BY name ASC");
            $stmt->execute([$user_id]);
        }
        $folders = $stmt->fetchAll();
        
        // Get document count per folder
        foreach ($folders as &$f) {
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE folder_id = ? AND user_id = ? AND deleted_at IS NULL");
            $countStmt->execute([$f['id'], $user_id]);
            $f['doc_count'] = $countStmt->fetchColumn();
            
            // Check for subfolders
            $subStmt = $pdo->prepare("SELECT COUNT(*) FROM folders WHERE parent_id = ? AND user_id = ?");
            $subStmt->execute([$f['id'], $user_id]);
            $f['has_subfolders'] = $subStmt->fetchColumn() > 0;
        }
        
        echo json_encode(['success' => true, 'folders' => $folders]);
        break;

    // ==================== CREATE FOLDER ====================
    case 'create':
        $name = trim($_POST['name'] ?? '');
        $parent_id = $_POST['parent_id'] ?? null;
        $color = $_POST['color'] ?? '#6366f1';
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Folder name is required']);
            exit;
        }
        
        // Check duplicate name in same level
        if ($parent_id) {
            $check = $pdo->prepare("SELECT id FROM folders WHERE user_id = ? AND parent_id = ? AND name = ?");
            $check->execute([$user_id, $parent_id, $name]);
        } else {
            $check = $pdo->prepare("SELECT id FROM folders WHERE user_id = ? AND parent_id IS NULL AND name = ?");
            $check->execute([$user_id, $name]);
        }
        
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'A folder with this name already exists']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO folders (user_id, name, parent_id, color) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $parent_id ?: null, $color]);
        
        logActivity($pdo, $user_id, 'Create Folder', "Created folder: $name");
        
        echo json_encode(['success' => true, 'message' => 'Folder created', 'id' => $pdo->lastInsertId()]);
        break;

    // ==================== RENAME FOLDER ====================
    case 'rename':
        $folder_id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        
        if (!$folder_id || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Folder ID and name are required']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE folders SET name = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $folder_id, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Folder renamed']);
        break;

    // ==================== DELETE FOLDER ====================
    case 'delete':
        $folder_id = $_POST['id'] ?? $_GET['id'] ?? null;
        
        if (!$folder_id) {
            echo json_encode(['success' => false, 'message' => 'Folder ID required']);
            exit;
        }
        
        // Move documents back to root
        $pdo->prepare("UPDATE documents SET folder_id = NULL WHERE folder_id = ? AND user_id = ?")->execute([$folder_id, $user_id]);
        
        // Delete subfolders recursively
        $pdo->prepare("DELETE FROM folders WHERE parent_id = ? AND user_id = ?")->execute([$folder_id, $user_id]);
        
        // Delete folder
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ? AND user_id = ?");
        $stmt->execute([$folder_id, $user_id]);
        
        logActivity($pdo, $user_id, 'Delete Folder', "Deleted folder #$folder_id");
        
        echo json_encode(['success' => true, 'message' => 'Folder deleted']);
        break;

    // ==================== MOVE DOCUMENT TO FOLDER ====================
    case 'move':
        $doc_id = $_POST['document_id'] ?? null;
        $folder_id = $_POST['folder_id'] ?? null; // null = move to root
        
        if (!$doc_id) {
            echo json_encode(['success' => false, 'message' => 'Document ID required']);
            exit;
        }
        
        // Verify ownership
        $check = $pdo->prepare("SELECT id FROM documents WHERE id = ? AND user_id = ?");
        $check->execute([$doc_id, $user_id]);
        if (!$check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Document not found']);
            exit;
        }
        
        if ($folder_id) {
            $fCheck = $pdo->prepare("SELECT id FROM folders WHERE id = ? AND user_id = ?");
            $fCheck->execute([$folder_id, $user_id]);
            if (!$fCheck->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Folder not found']);
                exit;
            }
        }
        
        $stmt = $pdo->prepare("UPDATE documents SET folder_id = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$folder_id ?: null, $doc_id, $user_id]);
        
        logActivity($pdo, $user_id, 'Move', "Moved doc #$doc_id to folder #$folder_id");
        
        echo json_encode(['success' => true, 'message' => 'Document moved']);
        break;

    // ==================== GET BREADCRUMB ====================
    case 'breadcrumb':
        $folder_id = $_GET['folder_id'] ?? null;
        $breadcrumb = [];
        
        while ($folder_id) {
            $stmt = $pdo->prepare("SELECT id, name, parent_id FROM folders WHERE id = ? AND user_id = ?");
            $stmt->execute([$folder_id, $user_id]);
            $folder = $stmt->fetch();
            if (!$folder) break;
            
            array_unshift($breadcrumb, ['id' => $folder['id'], 'name' => $folder['name']]);
            $folder_id = $folder['parent_id'];
        }
        
        echo json_encode(['success' => true, 'breadcrumb' => $breadcrumb]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
