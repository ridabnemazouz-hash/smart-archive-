<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Phase 15 Security Upgrade: Only admins can bulk delete
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Forbidden: Admin access required for bulk actions.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'] ?? [];
    $user_id = $_SESSION['user_id'];

    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No IDs provided']);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Check if we should soft delete or permanent delete
        $force = isset($_POST['force']) ? true : false;
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $params = array_merge($ids, [$user_id]);

        if (!$force) {
            // Soft Delete logic
            $stmt = $pdo->prepare("UPDATE documents SET deleted_at = NOW() WHERE id IN ($placeholders) AND user_id = ?");
            $stmt->execute($params);
            logActivity($pdo, $user_id, 'Bulk Move to Trash', "Moved " . count($ids) . " documents to trash");
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => count($ids) . ' documents moved to trash']);
        } else {
            // Permanent Delete logic
            $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id IN ($placeholders) AND user_id = ?");
            $stmt->execute($params);
            $files = $stmt->fetchAll();

            foreach ($files as $f) {
                $path = "../uploads/" . $f['file_path'];
                if (file_exists($path)) unlink($path);
            }

            $stmt = $pdo->prepare("DELETE FROM documents WHERE id IN ($placeholders) AND user_id = ?");
            $stmt->execute($params);

            logActivity($pdo, $user_id, 'Bulk Delete Permanently', "Permanently deleted " . count($ids) . " documents");
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => count($ids) . ' documents deleted permanently']);
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
