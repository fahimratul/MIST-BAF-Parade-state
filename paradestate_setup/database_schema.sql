-- Database schema for BAF Parade State Management System

-- Create database
CREATE DATABASE IF NOT EXISTS baf_parade_system;
USE baf_parade_system;

-- Officers table
CREATE TABLE officers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    rank VARCHAR(50) NOT NULL,
    department ENUM('CSE', 'EECE', 'ME', 'AE') NOT NULL,
    level ENUM('I', 'II', 'III', 'IV') NOT NULL,
    mess_location ENUM('MIST Dhaka Mess', 'MIST Mirpur Mess', 'BAF Base AKR') NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL DEFAULT 'Male',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Parade states table
CREATE TABLE parade_states (
    id INT PRIMARY KEY AUTO_INCREMENT,
    officer_id INT NOT NULL,
    parade_date DATE NOT NULL,
    status ENUM('Present', 'Leave', 'CMH', 'Sick Leave', 'SIQ', 'Isolation') NOT NULL DEFAULT 'Present',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date DATE DEFAULT NULL,
    FOREIGN KEY (officer_id) REFERENCES officers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_officer_date (officer_id, parade_date)
);

-- Reports table to store generated reports
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_date DATE NOT NULL,
    total_strength INT NOT NULL,
    total_on_parade INT NOT NULL,
    total_absent INT NOT NULL,
    total_female INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO officers (name, rank, department, level, mess_location, gender) VALUES ('Morshed', 'Flg Offr', 'CSE', 'III', 'MIST Dhaka Mess', 'Male')