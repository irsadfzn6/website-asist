<?php
defined('APK') or exit('No Access');

require_once __DIR__ . '/kpi/kpi_periode_helper.php';
require_once __DIR__ . '/surat_masuk/aktor_helper.php';

$userId = (int)($user['id_user'] ?? 0);
$username = trim((string)($user['username'] ?? ''));
$userLevel = strtolower(trim((string)($user['level'] ?? '')));
$userJabatan = trim((string)($user['jabatan'] ?? ''));
$userUnitSekolah = trim((string)($user['unit_sekolah'] ?? ''));

$isYayasanHome = in_array($userLevel, ['yayasan', 'ketua_yayasan', 'pembina_yayasan', 'keuangan'], true);
$isAdminHome = ($userLevel === 'admin');
$isKepsekHome = in_array($userLevel, ['kepsek', 'kepala_sekolah'], true);
$isStaffTuHome = in_array($userLevel, ['staff_tu', 'staff'], true);

// User dengan unit_sekolah kosong/null atau level Yayasan melihat semua sekolah
$seeAllSchools = ($userUnitSekolah === '' || $isYayasanHome);
$seeUnitData = !$seeAllSchools && ($isAdminHome || $isKepsekHome || $isStaffTuHome);
$seeOwnData = !$seeAllSchools && !$seeUnitData;

if (!function_exists('homeEscUnit')) {
	function homeEscUnit($koneksi, $unit) {
		return mysqli_real_escape_string($koneksi, trim((string)$unit));
	}
}

if (!function_exists('homeCountQuery')) {
	function homeCountQuery($koneksi, $sql) {
		$q = mysqli_query($koneksi, $sql);
		if ($q && ($row = mysqli_fetch_assoc($q))) {
			return (int)($row['total'] ?? 0);
		}
		return 0;
	}
}

if (!function_exists('homeProposalStatusLabel')) {
	function homeProposalStatusLabel($row) {
		if (($row['revisi_status'] ?? '') === 'pending') {
			return 'Revisi';
		}
		if ((int)($row['validasi_keuangan'] ?? 0) === 1) {
			return 'Selesai';
		}
		if ((int)($row['validasi_kepsek'] ?? 0) === 1 || (int)($row['validasi_ketua_yayasan'] ?? 0) === 1) {
			return 'Proses Validasi';
		}
		return 'Baru';
	}
}

if (!function_exists('homeKpiStatusLabel')) {
	function homeKpiStatusLabel($status) {
		$status = strtolower(trim((string)$status));
		if ($status === 'approved') return 'Disetujui';
		if ($status === 'rejected') return 'Ditolak';
		if ($status === 'pending') return 'Menunggu';
		return '-';
	}
}

if (!function_exists('homeSuratKeluarStatusLabel')) {
	function homeSuratKeluarStatusLabel($status) {
		$map = [
			'diajukan' => 'Diajukan',
			'diproses' => 'Diproses',
			'selesai' => 'Selesai',
			'ditolak' => 'Ditolak',
		];
		return $map[strtolower(trim((string)$status))] ?? '-';
	}
}

if (!function_exists('homeGetSelfKpiTypes')) {
	function homeGetSelfKpiTypes($userLevel, $userJabatan = '') {
		$lvl = strtolower(trim((string)$userLevel));

		if ($lvl === 'admin') {
			return [
				'guru' => 'KPI Guru',
				'kaprog' => 'KPI Kaprog',
				'wakasis' => 'KPI Wakasis',
				'wakakur' => 'KPI Wakakur',
				'kepsek' => 'KPI Kepsek',
			];
		}
		if (in_array($lvl, ['guru', 'keprog', 'kaprog', 'kesiswaan', 'wakasis', 'waka_kurikulum', 'wakakur', 'kepsek', 'kepala_sekolah'], true)) {
			$map = [
				'guru' => ['guru' => 'KPI Guru'],
				'keprog' => ['kaprog' => 'KPI Kaprog'],
				'kaprog' => ['kaprog' => 'KPI Kaprog'],
				'kesiswaan' => ['wakasis' => 'KPI Wakasis'],
				'wakasis' => ['wakasis' => 'KPI Wakasis'],
				'waka_kurikulum' => ['wakakur' => 'KPI Wakakur'],
				'wakakur' => ['wakakur' => 'KPI Wakakur'],
				'kepsek' => ['kepsek' => 'KPI Kepsek'],
				'kepala_sekolah' => ['kepsek' => 'KPI Kepsek'],
			];
			return $map[$lvl] ?? [];
		}
		return [];
	}
}

