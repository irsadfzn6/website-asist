<?php
defined('APK') or exit('No accsess');

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Dependensi ke unit_helper telah dihapus
// require_once __DIR__ . '/kpi_periode_helper.php';

global $koneksi;
$isKpiAdmin = isset($user['level']) && $user['level'] === 'admin';
$currentPg = isset($_GET['pg']) ? $_GET['pg'] : '';
$kpiType = isset($_GET['type']) ? $_GET['type'] : '';

// User unit sekolah for filtering
$userLevelKpi = strtolower(trim((string)($user['level'] ?? '')));
$userRawUnitKpi = trim((string)($user['unit_sekolah'] ?? ''));
$isYayasanKpi = in_array($userLevelKpi, ['yayasan', 'ketua_yayasan', 'pembina_yayasan', 'keuangan'], true);
$seeAllSchoolsKpi = ($userRawUnitKpi === '' || $userRawUnitKpi === 'wira_buana' || $isYayasanKpi);
$userUnitSekolahKpi = ($seeAllSchoolsKpi ? '' : $userRawUnitKpi);

// KPI Types Configuration
$kpiTypes = [
	'guru' => [
		'title' => 'KPI Guru',
		'description' => 'Sistem evaluasi kinerja guru dengan 7 perspektif dan perhitungan rating otomatis',
		'user_jabatan' => ['guru', 'Guru'],
		'table_prefix' => 'kpi_guru'
	],
	'kaprog' => [
		'title' => 'KPI Kepala Program',
		'description' => 'Sistem evaluasi kinerja kepala program dengan perspektif khusus',
		'user_jabatan' => ['Kepala Program', 'kaprog'],
		'table_prefix' => 'kpi_kaprog'
	],
	'wakasis' => [
		'title' => 'KPI Wakil Kesiswaan',
		'description' => 'Sistem evaluasi kinerja wakil kepala sekolah bidang kesiswaan',
		'user_jabatan' => ['Wakil Kesiswaan', 'wakasis'],
		'table_prefix' => 'kpi_wakasis'
	],
	'wakakur' => [
		'title' => 'KPI Wakil Kurikulum',
		'description' => 'Sistem evaluasi kinerja wakil kepala sekolah bidang kurikulum',
		'user_jabatan' => ['Wakil Kurikulum', 'wakakur'],
		'table_prefix' => 'kpi_wakakur'
	],
	'kepsek' => [
		'title' => 'KPI Kepala Sekolah',
		'description' => 'Sistem evaluasi kinerja kepala sekolah dengan perspektif manajerial',
		'user_jabatan' => ['Kepala Sekolah', 'kepsek'],
		'table_prefix' => 'kpi_kepsek'
	]
];

// Get current KPI type configuration
$currentKpiConfig = null;
if ($kpiType && isset($kpiTypes[$kpiType])) {
	$currentKpiConfig = $kpiTypes[$kpiType];
}

