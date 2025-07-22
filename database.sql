CREATE DATABASE IF NOT EXISTS dokumenty_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dokumenty_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_code VARCHAR(10) NOT NULL,
    shelf_type ENUM('civil', 'criminal') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_location_shelf (location_code, shelf_type)
);

CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_id INT,
    barcode VARCHAR(50) UNIQUE NOT NULL,
    defendant_name VARCHAR(255) NOT NULL,
    plaintiff_name VARCHAR(255) NOT NULL,
    case_number VARCHAR(100) NOT NULL,
    case_type ENUM('civil', 'criminal') NOT NULL,
    is_archived BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    archived_at TIMESTAMP NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id)
);

INSERT INTO users (username, password, is_admin) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE),
('user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE); 