<?php
require_once __DIR__ . '/../models/User.php';

class LeaderboardController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function index() {
        $topUsers = $this->userModel->getTopUsers(50);
        require __DIR__ . '/../views/leaderboard.php';
    }
}
