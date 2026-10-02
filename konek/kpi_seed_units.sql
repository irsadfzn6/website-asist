-- =====================================================
-- SEED DATA KPI UNTUK 3 UNIT SEKOLAH
-- WB 1, WB 2, WB 3
-- Jalankan di phpMyAdmin > SQL tab
-- =====================================================

-- =====================================================
-- KPI GURU - Perspectives & Indicators
-- =====================================================

-- UNIT: wb_2
INSERT INTO kpi_guru_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Pedagogik', 'Deskripsi perspektif 1', 1, 'wb_2', NOW()),
('Kepribadian', 'Deskripsi perspektif 2', 2, 'wb_2', NOW()),
('Sosial', 'Deskripsi perspektif 3', 3, 'wb_2', NOW()),
('Profesional', 'Deskripsi perspektif 4', 4, 'wb_2', NOW()),
('Out Put Laporan', 'Deskripsi perspektif 5', 5, 'wb_2', NOW()),
('Kehadiran', 'Deskripsi perspektif 6', 6, 'wb_2', NOW()),
('Kepatuhan', 'Deskripsi perspektif 7', 7, 'wb_2', NOW());

-- Indicators untuk wb_2 (perspective_id perlu disesuaikan setelah insert)
-- NOTE: Setelah insert perspectives, dapatkan ID nya lalu update indicators

-- =====================================================
-- UNIT: wb_1
INSERT INTO kpi_guru_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Pedagogik', 'Deskripsi perspektif 1', 1, 'wb_1', NOW()),
('Kepribadian', 'Deskripsi perspektif 2', 2, 'wb_1', NOW()),
('Sosial', 'Deskripsi perspektif 3', 3, 'wb_1', NOW()),
('Profesional', 'Deskripsi perspektif 4', 4, 'wb_1', NOW()),
('Out Put Laporan', 'Deskripsi perspektif 5', 5, 'wb_1', NOW()),
('Kehadiran', 'Deskripsi perspektif 6', 6, 'wb_1', NOW()),
('Kepatuhan', 'Deskripsi perspektif 7', 7, 'wb_1', NOW());

-- =====================================================
-- UNIT: wb_3
INSERT INTO kpi_guru_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Pedagogik', 'Deskripsi perspektif 1', 1, 'wb_3', NOW()),
('Kepribadian', 'Deskripsi perspektif 2', 2, 'wb_3', NOW()),
('Sosial', 'Deskripsi perspektif 3', 3, 'wb_3', NOW()),
('Profesional', 'Deskripsi perspektif 4', 4, 'wb_3', NOW()),
('Out Put Laporan', 'Deskripsi perspektif 5', 5, 'wb_3', NOW()),
('Kehadiran', 'Deskripsi perspektif 6', 6, 'wb_3', NOW()),
('Kepatuhan', 'Deskripsi perspektif 7', 7, 'wb_3', NOW());

-- =====================================================
-- KPI KEPSEK - Perspectives
-- =====================================================

-- UNIT: wb_2
INSERT INTO kpi_kepsek_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Manajerial', 'Manajemen sekolah', 1, 'wb_2', NOW()),
('Kepemimpinan', 'Kepemimpinan pendidikan', 2, 'wb_2', NOW()),
('Pengembangan', 'Pengembangan sekolah', 3, 'wb_2', NOW());

-- UNIT: wb_1
INSERT INTO kpi_kepsek_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Manajerial', 'Manajemen sekolah', 1, 'wb_1', NOW()),
('Kepemimpinan', 'Kepemimpinan pendidikan', 2, 'wb_1', NOW()),
('Pengembangan', 'Pengembangan sekolah', 3, 'wb_1', NOW());

-- UNIT: wb_3
INSERT INTO kpi_kepsek_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Manajerial', 'Manajemen sekolah', 1, 'wb_3', NOW()),
('Kepemimpinan', 'Kepemimpinan pendidikan', 2, 'wb_3', NOW()),
('Pengembangan', 'Pengembangan sekolah', 3, 'wb_3', NOW());

