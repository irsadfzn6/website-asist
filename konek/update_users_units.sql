-- =====================================================
-- Update unit_sekolah & admin multi-sekolah
-- Jalankan di phpMyAdmin > SQL tab
-- =====================================================

-- Yayasan level: lihat semua sekolah (unit_sekolah NULL)
UPDATE users SET unit_sekolah = NULL
WHERE level IN ('yayasan', 'ketua_yayasan', 'pembina_yayasan', 'keuangan');

-- Admin per sekolah (3 unit)
UPDATE users SET username = 'admin_wb1', nama = 'Admin WB 1', unit_sekolah = 'wb_1'
WHERE id_user = 1 AND level = 'admin';

INSERT INTO users (username, password, nama, unit_sekolah, level)
SELECT 'admin_wb2', 'demo1234', 'Admin WB 2', 'wb_2', 'admin'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin_wb2');

INSERT INTO users (username, password, nama, unit_sekolah, level)
SELECT 'admin_wb3', 'demo1234', 'Admin WB 3', 'wb_3', 'admin'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin_wb3');

-- Admin yayasan: lihat semua sekolah (unit_sekolah NULL)
INSERT INTO users (username, password, nama, unit_sekolah, level)
SELECT 'admin_yayasan', 'demo1234', 'Admin Yayasan', NULL, 'admin'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin_yayasan');
