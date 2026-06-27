-- ============================================================
-- JARU PORTFOLIO — MySQL Database Schema
-- Run this in phpMyAdmin or via CLI: mysql -u root < jaru.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS jaru_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jaru_db;

-- ------------------------------------------------------------
-- USERS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(120) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('user','admin') NOT NULL DEFAULT 'user',
    avatar      VARCHAR(255) DEFAULT NULL,
    bio         TEXT DEFAULT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin account — password: Admin@Jaru2026
INSERT INTO users (username, email, password, role) VALUES (
    'admin',
    'admin@jaru.local',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
);

-- ------------------------------------------------------------
-- MESSAGES (Contact / Ask Me submissions)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED DEFAULT NULL,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(120) NOT NULL,
    subject     VARCHAR(255) NOT NULL DEFAULT 'General',
    body        TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- PROJECTS (Portfolio CRUD)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    tech_stack  VARCHAR(255) DEFAULT NULL,
    image       VARCHAR(255) DEFAULT NULL,
    project_url VARCHAR(500) DEFAULT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed projects
INSERT INTO projects (title, description, tech_stack, is_featured, sort_order) VALUES
('Personal Portfolio', 'My personal portfolio website built with HTML, CSS, and JavaScript. Dynamic version powered by PHP + MySQL.', 'HTML, CSS, JS, PHP, MySQL', 1, 1),
('BSCS Coursework Projects', 'Collection of laboratory activities and projects from PUP Computer Science curriculum.', 'Python, Java, C', 0, 2);

-- ------------------------------------------------------------
-- SITE SETTINGS (Key-Value store)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key   VARCHAR(100) PRIMARY KEY,
    setting_value TEXT DEFAULT NULL,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_title', 'Jaru — John Ronie Ramiro'),
('maintenance_mode', '0'),
('allow_registration', '1');

-- ------------------------------------------------------------
-- QUIZ ATTEMPTS (Creative Signup tracking)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS quiz_attempts (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(120) NOT NULL,
    score       TINYINT NOT NULL DEFAULT 0,
    ip_address  VARCHAR(45) DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- SESSIONS (DB-backed optional — using PHP file sessions is fine too)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_sessions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    session_token VARCHAR(128) NOT NULL UNIQUE,
    ip_address  VARCHAR(45) DEFAULT NULL,
    user_agent  VARCHAR(500) DEFAULT NULL,
    last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
