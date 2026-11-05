-- BrainBoost timed mock exam tables. Safe to execute more than once.
CREATE TABLE IF NOT EXISTS mock_exam_questions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 subject VARCHAR(100) NOT NULL,
 topic VARCHAR(150) DEFAULT NULL,
 question_text TEXT NOT NULL,
 option_a TEXT NOT NULL, option_b TEXT NOT NULL,
 option_c TEXT NOT NULL, option_d TEXT NOT NULL,
 option_e TEXT DEFAULT NULL,
 correct_option ENUM('A','B','C','D','E') NOT NULL,
 explanation TEXT DEFAULT NULL,
 difficulty ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
 created_by INT DEFAULT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_mock_exam_subject (subject),
 INDEX idx_mock_exam_active (is_active),
 CONSTRAINT fk_mock_exam_question_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mock_exam_attempts (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 title VARCHAR(180) NOT NULL DEFAULT 'Genel Deneme',
 subject VARCHAR(100) DEFAULT NULL,
 question_count INT NOT NULL,
 duration_minutes INT NOT NULL,
 correct_count INT NOT NULL DEFAULT 0,
 wrong_count INT NOT NULL DEFAULT 0,
 blank_count INT NOT NULL DEFAULT 0,
 net_score DECIMAL(7,2) NOT NULL DEFAULT 0,
 score_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
 status ENUM('in_progress','completed') NOT NULL DEFAULT 'in_progress',
 started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 submitted_at DATETIME DEFAULT NULL,
 INDEX idx_mock_attempt_user (user_id, started_at),
 CONSTRAINT fk_mock_attempt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mock_exam_attempt_answers (
 id INT AUTO_INCREMENT PRIMARY KEY,
 attempt_id INT NOT NULL,
 question_id INT DEFAULT NULL,
 question_text_snapshot TEXT NOT NULL,
 option_a_snapshot TEXT NOT NULL, option_b_snapshot TEXT NOT NULL,
 option_c_snapshot TEXT NOT NULL, option_d_snapshot TEXT NOT NULL,
 option_e_snapshot TEXT DEFAULT NULL,
 correct_option_snapshot ENUM('A','B','C','D','E') NOT NULL,
 explanation_snapshot TEXT DEFAULT NULL,
 selected_option ENUM('A','B','C','D','E') DEFAULT NULL,
 is_correct TINYINT(1) NOT NULL DEFAULT 0,
 INDEX idx_mock_attempt_answer (attempt_id),
 CONSTRAINT fk_mock_attempt_answer_attempt FOREIGN KEY (attempt_id) REFERENCES mock_exam_attempts(id) ON DELETE CASCADE,
 CONSTRAINT fk_mock_attempt_answer_question FOREIGN KEY (question_id) REFERENCES mock_exam_questions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
