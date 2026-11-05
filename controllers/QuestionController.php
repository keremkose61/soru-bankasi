<?php
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/Answer.php';
require_once __DIR__ . '/../models/Like.php';

class QuestionController {
    private $questionModel;
    private $answerModel;
    private $likeModel;
    private $userModel;
    private $notificationModel;

    public function __construct() {
        $this->questionModel = new Question();
        $this->answerModel = new Answer();
        $this->likeModel = new Like();
        require_once __DIR__ . '/../models/User.php';
        $this->userModel = new User();
        require_once __DIR__ . '/../models/Notification.php';
        $this->notificationModel = new Notification();
    }

    public function index() {
        $orderBy = isset($_GET['sort']) && $_GET['sort'] == 'most_commented' ? 'most_commented' : 'newest';
        $subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';
        $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        
        $questions = $this->questionModel->getAll($orderBy, $subject, $searchQuery, $status);
        $subjects = $this->questionModel->getAllSubjects();

        // Personal dashboard data for signed-in users
        $dashboardStats = null;
        $recentPomodoroSessions = [];
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/../models/Pomodoro.php';
            $pomodoroModel = new Pomodoro();
            $dashboardStats = $pomodoroModel->getUserStats((int)$_SESSION['user_id']);

            $userQuestions = $this->questionModel->getByUserId((int)$_SESSION['user_id']);
            $dashboardStats['questions_count'] = count($userQuestions);
            $dashboardStats['answers_count'] = (int)$this->answerModel->getCountByUserId((int)$_SESSION['user_id']);

            $currentUser = $this->userModel->getUserById((int)$_SESSION['user_id']);
            $dashboardStats['points'] = (int)($currentUser['points'] ?? 0);
            $recentPomodoroSessions = $pomodoroModel->getRecentSessions((int)$_SESSION['user_id'], 4);
        }