-- =====================================================
-- KPI KAPROG - Perspectives
-- =====================================================

-- UNIT: wb_2
INSERT INTO kpi_kaprog_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Perencanaan Program', 'Perencanaan kegiatan program', 1, 'wb_2', NOW()),
('Pelaksanaan Program', 'Pelaksanaan kegiatan', 2, 'wb_2', NOW()),
('Evaluasi Program', 'Evaluasi hasil program', 3, 'wb_2', NOW());

-- UNIT: wb_1
INSERT INTO kpi_kaprog_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Perencanaan Program', 'Perencanaan kegiatan program', 1, 'wb_1', NOW()),
('Pelaksanaan Program', 'Pelaksanaan kegiatan', 2, 'wb_1', NOW()),
('Evaluasi Program', 'Evaluasi hasil program', 3, 'wb_1', NOW());

-- UNIT: wb_3
INSERT INTO kpi_kaprog_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Perencanaan Program', 'Perencanaan kegiatan program', 1, 'wb_3', NOW()),
('Pelaksanaan Program', 'Pelaksanaan kegiatan', 2, 'wb_3', NOW()),
('Evaluasi Program', 'Evaluasi hasil program', 3, 'wb_3', NOW());

-- =====================================================
-- KPI WAKAKUR - Perspectives
-- =====================================================

-- UNIT: wb_2
INSERT INTO kpi_wakakur_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Kurikulum', 'Pengembangan kurikulum', 1, 'wb_2', NOW()),
('Evaluasi Pembelajaran', 'Evaluasi hasil belajar', 2, 'wb_2', NOW());

-- UNIT: wb_1
INSERT INTO kpi_wakakur_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Kurikulum', 'Pengembangan kurikulum', 1, 'wb_1', NOW()),
('Evaluasi Pembelajaran', 'Evaluasi hasil belajar', 2, 'wb_1', NOW());

-- UNIT: wb_3
INSERT INTO kpi_wakakur_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Kurikulum', 'Pengembangan kurikulum', 1, 'wb_3', NOW()),
('Evaluasi Pembelajaran', 'Evaluasi hasil belajar', 2, 'wb_3', NOW());

-- =====================================================
-- KPI WAKASIS - Perspectives
-- =====================================================

-- UNIT: wb_2
INSERT INTO kpi_wakasis_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Kesiswaan', 'Pembinaan kesiswaan', 1, 'wb_2', NOW()),
('Kesiswaan 2', 'Pembinaan kesiswaan lainnya', 2, 'wb_2', NOW());

-- UNIT: wb_1
INSERT INTO kpi_wakasis_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Kesiswaan', 'Pembinaan kesiswaan', 1, 'wb_1', NOW()),
('Kesiswaan 2', 'Pembinaan kesiswaan lainnya', 2, 'wb_1', NOW());

-- UNIT: wb_3
INSERT INTO kpi_wakasis_perspectives (nama, deskripsi, urutan, unit_sekolah, created_at) VALUES
('Kesiswaan', 'Pembinaan kesiswaan', 1, 'wb_3', NOW()),
('Kesiswaan 2', 'Pembinaan kesiswaan lainnya', 2, 'wb_3', NOW());

-- =====================================================
-- KPI GURU - Indicators (menggunakan subquery untuk perspective_id)
-- =====================================================

-- wb_2 - Pedagogik
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Mengenal Karakteristik Peserta Didik', '', 1, 'wb_2', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_2';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Menguasai Teori Belajar dan Prinsip-Prinsip', '', 2, 'wb_2', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_2';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Mengembangkan Kurikulum', '', 3, 'wb_2', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_2';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Kegiatan Pembelajaran Yang Mendidik', '', 4, 'wb_2', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_2';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Pengembangan Potensi Peserta Didik', '', 5, 'wb_2', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_2';

