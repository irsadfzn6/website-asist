<?php
defined('APK') or exit('No accsess');

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

@include_once __DIR__ . '/../unit_helper.php';
@include_once __DIR__ . '/../../konek/user_unit_helper.php';
@include_once __DIR__ . '/kpi_unit_helper.php';
require_once __DIR__ . '/kpi_charts.inc.php';
require_once __DIR__ . '/kpi_periode_helper.php';

// Stub functions if kpi_unit_helper.php is not available
if (!function_exists('kpiInitUnitContext')) {
	function kpiInitUnitContext($koneksi, $prefix) {
		return [
			'unit_id' => 1,
			'label' => 'Default Unit',
			'filter' => '1=1',
			'filter_p' => '1=1',
			'filter_e' => '1=1',
			'filter_v' => '1=1'
		];
	}
}
if (!function_exists('kpiCalculatePerspectiveWeight')) {
	function kpiCalculatePerspectiveWeight($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator) {
		if (!$koneksi) return 0;
		$query = "SELECT AVG(e.kpi_range) as avg_range FROM `$tableEvaluation` e 
				  JOIN `$tableIndicator` i ON e.indicator_id = i.id 
				  WHERE e.user_id = $userId AND e.periode = '$periode' AND i.perspective_id = $perspectiveId";
		$result = mysqli_query($koneksi, $query);
		if ($result && $row = mysqli_fetch_assoc($result)) {
			return (float)($row['avg_range'] ?? 0);
		}
		return 0;
	}
}
if (!function_exists('kpiCalculatePerspectiveRating')) {
	function kpiCalculatePerspectiveRating($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator) {
		if (!$koneksi) return 0;
		$query = "SELECT AVG(e.rating) as avg_rating FROM `$tableEvaluation` e 
				  JOIN `$tableIndicator` i ON e.indicator_id = i.id 
				  WHERE e.user_id = $userId AND e.periode = '$periode' AND i.perspective_id = $perspectiveId";
		$result = mysqli_query($koneksi, $query);
		if ($result && $row = mysqli_fetch_assoc($result)) {
			return round((float)($row['avg_rating'] ?? 0), 2);
		}
		return 0;
	}
}
if (!function_exists('kpiCalculateTotalFinalRating')) {
	function kpiCalculateTotalFinalRating($koneksi, $userId, $periode, $tablePerspective, $tableEvaluation, $tableIndicator) {
		if (!$koneksi) return 0;
		$query = "SELECT AVG(e.rating) as total_rating FROM `$tableEvaluation` e 
				  WHERE e.user_id = $userId AND e.periode = '$periode'";
		$result = mysqli_query($koneksi, $query);
		if ($result && $row = mysqli_fetch_assoc($result)) {
			return round((float)($row['total_rating'] ?? 0), 2);
		}
		return 0;
	}
}

global $koneksi;

// KPI Types Configuration - semua jenis KPI dengan validator
$kpiTypes = [
	'guru' => [
		'title' => 'KPI Guru',
		'description' => 'Sistem evaluasi kinerja guru dengan 7 perspektif dan perhitungan rating otomatis',
		'user_level' => ['guru'],
		'table_prefix' => 'kpi_guru',
		'validator_level' => ['keprog'],
		'validator_jabatan' => ['Kepala Program', 'kaprog'],
		'validator_type' => 'kaprog'
	],
	'kaprog' => [
		'title' => 'KPI Kepala Program',
		'description' => 'Sistem evaluasi kinerja kepala program dengan perspektif khusus',
		'user_level' => ['keprog'],
		'table_prefix' => 'kpi_kaprog',
		'validator_level' => ['waka_kurikulum'],
		'validator_jabatan' => ['Wakil Kurikulum', 'wakakur'],
		'validator_type' => 'wakakur'
	],
	'wakasis' => [
		'title' => 'KPI Wakil Kesiswaan',
		'description' => 'Sistem evaluasi kinerja wakil kepala sekolah bidang kesiswaan',
		'user_level' => ['kesiswaan'],
		'table_prefix' => 'kpi_wakasis',
		'validator_level' => ['kepsek'],
		'validator_jabatan' => ['Kepala Sekolah', 'kepsek'],
		'validator_type' => 'kepsek'
	],
	'wakakur' => [
		'title' => 'KPI Wakil Kurikulum',
		'description' => 'Sistem evaluasi kinerja wakil kepala sekolah bidang kurikulum',
		'user_level' => ['waka_kurikulum'],
		'table_prefix' => 'kpi_wakakur',
		'validator_level' => ['kepsek'],
		'validator_jabatan' => ['Kepala Sekolah', 'kepsek'],
		'validator_type' => 'kepsek'
	],
	'kepsek' => [
		'title' => 'KPI Kepala Sekolah',
		'description' => 'Sistem evaluasi kinerja kepala sekolah dengan perspektif manajerial',
		'user_level' => ['kepsek'],
		'table_prefix' => 'kpi_kepsek',
		'validator_level' => ['yayasan'],
		'validator_jabatan' => ['Ketua Yayasan', 'ketua_yayasan'],
		'validator_type' => 'ketua_yayasan'
	]
	// Ketua Yayasan tidak diinclude di sini karena hanya validator, tidak perlu input KPI
];

