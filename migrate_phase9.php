<?php
require_once 'includes/config.php';

echo "=== Phase 9: AI Intelligence Migration ===\n";

$queries = [
    // Add OCR text column
    "ALTER TABLE documents ADD COLUMN IF NOT EXISTS ocr_text TEXT NULL AFTER description",
    // Add AI summary column
    "ALTER TABLE documents ADD COLUMN IF NOT EXISTS ai_summary TEXT NULL AFTER ocr_text",
    // Version control table
    "CREATE TABLE IF NOT EXISTS document_versions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        document_id INT NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        version_number INT DEFAULT 1,
        file_size BIGINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
        INDEX (document_id)
    ) ENGINE=InnoDB",
    // Shared links table
    "CREATE TABLE IF NOT EXISTS shared_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        document_id INT NOT NULL,
        user_id INT NOT NULL,
        share_token VARCHAR(64) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NULL,
        expires_at TIMESTAMP NULL,
        view_count INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX (share_token)
    ) ENGINE=InnoDB"
];

foreach ($queries as $i => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Query " . ($i + 1) . " executed successfully.\n";
    } catch (PDOException $e) {
        // Check if it's a "column already exists" or "table already exists" error
        if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "⚠️ Query " . ($i + 1) . " skipped (already exists).\n";
        } else {
            echo "❌ Query " . ($i + 1) . " failed: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== Migration Complete ===\n";
?>
