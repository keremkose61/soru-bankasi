<?php
require_once 'config.php';
require_once __DIR__ . '/core/Security.php';

// Validate every state-changing POST request before routing it.
csrf_validate_request();

// Add CSRF tokens to rendered forms and same-origin AJAX requests.
ob_start('csrf_inject_html');
require_once 'models/User.php';

if (isset($_SESSION['user_id'])) {
    $userModel = new User();
    $currentUser = $userModel->getUserById($_SESSION['user_id']);
    if (!$currentUser || (isset($currentUser['is_banned']) && $currentUser['is_banned'])) {
        session_destroy();
        header("Location: " . BASE_URL . "/login?banned=1");
        exit();
    }
}

// Remove the dynamic base directory from the request logic
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
if ($base_dir === '/' || $base_dir === '\\') {
    $base_dir = '';
}
$base_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($base_dir !== '' && strpos($base_path, $base_dir) === 0) {
    $base_path = substr($base_path, strlen($base_dir));
}
if ($base_path == '') {
    $base_path = '/';
}

// Simple router
switch ($base_path) {
    case '/':
    case '/index.php':
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->index();
        break;

    case '/manifest.json':
        header('Content-Type: application/manifest+json; charset=utf-8');
        readfile(__DIR__ . '/manifest.json');
        exit();

    case '/sw.js':
        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: /');
        readfile(__DIR__ . '/sw.js');
        exit();

    case '/offline.html':
        header('Content-Type: text/html; charset=utf-8');
        readfile(__DIR__ . '/offline.html');
        exit();

    case '/login':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;

    case '/register':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->register();
        break;

    case '/forgot-password':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->forgotPassword();
        break;

    case '/reset-password':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->resetPassword();
        break;
        
    case '/logout':
        require_post_method();
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case '/profile':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->profile();
        break;

    case (preg_match('/^\/profile\/(\d+)$/', $base_path, $matches) ? true : false):
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->profile((int)$matches[1]);
        break;

    case '/profile/update':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->updateProfile();
        break;

    case '/profile/update-photo':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->updatePhoto();
        break;

    case '/messages':
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->inbox();
        break;

    case '/messages/send':
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->send();
        break;

    case '/messages/unread':
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->getUnread();
        break;

    case (preg_match('/^\/messages\/(\d+)$/', $base_path, $matches) ? true : false):
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->inbox((int)$matches[1]);
        break;

    case (preg_match('/^\/messages\/poll\/(\d+)\/(\d+)$/', $base_path, $matches) ? true : false):
        require 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->poll((int)$matches[1], (int)$matches[2]);
        break;

    case '/question/create':
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->create();
        break;
        
    case (preg_match('/^\/question\/(\d+)$/', $base_path, $matches) ? true : false):
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->show($matches[1]);
        break;

    case (preg_match('/^\/question\/(\d+)\/answer$/', $base_path, $matches) ? true : false):
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->answer($matches[1]);
        break;

    case (preg_match('/^\/question\/(\d+)\/answer\/(\d+)\/comment$/', $base_path, $matches) ? true : false):
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->commentAnswer($matches[1], $matches[2]);
        break;

    case (preg_match('/^\/question\/(\d+)\/comment\/(\d+)\/delete$/', $base_path, $matches) ? true : false):
        require_post_method();
        require_post_method();
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->deleteComment($matches[1], $matches[2]);
        break;

    case (preg_match('/^\/question\/(\d+)\/delete$/', $base_path, $matches) ? true : false):
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->deleteOwn($matches[1]);
        break;

    case (preg_match('/^\/question\/(\d+)\/bookmark$/', $base_path, $matches) ? true : false):
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->toggleBookmark($matches[1]);
        break;

    case (preg_match('/^\/question\/(\d+)\/hint$/', $base_path, $matches) ? true : false):
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->getAiHint($matches[1]);
        break;

    case (preg_match('/^\/answer\/(\d+)\/like$/', $base_path, $matches) ? true : false):
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->like($matches[1]);
        break;

    case (preg_match('/^\/question\/(\d+)\/best-answer\/(\d+)$/', $base_path, $matches) ? true : false):
        require_post_method();
        require 'controllers/QuestionController.php';
        $controller = new QuestionController();
        $controller->markBestAnswer($matches[1], $matches[2]);
        break;

    case '/performance':
        require 'controllers/PerformanceController.php';
        $controller = new PerformanceController();
        $controller->index();
        break;

    case '/leaderboard':
        require 'controllers/LeaderboardController.php';
        $controller = new LeaderboardController();
        $controller->index();
        break;

    case '/pomodoro':
        require 'controllers/PomodoroController.php';
        $controller = new PomodoroController();
        $controller->index();
        break;

    case '/pomodoro/save':
        require 'controllers/PomodoroController.php';
        $controller = new PomodoroController();
        $controller->save();
        break;

    case '/exam/booklet':
        require 'controllers/ExamController.php';
        $controller = new ExamController();
        $controller->booklet();
        break;
        
    case '/notifications/unread':
        require 'controllers/NotificationController.php';
        $controller = new NotificationController();
        $controller->getUnread();
        break;

    case '/notifications/read':
        require_post_method();
        require 'controllers/NotificationController.php';
        $controller = new NotificationController();
        $controller->markAsRead();
        break;

    case '/report/create':
        require 'controllers/ReportController.php';
        $controller = new ReportController();
        $controller->create();
        break;

    case '/admin':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->index();
        break;

    case (preg_match('/^\/admin\/report\/(\d+)\/(pending|resolved|dismissed)$/', $base_path, $matches) ? true : false):
        require_post_method();
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->updateReportStatus($matches[1], $matches[2]);
        break;

    case '/admin/moderation':
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->moderationQueue();
        break;
        
    case (preg_match('/^\/admin\/moderation\/(approve|reject)\/(question|answer)\/(\d+)$/', $base_path, $matches) ? true : false):
        require_post_method();
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        if ($matches[1] === 'approve') {
            $controller->approveContent($matches[2], (int)$matches[3]);
        } else {
            $controller->rejectContent($matches[2], (int)$matches[3]);
        }
        break;

    case (preg_match('/^\/admin\/delete_question\/(\d+)$/', $base_path, $matches) ? true : false):
        require_post_method();
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->deleteQuestion($matches[1]);
        break;

    case (preg_match('/^\/admin\/delete_user\/(\d+)$/', $base_path, $matches) ? true : false):
        require_post_method();
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->deleteUser($matches[1]);
        break;
        
    case (preg_match('/^\/admin\/toggle_ban\/(\d+)\/(\d+)$/', $base_path, $matches) ? true : false):
        require_post_method();
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->toggleBan($matches[1], $matches[2]);
        break;

    case (preg_match('/^\/admin\/toggle_role\/(\d+)\/(student|teacher)$/', $base_path, $matches) ? true : false):
        require_post_method();
        require 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->toggleRole($matches[1], $matches[2]);
        break;
        
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
