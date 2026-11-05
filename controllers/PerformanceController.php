<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Answer.php';
require_once __DIR__ . '/../models/Question.php';

class PerformanceController {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $userModel = new User();
        $answerModel = new Answer();
        $questionModel = new Question();

        $user = $userModel->getUserById($userId);
        $totalAnswers = (int)$answerModel->getCountByUserId($userId);
        $bestAnswers = (int)$answerModel->getBestAnswerCountByUserId($userId);
        $totalLikes = (int)$answerModel->getTotalLikesByUserId($userId);
        $subjectStats = $answerModel->getSubjectDistributionByUserId($userId);
        $activityRows = $answerModel->getRecentActivityDays($userId);

        $activityByDate = [];
        foreach ($activityRows as $row) {
            $activityByDate[$row['date']] = (int)$row['count'];
        }

        $activityLabels = [];
        $activityValues = [];
        $activityTotal = 0;
        $today = new DateTimeImmutable('today');
        for ($offset = 6; $offset >= 0; $offset--) {
            $date = $today->modify('-' . $offset . ' days');
            $key = $date->format('Y-m-d');
            $count = $activityByDate[$key] ?? 0;
            $activityLabels[] = $date->format('d.m');
            $activityValues[] = $count;
            $activityTotal += $count;
        }

        $subjectTotal = array_sum(array_map(static function ($row) {
            return (int)($row['count'] ?? 0);
        }, $subjectStats));

        require __DIR__ . '/../views/performance.php';
    }
}
