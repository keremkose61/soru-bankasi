<?php
require_once __DIR__ . '/Database.php';

class Notification {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($user_id, $type, $sender_id, $content_id, $message) {
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, sender_id, content_id, message) VALUES (:user_id, :type, :sender_id, :content_id, :message)");
        return $stmt->execute([
            ':user_id' => $user_id,
            ':type' => $type,
            ':sender_id' => $sender_id,
            ':content_id' => $content_id,
            ':message' => $message
        ]);
    }

    public function getUnread($user_id) {
        // Bildirimler soru sayfasına yönlendirildiği için silinmiş sorulara ait
        // eski bildirimleri listeleme.
        $stmt = $this->db->prepare("
            SELECT n.*
            FROM notifications n
            INNER JOIN questions q ON q.id = n.content_id
            WHERE n.user_id = :user_id
              AND n.is_read = 0
            ORDER BY n.created_at DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll();
    }

    public function markAsRead($user_id) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $user_id]);
    }
}
