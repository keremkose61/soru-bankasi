<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Notification.php';

class Badge {
    private $db;
    private $userModel;
    private $notificationModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User();
        $this->notificationModel = new Notification();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM badges ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function getUserBadges(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT b.*, ub.awarded_at 
            FROM user_badges ub 
            JOIN badges b ON ub.badge_id = b.id 
            WHERE ub.user_id = :user_id 
            ORDER BY ub.awarded_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getUserBadgesWithStatus(int $userId): array {
        $allBadges = $this->getAll();
        $userBadges = $this->getUserBadges($userId);
        
        $userBadgeMap = [];
        foreach ($userBadges as $ub) {
            $userBadgeMap[$ub['id']] = $ub['awarded_at'];
        }

        $result = [];
        foreach ($allBadges as $b) {
            $b['is_unlocked'] = isset($userBadgeMap[$b['id']]);
            $b['awarded_at'] = $userBadgeMap[$b['id']] ?? null;
            $result[] = $b;
        }

        return $result;
    }

    public function award(int $userId, string $badgeSlug): bool {
        // Badge var mı?
        $stmt = $this->db->prepare("SELECT id, name FROM badges WHERE slug = :slug");
        $stmt->execute([':slug' => $badgeSlug]);
        $badge = $stmt->fetch();
        if (!$badge) {
            return false;
        }

        // Zaten alınmış mı?
        $check = $this->db->prepare("SELECT id FROM user_badges WHERE user_id = :user_id AND badge_id = :badge_id");
        $check->execute([':user_id' => $userId, ':badge_id' => $badge['id']]);
        if ($check->fetch()) {
            return false; // Zaten var
        }

        // Ekle
        $ins = $this->db->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (:user_id, :badge_id)");
        $success = $ins->execute([':user_id' => $userId, ':badge_id' => $badge['id']]);

        if ($success) {
            // Rozet bonusu: +15 Puan
            $this->userModel->addPoints($userId, 15);
            // Bildirim oluştur
            $msg = "Tebrikler! \"" . $badge['name'] . "\" rozetini ve +15 bonus puan kazandınız!";
            $this->notificationModel->create($userId, 'badge', null, $badge['id'], $msg);
            return true;
        }

        return false;
    }

    public function checkAndAwardBadges(int $userId): array {
        $newlyAwarded = [];

        // 1. Soru sayısı
        $qCountStmt = $this->db->prepare("SELECT COUNT(*) FROM questions WHERE user_id = :uid AND moderation_status != 'rejected'");
        $qCountStmt->execute([':uid' => $userId]);
        $qCount = (int)$qCountStmt->fetchColumn();
        if ($qCount >= 1 && $this->award($userId, 'first_question')) {
            $newlyAwarded[] = 'first_question';
        }

        // 2. Cevap sayısı
        $aCountStmt = $this->db->prepare("SELECT COUNT(*) FROM answers WHERE user_id = :uid AND moderation_status != 'rejected'");
        $aCountStmt->execute([':uid' => $userId]);
        $aCount = (int)$aCountStmt->fetchColumn();
        if ($aCount >= 1 && $this->award($userId, 'first_answer')) {
            $newlyAwarded[] = 'first_answer';
        }

        // 3. En iyi cevap sayısı
        $bestCountStmt = $this->db->prepare("SELECT COUNT(*) FROM answers WHERE user_id = :uid AND is_best_answer = 1");
        $bestCountStmt->execute([':uid' => $userId]);
        $bestCount = (int)$bestCountStmt->fetchColumn();
        if ($bestCount >= 3 && $this->award($userId, 'master_solver')) {
            $newlyAwarded[] = 'master_solver';
        }

        // 4. Toplam beğeni
        $likesStmt = $this->db->prepare("SELECT COALESCE(SUM(like_count), 0) FROM answers WHERE user_id = :uid");
        $likesStmt->execute([':uid' => $userId]);
        $likes = (int)$likesStmt->fetchColumn();
        if ($likes >= 10 && $this->award($userId, 'popular_author')) {
            $newlyAwarded[] = 'popular_author';
        }

        // 5. Favori sayısı
        $bmStmt = $this->db->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = :uid");
        $bmStmt->execute([':uid' => $userId]);
        $bmCount = (int)$bmStmt->fetchColumn();
        if ($bmCount >= 5 && $this->award($userId, 'bookworm')) {
            $newlyAwarded[] = 'bookworm';
        }

        // 6. Kullanıcı puanı
        $user = $this->userModel->getUserById($userId);
        if ($user && isset($user['points']) && $user['points'] >= 100) {
            if ($this->award($userId, 'centurion')) {
                $newlyAwarded[] = 'centurion';
            }
        }

        // 7. Matematik branşında en az 3 çözüm
        $mathStmt = $this->db->prepare("
            SELECT COUNT(*) FROM answers a
            JOIN questions q ON a.question_id = q.id
            WHERE a.user_id = :uid AND q.subject = 'Matematik' AND a.moderation_status != 'rejected'
        ");
        $mathStmt->execute([':uid' => $userId]);
        if ((int)$mathStmt->fetchColumn() >= 3 && $this->award($userId, 'math_whiz')) {
            $newlyAwarded[] = 'math_whiz';
        }

        // 8. Fen branşlarında en az 3 çözüm
        $sciStmt = $this->db->prepare("
            SELECT COUNT(*) FROM answers a
            JOIN questions q ON a.question_id = q.id
            WHERE a.user_id = :uid AND q.subject IN ('Fizik', 'Kimya', 'Biyoloji') AND a.moderation_status != 'rejected'
        ");
        $sciStmt->execute([':uid' => $userId]);
        if ((int)$sciStmt->fetchColumn() >= 3 && $this->award($userId, 'science_guru')) {
            $newlyAwarded[] = 'science_guru';
        }

        // 9. Gece Kuşu (00:00 - 05:00 saatleri arası paylaşım)
        $nightStmt = $this->db->prepare("
            SELECT 
                (SELECT COUNT(*) FROM questions WHERE user_id = :uid AND HOUR(created_at) >= 0 AND HOUR(created_at) < 5) +
                (SELECT COUNT(*) FROM answers WHERE user_id = :uid AND HOUR(created_at) >= 0 AND HOUR(created_at) < 5) as total_night
        ");
        $nightStmt->execute([':uid' => $userId]);
        if ((int)$nightStmt->fetchColumn() >= 1 && $this->award($userId, 'night_owl')) {
            $newlyAwarded[] = 'night_owl';
        }

        // 10. Pomodoro Seansları (en az 5 seans)
        $pomoStmt = $this->db->prepare("SELECT COUNT(*) FROM pomodoro_sessions WHERE user_id = :uid");
        $pomoStmt->execute([':uid' => $userId]);
        if ((int)$pomoStmt->fetchColumn() >= 5 && $this->award($userId, 'pomodoro_master')) {
            $newlyAwarded[] = 'pomodoro_master';
        }

        return $newlyAwarded;
    }
}
