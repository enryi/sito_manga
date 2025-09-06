CREATE DATABASE IF NOT EXISTS manga;
USE manga;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0
);
CREATE TABLE manga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    author VARCHAR(255) NOT NULL,
    type ENUM('Manga', 'Manwha', 'Manhua') NOT NULL,
    genre VARCHAR(255) NOT NULL,
    approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE lista_utente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    manga_id INT NOT NULL,
    rating FLOAT CHECK (rating >= 0 AND rating <= 10),
    chapters INT DEFAULT 0,
    status ENUM('reading', 'completed', 'dropped', 'plan_to_read') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (manga_id) REFERENCES manga(id) ON DELETE CASCADE
);
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    manga_id INT,
    manga_title VARCHAR(255),
    type ENUM('approval', 'disapproval') NOT NULL,
    message TEXT NOT NULL,
    reason TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created_at (created_at)
);

-- Aggiungi la colonna submitted_by alla tabella manga per tracciare chi ha aggiunto il manga
ALTER TABLE manga ADD COLUMN submitted_by INT DEFAULT NULL;
ALTER TABLE manga ADD FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL;