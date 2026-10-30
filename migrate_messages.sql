-- Özel Mesajlaşma Sistemi — Migration
-- Mevcut kurulumlar için çalıştırın

CREATE TABLE IF NOT EXISTS messages (
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