if (!function_exists('homeGetValidatorTypes')) {
	function homeGetValidatorTypes($userLevel, $userJabatan = '') {
		$lvl = strtolower(trim((string)$userLevel));
		$jbt = strtolower(trim((string)$userJabatan));
		
		if (in_array($lvl, ['keprog', 'kaprog'], true) || strpos($jbt, 'program') !== false) {
			return ['guru' => 'KPI Guru'];
		}
		if (in_array($lvl, ['waka_kurikulum', 'wakakur'], true) || strpos($jbt, 'kurikulum') !== false) {
			return ['kaprog' => 'KPI Kaprog'];
		}
		if (in_array($lvl, ['kepsek', 'kepala_sekolah'], true) || strpos($jbt, 'kepala sekolah') !== false) {
			return ['wakasis' => 'KPI Wakasis', 'wakakur' => 'KPI Wakakur'];
		}
		if (in_array($lvl, ['yayasan', 'ketua_yayasan'], true) || strpos($jbt, 'ketua yayasan') !== false) {
			return ['kepsek' => 'KPI Kepsek'];
		}
		return [];
	}
}

$canSeeSuratHome = in_array($userLevel, ['admin', 'staff', 'staff_tu', 'guru', 'keprog', 'kaprog', 'wakasis', 'kesiswaan', 'wakakur', 'waka_kurikulum', 'kepsek', 'kepala_sekolah'], true);
$canSeeSelfProposalHome = !$isStaffTuHome && !$isYayasanHome;
$canSeeValidasiProposalHome = !$isStaffTuHome && ($isYayasanHome || $isKepsekHome);

$selfKpiTypesHome = homeGetSelfKpiTypes($userLevel, $userJabatan);
$canSeeKpiHome = !$isStaffTuHome && !empty($selfKpiTypesHome);

$validatorKpiTypesHome = homeGetValidatorTypes($userLevel, $userJabatan);
$canSeeValidasiKpiHome = !$isStaffTuHome && !empty($validatorKpiTypesHome);

// --- Filter Surat Masuk ---
$smWhere = [];
if ($seeAllSchools) {
	// Semua sekolah
} elseif ($seeUnitData) {
	if ($userUnitSekolah !== '') {
		$smWhere[] = "unit_sekolah = '" . homeEscUnit($koneksi, $userUnitSekolah) . "'";
	}
} else {
	$aktorSql = suratMasukAktorMatchSql($koneksi, $userId, $username);
	$smWhere[] = $aktorSql;
	$smWhere[] = "disposisi IS NOT NULL AND TRIM(disposisi) <> ''";
	if ($userUnitSekolah !== '') {
		$smWhere[] = "unit_sekolah = '" . homeEscUnit($koneksi, $userUnitSekolah) . "'";
	}
}
$smWhereSql = $smWhere ? (' WHERE ' . implode(' AND ', $smWhere)) : '';

// --- Filter Surat Keluar ---
$skWhere = [];
if (!$seeAllSchools) {
	if ($userUnitSekolah !== '') {
		$escUnit = homeEscUnit($koneksi, $userUnitSekolah);
		$skWhere[] = "(sk.unit_sekolah = '$escUnit' OR u.unit_sekolah = '$escUnit')";
	}
	if ($seeOwnData) {
		$skWhere[] = 'sk.user_id = ' . $userId;
	}
}
$skWhereSql = $skWhere ? (' WHERE ' . implode(' AND ', $skWhere)) : '';

