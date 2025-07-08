-- Database: taskmanager_cms
CREATE DATABASE IF NOT EXISTS taskmanager_cms;
USE taskmanager_cms;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'manager') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Tasks
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    assigned_to INT,
    created_by INT,
    due_date DATE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default users
-- Password untuk semua user: password123
INSERT INTO users (username, email, password, full_name, role) VALUES 
('manager', 'manager@taskmanager.com', '$2y$12$/RloE1QmO4EbRzh2H8DeLO/6.0./jjCVe07dOEtw8LMyiN04SAE0K', 'Manager Utama', 'manager'),
('john_doe', 'john@taskmanager.com', '$2y$12$/RloE1QmO4EbRzh2H8DeLO/6.0./jjCVe07dOEtw8LMyiN04SAE0K', 'John Doe', 'user'),
('jane_smith', 'jane@taskmanager.com', '$2y$12$/RloE1QmO4EbRzh2H8DeLO/6.0./jjCVe07dOEtw8LMyiN04SAE0K', 'Jane Smith', 'user');

-- Insert sample tasks
INSERT INTO tasks (title, description, status, priority, assigned_to, created_by, due_date) VALUES 
('Setup Database', 'Membuat dan mengkonfigurasi database untuk aplikasi task manager', 'completed', 'high', 2, 1, '2024-01-15'),
('Design UI Dashboard', 'Mendesain interface user untuk dashboard task manager', 'in_progress', 'medium', 3, 1, '2024-01-20'),
('Implementasi Login System', 'Membuat sistem autentikasi untuk user dan manager', 'pending', 'high', 2, 1, '2024-01-25'),
('Testing Aplikasi', 'Melakukan testing menyeluruh pada semua fitur aplikasi', 'pending', 'medium', 3, 1, '2024-01-30'); 