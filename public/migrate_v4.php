<?php
require_once __DIR__ . '/../models/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // 1. Add image_path to questions
    try {
        $db->exec("ALTER TABLE questions ADD COLUMN image_path VARCHAR(255) DEFAULT NULL");
        echo "Added image_path to questions.<br>";
    } catch (PDOException $e) {
        echo "Column image_path already exists in questions or error: " . $e->getMessage() . "<br>";
    }

    // 2. Add image_path to answers
    try {
        $db->exec("ALTER TABLE answers ADD COLUMN image_path VARCHAR(255) DEFAULT NULL");
        echo "Added image_path to answers.<br>";
    } catch (PDOException $e) {
        echo "Column image_path already exists in answers or error: " . $e->getMessage() . "<br>";
    }

    // 3. Create tags table
    $db->exec("
        CREATE TABLE IF NOT EXISTS tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created tags table.<br>";

    // 4. Create question_tags table
    $db->exec("
        CREATE TABLE IF NOT EXISTS question_tags (
            question_id INT NOT NULL,
            tag_id INT NOT NULL,
            PRIMARY KEY (question_id, tag_id),
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created question_tags table.<br>";

    // 5. Create bookmarks table
    $db->exec("
        CREATE TABLE IF NOT EXISTS bookmarks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            question_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_bookmark (user_id, question_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created bookmarks table.<br>";

    echo "<h3>V4 Migration completed successfully!</h3>";

} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage());
}
