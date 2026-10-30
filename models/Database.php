<?php
require_once __DIR__ . '/../config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->ensureSchema();
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    private function ensureSchema() {
        try {
            // 1. tags table
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS tags (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(50) NOT NULL UNIQUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 2. question_tags table
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS question_tags (
                    question_id INT NOT NULL,
                    tag_id INT NOT NULL,
                    PRIMARY KEY (question_id, tag_id),
                    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
                    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 3. bookmarks table
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS bookmarks (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    question_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_bookmark (user_id, question_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 4. reports table
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS reports (
                    id INT NOT NULL AUTO_INCREMENT,
                    user_id INT NOT NULL,
                    item_type ENUM('question', 'answer', 'message') NOT NULL,
                    item_id INT NOT NULL,
                    reason VARCHAR(255) NOT NULL,
                    status ENUM('pending', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 5. messages table
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS messages (
                    id INT NOT NULL AUTO_INCREMENT,
                    sender_id INT NOT NULL,
                    receiver_id INT NOT NULL,
                    content TEXT NOT NULL,
                    is_read TINYINT(1) NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    INDEX idx_sender (sender_id),
                    INDEX idx_receiver (receiver_id),
                    INDEX idx_conv (sender_id, receiver_id),
                    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 6. moderation_log table
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS moderation_log (
                    id INT NOT NULL AUTO_INCREMENT,
                    content_type ENUM('question', 'answer') NOT NULL,
                    content_id INT NOT NULL,
                    status ENUM('approved', 'review', 'rejected') NOT NULL,
                    reason TEXT DEFAULT NULL,
                    confidence INT DEFAULT 0,
                    admin_id INT DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    INDEX idx_content (content_type, content_id),
                    INDEX idx_status (status),
                    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 7. badges table
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS badges (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    slug VARCHAR(50) NOT NULL UNIQUE,
                    name VARCHAR(100) NOT NULL,
                    description TEXT NOT NULL,
                    icon VARCHAR(50) NOT NULL,
                    color VARCHAR(50) NOT NULL DEFAULT 'text-primary'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 8. user_badges table
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS user_badges (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    badge_id INT NOT NULL,
                    awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_user_badge (user_id, badge_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 9. pomodoro_sessions table
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS pomodoro_sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    duration_minutes INT NOT NULL DEFAULT 25,
                    mode VARCHAR(20) NOT NULL DEFAULT 'pomodoro',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_pomo (user_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Seed default badges if table is empty
            $badgeCount = $this->conn->query("SELECT COUNT(*) FROM badges")->fetchColumn();
            if ($badgeCount == 0) {
                $badges = [
                    ['first_question', 'İlk Kıvılcım', 'İlk sorunu başarıyla sordun.', 'bi-lightbulb-fill', 'text-warning'],
                    ['first_answer', 'Yardım Eli', 'İlk çözümünü paylaştın.', 'bi-pencil-fill', 'text-primary'],
                    ['master_solver', 'Usta Çözücü', 'En az 3 çözümün soru sahibi tarafından en iyi cevap seçildi.', 'bi-trophy-fill', 'text-warning'],
                    ['popular_author', 'Popüler Üye', 'Çözümlerin toplam 10 veya daha fazla beğeni aldı.', 'bi-heart-fill', 'text-danger'],
                    ['night_owl', 'Gece Kuşu', 'Gece 00:00 ile 05:00 arasında soru veya çözüm paylaştın.', 'bi-moon-stars-fill', 'text-info'],
                    ['math_whiz', 'Matematik Kurdu', 'Matematik branşında en az 3 çözüm paylaştın.', 'bi-calculator-fill', 'text-success'],
                    ['science_guru', 'Fen Dehası', 'Fizik, Kimya veya Biyoloji branşlarında en az 3 çözüm paylaştın.', 'bi-radioactive', 'text-warning'],
                    ['pomodoro_master', 'Odak Ustası', 'En az 5 Pomodoro çalışma seansı tamamladın.', 'bi-stopwatch-fill', 'text-danger'],
                    ['centurion', '100\'ler Kulübü', '100 veya daha fazla puana ulaştın.', 'bi-lightning-charge-fill', 'text-warning'],
                    ['bookworm', 'Kütüphaneci', 'En az 5 soruyu favorilerine ekledin.', 'bi-bookmarks-fill', 'text-primary']
                ];
                $stmt = $this->conn->prepare("INSERT INTO badges (slug, name, description, icon, color) VALUES (?, ?, ?, ?, ?)");
                foreach ($badges as $b) {
                    $stmt->execute($b);
                }
            }

            // Ensure missing columns on answers
            $this->addColumnIfNotExists('answers', 'is_best_answer', 'TINYINT(1) NOT NULL DEFAULT 0');
            $this->addColumnIfNotExists('answers', 'image_path', 'VARCHAR(255) DEFAULT NULL');
            $this->addColumnIfNotExists('answers', 'moderation_status', "ENUM('approved','review','rejected') NOT NULL DEFAULT 'approved'");

            // Ensure missing columns on questions
            $this->addColumnIfNotExists('questions', 'image_path', 'VARCHAR(255) DEFAULT NULL');
            $this->addColumnIfNotExists('questions', 'pdf_path', 'VARCHAR(255) DEFAULT NULL');
            $this->addColumnIfNotExists('questions', 'moderation_status', "ENUM('approved','review','rejected') NOT NULL DEFAULT 'approved'");

            // Ensure missing columns on users
            $this->addColumnIfNotExists('users', 'google_id', 'VARCHAR(255) DEFAULT NULL');
            $this->addColumnIfNotExists('users', 'cover_photo', 'VARCHAR(255) DEFAULT NULL');
            $this->addColumnIfNotExists('users', 'profile_photo', 'VARCHAR(255) DEFAULT NULL');
            $this->addColumnIfNotExists('users', 'bio', 'TEXT DEFAULT NULL');
            $this->addColumnIfNotExists('users', 'points', 'INT DEFAULT 0');
            $this->addColumnIfNotExists('users', 'is_banned', 'TINYINT(1) NOT NULL DEFAULT 0');

            // Ensure missing columns on moderation_log
            $this->addColumnIfNotExists('moderation_log', 'confidence', 'INT DEFAULT 0');

        } catch (\Exception $e) {
            error_log('Schema auto-sync notice: ' . $e->getMessage());
        }
    }

    private function addColumnIfNotExists(string $table, string $column, string $definition) {
        try {
            $check = $this->conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
            if ($check && $check->rowCount() === 0) {
                $this->conn->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
            }
        } catch (\Exception $e) {
            // Table might not exist yet or column check failed
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
