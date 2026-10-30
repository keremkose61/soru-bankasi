<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    // Use IF NOT EXISTS equivalent for columns by ignoring duplicate column errors or just running and catching
    $db->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL, ADD COLUMN reset_expires DATETIME DEFAULT NULL");
    echo "SUCCESS";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