        require __DIR__ . '/../views/home.php';
    }

    public function show($id) {
        $question = $this->questionModel->getById($id);
        if (!$question) {
            header("HTTP/1.0 404 Not Found");
            echo "Question not found";
            exit();
        }

        $answers = $this->answerModel->getAnswersByQuestionId($id);
        $tags = $this->questionModel->getTags($id);
        
        $isBookmarked = false;
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/../models/Bookmark.php';
            $bookmarkModel = new Bookmark();
            $isBookmarked = $bookmarkModel->isBookmarked($_SESSION['user_id'], $id);
        }
        
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $likedAnswers = [];
        if ($user_id) {
            foreach ($answers as $ans) {
                if ($this->likeModel->hasLiked($ans['id'], $user_id)) {
                    $likedAnswers[] = $ans['id'];
                }
            }
        }

        require __DIR__ . '/../views/question_detail.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        if (isset($_SESSION['role']) && ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin')) {
            header("Location: " . BASE_URL . "/");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id    = $_SESSION['user_id'];
            $title      = trim($_POST['title']);
            $subject    = trim($_POST['subject']);
            $description = trim($_POST['description']);

            $image_path = null;
            $pdf_path   = null;

            // Handle Image Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_path = $this->handleUpload($_FILES['image'], 'images', ['image/jpeg', 'image/png']);
            }

            // Handle PDF Upload
            if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
                $pdf_path = $this->handleUpload($_FILES['pdf'], 'pdfs', ['application/pdf']);
            }

            // ── AI Moderation Check ─────────────────────────────────────────
            $textToCheck   = $title . ' ' . $description;
            $modResult     = ModerationService::check($textToCheck);
            $modStatus     = strtolower($modResult['status']); // approved | review | rejected

            if ($modStatus === 'rejected') {
                $_SESSION['mod_error'] = $modResult['reason'];
                header("Location: " . BASE_URL . "/question/create?mod=rejected");
                exit();
            }

            // Save question (approved or review)
            $questionId = $this->questionModel->create(
                $user_id, $title, $subject, $description,
                $image_path, $pdf_path, $modStatus
            );

            if ($questionId && !empty($_POST['tags'])) {
                $this->questionModel->addTags($questionId, $_POST['tags']);
            }

            // Log moderation decision
            if ($questionId) {
                // Soru oluşturma +2 Puan
                if ($modStatus !== 'rejected') {
                    $this->userModel->addPoints($user_id, 2);
                    require_once __DIR__ . '/../models/Badge.php';
                    $badgeModel = new Badge();
                    $badgeModel->checkAndAwardBadges($user_id);
                }

                require_once __DIR__ . '/../models/Database.php';
                $db = Database::getInstance()->getConnection();
                ModerationService::log($db, 'question', $questionId, $modResult);
            }

            if ($modStatus === 'review') {
                $_SESSION['mod_notice'] = $modResult['reason'];
                header("Location: " . BASE_URL . "/?mod=review");
            } else {
                header("Location: " . BASE_URL . "/");
            }
            exit();
        }

        require __DIR__ . '/../views/create_question.php';
    }

    public function deleteOwn($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        $question = $this->questionModel->getById($id);

        if (!$question) {
            header("HTTP/1.0 404 Not Found");
            echo "Soru bulunamadı.";
            exit();
        }

        // Sadece soru sahibi veya admin silebilir
        if ($question['user_id'] != $_SESSION['user_id'] && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
            header("HTTP/1.0 403 Forbidden");
            echo "Bu soruyu silme yetkiniz yok.";
            exit();
        }

        // Dosyaları sil
        if (!empty($question['image_path']) && file_exists(__DIR__ . '/..' . $question['image_path'])) {
            unlink(__DIR__ . '/..' . $question['image_path']);
        }
        if (!empty($question['pdf_path']) && file_exists(__DIR__ . '/..' . $question['pdf_path'])) {
            unlink(__DIR__ . '/..' . $question['pdf_path']);
        }

        $this->questionModel->delete($id);
        header("Location: " . BASE_URL . "/");
        exit();
    }

    public function answer($question_id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $content = trim($_POST['content']);

            if (!empty($content)) {
                // ── AI Moderation Check ─────────────────────────────────────
                $modResult     = ModerationService::check($content);
                $modStatus     = strtolower($modResult['status']);

                if ($modStatus === 'rejected') {
                    $_SESSION['mod_error'] = $modResult['reason'];
                    header("Location: " . BASE_URL . "/question/" . $question_id . "?mod=rejected");
                    exit();
                }

                // ── Resim Yükleme (Dosya veya Çizimli Tuval) ────────────────
                $image_path = null;
                if (isset($_FILES['answer_image']) && $_FILES['answer_image']['error'] === UPLOAD_ERR_OK) {
                    $image_path = $this->handleUpload(
                        $_FILES['answer_image'],
                        'answer_images',
                        ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
                    );
                } elseif (!empty($_POST['canvas_drawing']) && strpos($_POST['canvas_drawing'], 'data:image/png;base64,') === 0) {
                    // Only accept a real PNG image from the drawing canvas (max 5 MB).
                    $canvasData = substr($_POST['canvas_drawing'], strlen('data:image/png;base64,'));
                    $decoded = base64_decode($canvasData, true);
                    $imageInfo = $decoded !== false ? @getimagesizefromstring($decoded) : false;

                    if (
                        $decoded !== false &&
                        strlen($decoded) <= 5 * 1024 * 1024 &&
                        $imageInfo !== false &&
                        $imageInfo['mime'] === 'image/png'
                    ) {
                        $target_dir = UPLOAD_PATH . '/answer_images';
                        if (!is_dir($target_dir)) {
                            mkdir($target_dir, 0755, true);
                        }
                        $filename = 'wb_' . bin2hex(random_bytes(16)) . '.png';
                        if (file_put_contents($target_dir . '/' . $filename, $decoded, LOCK_EX) !== false) {
                            $image_path = '/public/uploads/answer_images/' . $filename;
                        }
                    }
                }

                $answerId = $this->answerModel->create($question_id, $user_id, $content, $modStatus, $image_path);

                if ($answerId) {
                    if ($modStatus !== 'rejected') {
                        $this->userModel->addPoints($user_id, 5);
                        
                        require_once __DIR__ . '/../models/Badge.php';
                        $badgeModel = new Badge();
                        $badgeModel->checkAndAwardBadges($user_id);
                        
                        $question = $this->questionModel->getById($question_id);
                        if ($question && $question['user_id'] != $user_id) {
                            $this->notificationModel->create($question['user_id'], 'answer', $user_id, $question_id, "Sorunuza yeni bir cevap geldi.");
                        }
                    }

                    require_once __DIR__ . '/../models/Database.php';
                    $db = Database::getInstance()->getConnection();
                    ModerationService::log($db, 'answer', $answerId, $modResult);
                }

                if ($modStatus === 'review') {
                    $_SESSION['mod_notice'] = $modResult['reason'];
                }
            }
        }

        header("Location: " . BASE_URL . "/question/" . $question_id);
        exit();
    }
    
    public function like($answer_id) {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit();
        }

        $user_id = $_SESSION['user_id'];
        
        $answer = $this->answerModel->getById($answer_id);
        if (!$answer) {
            echo json_encode(['success' => false, 'message' => 'Yorum bulunamadı']);
            exit();
        }

        if ($answer['user_id'] == $user_id) {
            echo json_encode(['success' => false, 'message' => 'Kendi yorumunuzu beğenemezsiniz']);
            exit();
        }

        if ($this->likeModel->hasLiked($answer_id, $user_id)) {
            $this->likeModel->removeLike($answer_id, $user_id);
            $this->answerModel->updateLikeCount($answer_id, -1);
            $this->userModel->addPoints($answer['user_id'], -2);
            echo json_encode(['success' => true, 'action' => 'unliked']);
        } else {
            $this->likeModel->addLike($answer_id, $user_id);
            $this->answerModel->updateLikeCount($answer_id, 1);
            $this->userModel->addPoints($answer['user_id'], 2);
            $this->notificationModel->create($answer['user_id'], 'like', $user_id, $answer['question_id'], "Cevabınız beğenildi.");
            echo json_encode(['success' => true, 'action' => 'liked']);
        }
    }

    public function markBestAnswer($question_id, $answer_id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        $question = $this->questionModel->getById($question_id);
        if (!$question || $question['user_id'] != $_SESSION['user_id']) {
            header("HTTP/1.0 403 Forbidden");
            echo "Yetkiniz yok.";
            exit();
        }

        $answer = $this->answerModel->getById($answer_id);
        if ($answer && $answer['question_id'] == $question_id) {
            $existingAnswers = $this->answerModel->getAnswersByQuestionId($question_id);
            $alreadySolved = false;
            foreach ($existingAnswers as $ea) {
                if (!empty($ea['is_best_answer'])) {
                    $alreadySolved = true;
                    break;
                }
            }

            if (!$alreadySolved) {
                $this->answerModel->markAsBest($answer_id, $question_id);
                $this->userModel->addPoints($answer['user_id'], 10);
                
                require_once __DIR__ . '/../models/Badge.php';
                $badgeModel = new Badge();
                $badgeModel->checkAndAwardBadges($answer['user_id']);
                
                if ($answer['user_id'] != $_SESSION['user_id']) {
                    $this->notificationModel->create($answer['user_id'], 'best_answer', $_SESSION['user_id'], $question_id, "Cevabınız en iyi cevap seçildi!");
                }
            }
        }

        header("Location: " . BASE_URL . "/question/" . $question_id);
        exit();
    }

    public function toggleBookmark($question_id) {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Lütfen giriş yapın']);
            return;
        }

        require_once __DIR__ . '/../models/Bookmark.php';
        $bookmarkModel = new Bookmark();
        $added = $bookmarkModel->toggle($_SESSION['user_id'], $question_id);
        echo json_encode(['success' => true, 'added' => $added]);
    }

    public function commentAnswer($questionId, $answerId) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['comment_content'] ?? '');
            if (!empty($content)) {
                $commentId = $this->answerModel->addComment((int)$answerId, (int)$_SESSION['user_id'], $content);
                if ($commentId) {
                    $ans = $this->answerModel->getById($answerId);
                    if ($ans && $ans['user_id'] != $_SESSION['user_id']) {
                        $this->notificationModel->create(
                            $ans['user_id'],
                            'comment',
                            $_SESSION['user_id'],
                            $questionId,
                            htmlspecialchars($_SESSION['username']) . ' çözümünüze yorum yazdı: "' . mb_substr($content, 0, 45) . '..."'
                        );
                    }
                }
            }
        }
        header("Location: " . BASE_URL . "/question/" . $questionId . "#answer-" . $answerId);
        exit();
    }

    public function deleteComment($questionId, $commentId) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        $this->answerModel->deleteComment((int)$commentId, (int)$_SESSION['user_id'], $isAdmin);
        header("Location: " . BASE_URL . "/question/" . $questionId);
        exit();
    }

    public function getAiHint($id) {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız.']);
            exit();
        }

        $question = $this->questionModel->getById($id);
        if (!$question) {
            echo json_encode(['success' => false, 'message' => 'Soru bulunamadı.']);
            exit();
        }

        $userPrompt = null;
        if (isset($_POST['prompt']) && !empty(trim($_POST['prompt']))) {
            $userPrompt = trim($_POST['prompt']);

            $modResult = ModerationService::check($userPrompt);
            $modStatus = strtolower($modResult['status']);

            if ($modStatus === 'rejected') {
                echo json_encode(['success' => false, 'message' => 'Mesajınız uygunsuz içerik (argo/küfür) nedeniyle reddedildi.']);
                exit();
            }
        }

        require_once __DIR__ . '/../core/AiService.php';
        $hint = AiService::getHint($question['title'], $question['description'], $userPrompt);

        if ($hint) {
            echo json_encode(['success' => true, 'hint' => $hint]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Yapay zeka şu an yanıt veremiyor.']);
        }
        exit();
    }

    private function handleUpload($file, $folder, $allowed_types) {
        $max_size = 5 * 1024 * 1024; // 5 MB
        $allowed_folders = ['images', 'pdfs', 'answer_images'];

        if (
            !in_array($folder, $allowed_folders, true) ||
            !is_array($file) ||
            !isset($file['error'], $file['size'], $file['tmp_name']) ||
            $file['error'] !== UPLOAD_ERR_OK ||
            !is_uploaded_file($file['tmp_name']) ||
            $file['size'] <= 0 ||
            $file['size'] > $max_size
        ) {
            return null;
        }

        // Never trust the browser-provided MIME type or the original filename.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }

        $detected_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $extension_by_mime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
        ];

        if (
            $detected_type === false ||
            !in_array($detected_type, $allowed_types, true) ||
            !isset($extension_by_mime[$detected_type])
        ) {
            return null;
        }

        $extension = $extension_by_mime[$detected_type];
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $target_dir = UPLOAD_PATH . '/' . $folder;
        if (!is_dir($target_dir) && !mkdir($target_dir, 0755, true) && !is_dir($target_dir)) {
            return null;
        }

        $destination = $target_dir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return '/public/uploads/' . $folder . '/' . $filename;
        }

        return null;
    }
}
