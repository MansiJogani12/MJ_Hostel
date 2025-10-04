-- MJ Hostel Database Setup
-- Run this SQL in phpMyAdmin or MySQL command line

-- Create database
CREATE DATABASE IF NOT EXISTS mj_hostel;
USE mj_hostel;

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    hostel VARCHAR(20) NOT NULL,
    registration_step INT DEFAULT 1,
    registration_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create parent_info table
CREATE TABLE IF NOT EXISTS parent_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    father_name VARCHAR(100),
    mother_name VARCHAR(100),
    father_phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Create addresses table
CREATE TABLE IF NOT EXISTS addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    line1 VARCHAR(255),
    line2 VARCHAR(255),
    city VARCHAR(100),
    taluka VARCHAR(100),
    pincode VARCHAR(10),
    district VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100) DEFAULT 'India',
    room_number INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Create login_logs table (optional - for tracking login history)
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT
);

-- Insert sample data (optional - you can remove this)
-- Password for demo accounts is 'password'
INSERT INTO students (user_id, name, email, phone, password, hostel, registration_completed) VALUES
('demo1', 'Demo Student 1', 'demo1@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hostel1', TRUE),
('demo2', 'Demo Student 2', 'demo2@example.com', '9876543211', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hostel2', TRUE);

-- Simple test accounts with plain text passwords for easy testing
INSERT INTO students (user_id, name, email, phone, password, hostel, registration_completed) VALUES
('admin', 'Admin User', 'admin@mjhostel.com', '9876543210', 'admin123', 'hostel1', TRUE),
('student1', 'Test Student 1', 'student1@example.com', '9876543211', 'pass123', 'hostel2', TRUE),
('user1', 'Sample User', 'user1@example.com', '9876543212', '123456', 'hostel3', TRUE);

-- Show tables
SHOW TABLES;
DESCRIBE students;