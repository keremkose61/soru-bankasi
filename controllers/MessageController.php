<?php
require_once __DIR__ . '/../models/Message.php';

class MessageController {
    private $model;

    public function __construct() {
        $this->model = new Message();
    }

    private function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }
            header("Location: " . BASE_URL . "/login");
            exit();
        }
    }

    private function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Ana mesaj kutusu — konuşma listesi + opsiyonel açık sohbet
     */
    public function inbox($otherId = null) {
        $this->requireLogin();
        $userId      = $_SESSION['user_id'];
        $currentRole = $_SESSION['role'];

        if ($currentRole === 'admin') {
            header("Location: " . BASE_URL . "/");
            exit();
        }

        $conversations = $this->model->getConversations($userId);
        $contacts      = $this->model->getContactList($userId, $currentRole);
        $activeUser    = null;
        $messages      = [];
        $lastMessageId = 0;

        if ($otherId) {
            // Erişim kontrolü: kişi listesinde olmayan biriyle mesajlaşma engeli
            $activeUser = $this->model->getUserInfo($otherId);
            if ($activeUser) {
                $this->model->markAsRead($userId, $otherId);
                $messages      = $this->model->getConversation($userId, $otherId);
                $lastMessageId = empty($messages) ? 0 : end($messages)['id'];
            }
        }

        require __DIR__ . '/../views/messages.php';
    }

    /**
     * Mesaj gönder — AJAX POST
     */
    public function send() {
        $this->requireLogin();
        header('Content-Type: application/json');

        if ($_SESSION['role'] === 'admin') {
            echo json_encode(['success' => false, 'error' => 'Admins cannot send messages']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit();
        }

        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $content    = trim($_POST['content'] ?? '');

        if (!$receiverId || $content === '') {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            exit();
        }

        if (mb_strlen($content) > 2000) {
            echo json_encode(['success' => false, 'error' => 'Message too long']);
            exit();
        }

        // Profanity filter
        $badWords = ['amk', 'küfür', 'aptal', 'salak', 'mal', 'gerizekalı', 'sg', 'siktir', 'oç', 'orospu'];
        foreach ($badWords as $word) {
            $pattern = '/' . preg_quote($word, '/') . '/iu';
            $content = preg_replace($pattern, '***', $content);
        }

        $userId  = $_SESSION['user_id'];
        $newId   = $this->model->send($userId, $receiverId, $content);

        if ($newId) {
            echo json_encode([
                'success'    => true,
                'id'         => $newId,
                'content'    => $content,
                'created_at' => date('H:i'),
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'DB error']);
        }
        exit();
    }

    /**
     * Yeni mesajları polling ile çek — AJAX GET
     */
    public function poll($otherId, $afterId) {
        $this->requireLogin();
        header('Content-Type: application/json');

        $userId   = $_SESSION['user_id'];
        $otherId  = (int)$otherId;
        $afterId  = (int)$afterId;

        // Okundu işaretle
        $this->model->markAsRead($userId, $otherId);

        $newMessages = $this->model->getConversation($userId, $otherId, $afterId);
        $result      = [];

        foreach ($newMessages as $msg) {
            $result[] = [
                'id'         => $msg['id'],
                'sender_id'  => $msg['sender_id'],
                'content'    => $msg['content'],
                'created_at' => date('H:i', strtotime($msg['created_at'])),
                'is_mine'    => ($msg['sender_id'] == $userId),
            ];
        }

        // Ayrıca navbar badge için güncel okunmamış sayıyı gönder
        $unread = $this->model->getUnreadCount($userId);

        echo json_encode(['messages' => $result, 'unread' => $unread]);
        exit();
    }

    /**
     * Navbar badge için okunmamış sayı — AJAX GET
     */
    public function getUnread() {
        $this->requireLogin();
        header('Content-Type: application/json');
        $count = $this->model->getUnreadCount($_SESSION['user_id']);
        echo json_encode(['count' => $count]);
        exit();
    }
}
