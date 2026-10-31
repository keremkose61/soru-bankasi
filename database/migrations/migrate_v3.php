<?php
// This migration is intentionally CLI-only.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not Found');
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Add is_best_answer to answers.
    try { $db->exec("ALTER TABLE answers ADD COLUMN is_best_answer TINYINT(1) NOT NULL DEFAULT 0"); } catch (PDOException $e) {}

    // Create reports table.
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS reports (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            item_type ENUM('question', 'answer', 'message') NOT NULL,
            item_id INT NOT NULL,
            reason VARCHAR(255) NOT NULL,
            status ENUM('pending', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (PDOException $e) {}

    // Add google_id to users.
    try { $db->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) DEFAULT NULL"); } catch (PDOException $e) {}

    echo "V3 Migration Successful!" . PHP_EOL;
} catch (PDOException $e) {
    fwrite(STDERR, "Migration failed. Check the application error log." . PHP_EOL);
    exit(1);
}
