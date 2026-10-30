ALTER TABLE questions ADD COLUMN moderation_status ENUM('approved','review','rejected') NOT NULL DEFAULT 'approved';
ALTER TABLE answers ADD COLUMN moderation_status ENUM('approved','review','rejected') NOT NULL DEFAULT 'approved';
CREATE TABLE IF NOT EXISTS moderation_log (id INT AUTO_INCREMENT PRIMARY KEY, content_type ENUM('question','answer') NOT NULL, content_id INT NOT NULL, status ENUM('approved','review','rejected') NOT NULL, reason TEXT, confidence INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_content (content_type, content_id), INDEX idx_status (status));