// Jika belum memilih jenis KPI, tampilkan halaman pilihan
if (!$kpiType || !isset($kpiTypes[$kpiType])) {
	?>
	<style>
		.kpi-selection-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
		.kpi-selection-header { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #4338ca 100%); color: white; padding: 40px; border-radius: 16px; margin-bottom: 30px; text-align: center; box-shadow: 0 10px 35px rgba(79,70,229,.25); }
		.kpi-selection-header h1 { margin: 0 0 12px; font-size: 32px; font-weight: 700; }
		.kpi-selection-header p { margin: 0; font-size: 16px; opacity: 0.9; }
		.kpi-selection-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
		.kpi-selection-card { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; text-align: center; transition: all 0.3s ease; cursor: pointer; text-decoration: none; color: inherit; display: block; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
		.kpi-selection-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); border-color: #8b5cf6; }
		.kpi-icon-wrapper { width: 100px; height: 100px; margin: 0 auto 24px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; color: white; box-shadow: 0 8px 24px rgba(139,92,246,0.3); }
		.kpi-selection-title { font-size: 22px; font-weight: 600; color: #1f2937; margin-bottom: 8px; }
		.kpi-selection-description { font-size: 14px; color: #6b7280; line-height: 1.6; }
		@media (max-width: 768px) {
			.kpi-selection-grid { grid-template-columns: 1fr; }
			.kpi-selection-header { padding: 30px 20px; }
			.kpi-selection-header h1 { font-size: 24px; }
		}
	</style>

	<div class="kpi-selection-container">
		<?php if (!empty($_SESSION['kpi_flash'])): ?>
		<div style="background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
			<?= htmlspecialchars((string)$_SESSION['kpi_flash'], ENT_QUOTES, 'UTF-8') ?>
		</div>
		<?php unset($_SESSION['kpi_flash']); endif; ?>
		<div class="kpi-selection-header">
			<h1>Pilih Jenis KPI</h1>
			<p>Silakan pilih jenis Key Performance Indicator yang ingin Anda kelola</p>
		</div>
		
		<div class="kpi-selection-grid">
			<?php foreach ($kpiTypes as $type => $config): ?>
			<a href="?pg=<?= urlencode($currentPg) ?>&type=<?= urlencode($type) ?>" class="kpi-selection-card">
				<div class="kpi-icon-wrapper">
					<span style="font-size: 48px; line-height: 1;">
						<?php
						$materialIcons = [
							'guru' => 'school',
							'kaprog' => 'business_center',
							'wakasis' => 'groups',
							'wakakur' => 'menu_book',
							'kepsek' => 'account_balance'
						];
						echo '<i class="material-icons" style="font-size: 48px;">' . ($materialIcons[$type] ?? 'assessment') . '</i>';
						?>
					</span>
				</div>
				<h3 class="kpi-selection-title"><?= htmlspecialchars($config['title']) ?></h3>
				<p class="kpi-selection-description"><?= htmlspecialchars($config['description']) ?></p>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return;
}

// Lanjut dengan KPI yang dipilih
function kpiRedirect($currentPg, $extra = '')
{
	$url = 'index.php?pg=' . rawurlencode($currentPg);
	global $kpiType;
	if ($kpiType) {
		$url .= '&type=' . rawurlencode($kpiType);
	}
	if ($extra) {
		$url .= '&' . ltrim($extra, '&');
	}
	if (!headers_sent()) {
		header('Location: ' . $url);
		exit;
	}
	echo '<script>window.location.href=' . json_encode($url) . ';</script>';
	exit;
}

// Tabel KPI - terpadu
$tablePerspective = 'kpi_perspectives';
$tableIndicator   = 'kpi_indicators';
$tableEvaluation  = 'kpi_evaluations';
$targetRole       = $kpiType;

// Copy/Paste data antar KPI secara global (tanpa pemisah unit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['copy_kpi_data']) && $koneksi) {
	$sourceType = $_POST['source_type'] ?? '';
	$targetType = $_POST['target_type'] ?? '';
	
	if ($sourceType && $targetType && $sourceType !== $targetType) {
		$sourceConfig = $kpiTypes[$sourceType];
		$targetConfig = $kpiTypes[$targetType];
		
		$escTargetRole = mysqli_real_escape_string($koneksi, $targetType);
		$escSourceRole = mysqli_real_escape_string($koneksi, $sourceType);

		// Clear target master data for target_role
		mysqli_query($koneksi, "DELETE i FROM `kpi_indicators` i INNER JOIN `kpi_perspectives` p ON i.perspective_id = p.id WHERE p.target_role = '$escTargetRole'");
		mysqli_query($koneksi, "DELETE FROM `kpi_perspectives` WHERE target_role = '$escTargetRole'");
		
		// Copy perspectives dengan unit_sekolah & target_role
		$unitSekolahInsert = mysqli_real_escape_string($koneksi, $userUnitSekolahKpi);
		mysqli_query($koneksi, "
			INSERT INTO `kpi_perspectives` (target_role, nama, deskripsi, urutan, created_at, unit_sekolah)
			SELECT '$escTargetRole', nama, deskripsi, urutan, NOW(), '$unitSekolahInsert' FROM `kpi_perspectives`
			WHERE target_role = '$escSourceRole'
			ORDER BY urutan
		");
		
		// Copy indicators dengan unit_sekolah & target_role
		mysqli_query($koneksi, "
			INSERT INTO `kpi_indicators` (perspective_id, target_role, nama, deskripsi, urutan, created_at, unit_sekolah)
			SELECT 
				tp.id, 
				'$escTargetRole',
				i.nama, 
				i.deskripsi, 
				i.urutan, 
				NOW(),
				'$unitSekolahInsert'
			FROM `kpi_indicators` i
			JOIN `kpi_perspectives` sp ON i.perspective_id = sp.id AND sp.target_role = '$escSourceRole'
			JOIN `kpi_perspectives` tp ON sp.nama = tp.nama AND sp.urutan = tp.urutan AND tp.target_role = '$escTargetRole'
			ORDER BY tp.urutan, i.urutan
		");
		
		$_SESSION['kpi_flash'] = "Data KPI berhasil disalin dari " . $sourceConfig['title'] . " ke " . $targetConfig['title'];
		kpiRedirect($currentPg, 'type=' . urlencode($targetType));
	} else {
		$errors[] = 'Pilih sumber dan tujuan yang berbeda.';
	}
}

$flash = '';
$errors = [];

// Simpan/Update Perspektif
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_perspektif']) && $koneksi) {
	$perspectiveId = isset($_POST['perspective_id']) ? (int)$_POST['perspective_id'] : 0;
	$nama = mysqli_real_escape_string($koneksi, trim($_POST['nama_perspektif'] ?? ''));
	$deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi_perspektif'] ?? ''));
	$urutan = (int)($_POST['urutan_perspektif'] ?? 0);
	
	if ($nama == '') $errors[] = 'Nama perspektif harus diisi.';
	
	if (empty($errors)) {
		$unitValSql = kpiFormatUnitSqlVal($koneksi, $userUnitSekolahKpi);
		if ($perspectiveId > 0) {
			$query = "UPDATE `$tablePerspective` SET nama = '$nama', deskripsi = '$deskripsi', urutan = $urutan, unit_sekolah = $unitValSql WHERE id = $perspectiveId";
		} else {
			$query = "INSERT INTO `$tablePerspective` (target_role, nama, deskripsi, urutan, unit_sekolah) VALUES ('$targetRole', '$nama', '$deskripsi', $urutan, $unitValSql)";
		}
		
		if (mysqli_query($koneksi, $query)) {
			$_SESSION['kpi_flash'] = $perspectiveId > 0 ? 'Perspektif berhasil diperbarui.' : 'Perspektif berhasil ditambahkan.';
			kpiRedirect($currentPg);
		} else {
			$errors[] = 'Gagal menyimpan perspektif: ' . mysqli_error($koneksi);
		}
	}
}

// Hapus Perspektif
if (isset($_GET['hapus_perspektif']) && $koneksi) {
	$id = (int)$_GET['hapus_perspektif'];
	if ($id > 0) {
		mysqli_query($koneksi, "DELETE FROM `$tablePerspective` WHERE id = $id");
		$_SESSION['kpi_flash'] = 'Perspektif berhasil dihapus.';
		kpiRedirect($currentPg);
	}
}

// Simpan/Update Indikator
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_indikator']) && $koneksi) {
	$indicatorId = isset($_POST['indicator_id']) ? (int)$_POST['indicator_id'] : 0;
	$perspectiveId = (int)$_POST['perspective_id'];
	$nama = mysqli_real_escape_string($koneksi, trim($_POST['nama_indikator'] ?? ''));
	$deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi_indikator'] ?? ''));
	$urutan = (int)($_POST['urutan_indikator'] ?? 0);
	
	if ($perspectiveId == 0) $errors[] = 'Perspective harus dipilih.';
	if ($nama == '') $errors[] = 'Nama indikator harus diisi.';
	
	if (empty($errors)) {
		$chkPers = mysqli_query($koneksi, "SELECT id FROM `$tablePerspective` WHERE id = $perspectiveId LIMIT 1");
		if (!$chkPers || mysqli_num_rows($chkPers) === 0) {
			$errors[] = 'Perspektif tidak valid.';
		}
	}
	if (empty($errors)) {
		$unitValSql = kpiFormatUnitSqlVal($koneksi, $userUnitSekolahKpi);
		if ($indicatorId > 0) {
			$query = "UPDATE `$tableIndicator` i INNER JOIN `$tablePerspective` p ON i.perspective_id = p.id SET i.perspective_id = $perspectiveId, i.nama = '$nama', i.deskripsi = '$deskripsi', i.urutan = $urutan, i.unit_sekolah = $unitValSql WHERE i.id = $indicatorId";
		} else {
			$query = "INSERT INTO `$tableIndicator` (perspective_id, target_role, nama, deskripsi, urutan, unit_sekolah) VALUES ($perspectiveId, '$targetRole', '$nama', '$deskripsi', $urutan, $unitValSql)";
		}
		
		if (mysqli_query($koneksi, $query)) {
			$_SESSION['kpi_flash'] = $indicatorId > 0 ? 'Indikator berhasil diperbarui.' : 'Indikator berhasil ditambahkan.';
			kpiRedirect($currentPg);
		} else {
			$errors[] = 'Gagal menyimpan indikator: ' . mysqli_error($koneksi);
		}
	}
}

// Hapus Indikator
if (isset($_GET['hapus_indikator']) && $koneksi) {
	$id = (int)$_GET['hapus_indikator'];
	if ($id > 0) {
		mysqli_query($koneksi, "DELETE i FROM `$tableIndicator` i INNER JOIN `$tablePerspective` p ON i.perspective_id = p.id WHERE i.id = $id");
		$_SESSION['kpi_flash'] = 'Indikator berhasil dihapus.';
		kpiRedirect($currentPg);
	}
}

// Flash message
if (!empty($_SESSION['kpi_flash'])) {
	$flash = $_SESSION['kpi_flash'];
	unset($_SESSION['kpi_flash']);
}

// Ambil data untuk form
// Build unit filter for queries
$unitFilterSql = '';
// Yayasan sees all units, everyone else filters by their unit_sekolah
if (!$isYayasanKpi && !empty($userUnitSekolahKpi)) {
	$escUnit = mysqli_real_escape_string($koneksi, $userUnitSekolahKpi);
	$unitFilterSql = " WHERE unit_sekolah = '$escUnit'";
}

$perspectives = [];
$indicators = [];
if ($koneksi && $currentKpiConfig) {
	// Perspectives
	$whereRoleP = "WHERE target_role = '$targetRole'" . (!empty($unitFilterSql) ? " AND " . ltrim($unitFilterSql, " WHERE") : "");
	$qPers = mysqli_query($koneksi, "SELECT * FROM `$tablePerspective` $whereRoleP ORDER BY urutan");
	if ($qPers) {
		while ($row = mysqli_fetch_assoc($qPers)) {
			$perspectives[] = $row;
		}
	}
	
	// Indicators
	$indUnitFilter = $unitFilterSql ? str_replace('WHERE', 'WHERE i.', $unitFilterSql) : '';
	$whereRoleI = "WHERE p.target_role = '$targetRole'" . (!empty($indUnitFilter) ? " AND " . ltrim($indUnitFilter, " WHERE") : "");
	$qInd = mysqli_query($koneksi, "SELECT i.*, p.nama as perspective_nama FROM `$tableIndicator` i JOIN `$tablePerspective` p ON i.perspective_id = p.id $whereRoleI ORDER BY p.urutan, i.urutan");
	if ($qInd) {
		while ($row = mysqli_fetch_assoc($qInd)) {
			$indicators[] = $row;
		}
	}
}

// Edit perspektif
$editPerspective = null;
if (isset($_GET['edit_perspektif']) && $koneksi) {
	$id = (int)$_GET['edit_perspektif'];
	if ($id > 0) {
		$result = mysqli_query($koneksi, "SELECT * FROM `$tablePerspective` WHERE id = $id");
		if ($result && mysqli_num_rows($result)) {
			$editPerspective = mysqli_fetch_assoc($result);
		}
	}
}

// Edit indikator
$editIndicator = null;
if (isset($_GET['edit_indikator']) && $koneksi) {
	$id = (int)$_GET['edit_indikator'];
	if ($id > 0) {
		$result = mysqli_query($koneksi, "SELECT i.* FROM `$tableIndicator` i JOIN `$tablePerspective` p ON i.perspective_id = p.id WHERE i.id = $id");
		if ($result && mysqli_num_rows($result)) {
			$editIndicator = mysqli_fetch_assoc($result);
		}
	}
}
?>

<style>
	:root {
		--card-bg: #ffffff;
		--card-border: #e2e8f0;
		--muted-text: #64748b;
		--brand: #4f46e5;
		--brand-dark: #4338ca;
		--table-header: #f1f5f9;
		--success: #059669;
		--danger: #dc2626;
		--warning: #f59e0b;
	}
	
	.kpi-container { max-width: 1200px; margin: 0 auto; padding: 20px; font-family: system-ui, -apple-system, sans-serif; }
	.kpi-header { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #4338ca 100%); color: white; padding: 24px; border-radius: 16px; margin-bottom: 24px; }
	.kpi-header h1 { margin: 0 0 8px; font-size: 24px; font-weight: 700; }
	.kpi-header p { margin: 0; opacity: 0.9; font-size: 14px; }
	
	.kpi-tabs { display: flex; gap: 8px; margin-bottom: 20px; background: #eef2ff; padding: 4px; border-radius: 12px; }
	.kpi-tab { padding: 10px 16px; border-radius: 8px; text-decoration: none; color: #4338ca; font-weight: 500; font-size: 14px; transition: all 0.2s; }
	.kpi-tab.active { background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
	.kpi-tab:hover { background: rgba(255,255,255,0.7); }
	
	.kpi-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
	.kpi-card-title { font-size: 18px; font-weight: 600; margin: 0 0 16px; color: #1f2937; }
	.kpi-section { margin-bottom: 32px; }
	.kpi-section-title { font-size: 16px; font-weight: 600; margin: 0 0 12px; color: #374151; display: flex; align-items: center; gap: 8px; }
	.kpi-section-title::before { content: ''; width: 4px; height: 16px; background: var(--brand); border-radius: 2px; }
	
	.kpi-table { width: 100%; border-collapse: collapse; }
	.kpi-table th, .kpi-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
	.kpi-table th { background: var(--table-header); font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; }
	.kpi-table tbody tr:hover { background: #f9fafb; }
	
	.kpi-form { display: grid; gap: 16px; }
	.kpi-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; }
	.kpi-form-group label { display: block; font-weight: 500; font-size: 14px; margin-bottom: 6px; color: #374151; }
	.kpi-form-group input, .kpi-form-group select, .kpi-form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s; }
	.kpi-form-group input:focus, .kpi-form-group select:focus, .kpi-form-group textarea:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
	
	.kpi-btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; transition: all 0.2s; border: none; }
	.kpi-btn-primary { background: var(--brand); color: white; }
	.kpi-btn-primary:hover { background: var(--brand-dark); }
	.kpi-btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
	.kpi-btn-secondary:hover { background: #e5e7eb; }
	.kpi-btn-danger { background: #fef2f2; color: var(--danger); border: 1px solid #fecaca; }
	.kpi-btn-danger:hover { background: #fee2e2; }
	
	.kpi-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
	.kpi-alert-success { background: #ecfdf5; border: 1px solid #bbf7d0; color: #166534; }
	.kpi-alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
	
	.kpi-perspective-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
	.kpi-perspective-title { font-weight: 600; margin: 0 0 12px; color: #1f2937; }
	.kpi-rating-display { font-size: 24px; font-weight: 700; color: var(--brand); }
	.kpi-final-rating { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px; border-radius: 12px; text-align: center; }
	.kpi-final-rating h3 { margin: 0 0 8px; font-size: 16px; opacity: 0.9; }
	.kpi-final-rating .rating { font-size: 36px; font-weight: 700; }
	
	.kpi-actions { display: flex; gap: 8px; }
	.kpi-actions a { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; }
	.kpi-badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; background: #eef2ff; color: #4338ca; }
	.kpi-add-btn { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; border: none; border-radius: 50%; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; font-weight: bold; transition: all 0.2s; box-shadow: 0 2px 8px rgba(139,92,246,0.3); }
	.kpi-add-btn:hover { transform: scale(1.1); box-shadow: 0 4px 12px rgba(139,92,246,0.4); }
	.kpi-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 10000; }
	.kpi-modal { background: white; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; position: relative; }
	.kpi-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
	.kpi-modal-title { font-size: 18px; font-weight: 600; margin: 0; }
	.kpi-modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280; }
	.kpi-modal-close:hover { color: #374151; }
	.kpi-perspective-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
	.kpi-charts-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 20px; }
	.kpi-chart-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
	.kpi-chart-box h4 { margin: 0 0 12px; font-size: 14px; font-weight: 600; color: #374151; }
	.kpi-chart-canvas { position: relative; height: 280px; width: 100%; }
</style>

<div class="kpi-container">
	<div class="kpi-header">
		<h1><?= htmlspecialchars($currentKpiConfig['title']) ?></h1>
		<p><?= htmlspecialchars($currentKpiConfig['description']) ?></p>
		<p style="margin-top:8px;font-size:13px;opacity:.95;">Kelola master perspektif & indikator. Untuk hasil evaluasi dan cetak, gunakan menu <strong>Hasil Evaluasi KPI</strong>.</p>
		<div style="margin-top: 12px;">
			<a href="?pg=<?= urlencode($currentPg) ?>" class="kpi-btn kpi-btn-secondary" style="font-size: 12px; padding: 6px 12px;">
				« Kembali ke Pilihan KPI
			</a>
			<a href="?pg=<?= enkripsi('kpi_results') ?>&type=<?= urlencode($kpiType) ?>" class="kpi-btn kpi-btn-secondary" style="font-size: 12px; padding: 6px 12px; margin-left: 8px;">
				Hasil Evaluasi KPI
			</a>
			<button class="kpi-btn kpi-btn-primary" style="font-size: 12px; padding: 6px 12px; margin-left: 8px;" onclick="showCopyModal()">
				📋 Salin antar jenis KPI
			</button>
		</div>
	</div>

	<div id="copyModal" class="kpi-modal-overlay" style="display: none;">
		<div class="kpi-modal">
			<div class="kpi-modal-header">
				<h3 class="kpi-modal-title">Salin Data KPI</h3>
				<button class="kpi-modal-close" onclick="hideCopyModal()">&times;</button>
			</div>
			
			<form method="post" class="kpi-form">
				<input type="hidden" name="copy_kpi_data" value="1">
				<input type="hidden" name="type" value="<?= htmlspecialchars($kpiType) ?>">
				
				<div class="kpi-form-grid">
					<div class="kpi-form-group">
						<label>Sumber KPI</label>
						<select name="source_type" required>
							<option value="">-- Pilih Sumber --</option>
							<?php foreach ($kpiTypes as $type => $config): ?>
							<option value="<?= $type ?>" <?= $type === $kpiType ? 'disabled' : '' ?>>
								<?= htmlspecialchars($config['title']) ?>
							</option>
							<?php endforeach; ?>
						</select>
					</div>
					
					<div class="kpi-form-group">
						<label>Tujuan KPI</label>
						<select name="target_type" required>
							<option value="">-- Pilih Tujuan --</option>
							<?php foreach ($kpiTypes as $type => $config): ?>
							<option value="<?= $type ?>" <?= $type === $kpiType ? 'disabled' : '' ?>>
								<?= htmlspecialchars($config['title']) ?>
							</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				
				<div style="margin-top: 16px;">
					<button type="submit" class="kpi-btn kpi-btn-primary">Salin Data</button>
					<button type="button" class="kpi-btn kpi-btn-secondary" onclick="hideCopyModal()">Batal</button>
				</div>
			</form>
		</div>
	</div>
	
	<?php if ($flash): ?>
	<div class="kpi-alert kpi-alert-success"> <?= htmlspecialchars($flash) ?></div>
	<?php endif; ?>
	
	<?php if (!empty($errors)): ?>
	<div class="kpi-alert kpi-alert-error">
		<ul style="margin: 0; padding-left: 20px;">
			<?php foreach ($errors as $error): ?>
			<li><?= htmlspecialchars($error) ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<div id="perspektif" class="kpi-card">
		<h2 class="kpi-card-title">Manajemen Perspektif & Indikator</h2>
		
		<div class="kpi-section">
			<h3 class="kpi-section-title"><?= $editPerspective ? 'Edit' : 'Tambah' ?> Perspektif</h3>
			<form method="post" class="kpi-form">
				<input type="hidden" name="simpan_perspektif" value="1">
				<input type="hidden" name="perspective_id" value="<?= $editPerspective ? $editPerspective['id'] : 0 ?>">
				
				<div class="kpi-form-grid">
					<div class="kpi-form-group">
						<label>Nama Perspektif</label>
						<input type="text" name="nama_perspektif" value="<?= $editPerspective ? htmlspecialchars($editPerspective['nama']) : '' ?>" required placeholder="Masukkan nama perspektif">
					</div>
					
					<div class="kpi-form-group">
						<label>Deskripsi</label>
						<textarea name="deskripsi_perspektif" rows="3" placeholder="Masukkan deskripsi perspektif"><?= $editPerspective ? htmlspecialchars($editPerspective['deskripsi']) : '' ?></textarea>
					</div>
					
					<div class="kpi-form-group">
						<label>Urutan</label>
						<input type="number" name="urutan_perspektif" value="<?= $editPerspective ? $editPerspective['urutan'] : 0 ?>" min="0">
					</div>
				</div>
				
				<div style="margin-top: 16px;">
					<button type="submit" class="kpi-btn kpi-btn-primary"><?= $editPerspective ? 'Update' : 'Simpan' ?> Perspektif</button>
					<?php if ($editPerspective): ?>
					<a href="?pg=<?= urlencode($currentPg) ?>&type=<?= urlencode($kpiType) ?>" class="kpi-btn kpi-btn-secondary">Batal</a>
					<?php endif; ?>
				</div>
			</form>
		</div>
		
		<div class="kpi-section">
			<h3 class="kpi-section-title">Daftar Perspektif</h3>
			
			<table class="kpi-table">
				<thead>
					<tr>
						<th style="width: 40px;">No</th>
						<th>Nama Perspektif</th>
						<th>Deskripsi</th>
						<th style="width: 80px;">Urutan</th>
						<th style="width: 120px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$no = 1;
					foreach ($perspectives as $perspective): ?>
					<tr>
						<td><?= $no++ ?></td>
						<td><?= htmlspecialchars($perspective['nama']) ?></td>
						<td><?= htmlspecialchars($perspective['deskripsi'] ?: '-') ?></td>
						<td><?= $perspective['urutan'] ?></td>
						<td>
							<div class="kpi-actions">
								<a href="?pg=<?= urlencode($currentPg) ?>&type=<?= urlencode($kpiType) ?>&edit_perspektif=<?= $perspective['id'] ?>" class="kpi-btn kpi-btn-secondary">Edit</a>
								<a href="?pg=<?= urlencode($currentPg) ?>&type=<?= urlencode($kpiType) ?>&hapus_perspektif=<?= $perspective['id'] ?>" class="kpi-btn kpi-btn-danger" onclick="return confirm('Hapus perspektif ini? Semua indikator di dalamnya juga akan terhapus.')">Hapus</a>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		
		<?php foreach ($perspectives as $perspective): ?>
		<div class="kpi-section">
			<div class="kpi-perspective-header">
				<h3 class="kpi-section-title">Indikator: <?= htmlspecialchars($perspective['nama']) ?></h3>
				<button class="kpi-add-btn" onclick="openIndicatorModal(<?= $perspective['id'] ?>, '<?= htmlspecialchars($perspective['nama']) ?>')" title="Tambah Indikator">+</button>
			</div>
			
			<table class="kpi-table">
				<thead>
					<tr>
						<th style="width: 40px;">No</th>
						<th>Nama Indikator</th>
						<th>Deskripsi</th>
						<th style="width: 80px;">Urutan</th>
						<th style="width: 120px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$indicatorsInPerspective = array_filter($indicators, function($ind) use ($perspective) {
						return $ind['perspective_id'] == $perspective['id'];
					});
					$no = 1;
					foreach ($indicatorsInPerspective as $indicator): ?>
					<tr>
						<td><?= $no++ ?></td>
						<td><?= htmlspecialchars($indicator['nama']) ?></td>
						<td><?= htmlspecialchars($indicator['deskripsi'] ?: '-') ?></td>
						<td><?= $indicator['urutan'] ?></td>
						<td>
							<div class="kpi-actions">
								<button class="kpi-btn kpi-btn-secondary" onclick="editIndicator(<?= $indicator['id'] ?>, <?= $indicator['perspective_id'] ?>, '<?= htmlspecialchars($perspective['nama']) ?>', '<?= htmlspecialchars($indicator['nama']) ?>', '<?= htmlspecialchars($indicator['deskripsi']) ?>', <?= $indicator['urutan'] ?>)">Edit</button>
								<a href="?pg=<?= urlencode($currentPg) ?>&type=<?= urlencode($kpiType) ?>&hapus_indikator=<?= $indicator['id'] ?>" class="kpi-btn kpi-btn-danger" onclick="return confirm('Hapus indikator ini?')">Hapus</a>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
					<?php if (empty($indicatorsInPerspective)): ?>
					<tr>
						<td colspan="5" style="text-align: center; color: var(--muted-text);">Belum ada indikator. Klik tombol (+) untuk menambahkan.</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php endforeach; ?>
	</div>

	<div id="indicatorModal" class="kpi-modal-overlay">
		<div class="kpi-modal">
			<div class="kpi-modal-header">
				<h3 class="kpi-modal-title" id="modalTitle">Tambah Indikator</h3>
				<button class="kpi-modal-close" onclick="closeIndicatorModal()">&times;</button>
			</div>
			
			<form method="post" class="kpi-form" id="indicatorForm">
				<input type="hidden" name="simpan_indikator" value="1">
				<input type="hidden" name="indicator_id" id="indicatorId" value="<?= $editIndicator ? $editIndicator['id'] : 0 ?>">
				<input type="hidden" name="perspective_id" id="perspectiveId" value="">
				
				<div class="kpi-form-grid">
					<div class="kpi-form-group">
						<label>Perspektif</label>
						<input type="text" id="perspectiveName" readonly style="background: #f3f4f6;" class="form-control">
					</div>
					
					<div class="kpi-form-group">
						<label>Nama Indikator</label>
						<input type="text" name="nama_indikator" id="namaIndikator" value="<?= $editIndicator ? htmlspecialchars($editIndicator['nama']) : '' ?>" required placeholder="Masukkan nama indikator">
					</div>
					
					<div class="kpi-form-group">
						<label>Deskripsi</label>
						<textarea name="deskripsi_indikator" id="deskripsiIndikator" rows="3" placeholder="Masukkan deskripsi indikator"><?= $editIndicator ? htmlspecialchars($editIndicator['deskripsi']) : '' ?></textarea>
					</div>
					
					<div class="kpi-form-group">
						<label>Urutan</label>
						<input type="number" name="urutan_indikator" id="urutanIndikator" value="<?= $editIndicator ? $editIndicator['urutan'] : 0 ?>" min="0">
					</div>
				</div>
				
				<div style="margin-top: 16px;">
					<button type="submit" class="kpi-btn kpi-btn-primary" id="submitBtn">Simpan Indikator</button>
					<button type="button" class="kpi-btn kpi-btn-secondary" onclick="closeIndicatorModal()">Batal</button>
				</div>
			</form>
		</div>
	</div>
	
	</div>

<script>
// Modal functions
function showCopyModal() {
	document.getElementById('copyModal').style.display = 'flex';
}

function hideCopyModal() {
	document.getElementById('copyModal').style.display = 'none';
}

function openIndicatorModal(perspectiveId, perspectiveName) {
	document.getElementById('indicatorModal').style.display = 'flex';
	document.getElementById('perspectiveId').value = perspectiveId;
	document.getElementById('perspectiveName').value = perspectiveName;
	document.getElementById('modalTitle').textContent = 'Tambah Indikator untuk ' + perspectiveName;
	
	// Reset form untuk tambah baru
	document.getElementById('indicatorId').value = '0';
	document.getElementById('namaIndikator').value = '';
	document.getElementById('deskripsiIndikator').value = '';
	document.getElementById('urutanIndikator').value = '0';
	document.getElementById('submitBtn').textContent = 'Simpan Indikator';
}

function closeIndicatorModal() {
	document.getElementById('indicatorModal').style.display = 'none';
}

// Edit indicator dari tombol edit di tabel
function editIndicator(indicatorId, perspectiveId, perspectiveName, nama, deskripsi, urutan) {
	document.getElementById('indicatorModal').style.display = 'flex';
	document.getElementById('perspectiveId').value = perspectiveId;
	document.getElementById('perspectiveName').value = perspectiveName;
	document.getElementById('indicatorId').value = indicatorId;
	document.getElementById('namaIndikator').value = nama;
	document.getElementById('deskripsiIndikator').value = deskripsi;
	document.getElementById('urutanIndikator').value = urutan;
	document.getElementById('modalTitle').textContent = 'Edit Indikator';
	document.getElementById('submitBtn').textContent = 'Update Indikator';
}

document.addEventListener('DOMContentLoaded', function() {
	// Copy/Paste functionality
	const copyForm = document.getElementById('copyForm');
	if (copyForm) {
		copyForm.addEventListener('submit', function(e) {
			const sourceType = document.getElementById('sourceType').value;
			const targetType = document.getElementById('targetType').value;
			
			if (sourceType === targetType) {
				e.preventDefault();
				alert('Sumber dan tujuan tidak boleh sama!');
				return false;
			}
		});
	}
	
	// Close modal when clicking outside
	const indicatorModal = document.getElementById('indicatorModal');
	if (indicatorModal) {
		indicatorModal.addEventListener('click', function(e) {
			if (e.target === this) {
				closeIndicatorModal();
			}
		});
	}
});
</script>