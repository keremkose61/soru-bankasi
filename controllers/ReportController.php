<?php
require_once __DIR__ . '/../models/Report.php';

class ReportController {
    private $reportModel;

    public function __construct() {
        $this->reportModel = new Report();
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header("HTTP/1.0 403 Forbidden");
            echo "Giriş yapmanız gerekiyor.";
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $item_type = $_POST['item_type'];
            $item_id = $_POST['item_id'];
            $reason = trim($_POST['reason']);
            
            // Soruya dönebilmek için referans URL'i alıyoruz (veya formdan question_id alabiliriz)
            $return_url = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/';

            if (!empty($reason) && in_array($item_type, ['question', 'answer', 'message'])) {
                $this->reportModel->create($user_id, $item_type, $item_id, $reason);
                // Başarı mesajı session'a atılabilir
            }
            
            header("Location: " . $return_url);
            exit();
        }
    }
}
