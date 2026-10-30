<?php
require_once __DIR__ . '/Database.php';

class Like {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function hasLiked($answer_id, $user_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE answer_id = :answer_id AND user_id = :user_id");
        $stmt->execute([
            ':answer_id' => $answer_id,
            ':user_id' => $user_id
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function addLike($answer_id, $user_id) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO likes (answer_id, user_id) VALUES (:answer_id, :user_id)");
        return $stmt->execute([
            ':answer_id' => $answer_id,
            ':user_id' => $user_id
        ]);
    }

    public function removeLike($answer_id, $user_id) {
        $stmt = $this->db->prepare("DELETE FROM likes WHERE answer_id = :answer_id AND user_id = :user_id");
        return $stmt->execute([
            ':answer_id' => $answer_id,
            ':user_id' => $user_id
        ]);
    }
}
