<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
// Matikan error display di production (aktifkan saat debugging)
error_reporting(0);
// Atau gunakan ini untuk debugging: error_reporting(E_ALL);
ini_set('display_errors', 0);

// ==========================================
// LOGIKA MULTI UNIT (PENTING!)
// ==========================================
$unit_param = $_GET['unit_sekolah'] ?? ($_POST['unit_sekolah'] ?? ($_GET['unit'] ?? null));
if ($unit_param !== null) {
	$unit_sekolah = preg_replace('/[^A-Za-z0-9_]/', '', (string) $unit_param);
} else {
	// Jika tidak ada di GET/POST, ambil dari session
	$unit_sekolah = $_SESSION['unit_sekolah'] ?? 'wira_buana';
}

// Normalisasi: Admin Super melihat semua (wira_buana)
if ($unit_sekolah == 'admin' || $unit_sekolah == '') {
	$unit_sekolah = 'wira_buana';
}
$_SESSION['unit_sekolah'] = $unit_sekolah;

// Logika Filter SQL Global
if ($unit_sekolah == 'wira_buana') {
	$sql_unit_filter = "unit_sekolah IN ('wb_1', 'wb_2', 'wb_3', 'wira_buana')";
} else {
	$sql_unit_filter = "unit_sekolah = '$unit_sekolah'";
}

$sql_finance_filter = "unit_sekolah IN ('wb_1', 'wb_2', 'wb_3', 'wira_buana')";
// ==========================================


// LOGIKA BASEURL
// Otomatis mengikuti lokasi instalasi, baik di root domain server maupun
// subfolder lokal seperti http://localhost/nama-sekolah.
$is_https = (
	(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
	(($_SERVER['SERVER_PORT'] ?? '') == 443) ||
	(($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
);
$scheme = $is_https ? 'https' : 'http';
$host_url = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseurl = $scheme . '://' . $host_url;

$document_root = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$project_root = realpath(__DIR__ . '/..');
if ($document_root && $project_root && stripos($project_root, $document_root) === 0) {
	$subfolder = trim(str_replace('\\', '/', substr($project_root, strlen($document_root))), '/');
	if ($subfolder !== '') {
		$baseurl .= '/' . $subfolder;
	}
}

// DATABASE CREDENTIALS
// Untuk localhost XAMPP (default: root, tanpa password)
$host = 'localhost';
$username = 'root';
$password = '';  // kosong untuk XAMPP default
$database = 'db_asist';

// Jika ingin menggunakan user khusus, buat dulu di phpMyAdmin:
// CREATE USER 'db_smart_wb'@'localhost' IDENTIFIED BY '1234webe';
// GRANT ALL PRIVILEGES ON db_smart_wb.* TO 'db_smart_wb'@'localhost';
// FLUSH PRIVILEGES;

$koneksi = mysqli_connect($host, $username, $password, "");
if ($koneksi) {
	mysqli_select_db($koneksi, $database);
	mysqli_set_charset($koneksi, 'utf8');
}

// Global Static Application Configuration (No DB Dependency)
$setting = [
	'sekolah' => 'SMA NEGARA CONTOH',
	'npsn' => '00000000',
	'nss' => '000000000000',
	'kepsek' => 'Nama Kepala Sekolah',
	'tp' => date('Y') . '/' . (date('Y') + 1),
	'semester' => (date('n') >= 7) ? 'Ganjil' : 'Genap',
	'waktu' => 'Asia/Jakarta',
	'logo' => 'logo683.png',
	'header' => 'Alamat Sekolah'
];

date_default_timezone_set($setting['waktu']);

$semester = $setting['semester'] ?? 'Ganjil';
$tapel = $setting['tp'] ?? (date('Y') . '/' . (date('Y') + 1));
$tanggal = date('Y-m-d');
$waktumu = date('Y-m-d H:i:s');
$bulan = date('m');
$tahun = date('Y');
?>