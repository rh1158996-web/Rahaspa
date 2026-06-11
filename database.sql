-- Raha Spa (formerly Serenity Spa) Database Schema
CREATE DATABASE IF NOT EXISTS serenity_spa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE serenity_spa;

-- SETTINGS TABLE
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL
);

-- ADMINS TABLE
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- USERS TABLE (Bilingual & Verification)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    father_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_email_verified TINYINT(1) DEFAULT 0,
    is_phone_verified TINYINT(1) DEFAULT 0,
    email_token VARCHAR(10) NULL,
    otp_code VARCHAR(10) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- BRANCHES TABLE
CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(100) NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    address_en TEXT,
    address_ar TEXT,
    map_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- WORKING HOURS TABLE
CREATE TABLE IF NOT EXISTS working_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    day_of_week TINYINT NOT NULL, -- 0=Sunday, 1=Monday, ..., 6=Saturday
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

-- BLOCKED DAYS TABLE (Holidays/Maintenance)
CREATE TABLE IF NOT EXISTS blocked_days (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    blocked_date DATE NOT NULL,
    reason VARCHAR(255),
    UNIQUE KEY unique_branch_date (branch_id, blocked_date),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

-- SERVICES TABLE (Bilingual)
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(100) NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    description_en TEXT,
    description_ar TEXT,
    duration_minutes INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- OFFERS TABLE (Bilingual)
CREATE TABLE IF NOT EXISTS offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(100) NOT NULL,
    title_ar VARCHAR(100) NOT NULL,
    description_en TEXT,
    description_ar TEXT,
    discount_percentage INT NOT NULL,
    image_url VARCHAR(255),
    valid_until DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- BOOKINGS TABLE
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    branch_id INT NOT NULL,
    service_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Rejected') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- PAYMENTS TABLE (Supports Stripe & Cash)
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    stripe_session_id VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'SAR',
    status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- REVIEWS TABLE
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- INSERT DEFAULT ADMIN (Password: admin123)
INSERT IGNORE INTO admins (username, password_hash) VALUES ('admin', '$2y$10$w8.b1W7Ea5s7UOf8S.sJme2Cj2QxT1qIq3x9Iu2YqJ7xQJz8v7V8i');

-- INSERT DEFAULT SETTINGS
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('site_name_en', 'Raha Spa'),
('site_name_ar', 'رها سبا'),
('contact_email', 'info@rahaspa.com'),
('contact_phone', '+966 50 000 0000');
