<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 'NULL';
file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - UserID: $user_id, Session: " . session_id() . "\n", FILE_APPEND);

$trash = isset($_GET['trash']) ? 1 : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$important = isset($_GET['important']) ? 1 : null;
$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;

$sort = $_GET['sort'] ?? 'newest';

$date = $_GET['date'] ?? null;
$date_start = $_GET['date_start'] ?? null;
$date_end = $_GET['date_end'] ?? null;
$folder_id = $_GET['folder_id'] ?? null;
$is_favorite = isset($_GET['is_favorite']) ? 1 : null;
$min_size = isset($_GET['min_size']) ? (int)$_GET['min_size'] : null;

$query = "SELECT * FROM documents WHERE user_id = ?";
$params = [$user_id];

if ($trash) {
    $query .= " AND deleted_at IS NOT NULL";
} else {
    $query .= " AND deleted_at IS NULL";
}

if ($important !== null) {
    $query .= " AND is_important = ?";
    $params[] = $important;
}

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if ($folder_id !== null) {
    if ($folder_id === '') {
        $query .= " AND folder_id IS NULL";
    } else {
        $query .= " AND folder_id = ?";
        $params[] = (int)$folder_id;
    }
}

if ($is_favorite !== null) {
    $query .= " AND is_favorite = ?";
    $params[] = $is_favorite;
}

if ($date) {
    $query .= " AND DATE(created_at) = ?";
    $params[] = $date;
}

if ($date_start) {
    $query .= " AND DATE(created_at) >= ?";
    $params[] = $date_start;
}

if ($date_end) {
    $query .= " AND DATE(created_at) <= ?";
    $params[] = $date_end;
}

if ($min_size !== null) {
    $query .= " AND file_size >= ?";
    $params[] = $min_size * 1024; // convert KB to Bytes
}

if ($search) {
    if (($_GET['ai_mode'] ?? 0) == 1) {
        // AI Smart Search Mode: Search more fields and simulate fuzzy matching
        $query .= " AND (title LIKE ? OR category LIKE ? OR description LIKE ? OR tags LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    } else {
        $query .= " AND (title LIKE ? OR category LIKE ? OR description LIKE ? OR tags LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
}

// Sorting logic
switch ($sort) {
    case 'oldest':
        $query .= " ORDER BY created_at ASC";
        break;
    case 'important':
        $query .= " ORDER BY is_important DESC, created_at DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY created_at DESC";
        break;
}

// Add pagination
$query .= " LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$documents = $stmt->fetchAll();

// Get total count for pagination
$countQuery = str_replace("SELECT *", "SELECT COUNT(*)", $query);
$countQuery = preg_replace('/LIMIT \d+ OFFSET \d+$/', '', $countQuery);
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

// Global stats (Calculated once)
$statsStmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(IF(is_important=1, 1, 0)) as important, IFNULL(SUM(file_size), 0) as size FROM documents WHERE user_id = ? AND deleted_at IS NULL");
$statsStmt->execute([$user_id]);
$stats = $statsStmt->fetch();

echo json_encode([
    'success' => true, 
    'documents' => $documents, 
    'total' => $total,
    'pages' => ceil($total / $limit),
    'current_page' => $page,
    'stats' => [
        'total_docs' => $stats['total'],
        'important_docs' => $stats['important'],
        'total_size' => $stats['size'] ?? 0
    ]
]);
?>
