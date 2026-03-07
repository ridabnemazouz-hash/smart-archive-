<?php
require_once 'includes/config.php';

echo "<pre>=== Phase 13: Folders & Favorites Migration ===\n\n";

$queries = [
    // Folders table
    "CREATE TABLE IF NOT EXISTS folders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        parent_id INT NULL DEFAULT NULL,
        color VARCHAR(7) DEFAULT '#6366f1',
        icon VARCHAR(50) DEFAULT 'bi-folder',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX (user_id),
        INDEX (parent_id)
    ) ENGINE=InnoDB",

    // Add folder_id to documents
    "ALTER TABLE documents ADD COLUMN folder_id INT NULL DEFAULT NULL AFTER user_id",
    
    // Add is_favorite to documents  
    "ALTER TABLE documents ADD COLUMN is_favorite TINYINT(1) DEFAULT 0 AFTER is_important",
    
    // Add foreign key for folder_id
    "ALTER TABLE documents ADD INDEX idx_folder (folder_id)"
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

echo "\n=== Done ===</pre>";
?>
