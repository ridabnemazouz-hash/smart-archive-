<?php
require_once 'includes/config.php';

/**
 * SmartArchive SaaS Migration Script
 * This script updates the database schema to support new features.
 */

$migrations = [
    // 1. Update Documents table
    "ALTER TABLE documents ADD COLUMN expiry_date DATE NULL AFTER description",
    "ALTER TABLE documents ADD COLUMN tags TEXT NULL AFTER expiry_date", // JSON or comma-separated
    
    // 2. Update Users table for 2FA and Security
    "ALTER TABLE users ADD COLUMN two_factor_secret VARCHAR(255) NULL AFTER storage_limit",
    "ALTER TABLE users ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0 AFTER two_factor_secret",
    
    // 3. Create Login History Table
    "CREATE TABLE IF NOT EXISTS login_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    
    // 4. Create Shares Table (for SaaS sharing feature)
    "CREATE TABLE IF NOT EXISTS shares (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT NOT NULL,
        doc_id INT NULL,
        folder_category VARCHAR(100) NULL,
        share_token VARCHAR(255) UNIQUE NOT NULL,
        expires_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (doc_id) REFERENCES documents(id) ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

echo "<h2>SmartArchive SaaS Migration</h2>";

foreach ($migrations as $sql) {
    try {
        $pdo->exec($sql);
        echo "<p style='color:green;'>SUCCESS: " . htmlspecialchars($sql) . "</p>";
    } catch (PDOException $e) {
        // If column already exists, it's fine
        if ($e->getCode() == '42S21') {
            echo "<p style='color:orange;'>SKIPPED (Already exists): " . htmlspecialchars($sql) . "</p>";
        } else {
            echo "<p style='color:red;'>ERROR: " . htmlspecialchars($sql) . " <br> Reason: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<hr><p>Migration completed. <a href='dashboard.php'>Back to Dashboard</a></p>";
?>
