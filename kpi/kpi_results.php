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
		if (!$koneksi || !$periode) return 0;
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

// KPI Types Configuration
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
];

// Get selected KPI type
$selectedType = isset($_GET['type']) ? $_GET['type'] : 'guru';
if (!isset($kpiTypes[$selectedType])) {
	$selectedType = 'guru';
}

$kpiConfig = $kpiTypes[$selectedType];

// Table names
$tablePerspective = 'kpi_perspectives';
$tableIndicator   = 'kpi_indicators';
$tableEvaluation  = 'kpi_evaluations';
$targetRole       = $selectedType;

$kpiUnit = kpiInitUnitContext($koneksi, 'kpi');
$kpiActiveUnitId = $kpiUnit['unit_id'];
$kpiActiveUnitLabel = $kpiUnit['label'];
$kpiUnitFilter = $kpiUnit['filter'];
$kpiUnitFilterP = $kpiUnit['filter_p'];
$kpiUnitFilterE = $kpiUnit['filter_e'];
$kpiUnitFilterV = $kpiUnit['filter_v'];

// Filter unit sekolah - kosong/null = semua sekolah, lainnya hanya unit sendiri
$userLevelKpi = strtolower(trim((string)($user['level'] ?? '')));
$userRawUnitKpi = trim((string)($user['unit_sekolah'] ?? ''));
$isYayasanKpi = in_array($userLevelKpi, ['yayasan', 'ketua_yayasan', 'pembina_yayasan', 'keuangan'], true);

if ($isYayasanKpi || $userRawUnitKpi === '' || $userRawUnitKpi === 'wira_buana') {
	$userUnitSekolahKpi = ($userLevelKpi === 'admin' && $userRawUnitKpi !== '' && $userRawUnitKpi !== 'wira_buana') ? $userRawUnitKpi : '';
} else {
	$userUnitSekolahKpi = $userRawUnitKpi;
}

if ($userUnitSekolahKpi !== '') {
	$escUnit = mysqli_real_escape_string($koneksi, $userUnitSekolahKpi);
	$kpiUnitFilter .= " AND unit_sekolah = '$escUnit'";
	$kpiUnitFilterP .= " AND p.unit_sekolah = '$escUnit'";
	$kpiUnitFilterE .= " AND e.unit_sekolah = '$escUnit'";
	$kpiUnitFilterV .= " AND v.unit_sekolah = '$escUnit'";
}


@include_once __DIR__ . '/kpi_print_report.php';

// Stub functions if kpi_print_report.php is not available
if (!function_exists('kpiRenderPrintButton')) {
	function kpiRenderPrintButton($koneksi, $tableValidation, $userId, $periode, $unitFilter, $url, $size, $label) {
		return '<button class="btn btn-sm btn-default" disabled>PDF</button>';
	}
}
if (!function_exists('kpiPrintReportUrl')) {
	function kpiPrintReportUrl($page, $type, $userId, $periode) {
		return '#';
	}
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

// Get available periods
$kpiPeriodeOptions = kpiPeriodeMergeOptions($koneksi, $tableEvaluation, '1=1');

// Get selected period
$selectedPeriode = isset($_GET['periode']) ? trim((string)$_GET['periode']) : kpiPeriodeCurrent();
$searchQ = trim((string)($_GET['q'] ?? ''));

// Pagination settings
$perPage = 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $perPage;

// Get users with KPI results (with pagination)
$usersQueryBase = "FROM users u
               WHERE u.level IN ('" . implode("','", array_map(function ($l) use ($koneksi) {
                return mysqli_real_escape_string($koneksi, $l);
               }, $kpiConfig['user_level'] ?? [])) . "')";

// Filter unit sekolah - kosong/null = semua sekolah
if ($userUnitSekolahKpi !== '') {
    $escUnit = mysqli_real_escape_string($koneksi, $userUnitSekolahKpi);
    $usersQueryBase .= " AND u.unit_sekolah = '$escUnit'";
}

