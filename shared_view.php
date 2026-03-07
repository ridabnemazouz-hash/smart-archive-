<?php
require_once 'includes/config.php';

// Handle password verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_password'])) {
    $token = $_POST['token'] ?? '';
    $password = $_POST['share_password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM shared_links WHERE share_token = ? AND is_active = 1");
    $stmt->execute([$token]);
    $link = $stmt->fetch();
    
    if ($link && password_verify($password, $link['password_hash'])) {
        $_SESSION['share_auth_' . $token] = true;
    }
}

$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (empty($token)) {
    die('<div style="text-align:center;padding:50px;font-family:Inter,sans-serif;"><h1>🔗 Invalid Link</h1><p>This share link is invalid.</p></div>');
}

// Fetch share link
$stmt = $pdo->prepare("
    SELECT sl.*, d.title, d.file_path, d.category, d.description, d.file_size, d.created_at as doc_created,
           u.name as owner_name
    FROM shared_links sl 
    JOIN documents d ON sl.document_id = d.id 
    JOIN users u ON sl.user_id = u.id
    WHERE sl.share_token = ? AND sl.is_active = 1
");
$stmt->execute([$token]);
$share = $stmt->fetch();

if (!$share) {
    die('<div style="text-align:center;padding:50px;font-family:Inter,sans-serif;"><h1>🔗 Link Not Found</h1><p>This share link does not exist or has been deactivated.</p></div>');
}

// Check expiry
if ($share['expires_at'] && strtotime($share['expires_at']) < time()) {
    die('<div style="text-align:center;padding:50px;font-family:Inter,sans-serif;"><h1>⏰ Link Expired</h1><p>This share link has expired on ' . date('d/m/Y H:i', strtotime($share['expires_at'])) . '.</p></div>');
}

// Check password
$needsPassword = !empty($share['password_hash']);
$isAuthenticated = !$needsPassword || (isset($_SESSION['share_auth_' . $token]) && $_SESSION['share_auth_' . $token]);

if ($needsPassword && !$isAuthenticated) {
    // Show password form
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>🔒 Protected Document - SmartArchive</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, #0a0e27 0%, #1a1040 50%, #0d1117 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
            .glass-card { background: rgba(255,255,255,0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 40px; max-width: 420px; width: 100%; color: white; }
            .btn-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); border: none; border-radius: 12px; padding: 12px 30px; }
            input { background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.15) !important; color: white !important; border-radius: 12px !important; }
        </style>
    </head>
    <body>
        <div class="glass-card text-center">
            <div style="font-size: 3rem; margin-bottom: 15px;">🔒</div>
            <h4 class="mb-3">Protected Document</h4>
            <p class="text-white-50 mb-4">This document is password-protected. Enter the password to view it.</p>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                    <input type="password" name="share_password" class="form-control" placeholder="Enter password..." required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100">🔓 Unlock Document</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Increment view count
$pdo->prepare("UPDATE shared_links SET view_count = view_count + 1 WHERE id = ?")->execute([$share['id']]);

// Display document
$filePath = 'uploads/' . $share['file_path'];
$ext = strtolower(pathinfo($share['file_path'], PATHINFO_EXTENSION));
$isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
$isPdf = $ext === 'pdf';
$fileSize = round($share['file_size'] / 1024, 1);
$unit = 'KB';
if ($fileSize > 1024) { $fileSize = round($fileSize / 1024, 1); $unit = 'MB'; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($share['title']) ?> - SmartArchive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0a0e27 0%, #1a1040 50%, #0d1117 100%); min-height: 100vh; font-family: 'Inter', sans-serif; color: white; }
        .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; }
        .badge-cat { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; border-radius: 8px; padding: 4px 12px; font-size: 0.8rem; }
        .preview-area { min-height: 400px; display: flex; align-items: center; justify-content: center; }
        .preview-area img { max-width: 100%; max-height: 500px; border-radius: 12px; }
        .btn-download { background: linear-gradient(135deg, #6366f1, #8b5cf6); border: none; border-radius: 12px; padding: 12px 30px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h6 class="text-white-50"><i class="bi bi-archive"></i> SmartArchive — Shared Document</h6>
                </div>

                <!-- Document Card -->
                <div class="glass p-4 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 rounded-3" style="background: rgba(99, 102, 241, 0.15);">
                            <i class="bi <?= $isImage ? 'bi-file-image' : ($isPdf ? 'bi-file-pdf' : 'bi-file-earmark') ?> fs-3 text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?= htmlspecialchars($share['title']) ?></h4>
                            <span class="badge-cat"><?= htmlspecialchars($share['category']) ?></span>
                            <small class="text-white-50 ms-2"><?= $fileSize ?> <?= $unit ?></small>
                        </div>
                    </div>
                    
                    <?php if (!empty($share['description'])): ?>
                    <p class="text-white-50"><?= htmlspecialchars($share['description']) ?></p>
                    <?php endif; ?>

                    <div class="d-flex gap-3 text-white-50" style="font-size: 0.85rem;">
                        <span><i class="bi bi-person"></i> <?= htmlspecialchars($share['owner_name']) ?></span>
                        <span><i class="bi bi-calendar"></i> <?= date('d/m/Y', strtotime($share['doc_created'])) ?></span>
                        <span><i class="bi bi-eye"></i> <?= $share['view_count'] + 1 ?> views</span>
                    </div>
                </div>

                <!-- Preview -->
                <div class="glass p-4 mb-4">
                    <div class="preview-area">
                        <?php if ($isImage): ?>
                            <img src="<?= $filePath ?>" alt="<?= htmlspecialchars($share['title']) ?>">
                        <?php elseif ($isPdf): ?>
                            <iframe src="<?= $filePath ?>" width="100%" height="500" style="border: none; border-radius: 12px;"></iframe>
                        <?php else: ?>
                            <div class="text-center text-white-50">
                                <i class="bi bi-file-earmark d-block" style="font-size: 4rem;"></i>
                                <p class="mt-3">Preview not available for this file type.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Download -->
                <div class="text-center">
                    <a href="<?= $filePath ?>" download class="btn btn-download text-white">
                        <i class="bi bi-download me-2"></i> Download File
                    </a>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4">
                    <small class="text-white-50">
                        <?php if ($share['expires_at']): ?>
                            🕐 This link expires on <?= date('d/m/Y H:i', strtotime($share['expires_at'])) ?>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
?>
