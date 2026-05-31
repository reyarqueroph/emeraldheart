-- ============================================
-- PRU LIFE U.K. Database Schema
-- Database: pru_life_db
-- ============================================

CREATE DATABASE IF NOT EXISTS pru_life_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pru_life_db;

-- Users table (admins and agents)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_code VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    position VARCHAR(100) DEFAULT 'Agent',
    role ENUM('admin','agent') DEFAULT 'agent',
    status ENUM('active','pending','inactive') DEFAULT 'pending',
    last_active DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL,
    sub_category VARCHAR(100) DEFAULT '',
    payment_type ENUM('Regular','Limited','Single') DEFAULT 'Regular',
    age_range VARCHAR(100) DEFAULT '7 days to 70 years old',
    min_premium_monthly DECIMAL(12,2) DEFAULT 0.00,
    description TEXT,
    primer_file VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Password requests table
CREATE TABLE IF NOT EXISTS password_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    agent_code VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    status ENUM('pending','approved','declined') DEFAULT 'pending',
    processed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Feedbacks table
CREATE TABLE IF NOT EXISTS feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending','replied') DEFAULT 'pending',
    admin_reply TEXT DEFAULT NULL,
    replied_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- Default admin accounts
-- ============================================

-- Account 1: System Administrator
-- Username: admin | Password: admin123
INSERT IGNORE INTO users (agent_code, username, email, password, full_name, position, role, status)
VALUES (
    'ADMIN001',
    'admin',
    'admin@prulifeuk.com.ph',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'System Administrator',
    'Admin',
    'admin',
    'active'
);

-- Account 2: eHeart Admin
-- Username: eheart_admin | Password: eHeart@2024!
-- Run reset_admin.php to regenerate hash if needed
INSERT IGNORE INTO users (agent_code, username, email, password, full_name, position, role, status)
VALUES (
    'EHEART001',
    'eheart_admin',
    'eheart@prulifeuk.com.ph',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'eHeart Administrator',
    'Admin',
    'admin',
    'active'
);

-- Sample products
INSERT IGNORE INTO products (product_name, category, sub_category, payment_type, age_range, min_premium_monthly, description) VALUES
('PRULink Assurance Account', 'VUL', 'Investment-Linked', 'Regular', '1 month to 70 years old', 3000.00, 'A variable unit-linked life insurance plan that combines life protection with investment opportunities.'),
('PRULife Your Term', 'Traditional Life Insurance', 'Term Insurance', 'Regular', '18 to 65 years old', 500.00, 'Affordable term life insurance providing pure protection for a specified period.'),
('PRUHealth', 'Traditional Life Insurance', 'Health Insurance', 'Regular', '1 month to 65 years old', 1500.00, 'Comprehensive health insurance coverage for hospitalization and medical expenses.'),
('PRUPersonal Accident', 'Stand-Alone Product', 'Accident Coverage', 'Regular', '18 to 65 years old', 300.00, 'Personal accident insurance providing coverage for accidental death and disability.'),
('PRULink Exact Protector', 'VUL', 'Investment-Linked', 'Regular', '1 month to 65 years old', 5000.00, 'A VUL plan with guaranteed death benefit and investment component.'),
('PRULife Endowment', 'Traditional Life Insurance', 'Endowment', 'Limited', '1 month to 60 years old', 2000.00, 'An endowment plan that provides life protection and savings in one package.'),
-- Product Guides
('Product Placemat – Choosing the Pru Life UK Product Solution', 'Product Guides', 'Product Placemat', 'Regular', 'All ages', 0.00, 'A quick-reference guide for choosing the right Pru Life UK product solution based on client needs — covering Protection, Critical Illness, Retirement, Education, Medium- to Long-term Goals, Short-term Goals, and Diversification of Investment.'),
('PRULink Product Specification Guide (PSG) – August 2025', 'Product Guides', 'Product Specification Guide', 'Regular', '0 – 70 years old', 0.00, 'Complete product specification guide covering all Investment-Linked and Traditional products. PSG as of August 2025.'),
('Accelerated Total and Permanent Disability (ATPD) – Benefits and Limitations', 'Product Guides', 'Rider Guide', 'Regular', 'All ages', 0.00, 'Detailed guide on the ATPD benefit — covering qualifying conditions, activities of daily living criteria, and exclusions.');
