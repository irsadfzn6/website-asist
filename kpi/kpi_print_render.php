<?php
/**
 * Render-only KPI print page (standalone). Opens in new tab for PDF printing.
 */
if (!defined('APK')) {
    require __DIR__ . '/../konek/koneksi.php';
    require __DIR__ . '/../konek/function.php';
    require __DIR__ . '/../konek/apk.php';
    require __DIR__ . '/../konek/crud.php';
    @include_once __DIR__ . '/../unit_helper.php';
    @include_once __DIR__ . '/../konek/user_unit_helper.php';
    if (session_status() === PHP_SESSION_NONE) session_start();
}

// Optional helpers
@include_once __DIR__ . '/kpi_unit_helper.php';
require_once __DIR__ . '/kpi_periode_helper.php';
require_once __DIR__ . '/kpi_print_report.php';

// ---- Config map ----
$kpiTypes = [
    'guru' => [
        'title' => 'KPI Guru',
        'table_prefix' => 'kpi_guru',
        'validator_jabatan' => ['Kepala Program', 'kaprog'],
    ],
    'kaprog' => [
        'title' => 'KPI Kepala Program',
        'table_prefix' => 'kpi_kaprog',
        'validator_jabatan' => ['Wakil Kurikulum', 'wakakur'],
    ],
    'wakasis' => [
        'title' => 'KPI Wakil Kesiswaan',
        'table_prefix' => 'kpi_wakasis',
        'validator_jabatan' => ['Kepala Sekolah', 'kepsek'],
    ],
    'wakakur' => [
        'title' => 'KPI Wakil Kurikulum',
        'table_prefix' => 'kpi_wakakur',
        'validator_jabatan' => ['Kepala Sekolah', 'kepsek'],
    ],
    'kepsek' => [
        'title' => 'KPI Kepala Sekolah',
        'table_prefix' => 'kpi_kepsek',
        'validator_jabatan' => ['Ketua Yayasan', 'ketua_yayasan', 'Yayasan'],
    ],
];

$kpiType = isset($_GET['type']) ? trim((string)$_GET['type']) : 'guru';
if (!isset($kpiTypes[$kpiType])) $kpiType = 'guru';
$kpiConfig = $kpiTypes[$kpiType];

$tablePerspective = 'kpi_perspectives';
$tableIndicator   = 'kpi_indicators';
$tableEvaluation  = 'kpi_evaluations';
$tableValidation  = 'kpi_validations';
$targetRole       = $kpiType;

$printUserId = (int)($_GET['user_id'] ?? 0);
$printPeriode = trim((string)($_GET['periode'] ?? ''));

if (!$koneksi || $printUserId <= 0 || $printPeriode === '' || !kpiPeriodeIsValid($printPeriode)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Cetak KPI</title></head><body style="font-family:sans-serif;padding:24px;max-width:520px;">'
        . '<h2>Parameter cetak tidak lengkap</h2><p>Pilih pegawai dan periode semester tertentu.</p>'
        . '<p><a href="javascript:history.back()">Kembali</a></p></body></html>';
    exit;
}

// Fallback permission check
if (!function_exists('kpiUserMayAccessPrint')) {
    function kpiUserMayAccessPrint(array $sessionUser, $kpiType, $printUserId, array $kpiTypesWithValidator = []) {
        $printUserId = (int)$printUserId;
        $sessionId = (int)($sessionUser['id_user'] ?? 0);
        if ($sessionId <= 0 || $printUserId <= 0) return false;
        if (($sessionUser['level'] ?? '') === 'admin') return true;
        if ($printUserId === $sessionId) return true;
        if (isset($kpiTypesWithValidator[$kpiType]['validator_jabatan'])) {
            $validators = $kpiTypesWithValidator[$kpiType]['validator_jabatan'];
            if (is_array($validators) && in_array($sessionUser['jabatan'] ?? '', $validators, true)) return true;
        }
        return false;
    }
}

$sessionUser = [];
if (isset($_SESSION['id_user']) && (int)$_SESSION['id_user'] > 0) {
    $q = mysqli_query($koneksi, 'SELECT * FROM users WHERE id_user='.(int)$_SESSION['id_user'].' LIMIT 1');
    if ($q && ($u = mysqli_fetch_assoc($q))) $sessionUser = $u;
}

if (!kpiUserMayAccessPrint($sessionUser, $kpiType, $printUserId, $kpiTypes)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Cetak KPI</title></head><body style="font-family:sans-serif;padding:24px;max-width:520px;">'
        . '<h2>Akses ditolak</h2><p>Anda hanya dapat mencetak laporan KPI milik sendiri atau sebagai validator.</p>'
        . '<p><a href="javascript:history.back()">Kembali</a></p></body></html>';
    exit;
}

// Unit context (optional)
$kpiUnit = function_exists('kpiInitUnitContext') ? kpiInitUnitContext($koneksi, $kpiConfig['table_prefix']) : ['label' => '', 'filter' => '1=1', 'filter_p' => '1=1', 'filter_e' => '1=1'];
$kpiActiveUnitLabel = $kpiUnit['label'] ?? '';
$kpiUnitFilter  = $kpiUnit['filter'] ?? '1=1';
$kpiUnitFilterP = $kpiUnit['filter_p'] ?? '1=1';
$kpiUnitFilterE = $kpiUnit['filter_e'] ?? '1=1';

// Guard: only allow print if validation is approved
if (!kpiCanPrintReport($koneksi, $tableValidation, $printUserId, $printPeriode, $kpiUnitFilter)) {
    header('Content-Type: text/html; charset=utf-8');
    $msg = htmlspecialchars(kpiPrintDeniedMessage($koneksi, $tableValidation, $printUserId, $printPeriode, $kpiUnitFilter), ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Cetak KPI</title></head><body style="font-family:sans-serif;padding:24px;max-width:520px;">'
        . '<h2>Cetak tidak diizinkan</h2><p>' . $msg . '</p>'
        . '<p><a href="javascript:history.back()">Kembali</a></p></body></html>';
    exit;
}

// Render report
kpiPrintReportOutput(
    $koneksi,
    $kpiConfig,
    $kpiType,
    $printUserId,
    $printPeriode,
    $tablePerspective,
    $tableIndicator,
    $tableEvaluation,
    $kpiUnitFilter,
    $kpiUnitFilterP,
    $kpiUnitFilterE,
    $kpiActiveUnitLabel,
    $tableValidation
);
exit;
