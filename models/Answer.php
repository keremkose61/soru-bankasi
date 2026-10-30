<?php
require_once __DIR__ . '/Database.php';

class Answer {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function ensureCommentsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS answer_comments (
            id INT NOT NULL AUTO_INCREMENT,
            answer_id INT NOT NULL,
            user_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_answer_id (answer_id),
            INDEX idx_user_id (user_id),
            FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        try {
            $this->db->exec($sql);
        } catch (Exception $e) {}
    }

    public function create($question_id, $user_id, $content, $moderation_status = 'approved', $image_path = null) {
        $stmt = $this->db->prepare("INSERT INTO answers (question_id, user_id, content, image_path, moderation_status) VALUES (:question_id, :user_id, :content, :image_path, :mod_status)");
        $result = $stmt->execute([
            ':question_id' => $question_id,
            ':user_id'     => $user_id,
            ':content'     => $content,
            ':image_path'  => $image_path,
            ':mod_status'  => $moderation_status,
        ]);
        return $result ? (int)$this->db->lastInsertId() : false;
    }

    public function updateModerationStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE answers SET moderation_status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function getForModeration(string $status = 'review'): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.username, q.title as question_title, q.id as question_id
             FROM answers a
             JOIN users u ON a.user_id = u.id
             JOIN questions q ON a.question_id = q.id
             WHERE a.moderation_status = :status
             ORDER BY a.created_at DESC"
        );
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT a.*, u.username, u.profile_photo, u.role FROM answers a LEFT JOIN users u ON a.user_id = u.id WHERE a.id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getAnswersByQuestionId($question_id) {
        $this->ensureCommentsTable();
        $stmt = $this->db->prepare("
            SELECT a.*, u.username, u.profile_photo, u.role 
            FROM answers a 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.question_id = :question_id AND a.moderation_status != 'rejected' 
            ORDER BY a.is_best_answer DESC, a.like_count DESC, a.created_at ASC
        ");
        $stmt->execute([':question_id' => $question_id]);
        $answers = $stmt->fetchAll();

        foreach ($answers as &$ans) {
            $ans['comments'] = $this->getCommentsByAnswerId($ans['id']);
        }
        unset($ans);

        return $answers;
    }

    public function addComment($answer_id, $user_id, $content) {
        $this->ensureCommentsTable();
        $stmt = $this->db->prepare("INSERT INTO answer_comments (answer_id, user_id, content) VALUES (:aid, :uid, :content)");
        $res = $stmt->execute([
            ':aid'     => $answer_id,
            ':uid'     => $user_id,
            ':content' => $content
        ]);
        return $res ? (int)$this->db->lastInsertId() : false;
    }

    public function getCommentsByAnswerId($answer_id) {
        $this->ensureCommentsTable();
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.username, u.profile_photo, u.role
                FROM answer_comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.answer_id = :aid
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([':aid' => $answer_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function deleteComment($comment_id, $user_id, $isAdmin = false) {
        $this->ensureCommentsTable();
        if ($isAdmin) {
            $stmt = $this->db->prepare("DELETE FROM answer_comments WHERE id = :id");
            return $stmt->execute([':id' => $comment_id]);
        }
        $stmt = $this->db->prepare("DELETE FROM answer_comments WHERE id = :id AND user_id = :uid");
        return $stmt->execute([':id' => $comment_id, ':uid' => $user_id]);
    }

    public function markAsBest($answer_id, $question_id) {
        $stmt1 = $this->db->prepare("UPDATE answers SET is_best_answer = 0 WHERE question_id = :question_id");
        $stmt1->execute([':question_id' => $question_id]);

        $stmt2 = $this->db->prepare("UPDATE answers SET is_best_answer = 1 WHERE id = :id AND question_id = :question_id");
        return $stmt2->execute([':id' => $answer_id, ':question_id' => $question_id]);
    }

    public function updateLikeCount($answer_id, $amount) {
        $stmt = $this->db->prepare("UPDATE answers SET like_count = like_count + :amount WHERE id = :id");
        return $stmt->execute([
            ':amount' => $amount,
            ':id' => $answer_id
        ]);
    }

    public function getCountByUserId($user_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM answers WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    public function getTotalLikesByUserId($user_id) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(like_count), 0) FROM answers WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    public function getBestAnswerCountByUserId($user_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM answers WHERE user_id = :user_id AND is_best_answer = 1");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    public function getSubjectDistributionByUserId(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT q.subject, COUNT(*) as count 
            FROM answers a
            JOIN questions q ON a.question_id = q.id
            WHERE a.user_id = :uid AND a.moderation_status != 'rejected'
            GROUP BY q.subject 
            ORDER BY count DESC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function getRecentActivityDays(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM answers 
            WHERE user_id = :uid AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(created_at)
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }
}
