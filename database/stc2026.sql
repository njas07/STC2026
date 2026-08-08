-- ============================================================
-- STC 2026 - Database Schema
-- STIBAJRA Technology Competition 2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS stc2026 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE stc2026;

-- ============================================================
-- TABLE: users (Peserta / Panitia / Admin)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('peserta','panitia','admin') NOT NULL DEFAULT 'peserta',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: announcements
-- ============================================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    content TEXT,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: registrations (data pendaftaran per lomba)
-- Data umum + JSON detail data lomba & tim disimpan di `data`
-- ============================================================
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,          -- STC26-00001
    user_id INT NOT NULL,
    competition VARCHAR(80) NOT NULL,          -- web_design / infografis / excel / speed_typing / mobile_legends / free_fire / cerdas_cermat
    name VARCHAR(150) NOT NULL,
    student_id VARCHAR(50) DEFAULT NULL,
    school_name VARCHAR(200) DEFAULT NULL,
    school_class VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    -- Data khusus per lomba (team, role, experience, dll) JSON
    data JSON DEFAULT NULL,
    -- Kartu pelajar
    student_card VARCHAR(255) DEFAULT NULL,
    -- Instagram follow
    instagram_school VARCHAR(100) DEFAULT NULL,
    instagram_school_proof VARCHAR(255) DEFAULT NULL,
    instagram_stc VARCHAR(100) DEFAULT NULL,
    instagram_stc_proof VARCHAR(255) DEFAULT NULL,
    -- Pembayaran
    payment_method VARCHAR(50) DEFAULT NULL,
    amount VARCHAR(30) DEFAULT NULL,
    transaction_date DATE DEFAULT NULL,
    payment_proof VARCHAR(255) DEFAULT NULL,
    -- Status
    status ENUM('PENDING','VERIFIED','REJECTED','PAID') NOT NULL DEFAULT 'PENDING',
    reject_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- AUTO SEED: admin & panitia & peserta contoh
-- Password semua contoh: password123 (hash bcrypt)
-- ============================================================
INSERT INTO users (name, email, phone, password, role) VALUES
('Admin STC', 'admin@stc2026.id', '081200000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Panitia STC', 'panitia@stc2026.id', '081200000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'panitia'),
('Budi Santoso', 'budi@student.id', '081200000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'peserta');