// Apply search filter
if ($searchQ !== '') {
	$qEsc = mysqli_real_escape_string($koneksi, $searchQ);
	$usersQueryBase .= " AND (u.nama LIKE '%$qEsc%' OR u.level LIKE '%$qEsc%' OR COALESCE(u.level, '') LIKE '%$qEsc%')";
}

// Count total users for pagination
$countQuery = "SELECT COUNT(DISTINCT u.id_user) as total " . $usersQueryBase;
$countResult = mysqli_query($koneksi, $countQuery);
$totalUsers = ($countResult && $row = mysqli_fetch_assoc($countResult)) ? (int)$row['total'] : 0;
$totalPages = ceil($totalUsers / $perPage);

// Get paginated users
$usersQuery = "SELECT DISTINCT u.id_user, u.nama, u.level, u.unit_sekolah " . $usersQueryBase . " ORDER BY u.nama LIMIT $perPage OFFSET $offset";

$usersResult = mysqli_query($koneksi, $usersQuery);

$listChartLabels = [];
$listChartTotals = [];

// Get all users for chart data (not paginated)
$chartQuery = "SELECT DISTINCT u.id_user, u.nama " . $usersQueryBase . " ORDER BY u.nama";
$chartResult = mysqli_query($koneksi, $chartQuery);
if ($chartResult && mysqli_num_rows($chartResult) > 0) {
	while ($rowUser = mysqli_fetch_assoc($chartResult)) {
		$tr = calculateTotalFinalRating($koneksi, (int)$rowUser['id_user'], $selectedPeriode);
		if ($tr > 0) {
			$listChartLabels[] = $rowUser['nama'];
			$listChartTotals[] = round($tr, 2);
		}
	}
}

// Handle detail view
$showDetail = false;
$detailUserId = 0;
$detailPeriode = '';

if (isset($_GET['user_id']) && isset($_GET['periode'])) {
	$showDetail = true;
	$detailUserId = (int)$_GET['user_id'];
	$detailPeriode = mysqli_real_escape_string($koneksi, $_GET['periode']);
}