// Tentukan KPI type berdasarkan jabatan atau level user
$userJabatan = strtolower($user['jabatan'] ?? '');
$userLevel = strtolower($user['level'] ?? '');
$kpiType = null;
$currentKpiConfig = null;

foreach ($kpiTypes as $type => $config) {
	// Cek berdasarkan level user
	if (isset($config['user_level']) && in_array($user['level'] ?? '', $config['user_level'])) {
		$kpiType = $type;
		$currentKpiConfig = $config;
		break;
	}
}

// Jika tidak ada KPI type yang sesuai, tampilkan pesan error
if (!$kpiType || !$currentKpiConfig) {
	// Debug: get user level from database
	$debugUserId = (int)($_SESSION['id_user'] ?? 0);
	$debugLevel = '';
	if ($debugUserId > 0 && $koneksi) {
		$qDebug = mysqli_query($koneksi, "SELECT level FROM users WHERE id_user = $debugUserId LIMIT 1");
		if ($qDebug && $rDebug = mysqli_fetch_assoc($qDebug)) {
			$debugLevel = $rDebug['level'];
		}
	}
	?>
	<div class="kpi-container">
		<div class="alert alert-danger">
			<strong>Akses Ditolak!</strong><br>
			Anda tidak memiliki akses ke sistem KPI. Silakan hubungi administrator untuk mengatur jabatan Anda.
			<br><br>
			<strong>Jabatan Anda:</strong> <?= htmlspecialchars($user['jabatan'] ?? '-') ?>
			<br><strong>Level (session):</strong> <?= htmlspecialchars($user['level'] ?? '-') ?>
			<br><strong>Level (database):</strong> <?= htmlspecialchars($debugLevel ?: '-') ?>
			<br><strong>Level yang diizinkan:</strong> guru, keprog, wakasis, wakakur, kepsek
		</div>
	</div>
	<?php
	return;
}

// Tabel KPI untuk user
$tablePerspective = 'kpi_perspectives';
$tableIndicator   = 'kpi_indicators';
$tableEvaluation  = 'kpi_evaluations';
$targetRole       = $kpiType;

$kpiUnit = kpiInitUnitContext($koneksi, 'kpi');
$kpiActiveUnitId = $kpiUnit['unit_id'];
$kpiActiveUnitLabel = $kpiUnit['label'];
$kpiUnitFilter = $kpiUnit['filter'];
$kpiUnitFilterP = $kpiUnit['filter_p'];
$kpiUnitFilterE = $kpiUnit['filter_e'];

