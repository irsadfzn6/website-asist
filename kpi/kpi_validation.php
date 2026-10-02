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

// Stub functions jika helper belum tersedia
if (!function_exists('kpiInitUnitContext')) {
	function kpiInitUnitContext($koneksi, $prefix) {
		return ['unit_id' => 1, 'label' => 'Default Unit', 'filter' => '1=1', 'filter_p' => '1=1', 'filter_e' => '1=1', 'filter_v' => '1=1'];
	}
}
if (!function_exists('kpiRangeToRating')) {
	function kpiRangeToRating($kpiRange) {
		$kpiRange = (float)$kpiRange;
		if ($kpiRange >= 0 && $kpiRange <= 20) return 1;
		if ($kpiRange > 20 && $kpiRange <= 40) return 2;
		if ($kpiRange > 40 && $kpiRange <= 60) return 3;
		if ($kpiRange > 60 && $kpiRange <= 80) return 4;
		if ($kpiRange > 80 && $kpiRange <= 100) return 5;
		return 1;
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

if (!function_exists('kpiNormalizeUserLevel')) {
	function kpiNormalizeUserLevel($value) {
		return strtolower(trim((string)($value ?? '')));
	}
}

if (!function_exists('kpiUserPositionLabel')) {
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
}

$kpiTypes = [
	'guru' => [
		'title' => 'KPI Guru',
		'user_level' => ['guru'],
		'table_prefix' => 'kpi_guru',
		'validator_level' => ['keprog'],
		'validator_jabatan' => ['Kepala Program', 'kaprog'],
		'evaluator_title' => 'Kepala Program'
	],
	'kaprog' => [
		'title' => 'KPI Kepala Program',
		'user_level' => ['keprog'],
		'table_prefix' => 'kpi_kaprog',
		'validator_level' => ['waka_kurikulum'],
		'validator_jabatan' => ['Wakil Kurikulum', 'wakakur'],
		'evaluator_title' => 'Wakil Kurikulum'
	],
	'wakasis' => [
		'title' => 'KPI Wakil Kesiswaan',
		'user_level' => ['kesiswaan'],
		'table_prefix' => 'kpi_wakasis',
		'validator_level' => ['kepsek'],
		'validator_jabatan' => ['Kepala Sekolah', 'kepsek'],
		'evaluator_title' => 'Kepala Sekolah'
	],
	'wakakur' => [
		'title' => 'KPI Wakil Kurikulum',
		'user_level' => ['waka_kurikulum'],
		'table_prefix' => 'kpi_wakakur',
		'validator_level' => ['kepsek'],
		'validator_jabatan' => ['Kepala Sekolah', 'kepsek'],
		'evaluator_title' => 'Kepala Sekolah'
	],
	'kepsek' => [
		'title' => 'KPI Kepala Sekolah',
		'user_level' => ['kepsek'],
		'table_prefix' => 'kpi_kepsek',
		'validator_level' => ['yayasan', 'ketua_yayasan'],
		'validator_jabatan' => ['Ketua Yayasan', 'ketua_yayasan', 'Yayasan'],
		'evaluator_title' => 'Ketua Yayasan'
	]
];

$validationType = isset($_GET['type']) ? $_GET['type'] : '';
if (!isset($kpiTypes[$validationType])) {
	echo '<div class="kpi-container"><div class="alert alert-danger"><strong>Akses Ditolak!</strong><br>Jenis KPI tidak valid.</div></div>';
	return;
}

$kpiConfig = $kpiTypes[$validationType];
$userLvlNorm = kpiNormalizeUserLevel($user['level'] ?? '');
$userJabNorm = trim((string)($user['jabatan'] ?? ''));

$hasValidatorAccess = (isset($kpiConfig['validator_level']) && in_array($userLvlNorm, array_map('kpiNormalizeUserLevel', $kpiConfig['validator_level']))) ||
                      (in_array($userJabNorm, $kpiConfig['validator_jabatan'] ?? [], true));

if (!$hasValidatorAccess) {
	echo '<div class="kpi-container"><div class="alert alert-danger"><strong>Akses Ditolak!</strong><br>Anda tidak memiliki otoritas untuk menilai KPI ini.<br>Level Anda: '.htmlspecialchars($user['level'] ?? '-').'<br>Posisi Anda: '.htmlspecialchars($userJabNorm !== '' ? $userJabNorm : (ucfirst(str_replace('_', ' ', $userLvlNorm)))).'</div></div>';
	return;
}

$tablePerspective = 'kpi_perspectives';
$tableIndicator   = 'kpi_indicators';
$tableEvaluation  = 'kpi_evaluations';
$targetRole       = $validationType;

$kpiUnit = kpiInitUnitContext($koneksi, 'kpi');
$kpiActiveUnitId = $kpiUnit['unit_id'];
$kpiUnitFilter   = $kpiUnit['filter'];
$kpiUnitFilterE  = $kpiUnit['filter_e'];
$kpiUnitFilterV  = $kpiUnit['filter_v'];

// Filter unit sekolah - yayasan & admin yayasan bisa lihat semua unit, lainnya hanya unit sekolahnya
$userLevelKpi = strtolower(trim((string)($user['level'] ?? '')));
$userRawUnitKpi = trim((string)($user['unit_sekolah'] ?? ''));
$isYayasanKpi = in_array($userLevelKpi, ['yayasan', 'ketua_yayasan', 'pembina_yayasan'], true);
$seeAllSchoolsKpi = ($userRawUnitKpi === '' || $userRawUnitKpi === 'wira_buana' || $isYayasanKpi);

if (!$seeAllSchoolsKpi && !empty($userRawUnitKpi)) {
	$escUnit = mysqli_real_escape_string($koneksi, $userRawUnitKpi);
	$kpiUnitFilter .= " AND unit_sekolah = '$escUnit'";
	$kpiUnitFilterE .= " AND e.unit_sekolah = '$escUnit'";
	$kpiUnitFilterV .= " AND v.unit_sekolah = '$escUnit'";
}

if (!function_exists('calculatePerspectiveWeight')) {
	function calculatePerspectiveWeight($koneksi, $userId, $periode, $perspectiveId) {
		global $tableEvaluation, $tableIndicator;
		return kpiCalculatePerspectiveWeight($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator);
	}
}
if (!function_exists('calculatePerspectiveRating')) {
	function calculatePerspectiveRating($koneksi, $userId, $periode, $perspectiveId) {
		global $tableEvaluation, $tableIndicator;
		return kpiCalculatePerspectiveRating($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator);
	}
}
if (!function_exists('calculateTotalFinalRating')) {
	function calculateTotalFinalRating($koneksi, $userId, $periode) {
		global $tablePerspective, $tableEvaluation, $tableIndicator;
		return kpiCalculateTotalFinalRating($koneksi, $userId, $periode, $tablePerspective, $tableEvaluation, $tableIndicator);
	}
}

// Handler Simpan Penilaian KPI langsung oleh Evaluator
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_evaluation') {
	$targetUserId = (int)($_POST['target_user_id'] ?? 0);
	$periode = mysqli_real_escape_string($koneksi, trim($_POST['periode'] ?? ''));
	$kpiRanges = $_POST['kpi_range'] ?? [];
	
	if ($targetUserId <= 0 || empty($periode) || !kpiPeriodeIsValid($periode)) {
		echo '<div class="alert alert-danger">Data penilaian tidak valid.</div>';
		return;
	}
	
	// Ambil unit sekolah target user
	$tUserStmt = mysqli_query($koneksi, "SELECT nama, unit_sekolah FROM users WHERE id_user = $targetUserId LIMIT 1");
	$tUserData = mysqli_fetch_assoc($tUserStmt);
	$targetUnit = $tUserData['unit_sekolah'] ?? ($user['unit_sekolah'] ?? '');
	$targetName = $tUserData['nama'] ?? 'Pengguna';
	$unitValSql = kpiFormatUnitSqlVal($koneksi, $targetUnit);
	
	$savedCount = 0;
	if (is_array($kpiRanges) && !empty($kpiRanges)) {
		foreach ($kpiRanges as $indicatorId => $rangeVal) {
			$indicatorId = (int)$indicatorId;
			$kpiRange = max(0, min(100, (float)$rangeVal));
			$rating = kpiRangeToRating($kpiRange);
			
			$saveSql = "INSERT INTO `$tableEvaluation` (user_id, indicator_id, target_role, periode, kpi_range, rating, unit_sekolah)
						VALUES ($targetUserId, $indicatorId, '$targetRole', '$periode', $kpiRange, $rating, $unitValSql)
						ON DUPLICATE KEY UPDATE kpi_range = $kpiRange, rating = $rating, target_role = '$targetRole', updated_at = NOW(), unit_sekolah = $unitValSql";
			if (mysqli_query($koneksi, $saveSql)) {
				$savedCount++;
			}
		}
	}
	
	if ($savedCount > 0) {
		$_SESSION['kpi_flash'] = "Penilaian KPI untuk <strong>" . htmlspecialchars($targetName) . "</strong> berhasil disimpan.";
		$redirectUrl = "?pg=" . enkripsi('kpi_validation') . "&type=$validationType&user_id=$targetUserId&periode=" . urlencode($periode);
		echo "<script>window.location.href='$redirectUrl';</script>";
		exit;
	} else {
		echo '<div class="alert alert-danger">Tidak ada data indikator KPI yang tersimpan.</div>';
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

$kpiPeriodeOptions = kpiPeriodeMergeOptions($koneksi, $tableEvaluation, '1=1');
$selectedPeriode = isset($_GET['periode']) ? trim((string)$_GET['periode']) : kpiPeriodeCurrent();

// Flash message
$flashMsg = '';
if (isset($_SESSION['kpi_flash'])) {
	$flashMsg = $_SESSION['kpi_flash'];
	unset($_SESSION['kpi_flash']);
}

// -----------------------------------------------------------------------------
// MODE DETAIL / INPUT PENILAIAN KPI UNTUK USER TERTENTU
// -----------------------------------------------------------------------------
if ($showDetail) {
	$userQuery = "SELECT * FROM users WHERE id_user = $detailUserId";
	$userResult = mysqli_query($koneksi, $userQuery);
	if (!$userResult || mysqli_num_rows($userResult) == 0) { 
		echo '<div class="kpi-container"><div class="alert alert-danger">User tidak ditemukan.</div></div>'; 
		return; 
	}
	$detailUserData = mysqli_fetch_assoc($userResult);
	
	// Evaluasi & Validasi eksisting
	$validationQuery = "SELECT * FROM `$tableValidation` WHERE user_id = $detailUserId AND periode = '$detailPeriode' ORDER BY id DESC LIMIT 1";
	$validationResult = mysqli_query($koneksi, $validationQuery);
	$detailValidationData = ($validationResult && mysqli_num_rows($validationResult) > 0) ? mysqli_fetch_assoc($validationResult) : null;
	
	// Ambil data evaluasi yang sudah pernah diinput sebelumnya
	$existingEvaluations = [];
	$evalQuery = mysqli_query($koneksi, "SELECT * FROM `$tableEvaluation` WHERE user_id = $detailUserId AND periode = '$detailPeriode'");
	if ($evalQuery) {
		while ($eRow = mysqli_fetch_assoc($evalQuery)) {
			$existingEvaluations[$eRow['indicator_id']] = $eRow;
		}
	}
	
	// Filter perspektif berdasarkan unit sekolah user target & target_role
	$targetUserUnit = $detailUserData['unit_sekolah'] ?? '';
	if (!empty($targetUserUnit)) {
		$escTargetUnit = mysqli_real_escape_string($koneksi, $targetUserUnit);
		$perspectivesQuery = "SELECT * FROM `$tablePerspective` WHERE target_role = '$targetRole' AND (unit_sekolah = '$escTargetUnit' OR unit_sekolah IS NULL OR unit_sekolah = '') ORDER BY urutan";
	} else {
		$perspectivesQuery = "SELECT * FROM `$tablePerspective` WHERE target_role = '$targetRole' ORDER BY urutan";
	}
	$perspectivesResult = mysqli_query($koneksi, $perspectivesQuery);
	
	// Hitung total rating eksisting
	$detailTotalRating = calculateTotalFinalRating($koneksi, $detailUserId, $detailPeriode);
	?>
	
	<div class="kpi-container">
		<?php if (!empty($flashMsg)): ?>
			<div class="alert alert-success" style="margin-bottom:20px;padding:14px 18px;background:#d1e7dd;border:1px solid #badbcc;color:#0f5132;border-radius:10px;">
				<?= $flashMsg ?>
			</div>
		<?php endif; ?>

		<div class="kpi-header" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color:white; padding:24px 30px; border-radius:16px; margin-bottom:24px;">
			<h1 style="margin:0 0 6px 0; font-size:24px; font-weight:700;"><i class="material-icons" style="vertical-align:middle;">edit_note</i> Form Penilaian <?= htmlspecialchars($kpiConfig['title']) ?></h1>
			<p style="margin:0; opacity:0.9; font-size:14px;">Input dan evaluasi skor KPI subordinat oleh atasan (Evaluator)</p>
		</div>

		<div class="kpi-card" style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid #e2e8f0; flex-wrap:wrap; gap:12px;">
				<div>
					<h2 style="margin:0 0 4px 0; font-size:20px; color:#1e293b;">Penilaian KPI: <?= htmlspecialchars($detailUserData['nama']) ?></h2>
					<div style="font-size:13px; color:#64748b;">
						<span><strong>Jabatan:</strong> <?= htmlspecialchars(kpiUserPositionLabel($detailUserData)) ?></span> | 
						<span><strong>Unit:</strong> <?= htmlspecialchars($detailUserData['unit_sekolah'] ?: 'Unit') ?></span> | 
						<span><strong>Periode:</strong> <?= htmlspecialchars(kpiPeriodeLabel($detailPeriode)) ?></span>
					</div>
				</div>
				<div>
					<a href="?pg=<?= enkripsi('kpi_validation') ?>&type=<?= $validationType ?>&periode=<?= htmlspecialchars($detailPeriode) ?>" class="btn btn-secondary" style="padding:9px 16px; background:#64748b; color:white; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-weight:500; font-size:13px;">
						<i class="material-icons" style="font-size:18px;">arrow_back</i> Kembali ke Daftar
					</a>
				</div>
			</div>

			<?php if ($detailTotalRating > 0): ?>
				<div style="margin-bottom:24px; padding:16px 20px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
					<div>
						<span style="display:inline-block; padding:4px 10px; background:#10b981; color:white; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:4px;">✓ Sudah Dinilai</span>
						<div style="font-size:13px; color:#166534;">Penilaian KPI telah disimpan. Anda dapat memperbarui skor kapan saja di bawah ini.</div>
					</div>
					<div style="text-align:right;">
						<div style="font-size:11px; color:#64748b;">Total Final Rating:</div>
						<div style="font-size:32px; font-weight:800; color:#10b981; line-height:1;"><?= round($detailTotalRating) ?></div>
					</div>
				</div>
			<?php else: ?>
				<div style="margin-bottom:24px; padding:16px 20px; background:#fffbeb; border:1px solid #fde68a; border-radius:12px;">
					<span style="display:inline-block; padding:4px 10px; background:#f59e0b; color:white; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:4px;">⏳ Belum Dinilai</span>
					<div style="font-size:13px; color:#92400e;">Silakan masukkan nilai skor (0 - 100) untuk setiap indikator di bawah ini lalu klik <strong>Simpan Penilaian KPI</strong>.</div>
				</div>
			<?php endif; ?>

			<form method="POST">
				<input type="hidden" name="action" value="save_evaluation">
				<input type="hidden" name="target_user_id" value="<?= $detailUserId ?>">
				<input type="hidden" name="periode" value="<?= htmlspecialchars($detailPeriode) ?>">

				<?php
				if ($perspectivesResult && mysqli_num_rows($perspectivesResult) > 0):
					while ($perspective = mysqli_fetch_assoc($perspectivesResult)):
						$pId = (int)$perspective['id'];
						$pName = $perspective['nama'];
						$pBobot = (float)($perspective['bobot'] ?? 0);
						
						// Ambil indikator untuk perspektif ini
						$indQuery = "SELECT * FROM `$tableIndicator` WHERE perspective_id = $pId ORDER BY urutan";
						$indResult = mysqli_query($koneksi, $indQuery);
						if (!$indResult || mysqli_num_rows($indResult) == 0) continue;
				?>
						<div class="perspective-block" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin-bottom:20px;">
							<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:10px; border-bottom:2px solid #cbd5e1;">
								<h3 style="margin:0; font-size:17px; color:#1e293b; font-weight:700;">
									<i class="material-icons" style="vertical-align:middle; color:#4f46e5; font-size:20px;">folder</i> <?= htmlspecialchars($pName) ?>
								</h3>
								<?php if ($pBobot > 0): ?>
									<span style="font-size:12px; background:#e0e7ff; color:#3730a3; padding:4px 10px; border-radius:12px; font-weight:600;">Bobot: <?= $pBobot ?>%</span>
								<?php endif; ?>
							</div>

							<div class="indicators-list" style="display:flex; flex-direction:column; gap:16px;">
								<?php 
								$noInd = 1; 
								while ($indicator = mysqli_fetch_assoc($indResult)): 
									$indId = (int)$indicator['id'];
									$existingEval = $existingEvaluations[$indId] ?? null;
									$existingVal = $existingEval !== null ? (float)$existingEval['kpi_range'] : '';
									$existingRating = $existingEval !== null ? (int)$existingEval['rating'] : 0;
								?>
									<div class="indicator-item" style="background:white; border:1px solid #e2e8f0; border-radius:10px; padding:16px; display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
										<div style="flex:1; min-width:250px;">
											<div style="font-weight:600; font-size:14px; color:#1e293b; margin-bottom:4px;">
												<?= $noInd++ ?>. <?= htmlspecialchars($indicator['nama']) ?>
											</div>
											<?php if (!empty($indicator['deskripsi'])): ?>
												<div style="font-size:12px; color:#64748b; margin-bottom:6px;">
													<?= htmlspecialchars($indicator['deskripsi']) ?>
												</div>
											<?php endif; ?>
										</div>

										<div style="display:flex; align-items:center; gap:12px;">
											<div style="text-align:right;">
												<label style="font-size:11px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Skor (0 - 100) *</label>
												<input type="number" name="kpi_range[<?= $indId ?>]" min="0" max="100" step="1" 
													   value="<?= $existingVal !== '' ? htmlspecialchars((string)$existingVal) : '' ?>" 
													   placeholder="0 - 100" required 
													   class="score-input" 
													   style="width:110px; padding:8px 12px; border:1px solid #cbd5e1; border-radius:8px; font-weight:600; text-align:center; font-size:14px;"
													   oninput="updateRatingBadge(this)">
											</div>
											<div style="text-align:center; min-width:80px;">
												<label style="font-size:11px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Rating (1-5)</label>
												<span class="rating-badge" style="display:inline-block; padding:6px 12px; background:#4f46e5; color:white; border-radius:8px; font-size:14px; font-weight:700; min-width:40px;">
													<?= $existingRating > 0 ? $existingRating : '-' ?>
												</span>
											</div>
										</div>
									</div>
								<?php endwhile; ?>
							</div>
						</div>
				<?php 
					endwhile;
				else:
				?>
					<div class="alert alert-warning">Belum ada indikator KPI yang diatur untuk kategori ini.</div>
				<?php endif; ?>

				<div style="margin-top:30px; padding-top:20px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:12px;">
					<a href="?pg=<?= enkripsi('kpi_validation') ?>&type=<?= $validationType ?>&periode=<?= htmlspecialchars($detailPeriode) ?>" class="btn btn-secondary" style="padding:10px 20px; background:#64748b; color:white; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
						Batal
					</a>
					<button type="submit" class="btn btn-success" style="padding:10px 24px; background:#10b981; color:white; border:none; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px;">
						<i class="material-icons">save</i> Simpan Penilaian KPI
					</button>
				</div>
			</form>
		</div>
	</div>

	<script>
	function updateRatingBadge(input) {
		var val = parseFloat(input.value);
		var badge = input.closest('.indicator-item').querySelector('.rating-badge');
		if (isNaN(val) || val < 0) {
			badge.innerText = '-';
			return;
		}
		var rating = 1;
		if (val >= 0 && val <= 20) rating = 1;
		else if (val > 20 && val <= 40) rating = 2;
		else if (val > 40 && val <= 60) rating = 3;
		else if (val > 60 && val <= 80) rating = 4;
		else if (val > 80 && val <= 100) rating = 5;
		badge.innerText = rating;
	}
	</script>
	<?php
	return;
}

// -----------------------------------------------------------------------------
// MODE DAFTAR USER YANG AKAN DINILAI OLEH EVALUATOR
// -----------------------------------------------------------------------------
$userLevelFilter = isset($kpiConfig['user_level']) ? $kpiConfig['user_level'] : [];
$levelIn = implode("','", array_map(function($l) use($koneksi) { return mysqli_real_escape_string($koneksi, $l); }, $userLevelFilter));

// Query seluruh user dengan level target
$usersQuery = "SELECT DISTINCT u.id_user, u.nama, u.level, u.unit_sekolah 
               FROM users u 
               WHERE u.level IN ('$levelIn')";

// Filter unit sekolah jika evaluator bukan Yayasan
if (!$seeAllSchoolsKpi && !empty($userRawUnitKpi)) {
	$escUnit = mysqli_real_escape_string($koneksi, $userRawUnitKpi);
	$usersQuery .= " AND u.unit_sekolah = '$escUnit'";
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
if (!empty($userList) && $selectedPeriode !== '') {
	foreach ($userList as $rowUser) {
		$tr = calculateTotalFinalRating($koneksi, (int)$rowUser['id_user'], $selectedPeriode);
		if ($tr > 0) { 
			$listChartLabels[] = $rowUser['nama']; 
			$listChartTotals[] = round($tr, 2); 
		}
	}
}
?>

<div class="kpi-container" style="padding:20px; max-width:1200px; margin:0 auto;">
	<?php if (!empty($flashMsg)): ?>
		<div class="alert alert-success" style="margin-bottom:20px;padding:14px 18px;background:#d1e7dd;border:1px solid #badbcc;color:#0f5132;border-radius:10px;">
			<?= $flashMsg ?>
		</div>
	<?php endif; ?>

	<div class="kpi-header" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color:white; padding:24px 30px; border-radius:16px; margin-bottom:24px;">
		<h1 style="margin:0 0 6px 0; font-size:24px; font-weight:700;"><i class="material-icons" style="vertical-align:middle;">edit_note</i> Penilaian <?= htmlspecialchars($kpiConfig['title']) ?></h1>
		<p style="margin:0; opacity:0.9; font-size:14px;">Input dan kelola penilaian evaluasi kinerja subordinat oleh <?= htmlspecialchars($kpiConfig['evaluator_title'] ?? 'Atasan') ?></p>
	</div>

	<div class="kpi-controls" style="background:white; padding:20px; border-radius:12px; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:24px;">
		<form method="GET" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
			<input type="hidden" name="pg" value="<?= enkripsi('kpi_validation') ?>">
			<input type="hidden" name="type" value="<?= htmlspecialchars($validationType) ?>">
			<label style="font-weight:600; color:#334155; font-size:14px;">Pilih Periode Evaluasi:</label>
			<?php kpiRenderPeriodeSelect('periode', $selectedPeriode, $kpiPeriodeOptions, true, 'onchange="this.form.submit()" style="padding:8px 12px; border:1px solid #cbd5e1; border-radius:8px;"'); ?>
		</form>
	</div>

	<div class="kpi-validation-list" style="background:white; padding:24px; border-radius:12px; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
		<h3 style="margin:0 0 20px 0; font-size:18px; color:#1e293b;">Daftar Anggota yang Perlu Dinilai</h3>
		
		<?php if (!empty($userList)): 
			if (!empty($listChartLabels)) kpiChartsRenderBlock(kpiChartsTotalPerUser('kpiValidationList', 'Kurva Total Final Rating', $listChartLabels, $listChartTotals));
		?>
			<div class="table-responsive" style="overflow-x:auto;">
				<table class="kpi-table" style="width:100%; border-collapse:collapse;">
					<thead>
						<tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
							<th style="padding:12px; text-align:center; font-weight:700; color:#334155;">No</th>
							<th style="padding:12px; text-align:left; font-weight:700; color:#334155;">Nama</th>
							<th style="padding:12px; text-align:left; font-weight:700; color:#334155;">Jabatan</th>
							<th style="padding:12px; text-align:center; font-weight:700; color:#334155;">Unit Sekolah</th>
							<th style="padding:12px; text-align:center; font-weight:700; color:#334155;">Periode</th>
							<th style="padding:12px; text-align:center; font-weight:700; color:#334155;">Status Penilaian</th>
							<th style="padding:12px; text-align:center; font-weight:700; color:#334155;">Total Rating</th>
							<th style="padding:12px; text-align:center; font-weight:700; color:#334155;">Aksi</th>
						</tr>
					</thead>
					<tbody>
					<?php $no=1; foreach ($userList as $userData): 
						$totalRating = calculateTotalFinalRating($koneksi, (int)$userData['id_user'], $selectedPeriode);
						$hasRated = $totalRating > 0;
						$statusBadge = $hasRated 
							? '<span class="badge badge-success" style="background:#10b981; color:white; padding:5px 10px; border-radius:12px; font-size:12px; font-weight:600;">✓ Sudah Dinilai</span>' 
							: '<span class="badge badge-warning" style="background:#f59e0b; color:white; padding:5px 10px; border-radius:12px; font-size:12px; font-weight:600;">⏳ Belum Dinilai</span>';
					?>
						<tr style="border-bottom:1px solid #f1f5f9;">
							<td style="padding:12px; text-align:center;"><?= $no++ ?></td>
							<td style="padding:12px; font-weight:600; color:#1e293b;"><?= htmlspecialchars($userData['nama']) ?></td>
							<td style="padding:12px; color:#64748b;"><?= htmlspecialchars(kpiUserPositionLabel($userData)) ?></td>
							<td style="padding:12px; text-align:center; color:#64748b;"><?= htmlspecialchars($userData['unit_sekolah'] ?: 'Unit') ?></td>
							<td style="padding:12px; text-align:center; color:#64748b;"><?= htmlspecialchars(kpiPeriodeLabel($selectedPeriode)) ?></td>
							<td style="padding:12px; text-align:center;"><?= $statusBadge ?></td>
							<td style="padding:12px; text-align:center;"><?= $totalRating > 0 ? '<span class="kpi-badge" style="background:#4f46e5; color:white; padding:4px 10px; border-radius:12px; font-weight:700;">'.round($totalRating).'</span>' : '-' ?></td>
							<td style="padding:12px; text-align:center;">
								<?php if ($hasRated): ?>
									<a href="?pg=<?= enkripsi('kpi_validation') ?>&type=<?= $validationType ?>&user_id=<?= $userData['id_user'] ?>&periode=<?= htmlspecialchars($selectedPeriode) ?>" class="btn btn-sm btn-primary" style="padding:6px 14px; background:#4f46e5; color:white; border-radius:8px; text-decoration:none; font-size:13px; font-weight:500; display:inline-flex; align-items:center; gap:4px;">
										<i class="material-icons" style="font-size:16px;">edit</i> Edit Penilaian
									</a>
								<?php else: ?>
									<a href="?pg=<?= enkripsi('kpi_validation') ?>&type=<?= $validationType ?>&user_id=<?= $userData['id_user'] ?>&periode=<?= htmlspecialchars($selectedPeriode) ?>" class="btn btn-sm btn-success" style="padding:6px 14px; background:#10b981; color:white; border-radius:8px; text-decoration:none; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:4px;">
										<i class="material-icons" style="font-size:16px;">edit_note</i> Input Penilaian
									</a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php else: ?>
			<div class="empty-state" style="text-align:center; padding:40px; color:#64748b;">
				<i class="material-icons" style="font-size:48px; color:#cbd5e1; margin-bottom:8px;">assignment</i>
				<p style="margin:0;">Tidak ada anggota yang dapat dinilai pada periode ini.</p>
			</div>
		<?php endif; ?>
	</div>
</div>
}</style>
