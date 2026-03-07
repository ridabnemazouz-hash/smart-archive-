<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();
header('Content-Type: application/json');

$message = $_POST['message'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($message)) {
    echo json_encode(['reply' => "Sewelni 3la ay 7aja!"]);
    exit;
}

// AI Brain - Heuristics & Analysis (Darija Support)
// 1. Get Global Stats for the bot to be "aware"
$statsStmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(IF(is_important=1, 1, 0)) as important, IFNULL(SUM(file_size), 0) as size FROM documents WHERE user_id = ? AND deleted_at IS NULL");
$statsStmt->execute([$user_id]);
$stats = $statsStmt->fetch();

// 1b. Get Credit Stats
$creditStmt = $pdo->prepare("SELECT COUNT(*) as client_count, IFNULL(SUM(total_debt), 0) as total_debt FROM clients WHERE user_id = ?");
$creditStmt->execute([$user_id]);
$creditStats = $creditStmt->fetch();

$total = $stats['total'];
$important_count = $stats['important'];
$size_mb = round($stats['size'] / (1024 * 1024), 2);
$client_count = $creditStats['client_count'];
$total_debt = $creditStats['total_debt'];

// 2. AI Brain - Heuristics & Analysis (Darija Support)
$message_lower = mb_strtolower($message);
$reply = "";

if (strpos($message_lower, 'facture') !== false || strpos($message_lower, 'invoice') !== false || strpos($message_lower, 'khalass') !== false) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE user_id = ? AND category = 'Facture' AND deleted_at IS NULL");
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();
    $reply = "Andek **$count** f les factures. 📁 Ila knti bghiti t'paya chi we7da fihom, goulli n'fekkrek!";
} 
elseif (strpos($message_lower, 'credit') !== false || strpos($message_lower, 'salaf') !== false || strpos($message_lower, 'flouss') !== false) {
    $reply = "Andek **$client_count** dyal les clients. 👥 Total li katsal (Salaf) houwa: **" . number_format($total_debt, 2) . " MAD**. \n\nBghiti n'showi lik l'ledger dyal chi client?";
}
elseif (strpos($message_lower, 'image') !== false || strpos($message_lower, 'tsawer') !== false || strpos($message_lower, 'photo') !== false) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE user_id = ? AND category = 'Image' AND deleted_at IS NULL");
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();
    $reply = "Andek **$count** dyal les images m7foudine. 🖼️ Bghiti n'warik chi wa7da t'priviewiha?";
}
elseif (strpos($message_lower, 'important') !== false || strpos($message_lower, 'darouri') !== false || strpos($message_lower, 'mohim') !== false) {
    $reply = "Andek **$important_count** dyal les documents marked as **Important**. ⭐ Ra7om m7toutin l'fo9 f l'important section.";
}
elseif (strpos($message_lower, 'salam') !== false || strpos($message_lower, 'hello') !== false || strpos($message_lower, 'hi') !== false) {
    $reply = "Salam! 👋 Ana SmartBot. Ki ghadi l'khma l'youma? Ara chi document n'analizih lik!";
}
elseif (strpos($message_lower, 'trash') !== false || strpos($message_lower, 'zbel') !== false || strpos($message_lower, 'msre7') !== false) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE user_id = ? AND deleted_at IS NOT NULL");
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();
    $reply = "Andek **$count** f Trash Bin. 🗑️ Ila knti bghiti t'khwiha permanent, goulli.";
}
else {
    // AI Fallback - Proactive Summary
    $reply = "Sma7 lia ma fhemthach hadik 🤖 walakin n'gollik chno 3ndek daba:\n\n";
    $reply .= "• **$total** Documents m7foudine.\n";
    $reply .= "• **$important_count** Files darouriyin.\n";
    $reply .= "• **$client_count** Clients (Salaf: " . number_format($total_debt, 2) . " MAD).\n\n";
    $reply .= "Sewelni 3la 'Factures', 'Images' aw 'Credit' bach n'fekkrek!";
}

// Log AI interaction
logActivity($pdo, $user_id, 'AI Chat', "User asked AI: " . substr($message, 0, 50));

echo json_encode(['reply' => $reply]);
?>