// Filter unit sekolah - yayasan & admin yayasan bisa lihat semua, lainnya hanya unit sendiri
$userLevelKpi = strtolower(trim((string)($user['level'] ?? '')));
$userRawUnitKpi = trim((string)($user['unit_sekolah'] ?? ''));
$userUnitSekolahKpi = $userRawUnitKpi;
if (function_exists('unit_normalize') && !empty($userUnitSekolahKpi)) {
	$userUnitSekolahKpi = unit_normalize($userUnitSekolahKpi);
}
$isYayasanKpi = in_array($userLevelKpi, ['yayasan', 'ketua_yayasan', 'pembina_yayasan', 'keuangan'], true);
$seeAllSchoolsKpi = ($userRawUnitKpi === '' || $userRawUnitKpi === 'wira_buana' || $isYayasanKpi);

if (!$seeAllSchoolsKpi && !empty($userRawUnitKpi)) {
	$escUnit = mysqli_real_escape_string($koneksi, $userRawUnitKpi);
	$kpiUnitFilter .= " AND unit_sekolah = '$escUnit'";
	$kpiUnitFilterP .= " AND p.unit_sekolah = '$escUnit'";
	$kpiUnitFilterE .= " AND e.unit_sekolah = '$escUnit'";
}

// Fungsi konversi KPI Range ke Rating (sesuai Excel)
function kpiRangeToRating($kpiRange) {
	$kpiRange = (float)$kpiRange;
	if ($kpiRange >= 0 && $kpiRange <= 20) return 1;
	if ($kpiRange > 20 && $kpiRange <= 40) return 2;
	if ($kpiRange > 40 && $kpiRange <= 60) return 3;
	if ($kpiRange > 60 && $kpiRange <= 80) return 4;
	if ($kpiRange > 80 && $kpiRange <= 100) return 5;
	return 1; // default untuk nilai di luar range
}

function calculatePerspectiveWeight($koneksi, $userId, $periode, $perspectiveId) {
	global $tableEvaluation, $tableIndicator;
	return kpiCalculatePerspectiveWeight($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator);
}

function calculatePerspectiveRating($koneksi, $userId, $periode, $perspectiveId) {
	global $tableEvaluation, $tableIndicator;
	return kpiCalculatePerspectiveRating($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator);
}

function calculateTotalFinalRating($koneksi, $userId, $periode) {
	global $tablePerspective, $tableEvaluation, $tableIndicator;
	return kpiCalculateTotalFinalRating($koneksi, $userId, $periode, $tablePerspective, $tableEvaluation, $tableIndicator);
}

$flash = '';
$errors = [];
$validationRecord = null;
$validationStatus = '';
$canEditEvaluation = true;

// Simpan Evaluasi KPI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_evaluasi']) && $koneksi) {
	$userId = (int)$_POST['user_id'];
	$periode = mysqli_real_escape_string($koneksi, trim($_POST['periode'] ?? ''));
	$indicatorIds = isset($_POST['indicator_id']) ? $_POST['indicator_id'] : [];
	$kpiRanges = isset($_POST['kpi_range']) ? $_POST['kpi_range'] : [];

	$postValidation = kpiGetValidationRecord($koneksi, $tableValidation, $userId, $periode);
	if (!kpiCanEditEvaluation($postValidation['status'] ?? '')) {
		$errors[] = 'KPI sudah diajukan dan tidak dapat diubah. Tunggu validasi atau hubungi validator jika ditolak.';
	}
	
	if ($userId == 0) $errors[] = 'User harus dipilih.';
	if ($periode == '') {
		$errors[] = 'Periode (semester) harus dipilih.';
	} elseif (!kpiPeriodeIsValid($periode)) {
		$errors[] = 'Format periode tidak valid. Pilih semester (Ganjil/Genap).';
	}
	if (empty($indicatorIds)) $errors[] = 'Tidak ada indikator yang dievaluasi.';
	
	if (empty($errors)) {
		$saved = 0;
		$unitValSql = kpiFormatUnitSqlVal($koneksi, $userUnitSekolahKpi);
		foreach ($indicatorIds as $key => $indicatorId) {
			$indicatorId = (int)$indicatorId;
			$kpiRange = isset($kpiRanges[$key]) ? (float)$kpiRanges[$key] : 0;
			$rating = kpiRangeToRating($kpiRange);
			
			// Insert atau update - sesuai struktur tabel dengan unit_sekolah yang valid / NULL
			$query = "INSERT INTO `$tableEvaluation` (user_id, indicator_id, periode, kpi_range, rating, unit_sekolah) 
					  VALUES ($userId, $indicatorId, '$periode', $kpiRange, $rating, $unitValSql)
					  ON DUPLICATE KEY UPDATE kpi_range = $kpiRange, rating = $rating, updated_at = NOW(), unit_sekolah = $unitValSql";
			
			if (mysqli_query($koneksi, $query)) {
				$saved++;
			} else {
				$errors[] = 'Error DB: ' . mysqli_error($koneksi) . ' Query: ' . substr($query, 0, 100);
			}
		}
		
		if ($saved > 0) {
			kpiSubmitValidation($koneksi, $tableValidation, $userId, $periode, $userUnitSekolahKpi);
			$wasRejected = ($postValidation['status'] ?? '') === 'rejected';
			$_SESSION['kpi_flash'] = $wasRejected
				? 'KPI berhasil diperbaiki dan diajukan ulang untuk validasi.'
				: 'Evaluasi KPI berhasil disimpan dan diajukan untuk validasi.';
			// Redirect ke halaman yang sama dengan parameter yang sama
			echo '<script>window.location.href="?pg=' . urlencode(enkripsi('kpi_user')) . '&user_id=' . $userId . '&periode=' . urlencode($periode) . '";</script>';
			exit;
		} else {
			$errors[] = 'Tidak ada data yang tersimpan.';
		}
	}
}

