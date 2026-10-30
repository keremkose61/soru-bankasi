<?php
require_once __DIR__ . '/../models/Pomodoro.php';
require_once __DIR__ . '/../models/Badge.php';

class PomodoroController {
    private $pomodoroModel;

    public function __construct() {
        $this->pomodoroModel = new Pomodoro();
    }

    public function index() {
        $userId = $_SESSION['user_id'] ?? null;
        $stats = [
            'total_sessions' => 0,
            'total_minutes'  => 0,
            'today_sessions' => 0,
            'today_minutes'  => 0,
        ];
        $recentSessions = [];
        $weeklyStats = [
            'labels' => ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'],
            'data'   => [0, 0, 0, 0, 0, 0, 0],
            'total_week_minutes' => 0
        ];

        if ($userId) {
            $stats = $this->pomodoroModel->getUserStats($userId);
            $recentSessions = $this->pomodoroModel->getRecentSessions($userId, 5);
            $weeklyStats = $this->pomodoroModel->getLast7DaysStats($userId);
        }

        require __DIR__ . '/../views/pomodoro.php';
    }

    public function save() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Lütfen seansınızı kaydetmek ve puan kazanmak için giriş yapın.'
            ]);
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        $duration = isset($data['duration_minutes']) ? (int)$data['duration_minutes'] : 25;
        $mode = isset($data['mode']) ? trim($data['mode']) : 'pomodoro';

        if ($duration <= 0 || $duration > 180) {
            $duration = 25;
        }

        $saved = $this->pomodoroModel->saveSession($userId, $duration, $mode);
        $updatedStats = $this->pomodoroModel->getUserStats($userId);

        echo json_encode([
            'success'      => $saved,
            'points_added' => 5,
            'stats'        => $updatedStats,
            'message'      => 'Tebrikler! +5 puan kazandınız.'
        ]);
        exit();
    }
}
