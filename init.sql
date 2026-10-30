-- ============================================================
--  Soru Bankası — Tam ve Güncel Veritabanı Kurulum Scripti
--  Versiyon: 6.0 (Complete & Synchronized)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Mevcut veritabanını sıfırla
DROP DATABASE IF EXISTS soru_bankasi;
CREATE DATABASE soru_bankasi
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE soru_bankasi;

-- ─────────────────────────────────────────────────────────────
-- 1. KULLANICILAR (users)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE users (
    id            INT          NOT NULL AUTO_INCREMENT,
    username      VARCHAR(50)  NOT NULL,
    email         VARCHAR(100) NOT NULL,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('student','teacher','admin') NOT NULL DEFAULT 'student',
    profile_photo VARCHAR(255)            DEFAULT NULL,
    cover_photo   VARCHAR(255)            DEFAULT NULL,
    bio           TEXT                    DEFAULT NULL,
    points        INT                     DEFAULT 0,
    google_id     VARCHAR(255)            DEFAULT NULL,
    reset_token   VARCHAR(255)            DEFAULT NULL,
    reset_expires DATETIME                DEFAULT NULL,
    created_at    TIMESTAMP               DEFAULT CURRENT_TIMESTAMP,
    is_banned     TINYINT(1)              NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_username (username),
    UNIQUE KEY uq_email    (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 2. KATEGORİLER (categories)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE categories (
    id          INT          NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL,
    icon        VARCHAR(50)           DEFAULT 'bi-collection',
    color       VARCHAR(20)           DEFAULT '#0d6efd',
    description TEXT                  DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 3. SORULAR (questions)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE questions (
    id                INT          NOT NULL AUTO_INCREMENT,
    user_id           INT          NOT NULL,
    category_id       INT                   DEFAULT NULL,
    title             VARCHAR(255) NOT NULL,
    subject           VARCHAR(100) NOT NULL,
    description       TEXT                  DEFAULT NULL,
    image_path        VARCHAR(255)          DEFAULT NULL,
    pdf_path          VARCHAR(255)          DEFAULT NULL,
    moderation_status ENUM('approved','review','rejected') NOT NULL DEFAULT 'approved',
    view_count        INT                   DEFAULT 0,
    created_at        TIMESTAMP             DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_user_id     (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_subject     (subject),
    INDEX idx_mod_stat    (moderation_status),
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 4. ETİKETLER (tags & question_tags)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE tags (
    id   INT         NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tag_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE question_tags (
    question_id INT NOT NULL,
    tag_id      INT NOT NULL,
    PRIMARY KEY (question_id, tag_id),
    INDEX idx_tag (tag_id),
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id)      REFERENCES tags(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 5. CEVAPLAR (answers)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE answers (
    id                INT  NOT NULL AUTO_INCREMENT,
    question_id       INT  NOT NULL,
    user_id           INT  NOT NULL,
    content           TEXT NOT NULL,
    image_path        VARCHAR(255)          DEFAULT NULL,
    like_count        INT                   DEFAULT 0,
    is_best_answer    TINYINT(1)            NOT NULL DEFAULT 0,
    moderation_status ENUM('approved','review','rejected') NOT NULL DEFAULT 'approved',
    created_at        TIMESTAMP             DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_question_id (question_id),
    INDEX idx_user_id     (user_id),
    INDEX idx_mod_stat    (moderation_status),
    INDEX idx_best        (is_best_answer),
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 6. BEĞENİLER (likes)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE likes (
    id          INT NOT NULL AUTO_INCREMENT,
    answer_id   INT NOT NULL,
    user_id     INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_answer_like (answer_id, user_id),
    FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 7. FAVORİLER / YER İŞARETLERİ (bookmarks)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE bookmarks (
    id          INT NOT NULL AUTO_INCREMENT,
    user_id     INT NOT NULL,
    question_id INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_bookmark (user_id, question_id),
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 8. BİLDİRİMLER (notifications)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE notifications (
    id          INT          NOT NULL AUTO_INCREMENT,
    user_id     INT          NOT NULL,
    type        VARCHAR(50)  NOT NULL,
    sender_id   INT                   DEFAULT NULL,
    content_id  INT                   DEFAULT NULL,
    message     TEXT         NOT NULL,
    is_read     TINYINT(1)            DEFAULT 0,
    created_at  TIMESTAMP             DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 9. MODERASYON LOGLARI (moderation_log)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE moderation_log (
    id           INT  NOT NULL AUTO_INCREMENT,
    content_type ENUM('question','answer') NOT NULL,
    content_id   INT  NOT NULL,
    status       ENUM('approved','review','rejected') NOT NULL,
    reason       TEXT DEFAULT NULL,
    confidence   INT  DEFAULT 0,
    admin_id     INT  DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_content (content_type, content_id),
    INDEX idx_status  (status),
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 10. ÖZEL MESAJLAR (messages)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE messages (
    id          INT          NOT NULL AUTO_INCREMENT,
    sender_id   INT          NOT NULL,
    receiver_id INT          NOT NULL,
    content     TEXT         NOT NULL,
    is_read     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP             DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_sender   (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_conv     (sender_id, receiver_id),
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 11. ŞİKAYETLER (reports)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE reports (
    id         INT NOT NULL AUTO_INCREMENT,
    user_id    INT NOT NULL,
    item_type  ENUM('question', 'answer', 'message') NOT NULL,
    item_id    INT NOT NULL,
    reason     VARCHAR(255) NOT NULL,
    status     ENUM('pending', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 12. ROZETLER (badges & user_badges)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE badges (
    id          INT          NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(50)  NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT         NOT NULL,
    icon        VARCHAR(50)  NOT NULL,
    color       VARCHAR(50)  NOT NULL DEFAULT 'text-primary',
    PRIMARY KEY (id),
    UNIQUE KEY uq_badge_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_badges (
    id         INT NOT NULL AUTO_INCREMENT,
    user_id    INT NOT NULL,
    badge_id   INT NOT NULL,
    awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_user_badge (user_id, badge_id),
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 13. POMODORO SEANSLARI (pomodoro_sessions)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE pomodoro_sessions (
    id               INT         NOT NULL AUTO_INCREMENT,
    user_id          INT         NOT NULL,
    duration_minutes INT         NOT NULL DEFAULT 25,
    mode             VARCHAR(20) NOT NULL DEFAULT 'pomodoro',
    created_at       TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_user_pomo (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 14. CEVAP YORUMLARI (answer_comments)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE answer_comments (
    id         INT  NOT NULL AUTO_INCREMENT,
    answer_id  INT  NOT NULL,
    user_id    INT  NOT NULL,
    content    TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_answer_id (answer_id),
    INDEX idx_user_id   (user_id),
    FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- BAŞLANGIÇ VERİLERİ (Seed Data)
-- ─────────────────────────────────────────────────────────────

-- Kategoriler
INSERT INTO categories (name, slug, icon, color) VALUES
('Matematik', 'matematik', 'bi-plus-slash-minus', '#0d6efd'),
('Fizik', 'fizik', 'bi-lightning-charge', '#ffc107'),
('Kimya', 'kimya', 'bi-reception-4', '#198754'),
('Biyoloji', 'biyoloji', 'bi-tree', '#20c997'),
('Türkçe', 'turkce', 'bi-type', '#dc3545'),
('Tarih', 'tarih', 'bi-hourglass-split', '#6f42c1'),
('Coğrafya', 'cografya', 'bi-globe', '#0dcaf0');

-- Rozetler
INSERT INTO badges (slug, name, description, icon, color) VALUES
('first_question', 'İlk Kıvılcım', 'İlk sorunu başarıyla sordun.', 'bi-lightbulb-fill', 'text-warning'),
('first_answer', 'Yardım Eli', 'İlk çözümünü paylaştın.', 'bi-pencil-fill', 'text-primary'),
('master_solver', 'Usta Çözücü', 'En az 3 çözümün soru sahibi tarafından en iyi cevap seçildi.', 'bi-trophy-fill', 'text-warning'),
('popular_author', 'Popüler Üye', 'Çözümlerin toplam 10 veya daha fazla beğeni aldı.', 'bi-heart-fill', 'text-danger'),
('night_owl', 'Gece Kuşu', 'Gece 00:00 ile 05:00 arasında soru veya çözüm paylaştın.', 'bi-moon-stars-fill', 'text-info'),
('math_whiz', 'Matematik Kurdu', 'Matematik branşında en az 3 çözüm paylaştın.', 'bi-calculator-fill', 'text-success'),
('science_guru', 'Fen Dehası', 'Fizik, Kimya veya Biyoloji branşlarında en az 3 çözüm paylaştın.', 'bi-radioactive', 'text-warning'),
('pomodoro_master', 'Odak Ustası', 'En az 5 Pomodoro çalışma seansı tamamladın.', 'bi-stopwatch-fill', 'text-danger'),
('centurion', '100\'ler Kulübü', '100 veya daha fazla puana ulaştın.', 'bi-lightning-charge-fill', 'text-warning'),
('bookworm', 'Kütüphaneci', 'En az 5 soruyu favorilerine ekledin.', 'bi-bookmarks-fill', 'text-primary');

-- Kullanıcılar (Şifre: password)
INSERT INTO users (username, email, password, role, bio, points) VALUES
('admin', 'admin@sorubankasi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Sistem Yöneticisi', 100),
('ahmet_hoca', 'hoca@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Matematik Öğretmeni', 50),
('elif_ogrenci', 'ogrenci@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'YKS Adayı', 25);

-- Örnek Etiketler
INSERT INTO tags (name) VALUES
('yks'), ('tyt'), ('ayt'), ('matematik'), ('fizik');

SET FOREIGN_KEY_CHECKS = 1;
