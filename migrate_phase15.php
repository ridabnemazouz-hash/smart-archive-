<?php
require_once 'includes/config.php';

echo "<pre>=== Phase 15: AI Intelligence & Security Migration ===\n\n";

$queries = [
    // Add IP and User Agent to activity logs
    "ALTER TABLE activity_logs ADD COLUMN ip_address VARCHAR(45) NULL AFTER user_id",
    "ALTER TABLE activity_logs ADD COLUMN user_agent TEXT NULL AFTER ip_address",
    
    // Add Role to users
    "ALTER TABLE users ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user' AFTER password",
    
    // Ensure the first user is an admin
    "UPDATE users SET role = 'admin' WHERE id = (SELECT id FROM (SELECT MIN(id) as id FROM users) as t)"
];

foreach ($queries as $i => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Query " . ($i + 1) . " OK\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "⚠️ Query " . ($i + 1) . " skipped (exists)\n";
        } else {
            echo "❌ Query " . ($i + 1) . ": " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== Phase 15 Migration Done ===</pre>";
?>
