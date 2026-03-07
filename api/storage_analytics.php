<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// Get storage analytics
try {
    // Per-category storage
    $stmt = $pdo->prepare("
        SELECT category, 
               COUNT(*) as doc_count, 
               IFNULL(SUM(file_size), 0) as total_size
        FROM documents 
        WHERE user_id = ? AND deleted_at IS NULL 
        GROUP BY category 
        ORDER BY total_size DESC
    ");
    $stmt->execute([$user_id]);
    $categories = $stmt->fetchAll();

    // Total storage used
    $totalStmt = $pdo->prepare("SELECT IFNULL(SUM(file_size), 0) as total FROM documents WHERE user_id = ? AND deleted_at IS NULL");
    $totalStmt->execute([$user_id]);
    $totalUsed = $totalStmt->fetchColumn();

    // Storage limit (from user profile)
    $limitStmt = $pdo->prepare("SELECT storage_limit FROM users WHERE id = ?");
    $limitStmt->execute([$user_id]);
    $storageLimit = $limitStmt->fetchColumn() ?: 10737418240; // 10GB default

    // Large files (> 10MB)
    $largeStmt = $pdo->prepare("
        SELECT id, title, file_size, category 
        FROM documents 
        WHERE user_id = ? AND deleted_at IS NULL AND file_size > ? 
        ORDER BY file_size DESC 
        LIMIT 5
    ");
    $largeStmt->execute([$user_id, 10 * 1024 * 1024]); // 10MB
    $largeFiles = $largeStmt->fetchAll();

    // Most used category
    $mostUsed = !empty($categories) ? $categories[0]['category'] : 'None';

    // Usage percentage
    $usagePercent = round(($totalUsed / $storageLimit) * 100, 1);

    echo json_encode([
        'success' => true,
        'analytics' => [
            'total_used' => $totalUsed,
            'storage_limit' => $storageLimit,
            'usage_percent' => $usagePercent,
            'most_used_category' => $mostUsed,
            'categories' => $categories,
            'large_files' => $largeFiles,
            'warning' => $usagePercent > 80 ? 'Storage usage is above 80%!' : null
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