// --- Filter Proposal Saya ---
$countSelfProposal = 0;
$recentSelfProposal = [];
if ($canSeeSelfProposalHome) {
	$selfPropWhere = [];
	if ($seeAllSchools) {
		// All
	} elseif ($seeUnitData && $isAdminHome) {
		if ($userUnitSekolah !== '') {
			$escUnit = homeEscUnit($koneksi, $userUnitSekolah);
			$selfPropWhere[] = "(p.unit_sekolah = '$escUnit' OR u.unit_sekolah = '$escUnit')";
		}
	} else {
		if ($userUnitSekolah !== '') {
			$escUnit = homeEscUnit($koneksi, $userUnitSekolah);
			$selfPropWhere[] = "(p.unit_sekolah = '$escUnit' OR u.unit_sekolah = '$escUnit')";
		}
		$selfPropWhere[] = 'p.pengaju_id = ' . $userId;
	}
	$selfPropWhereSql = $selfPropWhere ? (' WHERE ' . implode(' AND ', $selfPropWhere)) : '';

	$countSelfProposal = homeCountQuery($koneksi, 'SELECT COUNT(*) AS total FROM proposal_kegiatan p LEFT JOIN users u ON u.id_user = p.pengaju_id' . $selfPropWhereSql);

	$qSelfProp = mysqli_query($koneksi, 'SELECT p.nama_proposal, p.diajukan_oleh, p.tanggal, p.revisi_status, p.validasi_kepsek, p.validasi_ketua_yayasan, p.validasi_keuangan FROM proposal_kegiatan p LEFT JOIN users u ON u.id_user = p.pengaju_id' . $selfPropWhereSql . ' ORDER BY p.tanggal DESC, p.id DESC LIMIT 5');
	if ($qSelfProp) {
		while ($row = mysqli_fetch_assoc($qSelfProp)) {
			$recentSelfProposal[] = $row;
		}
	}
}

// --- Filter Validasi Proposal ---
$countValidasiProposal = 0;
$recentValidasiProposal = [];
if ($canSeeValidasiProposalHome) {
	$valPropWhere = [];
	if ($seeAllSchools) {
		// All
	} else {
		if ($userUnitSekolah !== '') {
			$escUnit = homeEscUnit($koneksi, $userUnitSekolah);
			$valPropWhere[] = "(p.unit_sekolah = '$escUnit' OR u.unit_sekolah = '$escUnit')";
		}
	}
	$valPropWhereSql = $valPropWhere ? (' WHERE ' . implode(' AND ', $valPropWhere)) : '';

	$countValidasiProposal = homeCountQuery($koneksi, 'SELECT COUNT(*) AS total FROM proposal_kegiatan p LEFT JOIN users u ON u.id_user = p.pengaju_id' . $valPropWhereSql);

	$qValProp = mysqli_query($koneksi, 'SELECT p.nama_proposal, p.diajukan_oleh, p.tanggal, p.revisi_status, p.validasi_kepsek, p.validasi_ketua_yayasan, p.validasi_keuangan FROM proposal_kegiatan p LEFT JOIN users u ON u.id_user = p.pengaju_id' . $valPropWhereSql . ' ORDER BY p.tanggal DESC, p.id DESC LIMIT 5');
	if ($qValProp) {
		while ($row = mysqli_fetch_assoc($qValProp)) {
			$recentValidasiProposal[] = $row;
		}
	}
}

$countSuratMasuk = homeCountQuery($koneksi, 'SELECT COUNT(*) AS total FROM surat_masuk' . $smWhereSql);
$countSuratKeluar = homeCountQuery($koneksi, 'SELECT COUNT(*) AS total FROM surat_keluar sk LEFT JOIN users u ON u.id_user = sk.user_id' . $skWhereSql);

