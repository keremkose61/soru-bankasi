<?php
require_once __DIR__ . '/Database.php';

class Question {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($user_id, $title, $subject, $description, $image_path = null, $pdf_path = null, $moderation_status = 'approved') {
        $stmt = $this->db->prepare("INSERT INTO questions (user_id, title, subject, description, image_path, pdf_path, moderation_status) VALUES (:user_id, :title, :subject, :description, :image_path, :pdf_path, :mod_status)");
        $result = $stmt->execute([
            ':user_id'     => $user_id,
            ':title'       => $title,
            ':subject'     => $subject,
            ':description' => $description,
            ':image_path'  => $image_path,
            ':pdf_path'    => $pdf_path,
            ':mod_status'  => $moderation_status,
        ]);
        return $result ? (int)$this->db->lastInsertId() : false;
    }

    public function updateModerationStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE questions SET moderation_status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function getForModeration(string $status = 'review'): array {
        $stmt = $this->db->prepare(
            "SELECT q.*, u.username FROM questions q
             JOIN users u ON q.user_id = u.id
             WHERE q.moderation_status = :status
             ORDER BY q.created_at DESC"
        );
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll();
    }

    public function getAll($orderBy = 'newest', $subject = '', $searchQuery = '', $status = '') {
        $params = [];
        $sql = "SELECT q.*, u.username, 
                (SELECT COUNT(*) FROM answers WHERE question_id = q.id) as answer_count,
                (SELECT COUNT(*) FROM answers WHERE question_id = q.id AND is_best_answer = 1) as is_solved,
                (SELECT GROUP_CONCAT(t.name) FROM tags t JOIN question_tags qt ON t.id = qt.tag_id WHERE qt.question_id = q.id) as tags_list
                FROM questions q 
                LEFT JOIN users u ON q.user_id = u.id
                WHERE q.moderation_status != 'rejected' ";

        if (!empty($subject)) {
            $sql .= "AND q.subject = :subject ";
            $params[':subject'] = $subject;
        }

        if (!empty($searchQuery)) {
            $cleanTag = ltrim($searchQuery, '#');
            $sql .= "AND (
                q.title LIKE :search 
                OR q.description LIKE :search 
                OR EXISTS (
                    SELECT 1 FROM question_tags qt 
                    JOIN tags t ON qt.tag_id = t.id 
                    WHERE qt.question_id = q.id 
                    AND (t.name LIKE :tagSearch OR t.name = :cleanTag)
                )
            ) ";
            $params[':search'] = '%' . $searchQuery . '%';
            $params[':tagSearch'] = '%' . $cleanTag . '%';
            $params[':cleanTag'] = $cleanTag;
        }
        
        if ($status === 'unsolved') {
            $sql .= "AND (SELECT COUNT(*) FROM answers WHERE question_id = q.id AND is_best_answer = 1) = 0 ";
        } elseif ($status === 'solved') {
            $sql .= "AND (SELECT COUNT(*) FROM answers WHERE question_id = q.id AND is_best_answer = 1) > 0 ";
        }
                
        if ($orderBy == 'most_commented') {
            $sql .= "ORDER BY answer_count DESC, q.created_at DESC";
        } else {
            $sql .= "ORDER BY q.created_at DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAllSubjects() {
        $stmt = $this->db->query("SELECT DISTINCT subject FROM questions ORDER BY subject ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getByUserId($user_id) {
        $stmt = $this->db->prepare("SELECT q.*, (SELECT COUNT(*) FROM answers WHERE question_id = q.id) as answer_count FROM questions q WHERE q.user_id = :user_id ORDER BY q.created_at DESC");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT q.*, u.username FROM questions q LEFT JOIN users u ON q.user_id = u.id WHERE q.id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM questions WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function addTags($question_id, $tags_string) {
        if (empty(trim($tags_string))) return;
        
        $tags = explode(',', $tags_string);
        foreach ($tags as $tag_name) {
            $tag_name = trim($tag_name);
            if (empty($tag_name)) continue;

            // Find or create tag
            $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = :name");
            $stmt->execute([':name' => $tag_name]);
            $tag_id = $stmt->fetchColumn();

            if (!$tag_id) {
                $insert = $this->db->prepare("INSERT INTO tags (name) VALUES (:name)");
                $insert->execute([':name' => $tag_name]);
                $tag_id = $this->db->lastInsertId();
            }

            // Link tag to question
            try {
                $link = $this->db->prepare("INSERT IGNORE INTO question_tags (question_id, tag_id) VALUES (:qid, :tid)");
                $link->execute([':qid' => $question_id, ':tid' => $tag_id]);
            } catch (Exception $e) {}
        }
    }

    public function getTags($question_id) {
        $stmt = $this->db->prepare("
            SELECT t.* FROM tags t 
            JOIN question_tags qt ON t.id = qt.tag_id 
            WHERE qt.question_id = :qid
        ");
        $stmt->execute([':qid' => $question_id]);
        return $stmt->fetchAll();
    }

    public function getSubjectDistributionByUserId(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT subject, COUNT(*) as count 
            FROM questions 
            WHERE user_id = :uid AND moderation_status != 'rejected'
            GROUP BY subject 
            ORDER BY count DESC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function getByIds(array $ids): array {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("
            SELECT q.*, u.username,
                   (SELECT COUNT(*) FROM answers WHERE question_id = q.id) as answer_count,
                   (SELECT COUNT(*) FROM answers WHERE question_id = q.id AND is_best_answer = 1) as is_solved
            FROM questions q
            LEFT JOIN users u ON q.user_id = u.id
            WHERE q.id IN ($placeholders) AND q.moderation_status != 'rejected'
            ORDER BY q.created_at ASC
        ");
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll();
    }
}