// Get data
$perspectives = [];
$indicators = [];
$evaluations = [];
$perspectiveWeights = [];
$totalFinalRating = 0;

if ($koneksi) {
	// Perspectives
	$qPers = mysqli_query($koneksi, "SELECT * FROM `$tablePerspective` WHERE target_role = '$targetRole' AND $kpiUnitFilter ORDER BY urutan");
	if ($qPers) {
		while ($row = mysqli_fetch_assoc($qPers)) {
			$perspectives[] = $row;
		}
	}

	// Indicators dengan perspective - filter by indicator unit_sekolah & target_role
	$indUnitFilter = $kpiUnitFilter ? str_replace('unit_sekolah', 'i.unit_sekolah', $kpiUnitFilter) : '1=1';
	$qInd = mysqli_query($koneksi, "SELECT i.*, p.nama as perspective_nama FROM `$tableIndicator` i JOIN `$tablePerspective` p ON i.perspective_id = p.id WHERE p.target_role = '$targetRole' AND $indUnitFilter ORDER BY p.urutan, i.urutan");
	if ($qInd) {
		while ($row = mysqli_fetch_assoc($qInd)) {
			$indicators[] = $row;
		}
	}
	
	// Get current user and periode
	$selectedUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $user['id_user'];
	$kpiPeriodeOptions = kpiPeriodeMergeOptions($koneksi, $tableEvaluation, '1=1');
	$selectedPeriode = isset($_GET['periode']) ? trim((string)$_GET['periode']) : kpiPeriodeCurrent();
	
	// Evaluasi untuk user dan periode yang dipilih
	if ($selectedUserId > 0 && $selectedPeriode != '') {
		$qEval = mysqli_query($koneksi, "SELECT * FROM `$tableEvaluation` WHERE user_id = $selectedUserId AND periode = '$selectedPeriode'");
		if ($qEval) {
			while ($row = mysqli_fetch_assoc($qEval)) {
				$evaluations[$row['indicator_id']] = $row;
			}
		}

		$validationRecord = kpiEnsureLegacyPendingValidation(
			$koneksi,
			$tableValidation,
			$tableEvaluation,
			$selectedUserId,
			$selectedPeriode,
			$userUnitSekolahKpi
		);
		$validationStatus = strtolower(trim((string)($validationRecord['status'] ?? '')));
		$canEditEvaluation = kpiCanEditEvaluation($validationStatus);
		
		// Hitung hasil akhir
		foreach ($perspectives as $perspective) {
			$rating = calculatePerspectiveRating($koneksi, $selectedUserId, $selectedPeriode, $perspective['id']);
			$perspectiveWeights[$perspective['id']] = $rating;
		}
		$totalFinalRating = calculateTotalFinalRating($koneksi, $selectedUserId, $selectedPeriode);
	}
}

