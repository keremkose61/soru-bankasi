<?php
require '../config.php';
require '../models/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // 1. Add is_best_answer to answers
    try { $db->exec("ALTER TABLE answers ADD COLUMN is_best_answer TINYINT(1) NOT NULL DEFAULT 0;"); } catch (PDOException $e) {}
    
    // 2. Create reports table
    try { $db->exec("CREATE TABLE IF NOT EXISTS reports (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        item_type ENUM('question', 'answer', 'message') NOT NULL,
        item_id INT NOT NULL,
        reason VARCHAR(255) NOT NULL,
        status ENUM('pending', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"); } catch (PDOException $e) {}

    // 3. Add google_id to users
    try { $db->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) DEFAULT NULL;"); } catch (PDOException $e) {}

    echo "V2 Migration Successful!";
} catch (PDOException $e) {
    echo "Fatal Error: " . $e->getMessage();
}
