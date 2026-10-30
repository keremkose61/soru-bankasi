<?php
require_once __DIR__ . '/Database.php';

class Bookmark {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function isBookmarked($user_id, $question_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = :uid AND question_id = :qid");
        $stmt->execute([':uid' => $user_id, ':qid' => $question_id]);
        return $stmt->fetchColumn() > 0;
    }

    public function toggle($user_id, $question_id) {
        if ($this->isBookmarked($user_id, $question_id)) {
            $stmt = $this->db->prepare("DELETE FROM bookmarks WHERE user_id = :uid AND question_id = :qid");
            $stmt->execute([':uid' => $user_id, ':qid' => $question_id]);
            return false; // Removed
        } else {
            $stmt = $this->db->prepare("INSERT INTO bookmarks (user_id, question_id) VALUES (:uid, :qid)");
            $stmt->execute([':uid' => $user_id, ':qid' => $question_id]);
            return true; // Added
        }
    }

    public function getBookmarksByUserId($user_id) {
        $stmt = $this->db->prepare("
            SELECT q.*, u.username, 
            (SELECT COUNT(*) FROM answers WHERE question_id = q.id) as answer_count 
            FROM questions q 
            JOIN bookmarks b ON q.id = b.question_id 
            JOIN users u ON q.user_id = u.id 
            WHERE b.user_id = :uid 
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([':uid' => $user_id]);
        return $stmt->fetchAll();
    }
}
