<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'teacher', 'admin') NOT NULL DEFAULT 'student'");
    echo "SUCCESS";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
