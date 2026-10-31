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
    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'teacher', 'admin') NOT NULL DEFAULT 'student'");
    echo "SUCCESS" . PHP_EOL;
} catch (Exception $e) {
    fwrite(STDERR, "Migration failed. Check the application error log." . PHP_EOL);
    exit(1);
}
