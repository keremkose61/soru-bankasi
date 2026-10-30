<?php
require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private $notificationModel;

    public function __construct() {
        $this->notificationModel = new Notification();
    }

    public function getUnread() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit();
        }

        $notifications = $this->notificationModel->getUnread($_SESSION['user_id']);
        echo json_encode(['success' => true, 'notifications' => $notifications]);
    }

    public function markAsRead() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit();
        }

        $this->notificationModel->markAsRead($_SESSION['user_id']);
        echo json_encode(['success' => true]);
    }
}