$chartPerspectiveLabels = [];
$chartPerspectiveRatings = [];
$chartPerspectiveWeights = [];
if (!empty($perspectiveWeights) && !empty($perspectives)) {
	foreach ($perspectives as $perspective) {
		$chartPerspectiveLabels[] = $perspective['nama'];
		$chartPerspectiveRatings[] = round($perspectiveWeights[$perspective['id']] ?? 0, 2);
		$chartPerspectiveWeights[] = round(calculatePerspectiveWeight($koneksi, $selectedUserId, $selectedPeriode, $perspective['id']), 1);
	}
}

// Flash message
if (isset($_SESSION['kpi_flash'])) {
	$flash = $_SESSION['kpi_flash'];
	unset($_SESSION['kpi_flash']);
}
?>

<style>
	:root {
		--card-bg: #ffffff;
		--card-border: #e2e8f0;
		--muted-text: #64748b;
		--primary-color: #4f46e5;
		--success-color: #10b981;
		--danger-color: #ef4444;
		--warning-color: #f59e0b;
	}
	
	.kpi-container {
		max-width: 1200px;
		margin: 0 auto;
		padding: 20px;
	}
	
	.kpi-header {
		background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #4338ca 100%);
		color: white;
		padding: 30px;
		border-radius: 16px;
		margin-bottom: 24px;
		box-shadow: 0 10px 35px rgba(79,70,229,.25);
	}
	
	.kpi-header h1 {
		margin: 0 0 8px;
		font-size: 28px;
		font-weight: 700;
	}
	
	.kpi-header p {
		margin: 0;
		font-size: 16px;
		opacity: 0.9;
	}
	
	.kpi-card {
		background: var(--card-bg);
		border: 1px solid var(--card-border);
		border-radius: 16px;
		padding: 24px;
		margin-bottom: 24px;
		box-shadow: 0 4px 12px rgba(0,0,0,0.05);
	}
	
	.kpi-card-title {
		margin: 0 0 20px;
		font-size: 20px;
		font-weight: 600;
		color: #1f2937;
	}
	
	.kpi-form-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
		gap: 16px;
		margin-bottom: 20px;
	}
	
	.kpi-form-group {
		display: flex;
		flex-direction: column;
	}
	
	.kpi-form-group label {
		font-weight: 500;
		margin-bottom: 6px;
		color: #374151;
	}
	
	.kpi-form-group input,
	.kpi-form-group select {
		padding: 10px 12px;
		border: 1px solid #d1d5db;
		border-radius: 8px;
		font-size: 14px;
		transition: border-color 0.2s;
	}
	
	.kpi-form-group input:focus,
	.kpi-form-group select:focus {
		outline: none;
		border-color: var(--primary-color);
		box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
	}
	
	.kpi-btn {
		padding: 10px 20px;
		border: none;
		border-radius: 8px;
		font-size: 14px;
		font-weight: 500;
		cursor: pointer;
		transition: all 0.2s;
		text-decoration: none;
		display: inline-block;
	}
	
	.kpi-btn-primary {
		background: var(--primary-color);
		color: white;
	}
	
	.kpi-btn-primary:hover {
		background: #4338ca;
		transform: translateY(-1px);
		box-shadow: 0 4px 12px rgba(79,70,229,0.3);
	}
	
	.kpi-btn-secondary {
		background: #6b7280;
		color: white;
	}
	
	.kpi-btn-secondary:hover {
		background: #4b5563;
	}
	
	.kpi-perspective-card {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		padding: 20px;
		margin-bottom: 20px;
	}
	
	.kpi-perspective-title {
		margin: 0 0 16px;
		font-size: 18px;
		font-weight: 600;
		color: #1f2937;
	}
	
	.kpi-badge {
		background: var(--primary-color);
		color: white;
		padding: 4px 8px;
		border-radius: 12px;
		font-size: 12px;
		font-weight: 500;
		margin-left: 8px;
	}
	
	.kpi-final-rating {
		background: linear-gradient(135deg, #10b981 0%, #059669 100%);
		color: white;
		padding: 24px;
		border-radius: 16px;
		text-align: center;
		margin-top: 24px;
		box-shadow: 0 10px 35px rgba(16,185,129,.25);
	}
	
	.kpi-final-rating h3 {
		margin: 0 0 12px;
		font-size: 18px;
		font-weight: 600;
	}
	
	.kpi-final-rating .rating {
		font-size: 48px;
		font-weight: 700;
		margin: 0;
	}
	
	.alert {
		padding: 12px 16px;
		border-radius: 8px;
		margin-bottom: 20px;
		font-size: 14px;
	}
	
	.alert-success {
		background: #d1fae5;
		color: #065f46;
		border: 1px solid #10b981;
	}
	
	.alert-danger {
		background: #fee2e2;
		color: #991b1b;
		border: 1px solid #ef4444;
	}

	.alert-warning {
		background: #fef3c7;
		color: #92400e;
		border: 1px solid #f59e0b;
	}

	.alert-info {
		background: #dbeafe;
		color: #1e40af;
		border: 1px solid #3b82f6;
	}

	.kpi-input-readonly {
		background: #f3f4f6;
		cursor: not-allowed;
	}
	
	@media (max-width: 768px) {
		.kpi-container {
			padding: 16px;
		}
		
		.kpi-header {
			padding: 24px;
		}
		
		.kpi-header h1 {
			font-size: 24px;
		}
		
		.kpi-form-grid {
			grid-template-columns: 1fr;
		}
	}
</style>

<div class="kpi-container">
	<div class="kpi-header">
		<h1><?= htmlspecialchars($currentKpiConfig['title']) ?></h1>
		<p><?= htmlspecialchars($currentKpiConfig['description']) ?></p>
	</div>
	
	<?php if ($flash): ?>
	<div class="alert alert-success">
		<?= htmlspecialchars($flash) ?>
	</div>
	<?php endif; ?>
	
	<?php if (!empty($errors)): ?>
	<div class="alert alert-danger">
		<strong>Error:</strong><br>
		<?php foreach ($errors as $error): ?>
		<?= htmlspecialchars($error) ?><br>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	
	<!-- Evaluasi KPI -->
	<div class="kpi-card">
		<h2 class="kpi-card-title">Evaluasi KPI</h2>
		
		<form method="get" style="margin-bottom: 20px;">
			<input type="hidden" name="pg" value="<?= htmlspecialchars(enkripsi('kpi_user')) ?>">
			<div class="kpi-form-grid">
				<div class="kpi-form-group">
					<label>Periode (Semester)</label>
					<?php kpiRenderPeriodeSelect('periode', $selectedPeriode, $kpiPeriodeOptions, false); ?>
				</div>
			</div>
			<button type="submit" class="kpi-btn kpi-btn-primary">Tampilkan Form Evaluasi</button>
		</form>
		
		<?php if ($selectedPeriode != ''): ?>
		<?php if ($validationStatus === 'pending'): ?>
		<div class="alert alert-info">
			<strong>Status: Menunggu Validasi</strong><br>
			KPI sudah diajukan dan tidak dapat diubah. Silakan tunggu persetujuan validator.
		</div>
		<?php elseif ($validationStatus === 'approved'): ?>
		<div class="alert alert-success">
			<strong>Status: Disetujui</strong><br>
			KPI Anda telah disetujui validator dan tidak dapat diubah lagi.
		</div>
		<?php elseif ($validationStatus === 'rejected'): ?>
		<div class="alert alert-warning">
			<strong>Status: Ditolak</strong><br>
			KPI ditolak validator. Silakan perbaiki data di bawah lalu klik <strong>Ajukan Ulang</strong>.
		</div>
		<?php endif; ?>

		<form method="post" class="kpi-form">
			<input type="hidden" name="simpan_evaluasi" value="1">
			<input type="hidden" name="user_id" value="<?= $user['id_user'] ?>">
			<input type="hidden" name="periode" value="<?= htmlspecialchars($selectedPeriode) ?>">
			
			<?php foreach ($perspectives as $perspective): ?>
			<div class="kpi-perspective-card">
				<h4 class="kpi-perspective-title"><?= htmlspecialchars($perspective['nama']) ?></h4>
				
				<?php 
				$indicatorsInPerspective = array_filter($indicators, function($ind) use ($perspective) {
					return $ind['perspective_id'] == $perspective['id'];
				});
				
				foreach ($indicatorsInPerspective as $indicator): ?>
				<div style="margin-bottom: 12px;">
					<label style="font-weight: 500; margin-bottom: 4px; display: block;">
						<?= htmlspecialchars($indicator['nama']) ?>
					</label>
					<input type="hidden" name="indicator_id[]" value="<?= $indicator['id'] ?>">
					<input type="number" 
						   name="kpi_range[]" 
						   min="0" 
						   max="100" 
						   step="0.01"
						   value="<?= isset($evaluations[$indicator['id']]) ? $evaluations[$indicator['id']]['kpi_range'] : '' ?>"
						   placeholder="0-100"
						   style="width: 150px;"
						   <?= $canEditEvaluation ? '' : 'readonly class="kpi-input-readonly"' ?>>
					<span style="margin-left: 8px; color: var(--muted-text); font-size: 12px;">%</span>
					<?php if (isset($evaluations[$indicator['id']])): ?>
					<span class="kpi-badge">Rating: <?= $evaluations[$indicator['id']]['rating'] ?></span>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
			
			<?php if ($canEditEvaluation): ?>
			<div style="margin-top: 20px;">
				<button type="submit" class="kpi-btn kpi-btn-primary">
					<?= $validationStatus === 'rejected' ? 'Ajukan Ulang KPI' : 'Simpan & Ajukan KPI' ?>
				</button>
			</div>
			<?php else: ?>
			<div style="margin-top: 20px; color: var(--muted-text); font-size: 14px;">
				Form terkunci — KPI sudah diajukan/disahkan.
			</div>
			<?php endif; ?>
		</form>
		<?php endif; ?>
	</div>
	
	<!-- Hasil Akhir -->
	<div class="kpi-card">
		<h2 class="kpi-card-title">Hasil Akhir KPI</h2>
		
		<?php if ($selectedPeriode != '' && !empty($perspectiveWeights)): ?>
		<div style="margin-bottom: 24px;">
			<h3 style="font-size: 16px; margin-bottom: 16px;">Detail per Perspektif:</h3>
			
			<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
				<thead>
					<tr style="background: #f8f9fa;">
						<th style="padding: 12px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Perspektif</th>
						<th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">Weight (%)</th>
						<th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">Rating</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($perspectives as $perspective): ?>
					<tr>
						<td style="padding: 12px; border: 1px solid #dee2e6;"><?= htmlspecialchars($perspective['nama']) ?></td>
						<td style="padding: 12px; text-align: center; border: 1px solid #dee2e6;">
							<?php 
							// Hitung weight (rata-rata persentase) untuk perspektif ini
							$weight = calculatePerspectiveWeight($koneksi, $user['id_user'], $selectedPeriode, $perspective['id']);
							echo round($weight) . '%';
							?>
						</td>
						<td style="padding: 12px; text-align: center; border: 1px solid #dee2e6;">
							<span class="kpi-badge"><?= round($perspectiveWeights[$perspective['id']]) ?></span>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php
		kpiChartsRenderBlock(kpiChartsPerspectivePair('kpiUser', $chartPerspectiveLabels, $chartPerspectiveRatings, $chartPerspectiveWeights));
		?>
		
		<div class="kpi-final-rating">
			<h3>Total Final Rating</h3>
			<div class="rating"><?= round($totalFinalRating) ?></div>
		</div>
		
		<?php else: ?>
		<div style="text-align: center; padding: 40px; color: var(--muted-text);">
			<p>Pilih periode untuk melihat hasil akhir KPI</p>
		</div>
		<?php endif; ?>
	</div>
</div>
