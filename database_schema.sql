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
INSERT INTO officers (name, rank, department, level, mess_location, gender) VALUES
-- CSE Department
('Officer Ahmed', 'Flg Offr', 'CSE', 'II', 'MIST Dhaka Mess', 'Male'),
('Officer Rahman', 'Flg Offr', 'CSE', 'II', 'MIST Dhaka Mess', 'Male'),
('Officer Khan', 'Flg Offr', 'CSE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Ali', 'Flg Offr', 'CSE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Hassan', 'Flg Offr', 'CSE', 'IV', 'MIST Dhaka Mess', 'Male'),
('Officer Hossain', 'Flg Offr', 'CSE', 'IV', 'MIST Dhaka Mess', 'Male'),
('Officer Karim', 'Flg Offr', 'CSE', 'IV', 'MIST Dhaka Mess', 'Male'),

-- EECE Department
('Officer Sultana', 'Flg Offr', 'EECE', 'II', 'MIST Mirpur Mess', 'Female'),
('Officer Begum', 'Flg Offr', 'EECE', 'II', 'MIST Mirpur Mess', 'Female'),
('Officer Mahmud', 'Flg Offr', 'EECE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Farid', 'Flg Offr', 'EECE', 'III', 'BAF Base AKR', 'Male'),
('Officer Islam', 'Flg Offr', 'EECE', 'III', 'BAF Base AKR', 'Male'),
('Officer Akter', 'Flg Offr', 'EECE', 'IV', 'MIST Dhaka Mess', 'Male'),
('Officer Uddin', 'Flg Offr', 'EECE', 'IV', 'MIST Dhaka Mess', 'Male'),
('Officer Sharif', 'Flg Offr', 'EECE', 'IV', 'MIST Dhaka Mess', 'Male'),
('Officer Rashid', 'Flg Offr', 'EECE', 'IV', 'MIST Dhaka Mess', 'Male'),

-- ME Department
('Officer Nasir', 'Flg Offr', 'ME', 'II', 'MIST Mirpur Mess', 'Male'),
('Officer Aziz', 'Flg Offr', 'ME', 'II', 'MIST Mirpur Mess', 'Male'),
('Officer Kabir', 'Flg Offr', 'ME', 'II', 'MIST Mirpur Mess', 'Male'),
('Officer Reza', 'Flg Offr', 'ME', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Salam', 'Flg Offr', 'ME', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Matin', 'Flg Offr', 'ME', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Hasan', 'Flg Offr', 'ME', 'III', 'MIST Mirpur Mess', 'Male'),
('Officer Sharmin', 'Flg Offr', 'ME', 'IV', 'MIST Dhaka Mess', 'Female'),
('Officer Nahian', 'Flg Offr', 'ME', 'IV', 'MIST Dhaka Mess', 'Male'),
('Officer Alvi', 'Flg Offr', 'ME', 'IV', 'MIST Dhaka Mess', 'Male'),
('Officer Marzan', 'Flg Offr', 'ME', 'IV', 'MIST Dhaka Mess', 'Male'),
('Officer Rahim', 'Flg Offr', 'ME', 'IV', 'MIST Dhaka Mess', 'Male'),

-- AE Department (largest department)
('Officer Shakib', 'Flg Offr', 'AE', 'II', 'MIST Dhaka Mess', 'Male'),
('Officer Tamim', 'Flg Offr', 'AE', 'II', 'MIST Dhaka Mess', 'Male'),
('Officer Mushfiq', 'Flg Offr', 'AE', 'II', 'MIST Dhaka Mess', 'Male'),
('Officer Mahmudullah', 'Flg Offr', 'AE', 'II', 'MIST Dhaka Mess', 'Male'),
('Officer Mortaza', 'Flg Offr', 'AE', 'II', 'MIST Dhaka Mess', 'Male'),
('Officer Razzaq', 'Flg Offr', 'AE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Akram', 'Flg Offr', 'AE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Murad', 'Flg Offr', 'AE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Kamal', 'Flg Offr', 'AE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Jamal', 'Flg Offr', 'AE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Fahim', 'Flg Offr', 'AE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Rafi', 'Flg Offr', 'AE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Sohel', 'Flg Offr', 'AE', 'III', 'MIST Dhaka Mess', 'Male'),
('Officer Farhana', 'Flg Offr', 'AE', 'III', 'BAF Base AKR', 'Female'),
('Officer Nasreen', 'Flg Offr', 'AE', 'III', 'BAF Base AKR', 'Female');

-- Add more AE Level IV officers to match the report
INSERT INTO officers (name, rank, department, level, mess_location, gender) VALUES
('Officer Nahian', 'Flg Offr', 'AE', 'IV', 'MIST Mirpur Mess', 'Male'),
('Officer Alvi', 'Flg Offr', 'AE', 'IV', 'MIST Mirpur Mess', 'Male'),
('Officer Marzan', 'Flg Offr', 'AE', 'IV', 'MIST Mirpur Mess', 'Male'),
('Officer Sharmin', 'Flg Offr', 'AE', 'IV', 'MIST Mirpur Mess', 'Female');

-- Add more officers to reach the required numbers...