// If showing detail, get user info
if ($showDetail) {
	// Get user info
	$userQuery = "SELECT * FROM users WHERE id_user = $detailUserId";
	$userResult = mysqli_query($koneksi, $userQuery);
	if (!$userResult || mysqli_num_rows($userResult) == 0) {
		echo '<div class="alert alert-danger">User tidak ditemukan.</div>';
		return;
	}
	$detailUserData = mysqli_fetch_assoc($userResult);
	$detailValidationData = null;
	
	// Get perspectives and results
	$perspectivesQuery = "SELECT * FROM `$tablePerspective` WHERE target_role = '$targetRole' AND $kpiUnitFilter ORDER BY urutan";
	$perspectivesResult = mysqli_query($koneksi, $perspectivesQuery);
	
	$detailTotalRating = calculateTotalFinalRating($koneksi, $detailUserId, $detailPeriode);

	$detailPerspectives = [];
	$detailChartLabels = [];
	$detailChartRatings = [];
	$detailChartWeights = [];
	if ($perspectivesResult) {
		while ($perspective = mysqli_fetch_assoc($perspectivesResult)) {
			$detailPerspectives[] = $perspective;
			$detailChartLabels[] = $perspective['nama'];
			$detailChartWeights[] = round(calculatePerspectiveWeight($koneksi, $detailUserId, $detailPeriode, $perspective['id']), 1);
			$detailChartRatings[] = round(calculatePerspectiveRating($koneksi, $detailUserId, $detailPeriode, $perspective['id']), 2);
		}
	}
	
	// Show detail view
	?>
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Hasil KPI <?= htmlspecialchars($kpiConfig['title']) ?></h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="?pg=<?= enkripsi('kpi_results') ?>&type=<?= $selectedType ?>">Hasil KPI</a></li>
						<li class="breadcrumb-item active">Detail</li>
					</ol>
				</div>
			</div>
		</div>
	</div>
	
	<section class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header d-flex align-items-center justify-content-between flex-wrap">
							<h3 class="card-title mb-0">KPI <?= htmlspecialchars($detailUserData['nama']) ?></h3>
							<div>
								<?= kpiRenderPrintButton($koneksi, $tableValidation, $detailUserId, $detailPeriode, $kpiUnitFilter, kpiPrintReportUrl('kpi_results', $selectedType, $detailUserId, $detailPeriode), 'bootstrap-lg', 'Cetak / PDF') ?>
								<a href="?pg=<?= enkripsi('kpi_results') ?>&type=<?= $selectedType ?>&periode=<?= htmlspecialchars($detailPeriode) ?>" class="btn btn-secondary">
									<i class="fas fa-arrow-left"></i> Kembali
								</a>
							</div>
						</div>
						<div class="card-body">
							<div class="row mb-3">
								<div class="col-md-6">
									<p><strong>Nama:</strong> <?= htmlspecialchars($detailUserData['nama']) ?></p>
									<p><strong>Jabatan:</strong> <?= htmlspecialchars(kpiUserPositionLabel($detailUserData)) ?></p>
									<p><strong>Periode:</strong> <?= htmlspecialchars(kpiPeriodeLabel($detailPeriode)) ?></p>
								</div>
								<div class="col-md-6">
									<?php if ($detailTotalRating > 0): ?>
										<p><strong>Status Penilaian:</strong> <span class="badge badge-success" style="background:#10b981;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">✓ Sudah Dinilai</span></p>
									<?php else: ?>
										<p><strong>Status Penilaian:</strong> <span class="badge badge-warning" style="background:#f59e0b;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">⏳ Belum Dinilai</span></p>
									<?php endif; ?>
								</div>
							</div>
							
							<?php if (!empty($detailPerspectives)): ?>
								<div class="table-responsive">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th>Perspektif</th>
												<th>Weight (%)</th>
												<th>Rating</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($detailPerspectives as $perspective): ?>
												<tr>
													<td><?= htmlspecialchars($perspective['nama']) ?></td>
													<td><?= round(calculatePerspectiveWeight($koneksi, $detailUserId, $detailPeriode, $perspective['id'])) ?>%</td>
													<td><span class="badge badge-primary"><?= round(calculatePerspectiveRating($koneksi, $detailUserId, $detailPeriode, $perspective['id'])) ?></span></td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>

								<?php
								kpiChartsRenderBlock([
									[
										'id' => 'kpiDetailRatingChart',
										'title' => 'Kurva rating per perspektif',
										'type' => 'line',
										'labels' => $detailChartLabels,
										'datasets' => [[
											'label' => 'Rating (1–5)',
											'data' => $detailChartRatings,
											'borderColor' => '#4f46e5',
											'backgroundColor' => 'rgba(79, 70, 229, 0.12)',
											'fill' => true,
											'tension' => 0.35,
											'pointRadius' => 5,
										]],
										'options' => [
											'responsive' => true,
											'maintainAspectRatio' => false,
											'scales' => ['y' => ['min' => 0, 'max' => 5, 'ticks' => ['stepSize' => 1]]],
										],
									],
									[
										'id' => 'kpiDetailWeightChart',
										'title' => 'Kurva weight (%) per perspektif',
										'type' => 'line',
										'labels' => $detailChartLabels,
										'datasets' => [[
											'label' => 'Weight (%)',
											'data' => $detailChartWeights,
											'borderColor' => '#f59e0b',
											'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
											'fill' => true,
											'tension' => 0.35,
											'pointRadius' => 4,
										]],
										'options' => [
											'responsive' => true,
											'maintainAspectRatio' => false,
											'scales' => ['y' => ['min' => 0, 'max' => 100]],
										],
									],
								]);
								?>
								
								<div class="text-center mt-4">
									<h3>Total Final Rating</h3>
									<div style="font-size: 48px; font-weight: bold; color: #007bff;">
										<?= round($detailTotalRating) ?>
									</div>
								</div>
							<?php else: ?>
								<div class="alert alert-warning">Belum ada data perspektif untuk KPI ini.</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	
	<?php
	return;
}
?>

