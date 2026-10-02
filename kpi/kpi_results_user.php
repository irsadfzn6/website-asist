<?php
defined('APK') or exit('No access');

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

@include_once __DIR__ . '/../unit_helper.php';
@include_once __DIR__ . '/../../konek/user_unit_helper.php';
@include_once __DIR__ . '/kpi_unit_helper.php';
require_once __DIR__ . '/kpi_charts.inc.php';
require_once __DIR__ . '/kpi_periode_helper.php';
@include_once __DIR__ . '/kpi_print_report.php';

// Stub functions if helpers are not available
if (!function_exists('kpiInitUnitContext')) {
	function kpiInitUnitContext($koneksi, $prefix) {
		return ['unit_id' => 1, 'label' => 'Default Unit', 'filter' => '1=1', 'filter_p' => '1=1', 'filter_e' => '1=1', 'filter_v' => '1=1'];
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
if (!function_exists('kpiRenderPrintButton')) {
	function kpiRenderPrintButton($koneksi, $tableValidation, $userId, $periode, $unitFilter, $url, $size, $label) {
		return '<button class="btn btn-sm btn-default" disabled>PDF</button>';
	}
}
if (!function_exists('kpiPrintReportUrl')) {
	function kpiPrintReportUrl($page, $type, $userId, $periode, $context = '') { return '#'; }
}

global $koneksi;

function kpiNormalizeUserLevel($value) {
	return strtolower(trim((string)($value ?? '')));
}

function kpiUserPositionLabel($userData) {
	$jabatan = trim((string)($userData['jabatan'] ?? ''));
	if ($jabatan !== '') {
		return $jabatan;
	}
	$level = trim((string)($userData['level'] ?? ''));
	if ($level !== '') {
		return ucfirst(str_replace('_', ' ', $level));
	}
	return '-';
}

$kpiTypes = ['guru' => ['title' => 'KPI Guru', 'description' => 'Sistem evaluasi kinerja guru', 'user_level' => ['guru'], 'table_prefix' => 'kpi_guru', 'validator_level' => ['keprog'], 'validator_jabatan' => ['Kepala Program','kaprog']],
	'kaprog' => ['title' => 'KPI Kepala Program', 'description' => 'Sistem evaluasi kinerja kepala program', 'user_level' => ['keprog'], 'table_prefix' => 'kpi_kaprog', 'validator_level' => ['waka_kurikulum'], 'validator_jabatan' => ['Wakil Kurikulum','wakakur']],
	'wakasis' => ['title' => 'KPI Wakil Kesiswaan', 'description' => 'Sistem evaluasi kinerja wakasis', 'user_level' => ['kesiswaan'], 'table_prefix' => 'kpi_wakasis', 'validator_level' => ['kepsek'], 'validator_jabatan' => ['Kepala Sekolah','kepsek']],
	'wakakur' => ['title' => 'KPI Wakil Kurikulum', 'description' => 'Sistem evaluasi kinerja wakakur', 'user_level' => ['waka_kurikulum'], 'table_prefix' => 'kpi_wakakur', 'validator_level' => ['kepsek'], 'validator_jabatan' => ['Kepala Sekolah','kepsek']],
	'kepsek' => ['title' => 'KPI Kepala Sekolah', 'description' => 'Sistem evaluasi kinerja kepsek', 'user_level' => ['kepsek'], 'table_prefix' => 'kpi_kepsek', 'validator_level' => ['yayasan'], 'validator_jabatan' => ['Ketua Yayasan','ketua_yayasan']]];

$userKpiType = null;
foreach ($kpiTypes as $type => $config) {
	if (isset($config['user_level']) && in_array($user['level'] ?? '', $config['user_level'])) { $userKpiType = $type; break; }
}

if (!$userKpiType) {
	echo '<div class="alert alert-danger"><strong>Akses Ditolak!</strong><br>Level Anda: ' . htmlspecialchars($user['level'] ?? '-') . '<br>Level diizinkan: guru, keprog, kesiswaan, waka_kurikulum, kepsek</div>';
	return;
}

function getAccessibleKpiTypes($userLevel, $kpiTypes) {
	$accessible = [];
	foreach ($kpiTypes as $type => $config) {
		if (isset($config['user_level']) && in_array($userLevel, $config['user_level'])) $accessible[$type] = $config;
	}
	return $accessible;
}

function calculatePerspectiveWeight($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator) {
	return kpiCalculatePerspectiveWeight($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator);
}
function calculatePerspectiveRating($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator) {
	return kpiCalculatePerspectiveRating($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator);
}
function calculateTotalFinalRating($koneksi, $userId, $periode, $tablePerspective, $tableEvaluation, $tableIndicator) {
	return kpiCalculateTotalFinalRating($koneksi, $userId, $periode, $tablePerspective, $tableEvaluation, $tableIndicator);
}

$accessibleKpiTypes = getAccessibleKpiTypes($user['level'] ?? '', $kpiTypes);
$selectedType = isset($_GET['type']) ? $_GET['type'] : $userKpiType;
if (!isset($accessibleKpiTypes[$selectedType])) $selectedType = array_key_first($accessibleKpiTypes);
$kpiConfig = $accessibleKpiTypes[$selectedType];

$tablePerspective = 'kpi_perspectives';
$tableIndicator   = 'kpi_indicators';
$tableEvaluation  = 'kpi_evaluations';
$targetRole       = $selectedType;

$kpiUnit = kpiInitUnitContext($koneksi, 'kpi');
$kpiActiveUnitId = $kpiUnit['unit_id'];
$kpiUnitFilter   = $kpiUnit['filter'];
$kpiUnitFilterE  = $kpiUnit['filter_e'];
$kpiUnitFilterV  = $kpiUnit['filter_v'];

// Filter unit sekolah - yayasan & admin yayasan bisa lihat semua, lainnya hanya unit sendiri
$userLevelKpi = strtolower(trim((string)($user['level'] ?? '')));
$userRawUnitKpi = trim((string)($user['unit_sekolah'] ?? ''));
$isYayasanKpi = in_array($userLevelKpi, ['yayasan', 'ketua_yayasan', 'pembina_yayasan', 'keuangan'], true);
$seeAllSchoolsKpi = ($userRawUnitKpi === '' || $userRawUnitKpi === 'wira_buana' || $isYayasanKpi);

if (!$seeAllSchoolsKpi && !empty($userRawUnitKpi)) {
	$escUnit = mysqli_real_escape_string($koneksi, $userRawUnitKpi);
	$kpiUnitFilter .= " AND unit_sekolah = '$escUnit'";
	$kpiUnitFilterE .= " AND e.unit_sekolah = '$escUnit'";
	$kpiUnitFilterV .= " AND v.unit_sekolah = '$escUnit'";
}

$kpiPeriodeOptions = kpiPeriodeMergeOptions($koneksi, $tableEvaluation, '1=1');
$selectedPeriode = isset($_GET['periode']) ? trim((string)$_GET['periode']) : kpiPeriodeCurrent();

// Query users
$escLevel = array_map(function($l) use($koneksi) { return mysqli_real_escape_string($koneksi, $l); }, $kpiConfig['user_level'] ?? []);
$usersQuery = "SELECT DISTINCT u.id_user, u.nama, u.level, u.unit_sekolah FROM users u WHERE u.level IN ('" . implode("','", $escLevel) . "')";
if (in_array($user['level'] ?? '', $kpiConfig['user_level'] ?? [])) {
	$usersQuery .= " AND u.id_user = " . (int)$user['id_user'];
}
// Filter unit sekolah untuk users
if (!$seeAllSchoolsKpi && !empty($userRawUnitKpi)) {
	$usersQuery .= " AND u.unit_sekolah = '" . mysqli_real_escape_string($koneksi, $userRawUnitKpi) . "'";
}
$usersQuery .= " ORDER BY u.nama";
$usersResult = mysqli_query($koneksi, $usersQuery);

$userList = [];
if ($usersResult && mysqli_num_rows($usersResult) > 0) {
	while ($uRow = mysqli_fetch_assoc($usersResult)) {
		$userList[] = $uRow;
	}
}

$listChartLabels = [];
$listChartTotals = [];
if (!empty($userList)) {
	foreach ($userList as $rowUser) {
		$tr = calculateTotalFinalRating($koneksi, (int)$rowUser['id_user'], $selectedPeriode, $tablePerspective, $tableEvaluation, $tableIndicator);
		if ($tr > 0) { $listChartLabels[] = $rowUser['nama']; $listChartTotals[] = round($tr, 2); }
	}
}

$showDetail = false;
$detailUserId = 0;
$detailPeriode = '';
if (isset($_GET['user_id']) && isset($_GET['periode'])) {
	$showDetail = true;
	$detailUserId = (int)$_GET['user_id'];
	$detailPeriode = mysqli_real_escape_string($koneksi, $_GET['periode']);
}

if ($showDetail) {
	$userQuery = "SELECT * FROM users WHERE id_user = $detailUserId";
	$userResult = mysqli_query($koneksi, $userQuery);
	if (!$userResult || mysqli_num_rows($userResult) == 0) { echo '<div class="alert alert-danger">User tidak ditemukan.</div>'; return; }
	$detailUserData = mysqli_fetch_assoc($userResult);
	
	$validationQuery = "SELECT * FROM `$tableValidation` WHERE user_id = $detailUserId AND periode = '$detailPeriode'";
	$validationResult = mysqli_query($koneksi, $validationQuery);
	$detailValidationData = mysqli_fetch_assoc($validationResult);

	$perspectivesQuery = "SELECT * FROM `$tablePerspective` WHERE target_role = '$targetRole' ORDER BY urutan";
	$perspectivesResult = mysqli_query($koneksi, $perspectivesQuery);
	
	$detailTotalRating = calculateTotalFinalRating($koneksi, $detailUserId, $detailPeriode, $tablePerspective, $tableEvaluation, $tableIndicator);
	$detailChartLabels = []; $detailChartRatings = []; $detailChartWeights = [];
	if ($perspectivesResult) {
		while ($perspective = mysqli_fetch_assoc($perspectivesResult)) {
			$detailChartLabels[] = $perspective['nama'];
			$detailChartWeights[] = round(calculatePerspectiveWeight($koneksi, $detailUserId, $detailPeriode, $perspective['id'], $tableEvaluation, $tableIndicator), 1);
			$detailChartRatings[] = round(calculatePerspectiveRating($koneksi, $detailUserId, $detailPeriode, $perspective['id'], $tableEvaluation, $tableIndicator), 2);
		}
	}
	?>
	<div class="kpi-container">
		<div class="kpi-header">
			<h2><i class="material-icons">analytics</i> Hasil KPI <?= htmlspecialchars($kpiConfig['title']) ?></h2>
			<p class="kpi-description"><?= htmlspecialchars($kpiConfig['description']) ?></p>
		</div>
		<div class="kpi-detail-card">
			<div class="detail-header">
				<h3>KPI <?= htmlspecialchars($detailUserData['nama']) ?></h3>
				<a href="?pg=<?= enkripsi('kpi_results_user') ?>&type=<?= $selectedType ?>&periode=<?= htmlspecialchars($detailPeriode) ?>" class="btn btn-secondary"><i class="material-icons">arrow_back</i> Kembali</a>
			</div>
			<div class="detail-info">
				<p><strong>Periode:</strong> <?= htmlspecialchars(kpiPeriodeLabel($detailPeriode)) ?></p>
				<p><strong>Jabatan:</strong> <?= htmlspecialchars(kpiUserPositionLabel($detailUserData)) ?></p>
				<?php if ($detailValidationData): ?>
					<p><strong>Status Penilaian:</strong> <?= ($detailValidationData['status'] == 'approved' || $detailTotalRating > 0) ? '<span class="badge badge-success" style="background:#10b981;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">✓ Sudah Dinilai</span>' : '<span class="badge badge-warning" style="background:#f59e0b;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">⏳ Belum Dinilai</span>' ?></p>
				<?php else: ?><p><strong>Status Penilaian:</strong> <span class="badge badge-warning" style="background:#f59e0b;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">⏳ Belum Dinilai</span></p><?php endif; ?>
			</div>
			<?php if (!empty($detailChartLabels)): ?>
				<div class="kpi-results-section">
					<h4>Hasil KPI</h4>
					<table class="kpi-results-table"><thead><tr><th>Perspektif</th><th>Weight (%)</th><th>Rating</th></tr></thead><tbody>
					<?php foreach ($detailChartLabels as $idx => $namaPers): ?>
						<tr><td><?= htmlspecialchars($namaPers) ?></td><td><?= round($detailChartWeights[$idx] ?? 0) ?>%</td><td><span class="kpi-badge"><?= round($detailChartRatings[$idx] ?? 0) ?></span></td></tr>
					<?php endforeach; ?>
					</tbody></table>
					<?php kpiChartsRenderBlock(kpiChartsPerspectivePair('kpiResultsDetail', $detailChartLabels, $detailChartRatings, $detailChartWeights)); ?>
					<div class="kpi-final-rating"><h3>Total Final Rating</h3><div class="rating"><?= round($detailTotalRating) ?></div></div>
				</div>
			<?php else: ?><div class="alert alert-warning">Belum ada data perspektif.</div><?php endif; ?>
		</div>
	</div>
	<style>.kpi-container{padding:20px;max-width:1200px;margin:0 auto}.kpi-header{text-align:center;margin-bottom:30px;padding:20px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border-radius:10px}.kpi-detail-card{background:white;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);padding:20px;margin-bottom:20px}.detail-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #eee}.detail-info{margin-bottom:20px;padding:15px;background:#f8f9fa;border-radius:4px}.kpi-results-table{width:100%;border-collapse:collapse;margin:20px 0}.kpi-results-table th,.kpi-results-table td{padding:12px;text-align:center;border:1px solid #dee2e6}.kpi-results-table th{background:#f8f9fa;font-weight:600}.kpi-badge{background:#667eea;color:white;padding:4px 8px;border-radius:12px;font-size:12px;font-weight:600}.kpi-final-rating{text-align:center;margin:30px 0;padding:20px;background:#f8f9fa;border-radius:8px}.kpi-final-rating .rating{font-size:48px;font-weight:bold;color:#667eea}.btn-secondary{background:#6c757d;color:white;padding:8px 16px;border-radius:4px;text-decoration:none;display:inline-flex;align-items:center;gap:5px}.badge{padding:4px 8px;border-radius:12px;font-size:11px;font-weight:600}.badge-success{background:#28a745;color:white}.badge-danger{background:#dc3545;color:white}.badge-warning{background:#ffc107;color:#333}.badge-secondary{background:#6c757d;color:white}</style>
	<?php return;
}
?>

<div class="kpi-container">
	<div class="kpi-header">
		<h2><i class="material-icons">analytics</i> Hasil Evaluasi KPI</h2>
		<p class="kpi-description">Lihat hasil evaluasi KPI berdasarkan hak akses Anda</p>
	</div>
	<div class="kpi-controls">
		<form method="GET" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
			<input type="hidden" name="pg" value="<?= enkripsi('kpi_results_user') ?>">
			<label>Jenis KPI:</label>
			<select name="type" onchange="this.form.submit()">
				<?php foreach ($accessibleKpiTypes as $type => $config): ?>
					<option value="<?= $type ?>" <?= $selectedType === $type ? 'selected' : '' ?>><?= htmlspecialchars($config['title']) ?></option>
				<?php endforeach; ?>
			</select>
			<label>Periode:</label>
			<?php kpiRenderPeriodeSelect('periode', $selectedPeriode, $kpiPeriodeOptions, true, 'onchange="this.form.submit()"'); ?>
		</form>
	</div>
	<div class="kpi-results-list">
		<h3>Daftar Hasil KPI <?= htmlspecialchars($kpiConfig['title']) ?></h3>
		<?php if (!empty($userList)):
		$perspectivesQuery = "SELECT * FROM `$tablePerspective` WHERE target_role = '$targetRole' AND $kpiUnitFilter ORDER BY urutan";
		$perspectivesResult = mysqli_query($koneksi, $perspectivesQuery);
		$perspectives = []; while ($perspectivesResult && $p = mysqli_fetch_assoc($perspectivesResult)) $perspectives[] = $p;
		if (!empty($listChartLabels)) kpiChartsRenderBlock(kpiChartsTotalPerUser('kpiResultsList', 'Kurva Total Final Rating', $listChartLabels, $listChartTotals));
		?>
		<div class="table-responsive">
			<table class="kpi-table">
				<thead><tr><th>No</th><th>Nama</th><th>Jabatan</th><th>Periode</th><th>Status Penilaian</th><?php foreach ($perspectives as $p) echo '<th>'.htmlspecialchars($p['nama']).'</th>'; ?><th>Total</th><th>Cetak</th></tr></thead>
				<tbody>
				<?php $no=1; foreach ($userList as $userData): 
					$totalRating = calculateTotalFinalRating($koneksi, $userData['id_user'], $selectedPeriode, $tablePerspective, $tableEvaluation, $tableIndicator);
					$rowPeriodePrint = $selectedPeriode ?: kpiLatestPeriodeForUser($koneksi, (int)$userData['id_user'], $tableEvaluation);
					$printUrlRow = ($rowPeriodePrint && kpiPeriodeIsValid($rowPeriodePrint)) ? kpiPrintReportUrl('', $selectedType, (int)$userData['id_user'], $rowPeriodePrint, 'myapp') : '';
					$statusBadge = ($totalRating > 0) ? '<span class="badge badge-success" style="background:#10b981;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">✓ Sudah Dinilai</span>' : '<span class="badge badge-warning" style="background:#f59e0b;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">⏳ Belum Dinilai</span>';
				?>
					<tr>
						<td><?= $no++ ?></td>
						<td><?= htmlspecialchars($userData['nama']) ?></td>
						<td><?= htmlspecialchars(kpiUserPositionLabel($userData)) ?></td>
						<td><?= $selectedPeriode ? htmlspecialchars(kpiPeriodeLabel($selectedPeriode)) : 'Semua' ?></td>
						<td><?= $statusBadge ?></td>
						<?php foreach ($perspectives as $p) { $rating = calculatePerspectiveRating($koneksi, $userData['id_user'], $selectedPeriode, $p['id'], $tableEvaluation, $tableIndicator); echo '<td>'.($rating>0?'<span class="kpi-badge">'.round($rating).'</span>':'-').'</td>'; } ?>
						<td><?= $totalRating>0?'<span class="kpi-badge total">'.round($totalRating).'</span>':'-' ?></td>
						<td><?= $printUrlRow ? kpiRenderPrintButton($koneksi, $tableValidation, (int)$userData['id_user'], $rowPeriodePrint, $kpiUnitFilter, $printUrlRow, 'myapp', 'PDF') : '-' ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php else: ?>
			<div class="empty-state"><i class="material-icons">assignment</i><p>Belum ada data KPI untuk jenis ini pada periode ini.</p></div>
		<?php endif; ?>
	</div>
</div>

<style>.kpi-container{padding:20px;max-width:1200px;margin:0 auto}.kpi-header{text-align:center;margin-bottom:30px;padding:20px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border-radius:10px}.kpi-controls{background:white;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);margin-bottom:20px}.kpi-results-list{background:white;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}.table-responsive{overflow-x:auto}.kpi-table{width:100%;border-collapse:collapse}.kpi-table th,.kpi-table td{padding:12px;text-align:left;border-bottom:1px solid #eee}.kpi-table th{background:#f8f9fa;font-weight:600}.kpi-badge{background:#667eea;color:white;padding:4px 8px;border-radius:12px;font-size:12px;font-weight:600}.kpi-badge.total{background:#28a745;padding:6px 12px;font-size:14px;font-weight:700;border:2px solid #1e7e34}.badge{padding:4px 8px;border-radius:12px;font-size:11px;font-weight:600}.badge-success{background:#28a745;color:white}.badge-danger{background:#dc3545;color:white}.badge-warning{background:#ffc107;color:#333}.empty-state{text-align:center;padding:40px;color:#666}.empty-state i{font-size:48px;margin-bottom:10px;color:#ccc}</style>
