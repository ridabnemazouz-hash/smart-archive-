<?php
require_once 'includes/config.php';

try {
    echo "Starting DB Migration v3.0...\n";

    // 1. Users table updates
    echo "- Updating 'users' table...\n";
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS subscription_plan VARCHAR(50) DEFAULT 'free'");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS storage_limit BIGINT DEFAULT 5368709120"); // 5GB
    
    // 2. Documents table updates
    echo "- Updating 'documents' table...\n";
    $pdo->exec("ALTER TABLE documents ADD COLUMN IF NOT EXISTS file_size BIGINT DEFAULT 0");

    // 3. Activity Log
    echo "- Creating 'activity_log' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(255),
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    echo "Migration Complete! Everything is ready for v3.0 features.\n";
} catch (Exception $e) {
    echo "MIGRATION ERROR: " . $e->getMessage() . "\n";
}
?>