-- wb_1 - Pedagogik
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Mengenal Karakteristik Peserta Didik', '', 1, 'wb_1', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_1';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Menguasai Teori Belajar dan Prinsip-Prinsip', '', 2, 'wb_1', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_1';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Mengembangkan Kurikulum', '', 3, 'wb_1', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_1';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Kegiatan Pembelajaran Yang Mendidik', '', 4, 'wb_1', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_1';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Pengembangan Potensi Peserta Didik', '', 5, 'wb_1', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_1';

-- wb_3 - Pedagogik
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Mengenal Karakteristik Peserta Didik', '', 1, 'wb_3', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_3';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Menguasai Teori Belajar dan Prinsip-Prinsip', '', 2, 'wb_3', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_3';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Mengembangkan Kurikulum', '', 3, 'wb_3', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_3';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Kegiatan Pembelajaran Yang Mendidik', '', 4, 'wb_3', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_3';
INSERT INTO kpi_guru_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Pengembangan Potensi Peserta Didik', '', 5, 'wb_3', NOW() FROM kpi_guru_perspectives WHERE nama = 'Pedagogik' AND unit_sekolah = 'wb_3';

-- =====================================================
-- KPI KEPSEK - Indicators
-- =====================================================

-- wb_2
INSERT INTO kpi_kepsek_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program dan Rencana Kerja', '', 1, 'wb_2', NOW() FROM kpi_kepsek_perspectives WHERE nama = 'Manajerial' AND unit_sekolah = 'wb_2';
INSERT INTO kpi_kepsek_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Pelaksanaan Manajemen Berbasis Sekolah', '', 2, 'wb_2', NOW() FROM kpi_kepsek_perspectives WHERE nama = 'Manajerial' AND unit_sekolah = 'wb_2';

-- wb_1
INSERT INTO kpi_kepsek_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program dan Rencana Kerja', '', 1, 'wb_1', NOW() FROM kpi_kepsek_perspectives WHERE nama = 'Manajerial' AND unit_sekolah = 'wb_1';
INSERT INTO kpi_kepsek_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Pelaksanaan Manajemen Berbasis Sekolah', '', 2, 'wb_1', NOW() FROM kpi_kepsek_perspectives WHERE nama = 'Manajerial' AND unit_sekolah = 'wb_1';

-- wb_3
INSERT INTO kpi_kepsek_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program dan Rencana Kerja', '', 1, 'wb_3', NOW() FROM kpi_kepsek_perspectives WHERE nama = 'Manajerial' AND unit_sekolah = 'wb_3';
INSERT INTO kpi_kepsek_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Pelaksanaan Manajemen Berbasis Sekolah', '', 2, 'wb_3', NOW() FROM kpi_kepsek_perspectives WHERE nama = 'Manajerial' AND unit_sekolah = 'wb_3';

-- =====================================================
-- KPI KAPROG - Indicators
-- =====================================================

-- wb_2
INSERT INTO kpi_kaprog_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program Tahunan', '', 1, 'wb_2', NOW() FROM kpi_kaprog_perspectives WHERE nama = 'Perencanaan Program' AND unit_sekolah = 'wb_2';
INSERT INTO kpi_kaprog_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program Semester', '', 2, 'wb_2', NOW() FROM kpi_kaprog_perspectives WHERE nama = 'Perencanaan Program' AND unit_sekolah = 'wb_2';

-- wb_1
INSERT INTO kpi_kaprog_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program Tahunan', '', 1, 'wb_1', NOW() FROM kpi_kaprog_perspectives WHERE nama = 'Perencanaan Program' AND unit_sekolah = 'wb_1';
INSERT INTO kpi_kaprog_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program Semester', '', 2, 'wb_1', NOW() FROM kpi_kaprog_perspectives WHERE nama = 'Perencanaan Program' AND unit_sekolah = 'wb_1';

