<?php
require_once __DIR__ . '/Database.php';

class Message {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Kullanıcının tüm konuşmalarını döndürür (son mesaj + okunmamış sayısı)
     */
    public function getConversations($userId) {
        $stmt = $this->db->prepare("
            SELECT
                u.id,
                u.username,
                u.profile_photo,
                u.role,
                m.content      AS last_message,
                m.created_at   AS last_at,
                m.sender_id    AS last_sender_id,
                SUM(CASE WHEN m2.is_read = 0 AND m2.receiver_id = :uid2 THEN 1 ELSE 0 END) AS unread_count
            FROM users u
            JOIN messages m ON (
                (m.sender_id = :uid3 AND m.receiver_id = u.id) OR
                (m.receiver_id = :uid4 AND m.sender_id = u.id)
            )
            LEFT JOIN messages m2 ON (
                m2.sender_id = u.id AND m2.receiver_id = :uid5
            )
            WHERE u.id != :uid6
              AND m.id = (
                  SELECT MAX(id) FROM messages
                  WHERE (sender_id = :uid7 AND receiver_id = u.id)
                     OR (receiver_id = :uid8 AND sender_id = u.id)
              )
            GROUP BY u.id, u.username, u.profile_photo, u.role, m.content, m.created_at, m.sender_id
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([
            ':uid2' => $userId, ':uid3' => $userId, ':uid4' => $userId,
            ':uid5' => $userId, ':uid6' => $userId, ':uid7' => $userId,
            ':uid8' => $userId,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * İki kullanıcı arasındaki mesajları döndürür
     */
    public function getConversation($userId, $otherId, $afterId = 0) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.username AS sender_name, u.profile_photo AS sender_photo
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE ((m.sender_id = :uid AND m.receiver_id = :oid)
                OR (m.sender_id = :oid2 AND m.receiver_id = :uid2))
              AND m.id > :after_id
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([
            ':uid'      => $userId,
            ':oid'      => $otherId,
            ':uid2'     => $userId,
            ':oid2'     => $otherId,
            ':after_id' => $afterId,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mesaj gönder
     */
    public function send($senderId, $receiverId, $content) {
        $stmt = $this->db->prepare("
            INSERT INTO messages (sender_id, receiver_id, content)
            VALUES (:sender, :receiver, :content)
        ");
        $stmt->execute([
            ':sender'   => $senderId,
            ':receiver' => $receiverId,
            ':content'  => $content,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Gelen mesajları okundu olarak işaretle
     */
    public function markAsRead($receiverId, $senderId) {
        $stmt = $this->db->prepare("
            UPDATE messages SET is_read = 1
            WHERE receiver_id = :receiver AND sender_id = :sender AND is_read = 0
        ");
        return $stmt->execute([':receiver' => $receiverId, ':sender' => $senderId]);
    }

    /**
     * Kullanıcının toplam okunmamış mesaj sayısı (navbar badge için)
     */
    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM messages
            WHERE receiver_id = :uid AND is_read = 0
        ");
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Mesaj atılabilecek kullanıcı listesi
     * Öğrenci → öğretmenleri görür, Öğretmen → öğrencileri görür, Admin kimseyle mesajlaşamaz
     */
    public function getContactList($userId, $currentRole) {
        if ($currentRole === 'admin') {
            return [];
        } elseif ($currentRole === 'student') {
            $stmt = $this->db->prepare("
                SELECT id, username, profile_photo, role
                FROM users WHERE role = 'teacher' AND id != :uid
                ORDER BY username ASC
            ");
            $stmt->execute([':uid' => $userId]);
        } else {
            // teacher
            $stmt = $this->db->prepare("
                SELECT id, username, profile_photo, role
                FROM users WHERE role = 'student' AND id != :uid
                ORDER BY username ASC
            ");
            $stmt->execute([':uid' => $userId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Belirli bir kullanıcı hakkında bilgi döndürür
     */
    public function getUserInfo($id) {
        $stmt = $this->db->prepare("SELECT id, username, profile_photo, role FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Son mesaj ID'sini döndür (polling için)
     */
    public function getLastMessageId($userId, $otherId) {
        $stmt = $this->db->prepare("
            SELECT MAX(id) FROM messages
            WHERE (sender_id = :uid AND receiver_id = :oid)
               OR (sender_id = :oid2 AND receiver_id = :uid2)
        ");
        $stmt->execute([':uid' => $userId, ':oid' => $otherId, ':uid2' => $userId, ':oid2' => $otherId]);
        return (int)$stmt->fetchColumn();
    }
}