<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0">Hasil Evaluasi KPI</h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Home</a></li>
					<li class="breadcrumb-item active">Hasil KPI</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Hasil Akhir Evaluasi KPI</h3>
					</div>
					<div class="card-body">
						<p class="text-muted small">Cetak laporan PDF tersedia untuk pegawai yang <strong>sudah dinilai</strong> oleh atasan/evaluator.</p>
						<div class="row mb-3">
							<div class="col-md-4">
								<label for="type">Jenis KPI:</label>
								<select id="type" name="type" class="form-control" onchange="window.location.href='?pg=<?= enkripsi('kpi_results') ?>&type=' + this.value + '&periode=<?= urlencode($selectedPeriode) ?>&q=<?= urlencode($searchQ) ?>'">
									<?php foreach ($kpiTypes as $type => $config): ?>
										<option value="<?= $type ?>" <?= $selectedType === $type ? 'selected' : '' ?>>
											<?= htmlspecialchars($config['title']) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4">
								<label for="periode">Periode (Semester):</label>
								<select id="periode" name="periode" class="form-control" onchange="window.location.href='?pg=<?= enkripsi('kpi_results') ?>&type=<?= urlencode($selectedType) ?>&periode=' + encodeURIComponent(this.value) + '&q=<?= urlencode($searchQ) ?>'">
									<option value="">-- Semua Periode --</option>
									<?php foreach ($kpiPeriodeOptions as $opt): ?>
										<option value="<?= htmlspecialchars($opt['value'], ENT_QUOTES, 'UTF-8') ?>" <?= $selectedPeriode === $opt['value'] ? 'selected' : '' ?>>
											<?= htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8') ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4">
								<label for="q">Search:</label>
								<form method="get" class="d-flex" style="gap:8px; align-items:center;">
									<input type="hidden" name="pg" value="<?= enkripsi('kpi_results') ?>">
									<input type="hidden" name="type" value="<?= htmlspecialchars($selectedType, ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="periode" value="<?= htmlspecialchars($selectedPeriode, ENT_QUOTES, 'UTF-8') ?>">
									<input type="text" id="q" name="q" value="<?= htmlspecialchars($searchQ, ENT_QUOTES, 'UTF-8') ?>" class="form-control" placeholder="Cari nama/jabatan...">
									<button type="submit" class="btn btn-primary">Cari</button>
									<a class="btn btn-default" href="?pg=<?= enkripsi('kpi_results') ?>&type=<?= urlencode($selectedType) ?>&periode=<?= urlencode($selectedPeriode) ?>">Reset</a>
								</form>
							</div>
						</div>
						
						<?php if (!empty($listChartLabels)): ?>
						<?php
						kpiChartsRenderBlock([
							[
								'id' => 'kpiListTotalChart',
								'title' => 'Kurva Total Final Rating per pegawai',
								'type' => 'line',
								'labels' => $listChartLabels,
								'datasets' => [[
									'label' => 'Total Final Rating',
									'data' => $listChartTotals,
									'borderColor' => '#059669',
									'backgroundColor' => 'rgba(5, 150, 105, 0.15)',
									'fill' => true,
									'tension' => 0.35,
									'pointRadius' => 4,
								]],
								'options' => [
									'responsive' => true,
									'maintainAspectRatio' => false,
									'scales' => ['y' => ['min' => 0, 'max' => 5, 'ticks' => ['stepSize' => 1]]],
								],
							],
						]);
						?>
						<hr class="my-4">
						<?php endif; ?>

						<div class="table-responsive">
							<table class="table table-bordered">
								<thead>
									<tr>
										<th>No</th>
										<th>Nama</th>
										<th>Jabatan</th>
										<th>Periode</th>
										<th>Status Penilaian</th>
										<th style="text-align:center;">Total Rating</th>
										<th style="text-align:center;">Cetak</th>
										<th>Aksi</th>
									</tr>
								</thead>
								<tbody>
									<?php if ($usersResult && mysqli_num_rows($usersResult) > 0): ?>
										<?php $no = ($currentPage - 1) * $perPage + 1; while ($userData = mysqli_fetch_assoc($usersResult)): ?>
											<?php 
											$totalRating = calculateTotalFinalRating($koneksi, $userData['id_user'], $selectedPeriode);
											$detailPeriodeLink = $selectedPeriode !== ''
												? $selectedPeriode
												: kpiLatestPeriodeForUser($koneksi, (int)$userData['id_user'], $tableEvaluation);
											$status = $userData['status'] ?: 'pending';
											$statusBadge = ($status === 'approved' || $totalRating > 0)
												? '<span class="badge badge-success" style="background:#10b981;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">Sudah Dinilai</span>'
												: '<span class="badge badge-warning" style="background:#f59e0b;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">Belum Dinilai</span>';
											?>
											<tr>
												<td><?= $no++ ?></td>
												<td><?= htmlspecialchars($userData['nama']) ?></td>
												<td><?= htmlspecialchars(kpiUserPositionLabel($userData)) ?></td>
												<td><?= $selectedPeriode !== '' ? htmlspecialchars(kpiPeriodeLabel($selectedPeriode)) : 'Semua' ?></td>
												<td><?= $statusBadge ?></td>
												<td style="text-align:center;">
													<?php if ($totalRating > 0): ?>
														<span class="badge badge-primary" style="font-size: 14px; padding: 8px 12px;">
															<?= round($totalRating) ?>
														</span>
													<?php else: ?>
														<span class="text-muted">-</span>
													<?php endif; ?>
												</td>
												<td style="text-align:center;">
													<?php
													$detailPeriodePrint = $detailPeriodeLink !== '' && kpiPeriodeIsValid($detailPeriodeLink) ? $detailPeriodeLink : '';
													if ($detailPeriodePrint !== '') {
														$printUrlRow = kpiPrintReportUrl('kpi_results', $selectedType, (int)$userData['id_user'], $detailPeriodePrint);
														echo kpiRenderPrintButton($koneksi, $tableValidation, (int)$userData['id_user'], $detailPeriodePrint, $kpiUnitFilter, $printUrlRow, 'inline', 'PDF');
													} else {
														echo '<span class="text-muted">-</span>';
													}
													?>
												</td>
												<td>
													<a href="?pg=<?= enkripsi('kpi_results') ?>&type=<?= $selectedType ?>&user_id=<?= $userData['id_user'] ?>&periode=<?= htmlspecialchars($detailPeriodeLink) ?>" class="btn btn-sm btn-info">
														<i class="fas fa-eye"></i> Detail
													</a>
												</td>
											</tr>
										<?php endwhile; ?>
									<?php else: ?>
										<tr>
											<td colspan="8" class="text-center">
												<div class="alert alert-info">
													Belum ada data KPI untuk jenis <?= htmlspecialchars($kpiConfig['title']) ?> pada periode ini.
												</div>
											</td>
										</tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
						
						<?php if ($totalPages > 1): ?>
						<div style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:15px;padding:10px;background:#f8fafc;border-radius:8px;">
							<?php if ($currentPage > 1): ?>
							<a href="?pg=<?= enkripsi('kpi_results') ?>&type=<?= urlencode($selectedType) ?>&periode=<?= urlencode($selectedPeriode) ?>&q=<?= urlencode($searchQ) ?>&page=<?= $currentPage - 1 ?>" class="btn btn-default btn-sm">Prev</a>
							<?php endif; ?>
							<span style="font-size:14px;color:#374151;">Halaman <?= $currentPage ?> dari <?= $totalPages ?></span>
							<?php if ($currentPage < $totalPages): ?>
							<a href="?pg=<?= enkripsi('kpi_results') ?>&type=<?= urlencode($selectedType) ?>&periode=<?= urlencode($selectedPeriode) ?>&q=<?= urlencode($searchQ) ?>&page=<?= $currentPage + 1 ?>" class="btn btn-default btn-sm">Next</a>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