-- wb_3
INSERT INTO kpi_kaprog_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program Tahunan', '', 1, 'wb_3', NOW() FROM kpi_kaprog_perspectives WHERE nama = 'Perencanaan Program' AND unit_sekolah = 'wb_3';
INSERT INTO kpi_kaprog_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program Semester', '', 2, 'wb_3', NOW() FROM kpi_kaprog_perspectives WHERE nama = 'Perencanaan Program' AND unit_sekolah = 'wb_3';

-- =====================================================
-- KPI WAKAKUR - Indicators
-- =====================================================

-- wb_2
INSERT INTO kpi_wakakur_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan KTSP', '', 1, 'wb_2', NOW() FROM kpi_wakakur_perspectives WHERE nama = 'Kurikulum' AND unit_sekolah = 'wb_2';
INSERT INTO kpi_wakakur_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Prota dan Promes', '', 2, 'wb_2', NOW() FROM kpi_wakakur_perspectives WHERE nama = 'Kurikulum' AND unit_sekolah = 'wb_2';

-- wb_1
INSERT INTO kpi_wakakur_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan KTSP', '', 1, 'wb_1', NOW() FROM kpi_wakakur_perspectives WHERE nama = 'Kurikulum' AND unit_sekolah = 'wb_1';
INSERT INTO kpi_wakakur_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Prota dan Promes', '', 2, 'wb_1', NOW() FROM kpi_wakakur_perspectives WHERE nama = 'Kurikulum' AND unit_sekolah = 'wb_1';

-- wb_3
INSERT INTO kpi_wakakur_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan KTSP', '', 1, 'wb_3', NOW() FROM kpi_wakakur_perspectives WHERE nama = 'Kurikulum' AND unit_sekolah = 'wb_3';
INSERT INTO kpi_wakakur_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Prota dan Promes', '', 2, 'wb_3', NOW() FROM kpi_wakakur_perspectives WHERE nama = 'Kurikulum' AND unit_sekolah = 'wb_3';

-- =====================================================
-- KPI WAKASIS - Indicators
-- =====================================================

-- wb_2
INSERT INTO kpi_wakasis_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program Kesiswaan', '', 1, 'wb_2', NOW() FROM kpi_wakasis_perspectives WHERE nama = 'Kesiswaan' AND unit_sekolah = 'wb_2';
INSERT INTO kpi_wakasis_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Pembinaan OSIS', '', 2, 'wb_2', NOW() FROM kpi_wakasis_perspectives WHERE nama = 'Kesiswaan' AND unit_sekolah = 'wb_2';

-- wb_1
INSERT INTO kpi_wakasis_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program Kesiswaan', '', 1, 'wb_1', NOW() FROM kpi_wakasis_perspectives WHERE nama = 'Kesiswaan' AND unit_sekolah = 'wb_1';
INSERT INTO kpi_wakasis_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Pembinaan OSIS', '', 2, 'wb_1', NOW() FROM kpi_wakasis_perspectives WHERE nama = 'Kesiswaan' AND unit_sekolah = 'wb_1';

-- wb_3
INSERT INTO kpi_wakasis_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Penyusunan Program Kesiswaan', '', 1, 'wb_3', NOW() FROM kpi_wakasis_perspectives WHERE nama = 'Kesiswaan' AND unit_sekolah = 'wb_3';
INSERT INTO kpi_wakasis_indicators (perspective_id, nama, deskripsi, urutan, unit_sekolah, created_at)
SELECT id, 'Pembinaan OSIS', '', 2, 'wb_3', NOW() FROM kpi_wakasis_perspectives WHERE nama = 'Kesiswaan' AND unit_sekolah = 'wb_3';

-- =====================================================
-- SEED COMPLETE
-- =====================================================
-- Catatan:
-- - Evaluations dan Validations akan terisi otomatis
--   saat user mengisi evaluasi KPI di aplikasi
-- - Data tidak akan nabrak antar unit karena ada
--   filter unit_sekolah di semua query
-- =====================================================
