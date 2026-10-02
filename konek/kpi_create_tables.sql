-- =====================================================
-- CREATE TABLES KPI UNTUK 3 UNIT SEKOLAH
-- SMA NEGARA CONTOH, Unit 1, Unit 2
-- Jalankan di phpMyAdmin > SQL tab
-- =====================================================

-- =====================================================
-- KPI GURU TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS kpi_guru_perspectives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_unit_sekolah (unit_sekolah),
    KEY idx_urutan (urutan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_guru_indicators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perspective_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_perspective_id (perspective_id),
    KEY idx_unit_sekolah (unit_sekolah),
    KEY idx_urutan (urutan),
    FOREIGN KEY (perspective_id) REFERENCES kpi_guru_perspectives(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_guru_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    indicator_id INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    kpi_range DECIMAL(5,2) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    catatan TEXT,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_eval (user_id, indicator_id, periode),
    KEY idx_user_id (user_id),
    KEY idx_indicator_id (indicator_id),
    KEY idx_periode (periode),
    KEY idx_unit_sekolah (unit_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_guru_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    validator_id INT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    validated_at DATETIME DEFAULT NULL,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_validation (user_id, periode),
    KEY idx_user_id (user_id),
    KEY idx_validator_id (validator_id),
    KEY idx_status (status),
    KEY idx_unit_sekolah (unit_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- KPI KEPSEK TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS kpi_kepsek_perspectives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_unit_sekolah (unit_sekolah),
    KEY idx_urutan (urutan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_kepsek_indicators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perspective_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_perspective_id (perspective_id),
    KEY idx_unit_sekolah (unit_sekolah),
    KEY idx_urutan (urutan),
    FOREIGN KEY (perspective_id) REFERENCES kpi_kepsek_perspectives(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_kepsek_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    indicator_id INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    kpi_range DECIMAL(5,2) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    catatan TEXT,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_eval (user_id, indicator_id, periode),
    KEY idx_user_id (user_id),
    KEY idx_indicator_id (indicator_id),
    KEY idx_periode (periode),
    KEY idx_unit_sekolah (unit_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_kepsek_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    validator_id INT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    validated_at DATETIME DEFAULT NULL,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_validation (user_id, periode),
    KEY idx_user_id (user_id),
    KEY idx_validator_id (validator_id),
    KEY idx_status (status),
    KEY idx_unit_sekolah (unit_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- KPI KAPROG TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS kpi_kaprog_perspectives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_unit_sekolah (unit_sekolah),
    KEY idx_urutan (urutan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_kaprog_indicators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perspective_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_perspective_id (perspective_id),
    KEY idx_unit_sekolah (unit_sekolah),
    KEY idx_urutan (urutan),
    FOREIGN KEY (perspective_id) REFERENCES kpi_kaprog_perspectives(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_kaprog_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    indicator_id INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    kpi_range DECIMAL(5,2) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    catatan TEXT,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_eval (user_id, indicator_id, periode),
    KEY idx_user_id (user_id),
    KEY idx_indicator_id (indicator_id),
    KEY idx_periode (periode),
    KEY idx_unit_sekolah (unit_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_kaprog_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    validator_id INT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    validated_at DATETIME DEFAULT NULL,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_validation (user_id, periode),
    KEY idx_user_id (user_id),
    KEY idx_validator_id (validator_id),
    KEY idx_status (status),
    KEY idx_unit_sekolah (unit_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- KPI WAKAKUR TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS kpi_wakakur_perspectives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_unit_sekolah (unit_sekolah),
    KEY idx_urutan (urutan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_wakakur_indicators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perspective_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_perspective_id (perspective_id),
    KEY idx_unit_sekolah (unit_sekolah),
    KEY idx_urutan (urutan),
    FOREIGN KEY (perspective_id) REFERENCES kpi_wakakur_perspectives(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_wakakur_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    indicator_id INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    kpi_range DECIMAL(5,2) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    catatan TEXT,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_eval (user_id, indicator_id, periode),
    KEY idx_user_id (user_id),
    KEY idx_indicator_id (indicator_id),
    KEY idx_periode (periode),
    KEY idx_unit_sekolah (unit_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_wakakur_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    validator_id INT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    validated_at DATETIME DEFAULT NULL,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_validation (user_id, periode),
    KEY idx_user_id (user_id),
    KEY idx_validator_id (validator_id),
    KEY idx_status (status),
    KEY idx_unit_sekolah (unit_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- KPI WAKASIS TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS kpi_wakasis_perspectives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_unit_sekolah (unit_sekolah),
    KEY idx_urutan (urutan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_wakasis_indicators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perspective_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_perspective_id (perspective_id),
    KEY idx_unit_sekolah (unit_sekolah),
    KEY idx_urutan (urutan),
    FOREIGN KEY (perspective_id) REFERENCES kpi_wakasis_perspectives(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_wakasis_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    indicator_id INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    kpi_range DECIMAL(5,2) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    catatan TEXT,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_eval (user_id, indicator_id, periode),
    KEY idx_user_id (user_id),
    KEY idx_indicator_id (indicator_id),
    KEY idx_periode (periode),
    KEY idx_unit_sekolah (unit_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kpi_wakasis_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    validator_id INT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    validated_at DATETIME DEFAULT NULL,
    unit_sekolah VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_validation (user_id, periode),
    KEY idx_user_id (user_id),
    KEY idx_validator_id (validator_id),
    KEY idx_status (status),
    KEY idx_unit_sekolah (unit_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- CREATE TABLES COMPLETE
-- =====================================================
-- Setelah ini, jalankan kpi_seed_units.sql untuk mengisi data
-- =====================================================
