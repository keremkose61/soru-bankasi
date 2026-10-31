<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/Answer.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role'] ?? 'student';

            if ($this->userModel->register($username, $email, $password, $role)) {
                header("Location: " . BASE_URL . "/login");
                exit();
            } else {
                $error = __('register_failed');
                require __DIR__ . '/../views/register.php';
                return;
            }
        }
        require __DIR__ . '/../views/register.php';
    }

    public function login() {
        if (isset($_GET['banned'])) {
            $error = "Hesabınız askıya alınmıştır.";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $user = $this->userModel->login($email, $password);

            if ($user) {
                if (isset($user['is_banned']) && $user['is_banned']) {
                    $error = "Hesabınız askıya alınmıştır.";
                    require __DIR__ . '/../views/login.php';
                    return;
                }
                // Prevent session fixation and always trust the role stored in the database.
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_photo'] = $user['profile_photo'] ?? null;
                $_SESSION['cover_photo'] = $user['cover_photo'] ?? null;

                header("Location: " . BASE_URL . "/");
                exit();
            } else {
                $error = __('invalid_credentials');
            }
        }
        require __DIR__ . '/../views/login.php';
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $user = $this->userModel->getUserByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $this->userModel->setResetToken($email, $token, $expiry);
                $resetLink = BASE_URL . "/reset-password?token=" . $token;
                $simulationLink = $resetLink;
                $success = "Şifre sıfırlama bağlantısı oluşturuldu. Test ortamında olduğunuz için aşağıdaki bağlantıyı kullanarak şifrenizi hemen sıfırlayabilirsiniz:";
            } else {
                $error = "Bu e-posta adresiyle kayıtlı bir hesap bulunamadı.";
            }
        }
        require __DIR__ . '/../views/forgot_password.php';
    }

    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        $user = $this->userModel->getUserByResetToken($token);
        if (!$user) {
            $error = "Geçersiz veya süresi dolmuş sıfırlama bağlantısı. Lütfen tekrar sıfırlama isteği gönderin.";
            require __DIR__ . '/../views/reset_password.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $passwordConfirm = $_POST['password_confirm'];

            if ($password !== $passwordConfirm) {
                $error = "Şifreler eşleşmiyor.";
            } elseif (strlen($password) < 6) {
                $error = "Şifre en az 6 karakter olmalıdır.";
            } else {
                if ($this->userModel->updatePassword($user['id'], $password)) {
                    $success = "Şifreniz başarıyla güncellendi. Şimdi <a href='" . BASE_URL . "/login' class='alert-link fw-bold'>giriş yapabilirsiniz</a>.";
                } else {
                    $error = "Şifre güncellenirken bir hata oluştu.";
                }
            }
        }
        require __DIR__ . '/../views/reset_password.php';
    }

    public function profile($targetUserId = null) {
        $isOwnProfile = false;

        if ($targetUserId !== null) {
            $targetUserId = (int)$targetUserId;
            $user = $this->userModel->getUserById($targetUserId);
            if (!$user) {
                header("Location: " . BASE_URL . "/");
                exit();
            }
            $userId = $targetUserId;
            $isOwnProfile = (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $targetUserId);
        } else {
            if (!isset($_SESSION['user_id'])) {
                header("Location: " . BASE_URL . "/login");
                exit();
            }
            $userId = (int)$_SESSION['user_id'];
            $user = $this->userModel->getUserById($userId);
            $isOwnProfile = true;
        }

        $questionModel = new Question();
        $answerModel = new Answer();

        $userQuestions = $questionModel->getByUserId($userId) ?: [];
        $userAnswerCount = (int)$answerModel->getCountByUserId($userId);
        $totalLikes = (int)$answerModel->getTotalLikesByUserId($userId);
        $bestAnswerCount = (int)$answerModel->getBestAnswerCountByUserId($userId);

        // Rozet Sistemi
        require_once __DIR__ . '/../models/Badge.php';
        $badgeModel = new Badge();
        if ($isOwnProfile) {
            $badgeModel->checkAndAwardBadges($userId);
        }
        $userBadges = $badgeModel->getUserBadges($userId);
        $allBadges = $badgeModel->getUserBadgesWithStatus($userId);

        // Pomodoro Odaklanma İstatistikleri
        require_once __DIR__ . '/../models/Pomodoro.php';
        $pomodoroModel = new Pomodoro();
        $pomodoroStats = $pomodoroModel->getUserStats($userId);

        // Başarı Oranı & Gelişim İstatistikleri
        $successRate = $userAnswerCount > 0 ? round(($bestAnswerCount / $userAnswerCount) * 100) : 0;

        // Ders Dağılım İstatistikleri (Sorular + Cevaplar)
        $qSubjects = $questionModel->getSubjectDistributionByUserId($userId);
        $aSubjects = $answerModel->getSubjectDistributionByUserId($userId);
        $subjectStats = [];
        foreach ($qSubjects as $qs) {
            $subjectStats[$qs['subject']] = ($subjectStats[$qs['subject']] ?? 0) + (int)$qs['count'];
        }
        foreach ($aSubjects as $as) {
            $subjectStats[$as['subject']] = ($subjectStats[$as['subject']] ?? 0) + (int)$as['count'];
        }
        
        $bookmarkedQuestions = [];
        if ($isOwnProfile) {
            require_once __DIR__ . '/../models/Bookmark.php';
            $bookmarkModel = new Bookmark();
            $bookmarkedQuestions = $bookmarkModel->getBookmarksByUserId($userId);
        }

        require __DIR__ . '/../views/profile.php';
    }

    public function updateProfile() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)$_SESSION['user_id'];
            $bio = trim($_POST['bio'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Biyografi güncelle
            $this->userModel->updateBio($userId, $bio);

            // Şifre değişikliği istendiyse
            if (!empty($newPassword)) {
                require_once __DIR__ . '/../models/Database.php';
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
                $stmt->execute([':id' => $userId]);
                $currentHash = $stmt->fetchColumn();

                if (!$currentHash || !password_verify($currentPassword, $currentHash)) {
                    $_SESSION['profile_error'] = 'Mevcut şifrenizi yanlış girdiniz.';
                    header("Location: " . BASE_URL . "/profile");
                    exit();
                }

                if ($newPassword !== $confirmPassword) {
                    $_SESSION['profile_error'] = 'Yeni şifreler birbiriyle eşleşmiyor.';
                    header("Location: " . BASE_URL . "/profile");
                    exit();
                }

                if (strlen($newPassword) < 6) {
                    $_SESSION['profile_error'] = 'Yeni şifre en az 6 karakter uzunluğunda olmalıdır.';
                    header("Location: " . BASE_URL . "/profile");
                    exit();
                }

                $this->userModel->updatePassword($userId, $newPassword);
            }

            $_SESSION['profile_success'] = 'Profil bilgileriniz başarıyla güncellendi!';
        }

        header("Location: " . BASE_URL . "/profile");
        exit();
    }

    public function updatePhoto() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/profile");
            exit();
        }

        $isCover = isset($_FILES['cover_photo']) && !empty($_FILES['cover_photo']['name']);
        $fileKey = $isCover ? 'cover_photo' : 'profile_photo';

        if (empty($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] == UPLOAD_ERR_NO_FILE) {
            $_SESSION['photo_error'] = 'Lütfen bir dosya seçin.';
            header("Location: " . BASE_URL . "/profile");
            exit();
        }

        $file = $_FILES[$fileKey];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'Dosya boyutu sunucunun PHP limitini aşıyor.',
                UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu form limitini aşıyor.',
                UPLOAD_ERR_PARTIAL => 'Dosya sadece kısmen yüklenebildi.',
                UPLOAD_ERR_NO_FILE => 'Lütfen bir dosya seçin.',
                UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör eksik.',
                UPLOAD_ERR_CANT_WRITE => 'Dosya diske yazılamadı.',
                UPLOAD_ERR_EXTENSION => 'Bir PHP eklentisi dosya yüklemesini durdurdu.'
            ];
            $msg = $errors[$file['error']] ?? 'Bilinmeyen bir yükleme hatası oluştu. (Hata Kodu: ' . $file['error'] . ')';
            $_SESSION['photo_error'] = $msg;
            header("Location: " . BASE_URL . "/profile");
            exit();
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['photo_error'] = 'Dosya boyutu 5MB\'den büyük olamaz.';
            header("Location: " . BASE_URL . "/profile");
            exit();
        }

        $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
        $real_mime = mime_content_type($file['tmp_name']);
        
        if (!$real_mime) {
            $real_mime = $file['type'];
        }

        if (!in_array($real_mime, $allowed_mime)) {
            $_SESSION['photo_error'] = 'Geçersiz dosya formatı. Sadece JPG, PNG veya WebP yükleyebilirsiniz.';
            header("Location: " . BASE_URL . "/profile");
            exit();
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($ext)) {
            $mime_to_ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $ext = $mime_to_ext[$real_mime] ?? 'jpg';
        }
        
        $prefix = $isCover ? 'cover_' : 'avatar_';
        $filename = $prefix . $_SESSION['user_id'] . '_' . uniqid() . '.' . $ext;
        $upload_dir = rtrim(UPLOAD_PATH, '/\\') . ($isCover ? '/covers' : '/avatars');

        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0777, true);
        }

        $user = $this->userModel->getUserById($_SESSION['user_id']);
        $old_path = $isCover ? $user['cover_photo'] : $user['profile_photo'];

        if (!empty($old_path)) {
            $old_file = rtrim(__DIR__ . '/..', '/\\') . '/' . ltrim($old_path, '/');
            if (file_exists($old_file) && is_file($old_file)) {
                @unlink($old_file);
            }
        }

        $target_file = $upload_dir . '/' . $filename;
        if (@move_uploaded_file($file['tmp_name'], $target_file)) {
            $photo_path = '/public/uploads/' . ($isCover ? 'covers/' : 'avatars/') . $filename;
            
            if ($isCover) {
                $this->userModel->updateCoverPhoto($_SESSION['user_id'], $photo_path);
                $_SESSION['cover_photo'] = $photo_path;
            } else {
                $this->userModel->updateProfilePhoto($_SESSION['user_id'], $photo_path);
                $_SESSION['profile_photo'] = $photo_path;
            }
            
            $_SESSION['photo_success'] = ($isCover ? 'Kapak' : 'Profil') . ' fotoğrafınız başarıyla güncellendi!';
        } else {
            $_SESSION['photo_error'] = 'Dosya sunucuda hedef klasöre taşınamadı. İzinleri kontrol edin.';
        }

        header("Location: " . BASE_URL . "/profile");
        exit();
    }

    public function logout() {
        session_destroy();
        header("Location: " . BASE_URL . "/login");
        exit();
    }
}
