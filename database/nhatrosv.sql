CREATE DATABASE nhatrosv;
USE nhatrosv;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('admin', 'landlord', 'student') NOT NULL,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add avatar column to users table if it doesn't exist
ALTER TABLE users
ADD COLUMN IF NOT EXISTS avatar varchar(255) DEFAULT NULL AFTER phone;

-- Rooms table
CREATE TABLE rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    landlord_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    area FLOAT NOT NULL,
    address VARCHAR(255) NOT NULL,
    district VARCHAR(100) NOT NULL,
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    status ENUM('available', 'rented', 'pending', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES users(id)
);

-- Room amenities table
CREATE TABLE amenities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT,
    has_ac BOOLEAN DEFAULT FALSE,
    has_parking BOOLEAN DEFAULT FALSE,
    has_security BOOLEAN DEFAULT FALSE,
    has_washing_machine BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT,
    user_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Thêm cột reply vào bảng reviews
ALTER TABLE reviews ADD COLUMN reply TEXT DEFAULT NULL;

-- Table for tracking room views
CREATE TABLE room_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT,
    viewer_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- Thêm bảng room_images
CREATE TABLE room_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT,
    image_path VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Bảng lưu phòng yêu thích
CREATE TABLE saved_rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    room_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    UNIQUE KEY unique_save (user_id, room_id)
);

-- Bảng khu vực yêu thích
CREATE TABLE favorite_areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    district VARCHAR(100),
    last_notification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_favorite (user_id, district)
);

-- Thêm tài khoản admin mặc định
INSERT INTO users (username, email, password, role, status) 
VALUES (
    'admin',
    'admin@nhatrosv.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'admin',
    'active'
);

-- Cập nhật bảng amenities với thêm tiện ích mới
ALTER TABLE amenities ADD COLUMN has_wifi BOOLEAN DEFAULT FALSE;
ALTER TABLE amenities ADD COLUMN has_fridge BOOLEAN DEFAULT FALSE;
ALTER TABLE amenities ADD COLUMN has_kitchen BOOLEAN DEFAULT FALSE;
ALTER TABLE amenities ADD COLUMN has_private_wc BOOLEAN DEFAULT FALSE;
ALTER TABLE amenities ADD COLUMN has_window BOOLEAN DEFAULT FALSE;
ALTER TABLE amenities ADD COLUMN has_balcony BOOLEAN DEFAULT FALSE;
ALTER TABLE amenities ADD COLUMN has_bed BOOLEAN DEFAULT FALSE;
ALTER TABLE amenities ADD COLUMN has_wardrobe BOOLEAN DEFAULT FALSE;
ALTER TABLE amenities ADD COLUMN has_tv BOOLEAN DEFAULT FALSE;

-- Bảng đặt lịch xem phòng
CREATE TABLE viewing_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT,
    student_id INT,
    landlord_id INT,
    viewing_date DATE,
    viewing_time TIME,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (landlord_id) REFERENCES users(id)
);


