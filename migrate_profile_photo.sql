-- Run this SQL to add profile photo support to existing database
ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL AFTER role;
