<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/Answer.php';
require_once __DIR__ . '/../models/Report.php';

class AdminController {
    private $userModel;
    private $questionModel;
    private $answerModel;
    private $reportModel;

    public function __construct() {
        $this->userModel     = new User();
        $this->questionModel = new Question();
        $this->answerModel   = new Answer();
        $this->reportModel   = new Report();
        $this->checkAdmin();
    }

    private function checkAdmin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("HTTP/1.0 403 Forbidden");
            echo "Access Denied";
            exit();
        }
    }

    public function index() {
        $users     = $this->userModel->getAllUsers();
        $questions = $this->questionModel->getAll();
        $reports   = $this->reportModel->getAll();
        // Count items in review/rejected queues for badge
        $reviewCount = count($this->questionModel->getForModeration('review'))
                     + count($this->answerModel->getForModeration('review'));
        require __DIR__ . '/../views/admin.php';
    }

    public function moderationQueue() {
        $reviewQuestions  = $this->questionModel->getForModeration('review');
        $reviewAnswers    = $this->answerModel->getForModeration('review');
        $rejectedQuestions = $this->questionModel->getForModeration('rejected');
        $rejectedAnswers   = $this->answerModel->getForModeration('rejected');
        require __DIR__ . '/../views/admin_moderation.php';
    }

    public function approveContent(string $type, int $id) {
        if ($type === 'question') {
            $this->questionModel->updateModerationStatus($id, 'approved');
        } elseif ($type === 'answer') {
            $this->answerModel->updateModerationStatus($id, 'approved');
        }
        header("Location: " . BASE_URL . "/admin/moderation");
        exit();
    }

    public function rejectContent(string $type, int $id) {
        if ($type === 'question') {
            $this->questionModel->updateModerationStatus($id, 'rejected');
        } elseif ($type === 'answer') {
            $this->answerModel->updateModerationStatus($id, 'rejected');
        }
        header("Location: " . BASE_URL . "/admin/moderation");
        exit();
    }

    public function deleteQuestion($id) {
        $question = $this->questionModel->getById($id);
        if ($question) {
            if ($question['image_path'] && file_exists(__DIR__ . '/..' . $question['image_path'])) {
                unlink(__DIR__ . '/..' . $question['image_path']);
            }
            if ($question['pdf_path'] && file_exists(__DIR__ . '/..' . $question['pdf_path'])) {
                unlink(__DIR__ . '/..' . $question['pdf_path']);
            }
            $this->questionModel->delete($id);
        }
        header("Location: " . BASE_URL . "/admin");
        exit();
    }

    public function deleteUser($id) {
        $this->userModel->deleteUser($id);
        header("Location: " . BASE_URL . "/admin");
        exit();
    }

    public function toggleBan($id, $status) {
        $this->userModel->toggleBan($id, $status);
        header("Location: " . BASE_URL . "/admin");
        exit();
    }

    public function toggleRole($id, $role) {
        $this->userModel->updateRole($id, $role);
        header("Location: " . BASE_URL . "/admin");
        exit();
    }

    public function updateReportStatus($id, $status) {
        $this->reportModel->updateStatus($id, $status);
        header("Location: " . BASE_URL . "/admin#reports");
        exit();
    }
}