// --- KPI User Data ---
$countKpi = 0;
$recentKpi = [];
if ($canSeeKpiHome) {
	foreach ($selfKpiTypesHome as $type => $label) {
		$tableValidation = 'kpi_' . $type . '_validations';
		$kpiWhere = [];
		if ($seeOwnData && $userId > 0) {
			$kpiWhere[] = 'v.user_id = ' . $userId;
		} elseif (!$seeAllSchools && $userUnitSekolah !== '') {
			$escUnit = homeEscUnit($koneksi, $userUnitSekolah);
			$kpiWhere[] = "u.unit_sekolah = '$escUnit'";
		}
		$kpiWhereSql = $kpiWhere ? (' WHERE ' . implode(' AND ', $kpiWhere)) : '';
		$countKpi += homeCountQuery($koneksi, "SELECT COUNT(DISTINCT v.id) AS total FROM `$tableValidation` v JOIN users u ON u.id_user = v.user_id" . $kpiWhereSql);

		$qKpi = mysqli_query($koneksi, "SELECT u.nama, v.periode, v.status, v.updated_at
			FROM `$tableValidation` v
			JOIN users u ON u.id_user = v.user_id" . $kpiWhereSql . "
			ORDER BY v.updated_at DESC, v.id DESC LIMIT 3");
		if ($qKpi) {
			while ($row = mysqli_fetch_assoc($qKpi)) {
				$row['jenis'] = $label;
				$recentKpi[] = $row;
			}
		}
	}
	usort($recentKpi, function ($a, $b) {
		return strcmp((string)($b['updated_at'] ?? ''), (string)($a['updated_at'] ?? ''));
	});
	$recentKpi = array_slice($recentKpi, 0, 5);
}

// --- KPI Validation Data ---
$countValidasiKpi = 0;
$recentValidasiKpi = [];
if ($canSeeValidasiKpiHome) {
	foreach ($validatorKpiTypesHome as $type => $label) {
		$tableValidation = 'kpi_' . $type . '_validations';
		$vWhere = [];
		if (!$seeAllSchools && $userUnitSekolah !== '') {
			$escUnit = homeEscUnit($koneksi, $userUnitSekolah);
			$vWhere[] = "u.unit_sekolah = '$escUnit'";
		}
		$vWhereSql = $vWhere ? (' WHERE ' . implode(' AND ', $vWhere)) : '';
		$countValidasiKpi += homeCountQuery($koneksi, "SELECT COUNT(DISTINCT v.id) AS total FROM `$tableValidation` v JOIN users u ON u.id_user = v.user_id" . $vWhereSql);

		$qVal = mysqli_query($koneksi, "SELECT u.nama, v.periode, v.status, v.updated_at
			FROM `$tableValidation` v
			JOIN users u ON u.id_user = v.user_id" . $vWhereSql . "
			ORDER BY v.updated_at DESC, v.id DESC LIMIT 5");
		if ($qVal) {
			while ($row = mysqli_fetch_assoc($qVal)) {
				$row['jenis'] = $label;
				$row['type'] = $type;
				$recentValidasiKpi[] = $row;
			}
		}
	}
	usort($recentValidasiKpi, function ($a, $b) {
		return strcmp((string)($b['updated_at'] ?? ''), (string)($a['updated_at'] ?? ''));
	});
	$recentValidasiKpi = array_slice($recentValidasiKpi, 0, 5);
}

$recentSuratMasuk = [];
$qSm = mysqli_query($koneksi, 'SELECT nomor_surat, perihal, tanggal_masuk, nama_instansi FROM surat_masuk' . $smWhereSql . ' ORDER BY tanggal_masuk DESC, id DESC LIMIT 5');
if ($qSm) {
	while ($row = mysqli_fetch_assoc($qSm)) {
		$recentSuratMasuk[] = $row;
	}
}

$recentSuratKeluar = [];
$qSk = mysqli_query($koneksi, 'SELECT sk.nomor_surat, sk.perihal, sk.tanggal_pengajuan, sk.status, u.nama AS pemohon
	FROM surat_keluar sk
	LEFT JOIN users u ON u.id_user = sk.user_id' . $skWhereSql . '
	ORDER BY sk.tanggal_pengajuan DESC, sk.id DESC LIMIT 5');
if ($qSk) {
	while ($row = mysqli_fetch_assoc($qSk)) {
		$recentSuratKeluar[] = $row;
	}
}

if ($isAdminHome) {
	$linkSm = '?pg=' . urlencode(enkripsi('smasuk'));
	$linkKpi = '?pg=' . urlencode(enkripsi('kpi_results'));
} elseif ($isKepsekHome) {
	$linkSm = '?pg=' . urlencode(enkripsi('surat_masuk_kepsek'));
	$linkKpi = '?pg=' . urlencode(enkripsi('kpi_results_user'));
} elseif ($isStaffTuHome) {
	$linkSm = '?pg=' . urlencode(enkripsi('surat_masuk_staff'));
	$linkKpi = '?pg=' . urlencode(enkripsi('kpi_results'));
} else {
	$linkSm = '?pg=' . urlencode(enkripsi('surat_masuk_guru'));
	$linkKpi = '?pg=' . urlencode(enkripsi('kpi_results_user'));
}
$linkSk = '?pg=' . urlencode(enkripsi('skeluar'));
$linkSelfProposal = '?pg=' . urlencode(enkripsi('proposal'));
$linkValidasiProposal = '?pg=' . urlencode(enkripsi('proposal')) . '&view=validation';

$primaryValType = !empty($validatorKpiTypesHome) ? array_key_first($validatorKpiTypesHome) : 'guru';
$linkValidasiKpi = '?pg=' . urlencode(enkripsi('kpi_validation')) . '&type=' . urlencode($primaryValType);

$showPemohonColumn = $seeAllSchools || $seeUnitData;

// Layout grid column width calculation for widgets
$widgetCount = 0;
if ($canSeeSuratHome) $widgetCount += 2;
if ($canSeeSelfProposalHome) $widgetCount += 1;
if ($canSeeValidasiProposalHome) $widgetCount += 1;
if ($canSeeKpiHome) $widgetCount += 1;
if ($canSeeValidasiKpiHome) $widgetCount += 1;

if ($widgetCount === 1 || $widgetCount === 2) {
	$widgetColClass = 'col-xl-6 col-md-6';
} elseif ($widgetCount === 3) {
	$widgetColClass = 'col-xl-4 col-md-6';
} elseif ($widgetCount === 4) {
	$widgetColClass = 'col-xl-3 col-md-6';
} elseif ($widgetCount === 6) {
	$widgetColClass = 'col-xl-2 col-md-4 col-sm-6';
} else {
	$widgetColClass = 'col-xl col-md-4';
}
?>

<div class="row">
	<?php if ($canSeeSuratHome): ?>
	<div class="<?= $widgetColClass ?>">
		<a href="<?= htmlspecialchars($linkSm) ?>" class="text-decoration-none text-dark">
			<div class="card widget widget-stats mb-3">
				<div class="card-body">
					<div class="widget-stats-container d-flex">
						<div class="widget-stats-icon widget-stats-icon-primary">
							<i class="material-icons-outlined">mail</i>
						</div>
						<div class="widget-stats-content flex-fill">
							<span class="widget-stats-title">Surat Masuk</span>
							<span class="widget-stats-amount"><?= $countSuratMasuk ?></span>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	<div class="<?= $widgetColClass ?>">
		<a href="<?= htmlspecialchars($linkSk) ?>" class="text-decoration-none text-dark">
			<div class="card widget widget-stats mb-3">
				<div class="card-body">
					<div class="widget-stats-container d-flex">
						<div class="widget-stats-icon widget-stats-icon-warning">
							<i class="material-icons-outlined">send</i>
						</div>
						<div class="widget-stats-content flex-fill">
							<span class="widget-stats-title">Surat Keluar</span>
							<span class="widget-stats-amount"><?= $countSuratKeluar ?></span>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	<?php endif; ?>
	<?php if ($canSeeSelfProposalHome): ?>
	<div class="<?= $widgetColClass ?>">
		<a href="<?= htmlspecialchars($linkSelfProposal) ?>" class="text-decoration-none text-dark">
			<div class="card widget widget-stats mb-3">
				<div class="card-body">
					<div class="widget-stats-container d-flex">
						<div class="widget-stats-icon widget-stats-icon-success">
							<i class="material-icons-outlined">event</i>
						</div>
						<div class="widget-stats-content flex-fill">
							<span class="widget-stats-title">Proposal</span>
							<span class="widget-stats-amount"><?= $countSelfProposal ?></span>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	<?php endif; ?>
	<?php if ($canSeeValidasiProposalHome): ?>
	<div class="<?= $widgetColClass ?>">
		<a href="<?= htmlspecialchars($linkValidasiProposal) ?>" class="text-decoration-none text-dark">
			<div class="card widget widget-stats mb-3">
				<div class="card-body">
					<div class="widget-stats-container d-flex">
						<div class="widget-stats-icon widget-stats-icon-success">
							<i class="material-icons-outlined">fact_check</i>
						</div>
						<div class="widget-stats-content flex-fill">
							<span class="widget-stats-title">Validasi Proposal</span>
							<span class="widget-stats-amount"><?= $countValidasiProposal ?></span>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	<?php endif; ?>
	<?php if ($canSeeKpiHome): ?>
	<div class="<?= $widgetColClass ?>">
		<a href="<?= htmlspecialchars($linkKpi) ?>" class="text-decoration-none text-dark">
			<div class="card widget widget-stats mb-3">
				<div class="card-body">
					<div class="widget-stats-container d-flex">
						<div class="widget-stats-icon widget-stats-icon-danger">
							<i class="material-icons-outlined">analytics</i>
						</div>
						<div class="widget-stats-content flex-fill">
							<span class="widget-stats-title">KPI</span>
							<span class="widget-stats-amount"><?= $countKpi ?></span>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	<?php endif; ?>
	<?php if ($canSeeValidasiKpiHome): ?>
	<div class="<?= $widgetColClass ?>">
		<a href="<?= htmlspecialchars($linkValidasiKpi) ?>" class="text-decoration-none text-dark">
			<div class="card widget widget-stats mb-3">
				<div class="card-body">
					<div class="widget-stats-container d-flex">
						<div class="widget-stats-icon widget-stats-icon-info">
							<i class="material-icons-outlined">verified</i>
						</div>
						<div class="widget-stats-content flex-fill">
							<span class="widget-stats-title">Validasi KPI</span>
							<span class="widget-stats-amount"><?= $countValidasiKpi ?></span>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	<?php endif; ?>
</div>

<?php if ($canSeeSuratHome): ?>
<div class="row">
	<div class="col-lg-6">
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0">Surat Masuk Terbaru</h5>
				<a href="<?= htmlspecialchars($linkSm) ?>" class="btn btn-sm btn-primary">Lihat Semua</a>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover mb-0" style="font-size:12px">
						<thead>
							<tr>
								<th>Nomor</th>
								<th>Perihal</th>
								<th>Instansi</th>
								<th>Tanggal</th>
							</tr>
						</thead>
						<tbody>
						<?php if (empty($recentSuratMasuk)): ?>
							<tr><td colspan="4" class="text-center text-muted">Belum ada data surat masuk.</td></tr>
						<?php else: foreach ($recentSuratMasuk as $row): ?>
							<tr>
								<td><?= htmlspecialchars($row['nomor_surat'] ?? '-') ?></td>
								<td><?= htmlspecialchars($row['perihal'] ?? '-') ?></td>
								<td><?= htmlspecialchars($row['nama_instansi'] ?? '-') ?></td>
								<td><?= htmlspecialchars($row['tanggal_masuk'] ?? '-') ?></td>
							</tr>
						<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0">Surat Keluar Terbaru</h5>
				<a href="<?= htmlspecialchars($linkSk) ?>" class="btn btn-sm btn-primary">Lihat Semua</a>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover mb-0" style="font-size:12px">
						<thead>
							<tr>
								<th>Nomor</th>
								<th>Perihal</th>
								<?php if ($showPemohonColumn): ?><th>Pemohon</th><?php endif; ?>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
						<?php if (empty($recentSuratKeluar)): ?>
							<tr><td colspan="<?= $showPemohonColumn ? 4 : 3 ?>" class="text-center text-muted">Belum ada data surat keluar.</td></tr>
						<?php else: foreach ($recentSuratKeluar as $row): ?>
							<tr>
								<td><?= htmlspecialchars($row['nomor_surat'] ?: '-') ?></td>
								<td><?= htmlspecialchars($row['perihal'] ?? '-') ?></td>
								<?php if ($showPemohonColumn): ?><td><?= htmlspecialchars($row['pemohon'] ?? '-') ?></td><?php endif; ?>
								<td><?= htmlspecialchars(homeSuratKeluarStatusLabel($row['status'] ?? '')) ?></td>
							</tr>
						<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<?php if ($canSeeSelfProposalHome || $canSeeValidasiProposalHome || $canSeeKpiHome || $canSeeValidasiKpiHome): ?>
<div class="row">
	<?php if ($canSeeSelfProposalHome): ?>
	<div class="<?= ($canSeeValidasiProposalHome || $canSeeKpiHome || $canSeeValidasiKpiHome) ? 'col-lg-6' : 'col-lg-12' ?>">
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0">Proposal Terbaru</h5>
				<a href="<?= htmlspecialchars($linkSelfProposal) ?>" class="btn btn-sm btn-primary">Lihat Semua</a>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover mb-0" style="font-size:12px">
						<thead>
							<tr>
								<th>Nama Proposal</th>
								<?php if ($seeAllSchools || ($seeUnitData && $isAdminHome)): ?><th>Pengaju</th><?php endif; ?>
								<th>Tanggal</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
						<?php if (empty($recentSelfProposal)): ?>
							<tr><td colspan="<?= ($seeAllSchools || ($seeUnitData && $isAdminHome)) ? 4 : 3 ?>" class="text-center text-muted">Belum ada data proposal.</td></tr>
						<?php else: foreach ($recentSelfProposal as $row): ?>
							<tr>
								<td><?= htmlspecialchars($row['nama_proposal'] ?? '-') ?></td>
								<?php if ($seeAllSchools || ($seeUnitData && $isAdminHome)): ?><td><?= htmlspecialchars($row['diajukan_oleh'] ?? '-') ?></td><?php endif; ?>
								<td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
								<td><?= htmlspecialchars(homeProposalStatusLabel($row)) ?></td>
							</tr>
						<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php if ($canSeeValidasiProposalHome): ?>
	<div class="col-lg-6">
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0">Validasi Proposal Terbaru</h5>
				<a href="<?= htmlspecialchars($linkValidasiProposal) ?>" class="btn btn-sm btn-primary">Lihat Semua</a>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover mb-0" style="font-size:12px">
						<thead>
							<tr>
								<th>Nama Proposal</th>
								<th>Pengaju</th>
								<th>Tanggal</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
						<?php if (empty($recentValidasiProposal)): ?>
							<tr><td colspan="4" class="text-center text-muted">Belum ada data pengajuan validasi proposal.</td></tr>
						<?php else: foreach ($recentValidasiProposal as $row): ?>
							<tr>
								<td><?= htmlspecialchars($row['nama_proposal'] ?? '-') ?></td>
								<td><?= htmlspecialchars($row['diajukan_oleh'] ?? '-') ?></td>
								<td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
								<td><?= htmlspecialchars(homeProposalStatusLabel($row)) ?></td>
							</tr>
						<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php elseif ($canSeeKpiHome): ?>
	<div class="col-lg-6">
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0">KPI Terbaru</h5>
				<a href="<?= htmlspecialchars($linkKpi) ?>" class="btn btn-sm btn-primary">Lihat Semua</a>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover mb-0" style="font-size:12px">
						<thead>
							<tr>
								<?php if ($seeAllSchools || $seeUnitData): ?><th>Nama</th><?php endif; ?>
								<th>Jenis KPI</th>
								<th>Periode</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
						<?php if (empty($recentKpi)): ?>
							<tr><td colspan="<?= ($seeAllSchools || $seeUnitData) ? 4 : 3 ?>" class="text-center text-muted">Belum ada pengajuan KPI.</td></tr>
						<?php else: foreach ($recentKpi as $row): ?>
							<tr>
								<?php if ($seeAllSchools || $seeUnitData): ?><td><?= htmlspecialchars($row['nama'] ?? '-') ?></td><?php endif; ?>
								<td><?= htmlspecialchars($row['jenis'] ?? '-') ?></td>
								<td><?= htmlspecialchars(kpiPeriodeLabel($row['periode'] ?? '')) ?></td>
								<td><?= htmlspecialchars(homeKpiStatusLabel($row['status'] ?? '')) ?></td>
							</tr>
						<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>

<?php if ($canSeeKpiHome && ($canSeeValidasiKpiHome || $canSeeValidasiProposalHome)): ?>
<div class="row">
	<?php if ($canSeeKpiHome): ?>
	<div class="<?= $canSeeValidasiKpiHome ? 'col-lg-6' : 'col-lg-12' ?>">
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0">KPI Terbaru</h5>
				<a href="<?= htmlspecialchars($linkKpi) ?>" class="btn btn-sm btn-primary">Lihat Semua</a>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover mb-0" style="font-size:12px">
						<thead>
							<tr>
								<?php if ($seeAllSchools || $seeUnitData): ?><th>Nama</th><?php endif; ?>
								<th>Jenis KPI</th>
								<th>Periode</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
						<?php if (empty($recentKpi)): ?>
							<tr><td colspan="<?= ($seeAllSchools || $seeUnitData) ? 4 : 3 ?>" class="text-center text-muted">Belum ada pengajuan KPI.</td></tr>
						<?php else: foreach ($recentKpi as $row): ?>
							<tr>
								<?php if ($seeAllSchools || $seeUnitData): ?><td><?= htmlspecialchars($row['nama'] ?? '-') ?></td><?php endif; ?>
								<td><?= htmlspecialchars($row['jenis'] ?? '-') ?></td>
								<td><?= htmlspecialchars(kpiPeriodeLabel($row['periode'] ?? '')) ?></td>
								<td><?= htmlspecialchars(homeKpiStatusLabel($row['status'] ?? '')) ?></td>
							</tr>
						<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php if ($canSeeValidasiKpiHome): ?>
	<div class="col-lg-6">
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0">Validasi KPI Terbaru</h5>
				<a href="<?= htmlspecialchars($linkValidasiKpi) ?>" class="btn btn-sm btn-primary">Lihat Semua</a>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover mb-0" style="font-size:12px">
						<thead>
							<tr>
								<th>Nama</th>
								<th>Jenis KPI</th>
								<th>Periode</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
						<?php if (empty($recentValidasiKpi)): ?>
							<tr><td colspan="4" class="text-center text-muted">Belum ada pengajuan validasi KPI.</td></tr>
						<?php else: foreach ($recentValidasiKpi as $row): ?>
							<tr>
								<td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
								<td><?= htmlspecialchars($row['jenis'] ?? '-') ?></td>
								<td><?= htmlspecialchars(kpiPeriodeLabel($row['periode'] ?? '')) ?></td>
								<td><?= htmlspecialchars(homeKpiStatusLabel($row['status'] ?? '')) ?></td>
							</tr>
						<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>
