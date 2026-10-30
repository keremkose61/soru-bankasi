<?php
require_once __DIR__ . '/Database.php';

class Report {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($user_id, $item_type, $item_id, $reason) {
        $stmt = $this->db->prepare("INSERT INTO reports (user_id, item_type, item_id, reason) VALUES (:user_id, :item_type, :item_id, :reason)");
        return $stmt->execute([
            ':user_id' => $user_id,
            ':item_type' => $item_type,
            ':item_id' => $item_id,
            ':reason' => $reason
        ]);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT r.*, u.username FROM reports r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE reports SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }
}
