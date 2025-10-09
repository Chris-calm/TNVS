-- Database updates for Settings.php functionality
-- Run these SQL commands in your database to support the new features

-- Add email and profile_picture columns to users table (if they don't exist)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS email VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL;

-- Create user_settings table for storing user preferences
CREATE TABLE IF NOT EXISTS user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    setting_name VARCHAR(100) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_setting (user_id, setting_name)
);

-- Insert default settings for existing users
INSERT IGNORE INTO user_settings (user_id, setting_name, setting_value)
SELECT id, 'theme', 'light' FROM users;

INSERT IGNORE INTO user_settings (user_id, setting_name, setting_value)
SELECT id, 'language', 'en' FROM users;

INSERT IGNORE INTO user_settings (user_id, setting_name, setting_value)
SELECT id, 'timezone', 'Asia/Manila' FROM users;

INSERT IGNORE INTO user_settings (user_id, setting_name, setting_value)
SELECT id, 'email_notifications', '1' FROM users;

INSERT IGNORE INTO user_settings (user_id, setting_name, setting_value)
SELECT id, 'push_notifications', '1' FROM users;

-- Create exports directory table to track exports
CREATE TABLE IF NOT EXISTS data_exports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    export_type VARCHAR(50) NOT NULL,
    file_size INT DEFAULT 0,
    exported_by INT NOT NULL,
    export_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exported_by) REFERENCES users(id)
);

-- Create system_logs table for tracking dangerous operations
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
