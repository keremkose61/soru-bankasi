<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Badge.php';

class Pomodoro {
    private $db;
    private $userModel;
    private $badgeModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User();
        $this->badgeModel = new Badge();
    }

    public function saveSession(int $userId, int $durationMinutes = 25, string $mode = 'pomodoro'): bool {
        $stmt = $this->db->prepare("
            INSERT INTO pomodoro_sessions (user_id, duration_minutes, mode) 
            VALUES (:user_id, :duration, :mode)
        ");
        $success = $stmt->execute([
            ':user_id'  => $userId,
            ':duration' => $durationMinutes,
            ':mode'     => $mode
        ]);

        if ($success) {
            // Tamamlanan her odaklanma oturumu için +5 Puan
            $this->userModel->addPoints($userId, 5);
            // Rozet kontrolü
            $this->badgeModel->checkAndAwardBadges($userId);
            return true;
        }

        return false;
    }

    public function getUserStats(int $userId): array {
        // Toplam seans ve dakika
        $totalStmt = $this->db->prepare("
            SELECT COUNT(*) as total_sessions, COALESCE(SUM(duration_minutes), 0) as total_minutes 
            FROM pomodoro_sessions 
            WHERE user_id = :user_id
        ");
        $totalStmt->execute([':user_id' => $userId]);
        $total = $totalStmt->fetch();

        // Bugünün seansı ve dakikası
        $todayStmt = $this->db->prepare("
            SELECT COUNT(*) as today_sessions, COALESCE(SUM(duration_minutes), 0) as today_minutes 
            FROM pomodoro_sessions 
            WHERE user_id = :user_id AND DATE(created_at) = CURDATE()
        ");
        $todayStmt->execute([':user_id' => $userId]);
        $today = $todayStmt->fetch();

        return [
            'total_sessions' => (int)($total['total_sessions'] ?? 0),
            'total_minutes'  => (int)($total['total_minutes'] ?? 0),
            'today_sessions' => (int)($today['today_sessions'] ?? 0),
            'today_minutes'  => (int)($today['today_minutes'] ?? 0),
        ];
    }

    public function getLast7DaysStats(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as session_date, COALESCE(SUM(duration_minutes), 0) as daily_minutes
            FROM pomodoro_sessions
            WHERE user_id = :user_id AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(created_at)
            ORDER BY session_date ASC
        ");
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        $labels = [];
        $data = [];
        $dayNames = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];

        for ($i = 6; $i >= 0; $i--) {
            $timestamp = strtotime("-$i days");
            $dateStr = date('Y-m-d', $timestamp);
            $dayOfWeek = (int)date('w', $timestamp);
            $label = ($i === 0) ? 'Bugün' : $dayNames[$dayOfWeek] . ' (' . date('d/m', $timestamp) . ')';
            
            $labels[] = $label;
            $data[] = (int)($rows[$dateStr] ?? 0);
        }

        return [
            'labels' => $labels,
            'data'   => $data,
            'total_week_minutes' => array_sum($data)
        ];
    }

    public function getRecentSessions(int $userId, int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT * FROM pomodoro_sessions 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
