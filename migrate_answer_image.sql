-- Mevcut veritabanına cevap resmi desteği ekler
-- Eğer veritabanın zaten varsa bu migration'ı çalıştır
ALTER TABLE answers ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER content;
