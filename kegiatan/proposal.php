<?php
defined('APK') or exit('No accsess');

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

@include_once __DIR__ . '/../../konek/unit_helper.php';

// Inisialisasi koneksi
global $koneksi;

// =========================================================================
// PENYESUAIAN VARIABEL USER UNTUK DATABASE & SESSION BARU
// =========================================================================
$userLevel = isset($user['level']) ? strtolower(trim($user['level'])) : (isset($_SESSION['user']['level']) ? strtolower(trim($_SESSION['user']['level'])) : 'admin');
$userLevel = str_replace(' ', '_', $userLevel);

$userId = isset($user['id_user']) ? (int)$user['id_user'] : (isset($_SESSION['user']['id_user']) ? (int)$_SESSION['user']['id_user'] : 0);
$userName = isset($user['nama']) ? trim($user['nama']) : (isset($_SESSION['user']['nama']) ? trim($_SESSION['user']['nama']) : 'User');
$userJabatan = isset($user['jabatan']) ? strtolower(trim($user['jabatan'])) : (isset($_SESSION['user']['jabatan']) ? strtolower(trim($_SESSION['user']['jabatan'])) : '');
$userRawUnitProp = trim((string)($user['unit_sekolah'] ?? (isset($_SESSION['user']['unit_sekolah']) ? $_SESSION['user']['unit_sekolah'] : '')));
$userUnitSekolah = $userRawUnitProp;
$userLevelProp = strtolower(trim((string)($user['level'] ?? '')));
$isAdmin = $userLevelProp === 'admin';
$isYayasan = in_array($userLevelProp, ['yayasan', 'ketua_yayasan', 'pembina_yayasan']);
$seeAllSchoolsProp = ($userRawUnitProp === '' || $userRawUnitProp === 'wira_buana' || $isYayasan);

// Filter unit sekolah - yayasan & admin yayasan melihat semua, lainnya unit sendiri
$unitSekolahFilter = '';
if (!$seeAllSchoolsProp && !empty($userRawUnitProp)) {
	$unitSekolahFilter = " AND unit_sekolah = '" . mysqli_real_escape_string($koneksi, $userRawUnitProp) . "'";
}
// =========================================================================

if (!function_exists('proposalActiveUnitId')) {
	function proposalActiveUnitId() {
		if (function_exists('getCurrentUnitId')) {
			return max(1, (int)getCurrentUnitId());
		}
		return isset($_SESSION['unit_id']) ? max(1, (int)$_SESSION['unit_id']) : 1;
	}
}

if (!function_exists('proposalUnitSql')) {
	function proposalUnitSql($unitId, $alias = '') {
		$unitId = max(1, (int)$unitId);
		$p = $alias !== '' ? $alias . '.' : '';
		return "({$p}unit_id = $unitId OR ({$p}unit_id IS NULL AND $unitId = 1))";
	}
}

// Ambil parameter pg untuk membangun tautan aksi
$currentPg = isset($_GET['pg']) ? $_GET['pg'] : '';

function redirectProposal($currentPg)
{
	$target = 'index.php?pg=' . rawurlencode($currentPg);
	// Jika sedang di halaman validasi, tetap di halaman validasi setelah redirect
	if (isset($_GET['view']) && $_GET['view'] === 'validation') {
		$target .= '&view=validation';
	}
	if (!headers_sent()) {
		header('Location: ' . $target);
		exit;
	}
	echo '<script>window.location.href=' . json_encode($target) . ';</script>';
	exit;
}

// Gunakan database untuk menyimpan data proposal
$tableProposal = 'proposal_kegiatan';

$propActiveUnitId = proposalActiveUnitId();
$propActiveUnitLabel = isset($_SESSION['unit_sekolah']) ? (string)$_SESSION['unit_sekolah'] : ('Unit #' . $propActiveUnitId);
$propUnitFilter = proposalUnitSql($propActiveUnitId);

// Fungsi untuk mendapatkan jabatan user (Aman untuk DB Baru)
if (!function_exists('getUserJabatan')) {
	function getUserJabatan($uid) {
		global $koneksi, $userJabatan;
		// Gunakan session/variabel global jika ada
		if (!empty($userJabatan)) return $userJabatan;
		
		$stmt = mysqli_prepare($koneksi, "SELECT jabatan FROM users WHERE id_user = ?");
		if($stmt){
			mysqli_stmt_bind_param($stmt, 'i', $uid);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_bind_result($stmt, $jabatan);
			if(mysqli_stmt_fetch($stmt)){
				mysqli_stmt_close($stmt);
				return strtolower(trim((string)$jabatan));
			}
			mysqli_stmt_close($stmt);
		}
		return '';
	}
}

// Fungsi mengecek hak validasi, memadukan Level & Jabatan + Unit Sekolah
if (!function_exists('canValidateRole')) {
	function canValidateRole($uid, $role, $lvl = '', $proposalUnit = '') {
		global $koneksi, $userUnitSekolah, $userRawUnitProp;
		
		$effectiveUnit = !empty($userUnitSekolah) ? $userUnitSekolah : (!empty($userRawUnitProp) ? $userRawUnitProp : '');
		
		// Pembina Yayasan hanya bisa melihat data proposal, tidak melakukan validasi
		if ($role === 'pembina_yayasan') {
			return false;
		}
		
		$jabatan = strtolower(trim(getUserJabatan($uid)));
		$lvl = strtolower(trim($lvl));
		
		$roleMapping = [
			'keuangan' => ['keuangan', 'bendahara', 'Keuangan', 'Bendahara'],
			'kepsek' => ['kepala sekolah', 'kepsek'],
			'ketua_yayasan' => ['ketua yayasan', 'ketua_yayasan', 'yayasan', 'Yayasan']
		];
		
		$allowed = $roleMapping[$role] ?? [];
		$hasRole = in_array($jabatan, $allowed) || in_array($lvl, $allowed);
		
		if (!$hasRole) {
			return false;
		}
		
		// Ketua Yayasan & Yayasan level bisa validasi semua unit
		if (in_array($lvl, ['ketua_yayasan', 'yayasan'])) {
			return true; // Yayasan level bisa validasi semua unit
		}
		
		// Kepsek & Keuangan unit sekolah hanya bisa validasi proposal dari unit sekolahnya sendiri
		if (in_array($role, ['kepsek', 'keuangan'])) {
			if (!empty($proposalUnit) && !empty($effectiveUnit)) {
				return $proposalUnit === $effectiveUnit;
			}
		}
		
		return $hasRole;
	}
}

// Fungsi untuk mengecek apakah semua validasi utama (Keuangan, Kepsek, Ketua Yayasan) sudah selesai
if (!function_exists('isAllValidated')) {
	function isAllValidated($proposalData) {
		return !empty($proposalData['validasi_keuangan']) &&
			   !empty($proposalData['validasi_kepsek']) &&
			   !empty($proposalData['validasi_ketua_yayasan']);
	}
}

// Upload file proposal (PDF/DOC/DOCX)
if (!function_exists('uploadFileProposal')) {
	function uploadFileProposal($file, $existingFile = null)
	{
		if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
			return ['success' => true, 'filename' => $existingFile];
		}

		if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
			return ['success' => false, 'message' => 'Gagal mengunggah file. Kode error: ' . ($file['error'] ?? '-')];
		}

		$uploadDir = __DIR__ . '/../uploads/proposal/';
		if (!file_exists($uploadDir)) {
			@mkdir($uploadDir, 0777, true);
		}

		$maxSize = 5 * 1024 * 1024; // 5MB
		if (($file['size'] ?? 0) > $maxSize) {
			return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB.'];
		}

		$ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
		if ($ext !== 'pdf') {
			return ['success' => false, 'message' => 'Format file harus PDF.'];
		}

		$baseName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($file['name']));
		$newName = 'prop_u' . proposalActiveUnitId() . '_' . date('YmdHis') . '_' . $baseName;
		$targetPath = $uploadDir . $newName;

		if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
			return ['success' => false, 'message' => 'Gagal menyimpan file di server.'];
		}

		if ($existingFile) {
			$oldPath = $uploadDir . $existingFile;
			if (is_file($oldPath)) {
				@unlink($oldPath);
			}
		}

		return ['success' => true, 'filename' => $newName];
	}
}

// Upload file LPJ (PDF/DOC/DOCX)
if (!function_exists('uploadFileLPJ')) {
	function uploadFileLPJ($file, $existingFile = null)
	{
		if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
			return ['success' => true, 'filename' => $existingFile];
		}

		if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
			return ['success' => false, 'message' => 'Gagal mengunggah file LPJ. Kode error: ' . ($file['error'] ?? '-')];
		}

		$uploadDir = __DIR__ . '/../uploads/lpj/';
		if (!file_exists($uploadDir)) {
			@mkdir($uploadDir, 0777, true);
		}

		$maxSize = 5 * 1024 * 1024; // 5MB
		if (($file['size'] ?? 0) > $maxSize) {
			return ['success' => false, 'message' => 'Ukuran file LPJ terlalu besar. Maksimal 5MB.'];
		}

		$ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
		if ($ext !== 'pdf') {
			return ['success' => false, 'message' => 'Format file LPJ harus PDF.'];
		}

		$baseName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($file['name']));
		$newName = 'lpj_u' . proposalActiveUnitId() . '_' . date('YmdHis') . '_' . $baseName;
		$targetPath = $uploadDir . $newName;

		if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
			return ['success' => false, 'message' => 'Gagal menyimpan file LPJ di server.'];
		}

		if ($existingFile) {
			$oldPath = $uploadDir . $existingFile;
			if (is_file($oldPath)) {
				@unlink($oldPath);
			}
		}

		return ['success' => true, 'filename' => $newName];
	}
}

// Proses validasi proposal
if (isset($_POST['validasi_proposal']) && is_numeric($_POST['validasi_proposal'])) {
	$proposalId = (int)$_POST['validasi_proposal'];
	$role = $_POST['role'] ?? '';
	
	if ($role === 'pembina_yayasan') {
		$_SESSION['proposal_flash'] = 'Error: Pembina Yayasan hanya memiliki akses untuk melihat proposal, bukan melakukan validasi.';
		redirectProposal($currentPg);
	}
	
	// Ambil data validasi & unit_sekolah dari proposal untuk cek urutan validasi
	$proposalUnit = '';
	$valKeuangan = 0;
	$valKepsek = 0;
	$valKetua = 0;
	
	$stmt = mysqli_prepare($koneksi, "SELECT unit_sekolah, validasi_keuangan, validasi_kepsek, validasi_ketua_yayasan, validasi_{$role} FROM `$tableProposal` WHERE id = ?");
	if ($stmt) {
		mysqli_stmt_bind_param($stmt, 'i', $proposalId);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$proposalData = mysqli_fetch_assoc($result);
		mysqli_stmt_close($stmt);
		
		if (!$proposalData) {
			$_SESSION['proposal_flash'] = 'Error: Proposal tidak ditemukan.';
			redirectProposal($currentPg);
		}
		
		$proposalUnit = $proposalData['unit_sekolah'] ?? '';
		$valKeuangan = (int)($proposalData['validasi_keuangan'] ?? 0);
		$valKepsek = (int)($proposalData['validasi_kepsek'] ?? 0);
		$valKetua = (int)($proposalData['validasi_ketua_yayasan'] ?? 0);
		
		// Cek apakah sudah divalidasi
		if (!empty($proposalData["validasi_{$role}"])) {
			$_SESSION['proposal_flash'] = 'Error: Proposal sudah divalidasi sebelumnya.';
			redirectProposal($currentPg);
		}
	}
	
	// Cek apakah user bisa validasi untuk role ini (dengan cek unit_sekolah)
	if (!canValidateRole($userId, $role, $userLevel, $proposalUnit)) {
		$_SESSION['proposal_flash'] = 'Error: Anda tidak memiliki hak untuk validasi proposal ini.';
		redirectProposal($currentPg);
	}
	
	// ENFORCE URUTAN VALIDASI BERURUTAN: Keuangan (1) -> Kepsek (2) -> Ketua Yayasan (3)
	if ($role === 'kepsek' && !$valKeuangan) {
		$_SESSION['proposal_flash'] = 'Error: Validasi harus berurutan. Menunggu validasi dari Keuangan terlebih dahulu.';
		redirectProposal($currentPg);
	}
	
	if ($role === 'ketua_yayasan') {
		if (!$valKeuangan) {
			$_SESSION['proposal_flash'] = 'Error: Validasi harus berurutan. Menunggu validasi dari Keuangan terlebih dahulu.';
			redirectProposal($currentPg);
		} else if (!$valKepsek) {
			$_SESSION['proposal_flash'] = 'Error: Validasi harus berurutan. Menunggu validasi dari Kepala Sekolah terlebih dahulu.';
			redirectProposal($currentPg);
		}
	}
	
	// Update validasi dengan prepared statement yang lebih aman
	$updateSql = "UPDATE `$tableProposal` SET 
		`validasi_{$role}` = 1,
		`validasi_{$role}_by` = ?,
		`validasi_{$role}_at` = NOW()
		WHERE id = ?";
	
	$stmt = mysqli_prepare($koneksi, $updateSql);
	if ($stmt) {
		mysqli_stmt_bind_param($stmt, 'ii', $userId, $proposalId);
		if (mysqli_stmt_execute($stmt)) {
			$_SESSION['proposal_flash'] = 'Proposal berhasil divalidasi.';
		} else {
			$_SESSION['proposal_flash'] = 'Error: Gagal memperbarui validasi.';
		}
		mysqli_stmt_close($stmt);
	} else {
		$_SESSION['proposal_flash'] = 'Error: Gagal mempersiapkan query validasi.';
	}
	
	redirectProposal($currentPg);
}

// Proses revisi proposal
if (isset($_POST['revisi_proposal']) && is_numeric($_POST['revisi_proposal'])) {
	$proposalId = (int)$_POST['revisi_proposal'];
	$role = $_POST['role'] ?? '';
	$keteranganRevisi = trim($_POST['keterangan_revisi'] ?? '');
	
	// Cek apakah user bisa revisi untuk role ini (hanya kepsek dan ketua yayasan)
	if (!in_array($role, ['kepsek', 'ketua_yayasan']) || !canValidateRole($userId, $role, $userLevel)) {
		$_SESSION['proposal_flash'] = 'Error: Anda tidak memiliki hak untuk memberi revisi role ini.';
		redirectProposal($currentPg);
	}
	
	// Validasi keterangan revisi
	if (empty($keteranganRevisi)) {
		$_SESSION['proposal_flash'] = 'Error: Keterangan revisi harus diisi.';
		redirectProposal($currentPg);
	}
	
	// Cek urutan validasi sebelum revisi
	$stmt = mysqli_prepare($koneksi, "SELECT id, nama_proposal, validasi_keuangan, validasi_kepsek FROM `$tableProposal` WHERE id = ?");
	if ($stmt) {
		mysqli_stmt_bind_param($stmt, 'i', $proposalId);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$proposalData = mysqli_fetch_assoc($result);
		mysqli_stmt_close($stmt);
		
		if (!$proposalData) {
			$_SESSION['proposal_flash'] = 'Error: Proposal tidak ditemukan.';
			redirectProposal($currentPg);
		}
		
		if ($role === 'kepsek' && empty($proposalData['validasi_keuangan'])) {
			$_SESSION['proposal_flash'] = 'Error: Validasi harus berurutan. Menunggu validasi dari Keuangan terlebih dahulu.';
			redirectProposal($currentPg);
		}
		if ($role === 'ketua_yayasan' && empty($proposalData['validasi_kepsek'])) {
			$_SESSION['proposal_flash'] = 'Error: Validasi harus berurutan. Menunggu validasi dari Kepala Sekolah terlebih dahulu.';
			redirectProposal($currentPg);
		}
		
		// Simpan revisi ke database
		$stmt = mysqli_prepare($koneksi, "INSERT INTO `proposal_revisi` (proposal_id, revisi_oleh, revisi_role, keterangan_revisi) VALUES (?, ?, ?, ?)");
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, 'iiss', $proposalId, $userId, $role, $keteranganRevisi);
			if (mysqli_stmt_execute($stmt)) {
				// Update status revisi di tabel proposal
				$updateRevisiStatus = mysqli_prepare($koneksi, "UPDATE `$tableProposal` SET revisi_status = 'pending' WHERE id = ?");
				mysqli_stmt_bind_param($updateRevisiStatus, 'i', $proposalId);
				mysqli_stmt_execute($updateRevisiStatus);
				mysqli_stmt_close($updateRevisiStatus);
				
				// Reset validasi untuk role yang memberi revisi
				$resetValidasi = mysqli_prepare($koneksi, "UPDATE `$tableProposal` SET `validasi_{$role}` = 0, `validasi_{$role}_by` = NULL, `validasi_{$role}_at` = NULL WHERE id = ?");
				mysqli_stmt_bind_param($resetValidasi, 'i', $proposalId);
				mysqli_stmt_execute($resetValidasi);
				mysqli_stmt_close($resetValidasi);
				
				$_SESSION['proposal_flash'] = 'Revisi proposal berhasil dikirim.';
			} else {
				$_SESSION['proposal_flash'] = 'Error: Gagal menyimpan revisi proposal.';
			}
			mysqli_stmt_close($stmt);
		} else {
			$_SESSION['proposal_flash'] = 'Error: Gagal mempersiapkan query revisi.';
		}
	} else {
		$_SESSION['proposal_flash'] = 'Error: Gagal mempersiapkan query.';
	}
	
	redirectProposal($currentPg);
}

// Proses perbaikan revisi
if (isset($_POST['perbaiki_revisi']) && is_numeric($_POST['perbaiki_revisi'])) {
	$proposalId = (int)$_POST['perbaiki_revisi'];
	
	// Cek apakah user adalah pengaju proposal
	$stmt = mysqli_prepare($koneksi, "SELECT id, pengaju_id FROM `$tableProposal` WHERE id = ?");
	if ($stmt) {
		mysqli_stmt_bind_param($stmt, 'i', $proposalId);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$proposalData = mysqli_fetch_assoc($result);
		mysqli_stmt_close($stmt);
		
		if (!$proposalData) {
			$_SESSION['proposal_flash'] = 'Error: Proposal tidak ditemukan.';
			redirectProposal($currentPg);
		}
		
		// Cek apakah user adalah pengaju proposal (admin bisa edit semua)
		if (!$isAdmin && $proposalData['pengaju_id'] != $userId) {
			$_SESSION['proposal_flash'] = 'Error: Anda hanya bisa memperbaiki revisi proposal Anda sendiri.';
			redirectProposal($currentPg);
		}
		
		// Update status revisi menjadi diperbaiki
		$updateRevisiStatus = mysqli_prepare($koneksi, "UPDATE `proposal_revisi` SET status = 'diperbaiki' WHERE proposal_id = ? AND status = 'pending'");
		mysqli_stmt_bind_param($updateRevisiStatus, 'i', $proposalId);
		mysqli_stmt_execute($updateRevisiStatus);
		mysqli_stmt_close($updateRevisiStatus);
		
		// Update status revisi di tabel proposal
		$updateProposalStatus = mysqli_prepare($koneksi, "UPDATE `$tableProposal` SET revisi_status = 'diperbaiki' WHERE id = ?");
		mysqli_stmt_bind_param($updateProposalStatus, 'i', $proposalId);
		mysqli_stmt_execute($updateProposalStatus);
		mysqli_stmt_close($updateProposalStatus);
		
		$_SESSION['proposal_flash'] = 'Revisi proposal berhasil diperbaiki.';
	} else {
		$_SESSION['proposal_flash'] = 'Error: Gagal mempersiapkan query.';
	}
	
	redirectProposal($currentPg);
}

// Handler untuk GET parameter revisi (buka modal revisi)
if (isset($_GET['revisi']) && is_numeric($_GET['revisi'])) {
	$revisiProposalId = (int)$_GET['revisi'];
	$revisiRole = $_GET['role'] ?? '';
	
	// Cek apakah user bisa revisi untuk role ini
	if (!in_array($revisiRole, ['kepsek', 'ketua_yayasan']) || !canValidateRole($userId, $revisiRole, $userLevel)) {
		$_SESSION['proposal_flash'] = 'Error: Anda tidak memiliki hak untuk memberi revisi role ini.';
		redirectProposal($currentPg);
	}
	
	// Cek apakah proposal ada
	$stmt = mysqli_prepare($koneksi, "SELECT id, nama_proposal FROM `$tableProposal` WHERE id = ?");
	if ($stmt) {
		mysqli_stmt_bind_param($stmt, 'i', $revisiProposalId);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$proposalData = mysqli_fetch_assoc($result);
		mysqli_stmt_close($stmt);
		
		if (!$proposalData) {
			$_SESSION['proposal_flash'] = 'Error: Proposal tidak ditemukan.';
			redirectProposal($currentPg);
		}
		
		// Set variabel untuk modal revisi
		$showRevisiModal = true;
		$revisiModalData = [
			'proposal_id' => $revisiProposalId,
			'role' => $revisiRole,
			'proposal_name' => $proposalData['nama_proposal'],
			'role_name' => $revisiRole === 'kepsek' ? 'Kepala Sekolah' : 'Ketua Yayasan'
		];
	} else {
		$_SESSION['proposal_flash'] = 'Error: Gagal mempersiapkan query.';
		redirectProposal($currentPg);
	}
}

// Handler untuk GET parameter view_revisi (lihat detail revisi)
if (isset($_GET['view_revisi']) && is_numeric($_GET['view_revisi'])) {
	$viewRevisiProposalId = (int)$_GET['view_revisi'];
	$viewRevisiRole = $_GET['role'] ?? '';
	
	// Cek apakah proposal ada
	$stmt = mysqli_prepare($koneksi, "SELECT id, nama_proposal, pengaju_id FROM `$tableProposal` WHERE id = ?");
	if ($stmt) {
		mysqli_stmt_bind_param($stmt, 'i', $viewRevisiProposalId);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$proposalData = mysqli_fetch_assoc($result);
		mysqli_stmt_close($stmt);
		
		if (!$proposalData) {
			$_SESSION['proposal_flash'] = 'Error: Proposal tidak ditemukan.';
			redirectProposal($currentPg);
		}
		
		// Ambil data revisi dengan fallback fleksibel
		$revisiData = null;
		
		// Try 1: match role & status pending
		if (!empty($viewRevisiRole)) {
			$stmt = mysqli_prepare($koneksi, "
				SELECT pr.keterangan_revisi, pr.status, pr.created_at, pr.revisi_role, u.nama as revisi_oleh_nama
				FROM proposal_revisi pr
				LEFT JOIN users u ON pr.revisi_oleh = u.id_user
				WHERE pr.proposal_id = ? AND pr.revisi_role = ? AND pr.status = 'pending'
				ORDER BY pr.created_at DESC
				LIMIT 1
			");
			if ($stmt) {
				mysqli_stmt_bind_param($stmt, 'is', $viewRevisiProposalId, $viewRevisiRole);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);
				$revisiData = mysqli_fetch_assoc($result);
				mysqli_stmt_close($stmt);
			}
		}
		
		// Try 2: match status pending (sembarang role)
		if (!$revisiData) {
			$stmt = mysqli_prepare($koneksi, "
				SELECT pr.keterangan_revisi, pr.status, pr.created_at, pr.revisi_role, u.nama as revisi_oleh_nama
				FROM proposal_revisi pr
				LEFT JOIN users u ON pr.revisi_oleh = u.id_user
				WHERE pr.proposal_id = ? AND pr.status = 'pending'
				ORDER BY pr.created_at DESC
				LIMIT 1
			");
			if ($stmt) {
				mysqli_stmt_bind_param($stmt, 'i', $viewRevisiProposalId);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);
				$revisiData = mysqli_fetch_assoc($result);
				mysqli_stmt_close($stmt);
			}
		}
		
		// Try 3: match role (sembarang status)
		if (!$revisiData && !empty($viewRevisiRole)) {
			$stmt = mysqli_prepare($koneksi, "
				SELECT pr.keterangan_revisi, pr.status, pr.created_at, pr.revisi_role, u.nama as revisi_oleh_nama
				FROM proposal_revisi pr
				LEFT JOIN users u ON pr.revisi_oleh = u.id_user
				WHERE pr.proposal_id = ? AND pr.revisi_role = ?
				ORDER BY pr.created_at DESC
				LIMIT 1
			");
			if ($stmt) {
				mysqli_stmt_bind_param($stmt, 'is', $viewRevisiProposalId, $viewRevisiRole);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);
				$revisiData = mysqli_fetch_assoc($result);
				mysqli_stmt_close($stmt);
			}
		}
		
		// Try 4: match proposal_id (sembarang role & status)
		if (!$revisiData) {
			$stmt = mysqli_prepare($koneksi, "
				SELECT pr.keterangan_revisi, pr.status, pr.created_at, pr.revisi_role, u.nama as revisi_oleh_nama
				FROM proposal_revisi pr
				LEFT JOIN users u ON pr.revisi_oleh = u.id_user
				WHERE pr.proposal_id = ?
				ORDER BY pr.created_at DESC
				LIMIT 1
			");
			if ($stmt) {
				mysqli_stmt_bind_param($stmt, 'i', $viewRevisiProposalId);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);
				$revisiData = mysqli_fetch_assoc($result);
				mysqli_stmt_close($stmt);
			}
		}
		
		// Set data modal revisi (selalu tampilkan modal)
		$showDetailRevisiModal = true;
		$actualRole = !empty($revisiData['revisi_role']) ? $revisiData['revisi_role'] : (!empty($viewRevisiRole) ? $viewRevisiRole : 'ketua_yayasan');
		$roleName = $actualRole === 'kepsek' ? 'Kepala Sekolah' : ($actualRole === 'ketua_yayasan' ? 'Ketua Yayasan' : ucfirst($actualRole));
		
		$detailRevisiData = [
			'proposal_id' => $viewRevisiProposalId,
			'proposal_name' => $proposalData['nama_proposal'],
			'role' => $actualRole,
			'role_name' => $roleName,
			'keterangan_revisi' => !empty($revisiData['keterangan_revisi']) ? $revisiData['keterangan_revisi'] : 'Proposal ini memerlukan revisi. Silakan periksa kembali berkas proposal Anda.',
			'status' => !empty($revisiData['status']) ? $revisiData['status'] : 'pending',
			'created_at' => !empty($revisiData['created_at']) ? date('d/m/Y H:i', strtotime($revisiData['created_at'])) : date('d/m/Y H:i'),
			'revisi_oleh' => !empty($revisiData['revisi_oleh_nama']) ? $revisiData['revisi_oleh_nama'] : 'Validator'
		];
	} else {
		$_SESSION['proposal_flash'] = 'Error: Gagal mempersiapkan query.';
		redirectProposal($currentPg);
	}
}

// Handler AJAX untuk get revisi
if (isset($_GET['get_revisi']) && isset($_GET['proposal_id']) && isset($_GET['role'])) {
	$proposalId = (int)$_GET['proposal_id'];
	$role = $_GET['role'];
	
	header('Content-Type: application/json');
	
	try {
		$stmt = mysqli_prepare($koneksi, "
			SELECT pr.keterangan_revisi, pr.status, pr.created_at, p.nama_proposal, u.nama as revisi_oleh_nama
			FROM proposal_revisi pr
			LEFT JOIN proposal_kegiatan p ON pr.proposal_id = p.id
			LEFT JOIN users u ON pr.revisi_oleh = u.id_user
			WHERE pr.proposal_id = ? AND pr.revisi_role = ? AND pr.status = 'pending'
			AND " . proposalUnitSql(proposalActiveUnitId(), 'p') . "
			ORDER BY pr.created_at DESC
			LIMIT 1
		");
		
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, 'is', $proposalId, $role);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);
			$revisiData = mysqli_fetch_assoc($result);
			mysqli_stmt_close($stmt);
			
			if ($revisiData) {
				echo json_encode([
					'success' => true,
					'proposal_name' => $revisiData['nama_proposal'],
					'role_name' => $role === 'kepsek' ? 'Kepala Sekolah' : 'Ketua Yayasan',
					'keterangan_revisi' => $revisiData['keterangan_revisi'],
					'status' => $revisiData['status'],
					'created_at' => date('d/m/Y H:i', strtotime($revisiData['created_at'])),
					'revisi_oleh' => $revisiData['revisi_oleh_nama']
				]);
			} else {
				echo json_encode(['success' => false, 'message' => 'Data revisi tidak ditemukan']);
			}
		} else {
			echo json_encode(['success' => false, 'message' => 'Query error']);
		}
	} catch (Exception $e) {
		echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
	}
	exit;
}

// Handler untuk GET parameter upload_lpj (buka modal LPJ & Dokumentasi)
if (isset($_GET['upload_lpj']) && is_numeric($_GET['upload_lpj'])) {
	$lpjProposalId = (int)$_GET['upload_lpj'];
	$stmt = mysqli_prepare($koneksi, "SELECT * FROM `$tableProposal` WHERE id = ?");
	if ($stmt) {
		mysqli_stmt_bind_param($stmt, 'i', $lpjProposalId);
		mysqli_stmt_execute($stmt);
		$resLpj = mysqli_stmt_get_result($stmt);
		$lpjProposalData = mysqli_fetch_assoc($resLpj);
		mysqli_stmt_close($stmt);
		
		if ($lpjProposalData) {
			if (!isAllValidated($lpjProposalData)) {
				$_SESSION['proposal_flash'] = 'Error: LPJ & Dokumentasi hanya dapat diinput setelah semua 4 validasi selesai.';
				redirectProposal($currentPg);
			}
			$showLpjModal = true;
			$lpjModalData = $lpjProposalData;
		} else {
			$_SESSION['proposal_flash'] = 'Error: Proposal tidak ditemukan.';
			redirectProposal($currentPg);
		}
	}
}

// Proses simpan LPJ & Dokumentasi (Form Terpisah)
if (isset($_POST['simpan_lpj_dok'])) {
	$proposalId = (int)($_POST['proposal_id'] ?? 0);
	$dokumentasiLink = trim($_POST['dokumentasi_link'] ?? '');
	$errors = [];
	
	if ($proposalId <= 0) {
		$errors[] = 'Proposal tidak valid.';
	}
	
	$propData = null;
	if (!$errors) {
		$stmt = mysqli_prepare($koneksi, "SELECT * FROM `$tableProposal` WHERE id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $proposalId);
		mysqli_stmt_execute($stmt);
		$resProp = mysqli_stmt_get_result($stmt);
		$propData = mysqli_fetch_assoc($resProp);
		mysqli_stmt_close($stmt);
		
		if (!$propData) {
			$errors[] = 'Proposal tidak ditemukan.';
		} else if (!isAllValidated($propData)) {
			$errors[] = 'LPJ & Dokumentasi hanya bisa diisi setelah semua 4 validasi selesai.';
		}
	}
	
	$fileLPJName = null;
	if (!$errors && !empty($_FILES['file_lpj']['name'])) {
		$uploadResult = uploadFileLPJ($_FILES['file_lpj'] ?? null, $propData['file_lpj'] ?? null);
		if (!$uploadResult['success']) {
			$errors[] = $uploadResult['message'];
		} else {
			$fileLPJName = $uploadResult['filename'];
		}
	} else if ($propData) {
		$fileLPJName = $propData['file_lpj'] ?? null;
	}
	
	if ($errors) {
		$_SESSION['proposal_flash'] = 'Error: ' . implode(', ', $errors);
	} else {
		$stmt = mysqli_prepare($koneksi, "UPDATE `$tableProposal` SET dokumentasi_link = ?, file_lpj = ? WHERE id = ?");
		mysqli_stmt_bind_param($stmt, 'ssi', $dokumentasiLink, $fileLPJName, $proposalId);
		if (mysqli_stmt_execute($stmt)) {
			$_SESSION['proposal_flash'] = 'LPJ dan Link Dokumentasi berhasil disimpan.';
		} else {
			$_SESSION['proposal_flash'] = 'Gagal menyimpan LPJ dan Dokumentasi: ' . mysqli_stmt_error($stmt);
		}
		mysqli_stmt_close($stmt);
	}
	
	redirectProposal($currentPg);
}

// Proses simpan/update proposal
if (isset($_POST['simpan'])) {
	if ($isAdmin) {
		$_SESSION['proposal_flash'] = 'Error: Admin hanya memiliki akses untuk melihat data proposal.';
		redirectProposal($currentPg);
	}
	$namaProposal = trim($_POST['nama_proposal'] ?? '');
	// Gunakan nama user yang login otomatis
	$diajukanOleh = $userName;
	$tanggal = $_POST['tanggal'] ?? '';
	$dokumentasiLink = trim($_POST['dokumentasi_link'] ?? '');
	
	$errors = [];
	
	if (empty($namaProposal)) {
		$errors[] = 'Nama proposal harus diisi';
	}
	$today = date('Y-m-d');
	$editingId = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
	if (empty($tanggal)) {
		$errors[] = 'Tanggal harus diisi';
	} else if ($editingId === null && $tanggal < $today) {
		$errors[] = 'Tanggal proposal tidak boleh backdate (lebih awal dari hari ini)';
	}
	
	// Upload file proposal
	$fileProposalName = null;
	if (!$errors) {
		$uploadResult = uploadFileProposal($_FILES['file_proposal'] ?? null);
		if (!$uploadResult['success']) {
			$errors[] = $uploadResult['message'];
		} else {
			$fileProposalName = $uploadResult['filename'];
		}
	}
	
	// Upload file LPJ
	$fileLPJName = null;
	if (!$errors && !empty($_FILES['file_lpj']['name'])) {
		$uploadResult = uploadFileLPJ($_FILES['file_lpj'] ?? null);
		if (!$uploadResult['success']) {
			$errors[] = $uploadResult['message'];
		} else {
			$fileLPJName = $uploadResult['filename'];
		}
	}
	
	if (!$errors) {
		$editingId = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
		
		if ($editingId !== null) {
			// Update - ambil data lama
			$stmt = mysqli_prepare($koneksi, "SELECT file_proposal, file_lpj, dokumentasi_link FROM `$tableProposal` WHERE id = ?");
			mysqli_stmt_bind_param($stmt, 'i', $editingId);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_bind_result($stmt, $oldFileProposal, $oldFileLPJ, $oldDokumentasiLink);
			mysqli_stmt_fetch($stmt);
			mysqli_stmt_close($stmt);
			
			// Cek validasi untuk dokumentasi dan LPJ
			$stmt = mysqli_prepare($koneksi, "SELECT validasi_kepsek, validasi_ketua_yayasan, validasi_pembina_yayasan, validasi_keuangan FROM `$tableProposal` WHERE id = ?");
			mysqli_stmt_bind_param($stmt, 'i', $editingId);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_bind_result($stmt, $valKepsek, $valKetua, $valPembina, $valKeuangan);
			mysqli_stmt_fetch($stmt);
			mysqli_stmt_close($stmt);
			
			$allValidated = $valKepsek && $valKetua && $valPembina && $valKeuangan;
			$kepsekAndKetuaValidated = $valKepsek && $valKetua;
			
			// Jika tidak semua validasi, tidak boleh update dokumentasi/LPJ
			if (!$allValidated) {
				if (!empty($dokumentasiLink) && $dokumentasiLink !== $oldDokumentasiLink) {
					$errors[] = 'Dokumentasi hanya bisa diisi setelah semua validasi selesai.';
				}
				if ($fileLPJName !== null && $fileLPJName !== $oldFileLPJ) {
					$errors[] = 'LPJ hanya bisa diupload setelah semua validasi selesai.';
				}
			}
			
			// Jika kepsek dan ketua yayasan sudah divalidasi, tidak boleh ubah file proposal
			if ($kepsekAndKetuaValidated && $fileProposalName !== null && $fileProposalName !== $oldFileProposal) {
				$errors[] = 'File proposal tidak bisa diganti setelah divalidasi oleh Kepala Sekolah dan Ketua Yayasan.';
			}
			
			if ($fileProposalName === null) $fileProposalName = $oldFileProposal;
			if ($fileLPJName === null) $fileLPJName = $oldFileLPJ;
			if (empty($dokumentasiLink)) $dokumentasiLink = $oldDokumentasiLink;
			
			if (empty($errors)) {
				if (!$isAdmin) {
					$chkOwn = mysqli_prepare($koneksi, "SELECT id FROM `$tableProposal` WHERE id = ? AND pengaju_id = ? LIMIT 1");
					mysqli_stmt_bind_param($chkOwn, 'ii', $editingId, $userId);
					mysqli_stmt_execute($chkOwn);
					$ownRes = mysqli_stmt_get_result($chkOwn);
					$ownOk = $ownRes && mysqli_fetch_assoc($ownRes);
					mysqli_stmt_close($chkOwn);
					if (!$ownOk) {
						$_SESSION['proposal_flash'] = 'Error: Proposal tidak ditemukan di unit aktif.';
						redirectProposal($currentPg);
					}
				}
				$stmt = mysqli_prepare($koneksi, "UPDATE `$tableProposal` SET nama_proposal = ?, diajukan_oleh = ?, pengaju_id = ?, tanggal = ?, file_proposal = ? WHERE id = ?");
				mysqli_stmt_bind_param($stmt, 'ssissi', $namaProposal, $diajukanOleh, $userId, $tanggal, $fileProposalName, $editingId);
				if (mysqli_stmt_execute($stmt)) {
					// Update status revisi di proposal_revisi dan proposal_kegiatan
					$uRev = mysqli_prepare($koneksi, "UPDATE `proposal_revisi` SET status = 'diperbaiki' WHERE proposal_id = ? AND status = 'pending'");
					if ($uRev) {
						mysqli_stmt_bind_param($uRev, 'i', $editingId);
						mysqli_stmt_execute($uRev);
						mysqli_stmt_close($uRev);
					}
					$uProp = mysqli_prepare($koneksi, "UPDATE `$tableProposal` SET revisi_status = 'diperbaiki' WHERE id = ?");
					if ($uProp) {
						mysqli_stmt_bind_param($uProp, 'i', $editingId);
						mysqli_stmt_execute($uProp);
						mysqli_stmt_close($uProp);
					}
					$_SESSION['proposal_flash'] = 'Data proposal berhasil diupdate dan revisi ditandai sudah diperbaiki.';
				} else {
					$_SESSION['proposal_flash'] = 'Gagal mengupdate data proposal: ' . mysqli_stmt_error($stmt);
				}
				mysqli_stmt_close($stmt);
			}
		} else {
			// Insert
			$unitSave = proposalActiveUnitId();
			// Insert dengan unit_sekolah
			$escUnitProposal = mysqli_real_escape_string($koneksi, $userRawUnitProp);
			if (!empty($userRawUnitProp)) {
				$stmt = mysqli_prepare($koneksi, "INSERT INTO `$tableProposal` (nama_proposal, diajukan_oleh, pengaju_id, tanggal, file_proposal, unit_sekolah) VALUES (?, ?, ?, ?, ?, ?)");
				mysqli_stmt_bind_param($stmt, 'ssisss', $namaProposal, $diajukanOleh, $userId, $tanggal, $fileProposalName, $escUnitProposal);
			} else {
				$stmt = mysqli_prepare($koneksi, "INSERT INTO `$tableProposal` (nama_proposal, diajukan_oleh, pengaju_id, tanggal, file_proposal) VALUES (?, ?, ?, ?, ?)");
				mysqli_stmt_bind_param($stmt, 'ssiss', $namaProposal, $diajukanOleh, $userId, $tanggal, $fileProposalName);
			}
			if ($stmt === false) {
				$_SESSION['proposal_flash'] = 'Gagal prepare query: ' . mysqli_error($koneksi);
			} else {
				if (mysqli_stmt_execute($stmt)) {
					$_SESSION['proposal_flash'] = 'Data proposal berhasil disimpan.';
				} else {
					$_SESSION['proposal_flash'] = 'Gagal eksekusi query: ' . mysqli_stmt_error($stmt);
				}
				mysqli_stmt_close($stmt);
			}
		}
	}
	
	redirectProposal($currentPg);
}

// Proses hapus proposal
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
	$hapusId = (int)$_GET['hapus'];
	
	// Cek apakah user adalah pengaju proposal
	$stmt = mysqli_prepare($koneksi, "SELECT pengaju_id, file_proposal, file_lpj FROM `$tableProposal` WHERE id = ?");
	mysqli_stmt_bind_param($stmt, 'i', $hapusId);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $pengajuId, $fileProposal, $fileLPJ);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);
	
	// Cek apakah user adalah pengaju proposal (admin bisa hapus semua)
	if (!$isAdmin && $pengajuId != $userId) {
		$_SESSION['proposal_flash'] = 'Error: Anda hanya bisa menghapus proposal Anda sendiri.';
		redirectProposal($currentPg);
	}
	
	// Hapus record dari database
	$stmt = mysqli_prepare($koneksi, "DELETE FROM `$tableProposal` WHERE id = ?");
	mysqli_stmt_bind_param($stmt, 'i', $hapusId);
	if (mysqli_stmt_execute($stmt)) {
		$_SESSION['proposal_flash'] = 'Data proposal berhasil dihapus.';
		
		// Hapus file dari server
		if ($fileProposal) {
			$path = __DIR__ . '/../uploads/proposal/' . $fileProposal;
			if (is_file($path)) {
				@unlink($path);
			}
		}
		if ($fileLPJ) {
			$path = __DIR__ . '/../uploads/lpj/' . $fileLPJ;
			if (is_file($path)) {
				@unlink($path);
			}
		}
	} else {
		$_SESSION['proposal_flash'] = 'Gagal menghapus data proposal: ' . mysqli_stmt_error($stmt);
	}
	mysqli_stmt_close($stmt);
	
	redirectProposal($currentPg);
}

// Filter dan pagination
$filterNama = $_GET['cari'] ?? '';
$filterTahun = (int)($_GET['tahun'] ?? 0);
$filterUnit = trim($_GET['unit_sekolah'] ?? '');
$page = (int)($_GET['page'] ?? 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Ambil daftar unit sekolah untuk opsi filter (khusus Pembina Yayasan, Ketua Yayasan, Yayasan, Admin)
$availableUnits = [];
$uQuery = mysqli_query($koneksi, "SELECT kode, nama FROM unit_sekolah WHERE aktif = 1 ORDER BY kode ASC");
if ($uQuery && mysqli_num_rows($uQuery) > 0) {
	while ($uRow = mysqli_fetch_assoc($uQuery)) {
		$availableUnits[$uRow['kode']] = $uRow['nama'];
	}
} else {
	$availableUnits = [
		'wb_1' => 'Unit 1',
		'wb_2' => 'Unit 2',
		'wb_3' => 'Unit 3',
	];
}

// Ambil data proposal dengan filter
$whereConditions = [];
$params = [];
$types = '';

if (!empty($filterNama)) {
	$whereConditions[] = "nama_proposal LIKE ?";
	$params[] = '%' . $filterNama . '%';
	$types .= 's';
}

if ($filterTahun > 0) {
	$whereConditions[] = "YEAR(tanggal) = ?";
	$params[] = $filterTahun;
	$types .= 'i';
}

// Filter berdasarkan unit sekolah - Yayasan/Admin bisa pilih unit atau lihat semua, role sekolah melihat unit sendiri
if ($seeAllSchoolsProp) {
	if (!empty($filterUnit)) {
		$whereConditions[] = "unit_sekolah = ?";
		$params[] = $filterUnit;
		$types .= 's';
	}
} elseif (!empty($userRawUnitProp)) {
	$whereConditions[] = "(unit_sekolah = ? OR unit_sekolah IS NULL)";
	$params[] = $userRawUnitProp;
	$types .= 's';
}

// Filter berdasarkan user: admin, yayasan (ketua/pembina), kepsek, keuangan bisa lihat semua proposal dalam cakupannya; pengaju biasa hanya proposal sendiri
$canViewAllInScope = $isYayasan || in_array($userLevelProp, ['kepsek', 'keuangan', 'bendahara']) || in_array($userJabatan, ['keuangan', 'bendahara', 'kepala sekolah', 'kepsek']) || !empty($validatorRoles);
if (!$isAdmin && !$canViewAllInScope) {
	$whereConditions[] = "pengaju_id = ?";
	$params[] = $userId;
	$types .= 'i';
}

$whereSql = '';
if (!empty($whereConditions)) {
	$whereSql = ' WHERE ' . implode(' AND ', $whereConditions);
}

// Hitung total data
$countSql = "SELECT COUNT(*) as total FROM `$tableProposal` $whereSql";
$stmt = mysqli_prepare($koneksi, $countSql);
if ($stmt) {
	if (!empty($params)) {
		mysqli_stmt_bind_param($stmt, $types, ...$params);
	}
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $total);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);
} else {
	$total = 0;
}

$totalPages = ceil($total / $perPage);

// Ambil data untuk halaman ini dengan query yang lebih efisien
$sql = "SELECT id, nama_proposal, diajukan_oleh, pengaju_id, tanggal, file_proposal, dokumentasi_link, file_lpj,
		validasi_kepsek, validasi_ketua_yayasan, validasi_keuangan,
		validasi_kepsek_at, validasi_ketua_yayasan_at, validasi_keuangan_at,
		revisi_status
		FROM `$tableProposal` $whereSql ORDER BY tanggal DESC, id DESC LIMIT $perPage OFFSET $offset";
$stmt = mysqli_prepare($koneksi, $sql);
if (!empty($params)) {
	mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$proposals = [];
while ($row = mysqli_fetch_assoc($result)) {
	$proposals[] = $row;
}
mysqli_stmt_close($stmt);

// Ambil tahun-tahun yang ada untuk filter
$availableYears = [];
if ($isAdmin) {
	$yearSql = "SELECT DISTINCT YEAR(tanggal) as tahun FROM `$tableProposal` WHERE tanggal IS NOT NULL ORDER BY tahun DESC";
	$yearResult = mysqli_query($koneksi, $yearSql);
	if ($yearResult) {
		while ($row = mysqli_fetch_assoc($yearResult)) {
			$availableYears[] = $row['tahun'];
		}
		mysqli_free_result($yearResult);
	}
} else {
	$yearSql = "SELECT DISTINCT YEAR(tanggal) as tahun FROM `$tableProposal` WHERE tanggal IS NOT NULL AND pengaju_id = ? ORDER BY tahun DESC";
	$yearStmt = mysqli_prepare($koneksi, $yearSql);
	mysqli_stmt_bind_param($yearStmt, 'i', $userId);
	mysqli_stmt_execute($yearStmt);
	$yearResult = mysqli_stmt_get_result($yearStmt);
	while ($row = mysqli_fetch_assoc($yearResult)) {
		$availableYears[] = $row['tahun'];
	}
	mysqli_free_result($yearResult);
	mysqli_stmt_close($yearStmt);
}

// Mode edit
$editingId = null;
$editingData = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
	$editingId = (int)$_GET['edit'];
	
	// Admin bisa edit semua proposal, user biasa hanya proposal sendiri
	if ($isAdmin) {
		$stmt = mysqli_prepare($koneksi, "SELECT * FROM `$tableProposal` WHERE id = ?");
		mysqli_stmt_bind_param($stmt, 'i', $editingId);
	} else {
		$stmt = mysqli_prepare($koneksi, "SELECT * FROM `$tableProposal` WHERE id = ? AND pengaju_id = ?");
		mysqli_stmt_bind_param($stmt, 'ii', $editingId, $userId);
	}
	
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	if ($row = mysqli_fetch_assoc($result)) {
		$editingData = $row;
	} else {
		$editingId = null;
	}
	mysqli_stmt_close($stmt);
}

// Ambil catatan revisi jika sedang mode edit
$pendingRevisis = [];
if ($editingId !== null) {
	$revStmt = mysqli_prepare($koneksi, "
		SELECT pr.id, pr.revisi_role, pr.keterangan_revisi, pr.created_at, u.nama as revisi_oleh_nama
		FROM proposal_revisi pr
		LEFT JOIN users u ON pr.revisi_oleh = u.id_user
		WHERE pr.proposal_id = ? AND pr.status = 'pending'
		ORDER BY pr.created_at DESC
	");
	if ($revStmt) {
		mysqli_stmt_bind_param($revStmt, 'i', $editingId);
		mysqli_stmt_execute($revStmt);
		$revRes = mysqli_stmt_get_result($revStmt);
		while ($rRow = mysqli_fetch_assoc($revRes)) {
			$pendingRevisis[] = $rRow;
		}
		mysqli_stmt_close($revStmt);
	}
}

// Tentukan apakah semua validasi sudah selesai
$allValidated = false;
if ($editingData) {
	$allValidated = isAllValidated($editingData);
}

// Cek apakah user adalah validator dan buat tampilan validasi
// Variabel $userJabatan sudah didefinisikan secara global di baris atas
$validatorRoles = [];
$validationRole = '';

// Tentukan role validasi user
if (canValidateRole($userId, 'kepsek', $userLevel)) {
	$validatorRoles[] = 'kepsek';
	$validationRole = 'kepsek';
}
if (canValidateRole($userId, 'ketua_yayasan', $userLevel)) {
	$validatorRoles[] = 'ketua_yayasan';
	$validationRole = 'ketua_yayasan';
}
if (canValidateRole($userId, 'keuangan', $userLevel)) {
	$validatorRoles[] = 'keuangan';
	$validationRole = 'keuangan';
}

// Cek apakah user ingin mengakses halaman validasi secara eksplisit
$isValidationPage = isset($_GET['view']) && $_GET['view'] === 'validation';

// Jika user adalah validator dan ingin mengakses halaman validasi
if (!empty($validatorRoles) && $isValidationPage) {
	// Ambil data proposal untuk validasi
	$whereConditions = [];
	$params = [];
	$types = '';
	
	// Filter berdasarkan unit sekolah - Yayasan/Admin bisa pilih unit atau lihat semua, role sekolah melihat unit sendiri
	if ($seeAllSchoolsProp) {
		if (!empty($filterUnit)) {
			$whereConditions[] = "unit_sekolah = ?";
			$params[] = $filterUnit;
			$types .= 's';
		}
	} elseif (!empty($userRawUnitProp)) {
		$whereConditions[] = "(unit_sekolah = ? OR unit_sekolah IS NULL)";
		$params[] = $userRawUnitProp;
		$types .= 's';
	}
	
	// Filter pencarian
	if (!empty($filterNama)) {
		$whereConditions[] = "nama_proposal LIKE ?";
		$params[] = '%' . $filterNama . '%';
		$types .= 's';
	}
	
	if ($filterTahun > 0) {
		$whereConditions[] = "YEAR(tanggal) = ?";
		$params[] = $filterTahun;
		$types .= 'i';
	}
	
	$whereSql = '';
	if (!empty($whereConditions)) {
		$whereSql = ' WHERE ' . implode(' AND ', $whereConditions);
	}
	
	// Hitung total data untuk validasi dengan error handling
	$total = 0;
	try {
		$countSql = "SELECT COUNT(*) as total FROM `$tableProposal` $whereSql";
		$stmt = mysqli_prepare($koneksi, $countSql);
		if ($stmt) {
			if (!empty($params)) {
				mysqli_stmt_bind_param($stmt, $types, ...$params);
			}
			if (mysqli_stmt_execute($stmt)) {
				mysqli_stmt_bind_result($stmt, $total);
				mysqli_stmt_fetch($stmt);
			}
			mysqli_stmt_close($stmt);
		}
	} catch (Exception $e) {
		// Jika ada error, set total ke 0
		$total = 0;
	}
	
	$totalPages = ceil($total / $perPage);
	
	// Ambil data untuk validasi dengan error handling
	$validationProposals = [];
	try {
		// Tentukan field validasi berdasarkan role user
		$roleValidasiField = '';
		if ($validationRole === 'kepsek') {
			$roleValidasiField = 'validasi_kepsek';
		} elseif ($validationRole === 'ketua_yayasan') {
			$roleValidasiField = 'validasi_ketua_yayasan';
		} elseif ($validationRole === 'keuangan') {
			$roleValidasiField = 'validasi_keuangan';
		}

		// Urutkan: yang belum divalidasi (0) di atas, kemudian tanggal terbaru
		$orderBy = 'tanggal DESC, id DESC';
		if (!empty($roleValidasiField)) {
			$orderBy = "$roleValidasiField ASC, tanggal DESC, id DESC";
		}

		$sql = "SELECT id, nama_proposal, diajukan_oleh, tanggal, file_proposal, dokumentasi_link, file_lpj,
				validasi_kepsek, validasi_ketua_yayasan, validasi_keuangan,
				validasi_kepsek_at, validasi_ketua_yayasan_at, validasi_keuangan_at,
				validasi_kepsek_by, validasi_ketua_yayasan_by, validasi_keuangan_by,
				revisi_status
				FROM `$tableProposal` $whereSql ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
		$stmt = mysqli_prepare($koneksi, $sql);
		if ($stmt) {
			if (!empty($params)) {
				mysqli_stmt_bind_param($stmt, $types, ...$params);
			}
			if (mysqli_stmt_execute($stmt)) {
				$result = mysqli_stmt_get_result($stmt);
				while ($row = mysqli_fetch_assoc($result)) {
					$validationProposals[] = $row;
				}
			}
			mysqli_stmt_close($stmt);
		}
	} catch (Exception $e) {
		// Jika ada error, set array kosong
		$validationProposals = [];
	}
	
	// Tampilkan halaman validasi
	?>
	<style>
		/* Scoped CSS untuk validation container - tidak mengganggu layout global */
		.validation-container {
			max-width: 1200px;
			margin: 20px auto;
			padding: 20px;
			font-family: 'Inter', sans-serif;
			position: relative;
			z-index: 1;
		}
		.validation-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 12px;
			padding: 24px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			margin-bottom: 24px;
		}
		.validation-title {
			font-size: 24px;
			font-weight: 700;
			margin: 0 0 8px 0;
			color: #1f2937;
		}
		.validation-subtitle {
			color: #6b7280;
			margin-bottom: 20px;
		}
		.validation-table-wrap { 
			overflow: auto; 
			border: 1px solid #e5e7eb; 
			border-radius: 10px; 
			background: #fff; 
		}
		.validation-table { 
			width: 100%; 
			border-collapse: separate; 
			border-spacing: 0; 
		}
		.validation-table thead th { 
			background: #f9fafb; 
			position: relative; /* Changed from sticky */
			z-index: auto; /* Changed from 1 */
		}
		.validation-table th, .validation-table td { 
			border-bottom: 1px solid #e5e7eb; 
			padding: 12px; 
			vertical-align: middle; 
		}
		.validation-table tbody tr:hover { background:#fafafa; }
		.validation-table th { 
			text-align: left; 
			color:#111827; 
			font-weight:700; 
			font-size: 12px; 
		}
		.validation-table td { 
			color:#111827; 
			font-size: 12px; 
		}
		.validation-table tbody tr:nth-child(odd) { background:#fcfcff; }
		
		.validation-badge {
			padding: 4px 8px;
			border-radius: 12px;
			font-size: 11px;
			font-weight: 600;
			text-transform: uppercase;
		}
		.validation-badge.pending {
			background: #fef3c7;
			color: #92400e;
		}
		.validation-badge.completed {
			background: #ecfdf5;
			color: #065f46;
		}
		
		.btn-validate {
			padding: 8px 16px;
			background: #10b981;
			color: white;
			border: none;
			border-radius: 6px;
			cursor: pointer;
			font-weight: 600;
			font-size: 12px;
			transition: all 0.2s ease;
		}
		.btn-validate:hover {
			background: #059669;
		}
		.btn-validate:disabled {
			background: #9ca3af;
			cursor: not-allowed;
		}
		
		.file-link { 
			color: #4f46e5; 
			text-decoration: none; 
		}
		.file-link:hover { 
			text-decoration: underline; 
		}
		
		.alert {
			padding: 12px 16px;
			border-radius: 8px;
			margin-bottom: 16px;
		}
		.alert-success {
			background: #ecfdf5;
			border: 1px solid #bbf7d0;
			color: #047857;
		}
		.alert-info {
			background: #eff6ff;
			border: 1px solid #bfdbfe;
			color: #1e40af;
		}
		.alert-error {
			background: #fef2f2;
			border: 1px solid #fecaca;
			color: #dc2626;
		}
		
		.validation-filters {
			display: flex;
			gap: 12px;
			margin-bottom: 20px;
			align-items: center;
		}
		.validation-filters input, .validation-filters select {
			padding: 8px 12px;
			border: 1px solid #d1d5db;
			border-radius: 6px;
		}
		.validation-filters button {
			padding: 8px 16px;
			background: #4f46e5;
			color: white;
			border: none;
			border-radius: 6px;
			cursor: pointer;
		}
		
		@media (max-width: 768px) {
			.validation-filters { 
				flex-direction: column; 
				align-items: stretch; 
			}
		}
	</style>
	
	<div class="validation-container">
		<div class="validation-card">
			<?php if (!empty($_SESSION['proposal_flash'])): ?>
			<div class="alert alert-success">
				<?= htmlspecialchars($_SESSION['proposal_flash']); ?>
			</div>
			<?php unset($_SESSION['proposal_flash']); endif; ?>
			
			<h1 class="validation-title">
	Validasi Proposal
	<?php if (!$isYayasan || $isAdmin): ?>
	<a href="?pg=<?= htmlspecialchars($currentPg) ?>" style="float: right; font-size: 14px; background: #6b7280; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none;">
		← Kembali ke Proposal
	</a>
	<?php endif; ?>
</h1>
			<p class="validation-subtitle">
				Anda login sebagai <strong><?= htmlspecialchars($userJabatan ?: $userLevel) ?></strong>.
			</p>
			
			<div class="alert alert-info">
				<strong>Informasi:</strong> Anda hanya bisa melakukan validasi untuk proposal yang belum divalidasi oleh role Anda.
			</div>
			
			<div class="validation-filters">
				<form method="get" style="display: flex; gap: 12px; align-items: center; flex: 1; flex-wrap: wrap;">
					<input type="hidden" name="pg" value="<?= htmlspecialchars($currentPg) ?>">
					<input type="hidden" name="view" value="validation">
					<input type="text" name="cari" placeholder="Cari nama proposal..." value="<?= htmlspecialchars($filterNama) ?>" style="flex: 1; min-width: 150px;">
					<?php if ($seeAllSchoolsProp): ?>
						<select name="unit_sekolah" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
							<option value="">Semua Sekolah</option>
							<?php foreach ($availableUnits as $uKode => $uNama): ?>
								<option value="<?= htmlspecialchars($uKode) ?>" <?= $filterUnit === $uKode ? 'selected' : '' ?>><?= htmlspecialchars($uNama) ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
					<select name="tahun">
						<option value="">Semua Tahun</option>
						<?php if (!empty($availableYears)): ?>
							<?php foreach ($availableYears as $th): ?>
								<option value="<?= (int)$th ?>" <?= $filterTahun === (int)$th ? 'selected' : '' ?>><?= (int)$th ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
					<button type="submit">Filter</button>
					<?php if ($filterNama !== '' || $filterTahun > 0 || $filterUnit !== ''): ?>
						<a href="?pg=<?= htmlspecialchars($currentPg) ?>&view=validation" style="color: #6b7280; text-decoration: none;">Reset</a>
					<?php endif; ?>
				</form>
			</div>
			
			<div class="validation-table-wrap">
				<table class="validation-table">
					<thead>
						<tr>
							<th>No</th>
							<th>Nama Proposal</th>
							<th>Pengaju</th>
							<th>Tanggal</th>
							<th>File Proposal</th>
							<th colspan="3">Status Validasi</th>
							<th>Dokumentasi</th>
							<th>LPJ</th>
							<th>Status Anda</th>
							<th>Aksi</th>
						</tr>
						<tr>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th>Keuangan</th>
							<th>Kepsek</th>
							<th>Ketua Yayasan</th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($validationProposals)): ?>
							<tr>
								<td colspan="12" style="text-align: center; padding: 40px; color: #6b7280;">
									Tidak ada data proposal.
								</td>
							</tr>
						<?php else: ?>
							<?php $no = $offset + 1; foreach ($validationProposals as $row): ?>
							<tr>
								<td><?= $no++ ?></td>
								<td style="font-weight: 500;">
									<?= htmlspecialchars($row['nama_proposal']) ?>
									<div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
										ID: #<?= (int)$row['id'] ?>
									</div>
								</td>
								<td><?= htmlspecialchars($row['diajukan_oleh']) ?></td>
								<td><?= htmlspecialchars($row['tanggal']) ?></td>
								<td>
									<?php if (!empty($row['file_proposal'])): ?>
										<a href="uploads/proposal/<?= htmlspecialchars($row['file_proposal']) ?>" target="_blank" class="file-link">
											<i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">description</i> Lihat
										</a>
									<?php else: ?>
										<span style="color: #9ca3af;">-</span>
									<?php endif; ?>
								</td>
								<!-- 1. Keuangan -->
								<td style="text-align: center;">
									<?php if ($row['validasi_keuangan']): ?>
										<span style="color: #10b981; font-size: 18px; font-weight: bold;">✓</span>
										<div style="font-size: 9px; color: #6b7280;">
											<?= date('d/m', strtotime($row['validasi_keuangan_at'])) ?>
										</div>
									<?php else: ?>
										<span style="color: #ef4444; font-size: 18px; font-weight: bold;">✗</span>
									<?php endif; ?>
								</td>
								<!-- 2. Kepsek -->
								<td style="text-align: center;">
									<?php 
									if ($row['validasi_kepsek']): ?>
										<span style="color: #10b981; font-size: 18px; font-weight: bold;">✓</span>
										<div style="font-size: 9px; color: #6b7280;">
											<?= date('d/m', strtotime($row['validasi_kepsek_at'])) ?>
										</div>
									<?php else: ?>
										<?php 
										$hasRevisiKepsek = false;
										$stmt = mysqli_prepare($koneksi, "SELECT id FROM proposal_revisi WHERE proposal_id = ? AND revisi_role = 'kepsek' AND status = 'pending'");
										if ($stmt) {
											mysqli_stmt_bind_param($stmt, 'i', $row['id']);
											mysqli_stmt_execute($stmt);
											mysqli_stmt_store_result($stmt);
											$hasRevisiKepsek = mysqli_stmt_num_rows($stmt) > 0;
											mysqli_stmt_close($stmt);
										}
										?>
										<?php if ($hasRevisiKepsek): ?>
											<a href="?pg=<?= htmlspecialchars($currentPg) ?>&view=validation&view_revisi=<?= (int)$row['id'] ?>&role=kepsek" class="validation-icon pending" style="background: #f59e0b; cursor: pointer; text-decoration: none; color: white; display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center;" title="Klik untuk lihat revisi">✏️</a>
										<?php else: ?>
											<span style="color: #ef4444; font-size: 18px; font-weight: bold;">✗</span>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<!-- 3. Ketua Yayasan -->
								<td style="text-align: center;">
									<?php 
									if ($row['validasi_ketua_yayasan']): ?>
										<span style="color: #10b981; font-size: 18px; font-weight: bold;">✓</span>
										<div style="font-size: 9px; color: #6b7280;">
											<?= date('d/m', strtotime($row['validasi_ketua_yayasan_at'])) ?>
										</div>
									<?php else: ?>
										<?php 
										$hasRevisiKetua = false;
										$stmt = mysqli_prepare($koneksi, "SELECT id FROM proposal_revisi WHERE proposal_id = ? AND revisi_role = 'ketua_yayasan' AND status = 'pending'");
										if ($stmt) {
											mysqli_stmt_bind_param($stmt, 'i', $row['id']);
											mysqli_stmt_execute($stmt);
											mysqli_stmt_store_result($stmt);
											$hasRevisiKetua = mysqli_stmt_num_rows($stmt) > 0;
											mysqli_stmt_close($stmt);
										}
										?>
										<?php if ($hasRevisiKetua): ?>
											<a href="?pg=<?= htmlspecialchars($currentPg) ?>&view=validation&view_revisi=<?= (int)$row['id'] ?>&role=ketua_yayasan" class="validation-icon pending" style="background: #f59e0b; cursor: pointer; text-decoration: none; color: white; display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center;" title="Klik untuk lihat revisi">✏️</a>
										<?php else: ?>
											<span style="color: #ef4444; font-size: 18px; font-weight: bold;">✗</span>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<td style="text-align: center;">
									<?php if (!empty($row['dokumentasi_link'])): ?>
										<a href="<?= htmlspecialchars($row['dokumentasi_link']) ?>" target="_blank" class="file-link">
											<i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">link</i> Link
										</a>
									<?php else: ?>
										<?php 
										$allValidated = isAllValidated($row);
										if ($allValidated): ?>
											<span style="color: #f59e0b; font-size: 11px;">Belum</span>
										<?php else: ?>
											<span style="color: #9ca3af; font-size: 11px;">-</span>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<td style="text-align: center;">
									<?php if (!empty($row['file_lpj'])): ?>
										<a href="uploads/lpj/<?= htmlspecialchars($row['file_lpj']) ?>" target="_blank" class="file-link">
											<i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">description</i> LPJ
										</a>
									<?php else: ?>
										<?php 
										$allValidated = isAllValidated($row);
										if ($allValidated): ?>
											<span style="color: #f59e0b; font-size: 11px;">Belum</span>
										<?php else: ?>
											<span style="color: #9ca3af; font-size: 11px;">-</span>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<td style="text-align: center;">
									<?php 
									// Status validasi untuk role user ini
									$userValidationStatus = $row["validasi_{$validationRole}"];
									$userValidationAt = $row["validasi_{$validationRole}_at"];
									$userValidationBy = $row["validasi_{$validationRole}_by"];
									
									if ($userValidationStatus): ?>
										<div style="color: #10b981; font-weight: bold;">
											✓ Sudah Divalidasi
										</div>
										<div style="font-size: 9px; color: #6b7280;">
											<?= date('d/m/Y H:i', strtotime($userValidationAt)) ?>
										</div>
									<?php else: ?>
										<div style="color: #f59e0b; font-weight: bold;">
											⏳ Belum Divalidasi
										</div>
									<?php endif; ?>
								</td>
								<td>
									<?php if (!$userValidationStatus): ?>
										<?php if (in_array($validationRole, ['kepsek', 'ketua_yayasan'])): ?>
											<div style="display: flex; gap: 4px; flex-wrap: wrap;">
												<form method="post" style="display: inline; margin: 0;">
													<input type="hidden" name="validasi_proposal" value="<?= (int)$row['id'] ?>">
													<input type="hidden" name="role" value="<?= htmlspecialchars($validationRole) ?>">
													<button type="submit" class="btn-validate" style="font-size: 11px; padding: 4px 8px; margin: 0;">
														Validasi
													</button>
												</form>
												<form method="get" style="display: inline; margin: 0;">
													<input type="hidden" name="pg" value="<?= htmlspecialchars($currentPg) ?>">
													<input type="hidden" name="view" value="validation">
													<input type="hidden" name="revisi" value="<?= (int)$row['id'] ?>">
													<input type="hidden" name="role" value="<?= htmlspecialchars($validationRole) ?>">
													<button type="submit" class="btn-revisi" style="font-size: 11px; padding: 4px 8px; background: #f59e0b; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 0;">
														Revisi
													</button>
												</form>
											</div>
										<?php else: ?>
											<form method="post" style="display: inline;">
												<input type="hidden" name="validasi_proposal" value="<?= (int)$row['id'] ?>">
												<input type="hidden" name="role" value="<?= htmlspecialchars($validationRole) ?>">
												<button type="submit" class="btn-validate" onclick="return confirm('Validasi proposal ini?')">
													Validasi
												</button>
											</form>
										<?php endif; ?>
									<?php else: ?>
										<span style="color: #6b7280; font-size: 11px;">
											Selesai
										</span>
									<?php endif; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			
			<?php if ($totalPages > 1): ?>
			<div style="display: flex; justify-content: center; gap: 8px; margin-top: 20px;">
				<?php 
				$baseUrl = '?pg=' . htmlspecialchars($currentPg) . '&view=validation';
				if (!empty($filterNama)) $baseUrl .= '&cari=' . urlencode($filterNama);
				if (!empty($filterUnit)) $baseUrl .= '&unit_sekolah=' . urlencode($filterUnit);
				if ($filterTahun > 0) $baseUrl .= '&tahun=' . $filterTahun;
				
				if ($page > 1): ?>
					<a href="<?= $baseUrl ?>&page=<?= $page - 1 ?>" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; color: #374151;">«</a>
				<?php else: ?>
					<span style="padding: 8px 12px; color: #9ca3af;">«</span>
				<?php endif; ?>
				
				<?php for ($i = 1; $i <= $totalPages; $i++): ?>
					<?php if ($i == $page): ?>
						<span style="padding: 8px 12px; background: var(--brand); color: white; border-radius: 6px; font-weight: 600;"><?= $i ?></span>
					<?php else: ?>
						<a href="<?= $baseUrl ?>&page=<?= $i ?>" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; color: #374151;"><?= $i ?></a>
					<?php endif; ?>
				<?php endfor; ?>
				
				<?php if ($page < $totalPages): ?>
					<a href="<?= $baseUrl ?>&page=<?= $page + 1 ?>" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; color: #374151;">»</a>
				<?php else: ?>
					<span style="padding: 8px 12px; color: #9ca3af;">»</span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
	
	<?php
	// Tampilkan halaman validasi selesai, lanjut ke layout normal
} else {
		// Tampilkan halaman proposal normal untuk user biasa
	?>
<style>
	:root {
		--brand: #4f46e5;
		--brand-dark: #4338ca;
		--card-bg: #ffffff;
		--card-border: #e5e7eb;
		--table-header: #f9fafb;
	}
	
	.proposal-container {
		max-width: 1200px;
		margin: 0 auto;
		padding: 20px;
		font-family: 'Inter', sans-serif;
	}
	.proposal-layout {
		display: flex;
		flex-direction: column;
		gap: 24px;
	}
	.proposal-main {
		flex: 1;
	}
	.proposal-card {
		background: var(--card-bg);
		border: 1px solid var(--card-border);
		border-radius: 12px;
		padding: 24px;
		box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	}
	.proposal-title {
		font-size: 20px;
		font-weight: 700;
		margin: 0 0 20px 0;
		color: #1f2937;
		display: flex;
		align-items: center;
		gap: 12px;
	}
	.proposal-title .dot {
		width: 8px;
		height: 8px;
		background: var(--brand);
		border-radius: 50%;
	}
	.btn-new-proposal {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 10px 16px;
		background: var(--brand);
		color: white;
		border: none;
		border-radius: 8px;
		cursor: pointer;
		font-weight: 600;
		font-size: 14px;
		transition: all 0.3s ease;
	}
	.btn-new-proposal:hover {
		filter: brightness(0.95);
		transform: translateY(-1px);
		box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
	}
	.table-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 20px;
		flex-wrap: wrap;
		gap: 16px;
	}
	.button-group {
		display: flex;
		align-items: center;
		gap: 12px;
		flex-wrap: wrap;
	}
	
	.proposal-form .form-row { display: flex; flex-wrap: wrap; gap: 10px; }
	.proposal-form .form-group { flex: 1 1 100%; display: flex; flex-direction: column; }
	.proposal-form .form-group.half { flex: 1 1 calc(50% - 5px); }
	.proposal-form label { font-weight: 600; margin-bottom: 6px; color:#111827; }
	.proposal-form input[type="text"],
	.proposal-form input[type="date"],
	.proposal-form input[type="file"],
	.proposal-form input[type="url"],
	.proposal-form textarea { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; background:#ffffff; transition: all .15s ease; }
	.proposal-form input[type="text"]:focus,
	.proposal-form input[type="date"]:focus,
	.proposal-form input[type="file"]:focus,
	.proposal-form input[type="url"]:focus,
	.proposal-form textarea:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(79,70,229,.15); }
	.proposal-form textarea { min-height: 80px; resize: vertical; }
	.proposal-form input:disabled { background: #f3f4f6; cursor: not-allowed; }
	
	/* Validasi section */
	.validasi-section {
		border: 1px solid #e5e7eb;
		border-radius: 8px;
		padding: 16px;
		margin: 16px 0;
		background: #f9fafb;
	}
	.validasi-title {
		font-weight: 600;
		margin-bottom: 12px;
		color: #374151;
	}
	.validasi-grid {
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
		justify-content: flex-start;
	}
	.validasi-item {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 8px 12px;
		border-radius: 6px;
		background: white;
		border: 1px solid #e5e7eb;
		min-width: 180px;
		flex: 0 0 auto;
	}
	.validasi-item.validated {
		background: #ecfdf5;
		border-color: #bbf7d0;
	}
	.validasi-item.pending {
		background: #fef3c7;
		border-color: #fde68a;
	}
	.validasi-icon {
		width: 20px;
		height: 20px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 12px;
		font-weight: bold;
		flex-shrink: 0;
	}
	.validasi-icon.validated {
		background: #10b981;
		color: white;
	}
	.validasi-icon.pending {
		background: #f59e0b;
		color: white;
	}
	.validasi-text {
		flex: 1;
		font-size: 14px;
	}
	.validasi-action {
		font-size: 12px;
		color: #6b7280;
	}
	
	.proposal-actions { margin-top: 10px; }
	.proposal-actions button { padding: 9px 14px; border: 1px solid var(--brand); background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; border-radius: 8px; cursor: pointer; font-weight:600; }
	.proposal-actions button:hover { filter: brightness(.95); }
	.proposal-actions button.btn-secondary { background: #6b7280; border-color: #6b7280; }
	.proposal-actions button:disabled { background: #9ca3af; border-color: #9ca3af; cursor: not-allowed; }

	.proposal-table-wrap { overflow:auto; border:1px solid var(--card-border); border-radius:10px; background:#fff; }
	.proposal-table { width: 100%; border-collapse: separate; border-spacing: 0; }
	.proposal-table thead th { position: sticky; top:0; background: var(--table-header); z-index:1; }
	.proposal-table th, .proposal-table td { border-bottom: 1px solid var(--card-border); padding: 10px 12px; vertical-align: middle; text-align: center; }
	.proposal-table tbody tr:hover { background:#fafafa; }
	.proposal-table th { text-align: center; color:#111827; font-weight:700; font-size: 12px; }
	.proposal-table td { color:#111827; font-size: 12px; }
	.proposal-table tbody tr:nth-child(odd) { background:#fcfcff; }
	
	/* Header ganda styling */
	.proposal-table thead tr:first-child th {
		border-bottom: 2px solid var(--card-border);
	}
	.proposal-table thead tr:last-child th {
		border-bottom: 1px solid var(--card-border);
		background: #f8fafc;
		font-size: 11px;
		padding: 8px 6px;
	}
	
	/* Ceklis validasi styling */
	.validation-checklist {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 2px;
	}
	.validation-icon {
		font-size: 18px;
		font-weight: bold;
		line-height: 1;
	}
	.validation-date {
		font-size: 9px;
		color: #6b7280;
		white-space: nowrap;
	}
	.validation-icon.validated {
		color: #10b981;
	}
	.validation-icon.pending {
		color: #ef4444;
	}
	
	.validasi-check { display: inline-block; width: 20px; height: 20px; text-align: center; }
	.validasi-check.checked { color: #10b981; font-weight: bold; }
	.validasi-check.unchecked { color: #ef4444; }
	
	.icon-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid transparent; text-decoration:none; }
	.icon-edit { background:#e8f7ee; border-color:#bbf7d0; color:#047857; }
	.icon-edit:hover { background:#d1fae5; }
	.icon-del { background:#fee2e2; border-color:#fecaca; color:#b91c1c; }
	.icon-del:hover { background:#fecaca; }
	.icon-btn i { font-size:18px; line-height:1; }
	
	.file-link { color: var(--brand); text-decoration: none; }
	.file-link:hover { text-decoration: underline; }
	
	.status-badge {
		padding: 4px 8px;
		border-radius: 12px;
		font-size: 11px;
		font-weight: 600;
		text-transform: uppercase;
	}
	.status-badge.pending {
		background: #fef3c7;
		color: #92400e;
	}
	.status-badge.validated {
		background: #ecfdf5;
		color: #065f46;
	}
	.status-badge.completed {
		background: #dbeafe;
		color: #1e40af;
	}
	
	.alert {
		padding: 12px 16px;
		border-radius: 8px;
		margin-bottom: 16px;
	}
	.alert-success {
		background: #ecfdf5;
		border: 1px solid #bbf7d0;
		color: #047857;
	}
	.alert-error {
		background: #fef2f2;
		border: 1px solid #fecaca;
		color: #dc2626;
	}
	
	@media (max-width: 768px) {
		.table-header { flex-direction: column; align-items: stretch; gap: 16px; }
		.button-group { justify-content: center; width: 100%; }
		.btn-new-proposal { flex: 1; justify-content: center; min-width: 140px; }
		.proposal-filters { width: 100%; justify-content: flex-start; }
		.validasi-grid { flex-direction: column; }
		.validasi-item { min-width: auto; }
	}
</style>

<div class="proposal-container">
	<div class="proposal-layout">
		<div class="proposal-main">
			<div class="proposal-card">
				<?php if (!empty($_SESSION['proposal_flash'])): ?>
				<div class="alert alert-success">
					<?= htmlspecialchars($_SESSION['proposal_flash']); ?>
				</div>
				<?php unset($_SESSION['proposal_flash']); endif; ?>
				
				<h3 class="proposal-title"><span class="dot"></span>Data Proposal</h3>
				
				<div class="table-header">
					<div class="button-group">
						<?php
						// Yayasan level & Admin tidak menampilkan tombol Proposal Baru
						$canCreateProposal = !$isAdmin && !in_array($userLevelProp, ['yayasan', 'ketua_yayasan', 'pembina_yayasan']);
						if ($canCreateProposal):
						?>
						<button class="btn-new-proposal" onclick="openProposalModal()">
							<i class="material-icons" style="font-size: 18px;">add</i>
							Proposal Baru
						</button>
						<?php endif; ?>
						<?php if (!empty($validatorRoles)): ?>
						<a href="?pg=<?= htmlspecialchars($currentPg) ?>&view=validation" class="btn-new-proposal" style="background: #10b981; margin-left: 12px; text-decoration: none; display: inline-flex; align-items: center;">
							<i class="material-icons" style="font-size: 18px;">check_circle</i>
							Validasi Proposal
						</a>
						<?php endif; ?>
					</div>
					<div class="proposal-filters">
						<form method="get" class="proposal-filter-form">
							<input type="hidden" name="pg" value="<?= htmlspecialchars($currentPg) ?>">
							<input type="text" name="cari" placeholder="Cari nama proposal..." value="<?= htmlspecialchars($filterNama) ?>" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
							<?php if ($seeAllSchoolsProp): ?>
								<select name="unit_sekolah" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
									<option value="">Semua Sekolah</option>
									<?php foreach ($availableUnits as $uKode => $uNama): ?>
										<option value="<?= htmlspecialchars($uKode) ?>" <?= $filterUnit === $uKode ? 'selected' : '' ?>><?= htmlspecialchars($uNama) ?></option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>
							<select name="tahun" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
								<option value="">Semua Tahun</option>
								<?php if (!empty($availableYears)): ?>
									<?php foreach ($availableYears as $th): ?>
										<option value="<?= (int)$th ?>" <?= $filterTahun === (int)$th ? 'selected' : '' ?>><?= (int)$th ?></option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
							<button type="submit" style="padding: 8px 16px; background: var(--brand); color: white; border: none; border-radius: 6px; cursor: pointer;">Filter</button>
							<?php if ($filterNama !== '' || $filterTahun > 0 || $filterUnit !== ''): ?>
								<a href="?pg=<?= htmlspecialchars($currentPg) ?>" style="margin-left: 8px; color: #6b7280; text-decoration: none;">Reset</a>
							<?php endif; ?>
						</form>
					</div>
				</div>
				
				<div class="proposal-table-wrap">
					<table class="proposal-table">
						<thead>
							<tr>
								<th>No</th>
								<th>Nama Proposal</th>
								<th>Tanggal</th>
								<th>File Proposal</th>
								<th colspan="3">Validasi</th>
								<th>Dokumentasi</th>
								<th>LPJ</th>
								<th>Aksi</th>
							</tr>
							<tr>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th>Keuangan</th>
								<th>Kepsek</th>
								<th>Ketua Yayasan</th>
								<th></th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($proposals)): ?>
								<tr>
									<td colspan="10" style="text-align: center; padding: 40px; color: #6b7280;">
										Belum ada data proposal.
									</td>
								</tr>
							<?php else: ?>
								<?php $no = $offset + 1; foreach ($proposals as $row): ?>
								<tr>
									<td><?= $no++ ?></td>
									<td style="text-align: left; font-weight: 500;">
										<?= htmlspecialchars($row['nama_proposal']) ?>
										<div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
											<?= htmlspecialchars($row['diajukan_oleh']) ?>
										</div>
									</td>
									<td><?= htmlspecialchars($row['tanggal']) ?></td>
									<td>
										<?php if (!empty($row['file_proposal'])): ?>
											<a href="uploads/proposal/<?= htmlspecialchars($row['file_proposal']) ?>" target="_blank" class="file-link">
												<i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">description</i> Lihat
											</a>
										<?php else: ?>
											<span style="color: #9ca3af;">-</span>
										<?php endif; ?>
									</td>
									<!-- 1. Keuangan -->
									<td style="text-align: center;">
										<div class="validation-checklist">
											<?php if (!empty($row['validasi_keuangan'])): ?>
												<span class="validation-icon validated">✓</span>
												<?php if (!empty($row['validasi_keuangan_at']) && $row['validasi_keuangan_at'] !== '0000-00-00 00:00:00'): ?>
													<span class="validation-date"><?= date('d/m', strtotime($row['validasi_keuangan_at'])) ?></span>
												<?php endif; ?>
											<?php else: ?>
												<span class="validation-icon pending">⏳</span>
											<?php endif; ?>
										</div>
									</td>
									<!-- 2. Kepsek -->
									<td style="text-align: center;">
										<div class="validation-checklist">
											<?php if (!empty($row['validasi_kepsek'])): ?>
												<span class="validation-icon validated">✓</span>
												<?php if (!empty($row['validasi_kepsek_at']) && $row['validasi_kepsek_at'] !== '0000-00-00 00:00:00'): ?>
													<span class="validation-date"><?= date('d/m', strtotime($row['validasi_kepsek_at'])) ?></span>
												<?php endif; ?>
											<?php else: ?>
												<?php 
												$hasRevisiKepsek = false;
												$stmt = mysqli_prepare($koneksi, "SELECT id FROM proposal_revisi WHERE proposal_id = ? AND revisi_role = 'kepsek' AND status = 'pending'");
												if ($stmt) {
													mysqli_stmt_bind_param($stmt, 'i', $row['id']);
													mysqli_stmt_execute($stmt);
													mysqli_stmt_store_result($stmt);
													$hasRevisiKepsek = mysqli_stmt_num_rows($stmt) > 0;
													mysqli_stmt_close($stmt);
												}
												?>
												<?php if ($hasRevisiKepsek): ?>
													<a href="?pg=<?= htmlspecialchars($currentPg) ?>&view_revisi=<?= (int)$row['id'] ?>&role=kepsek" class="validation-icon pending" style="background: #f59e0b; color: white; cursor: pointer; text-decoration: none; display: inline-block; width: 22px; height: 22px; line-height: 22px; text-align: center; border-radius: 50%;" title="Klik untuk lihat catatan revisi dari Kepsek">✏️</a>
												<?php else: ?>
													<span class="validation-icon pending">⏳</span>
												<?php endif; ?>
											<?php endif; ?>
										</div>
									</td>
									<!-- 3. Ketua Yayasan -->
									<td style="text-align: center;">
										<div class="validation-checklist">
											<?php if (!empty($row['validasi_ketua_yayasan'])): ?>
												<span class="validation-icon validated">✓</span>
												<?php if (!empty($row['validasi_ketua_yayasan_at']) && $row['validasi_ketua_yayasan_at'] !== '0000-00-00 00:00:00'): ?>
													<span class="validation-date"><?= date('d/m', strtotime($row['validasi_ketua_yayasan_at'])) ?></span>
												<?php endif; ?>
											<?php else: ?>
												<?php 
												$hasRevisiKetua = false;
												$stmt = mysqli_prepare($koneksi, "SELECT id FROM proposal_revisi WHERE proposal_id = ? AND revisi_role = 'ketua_yayasan' AND status = 'pending'");
												if ($stmt) {
													mysqli_stmt_bind_param($stmt, 'i', $row['id']);
													mysqli_stmt_execute($stmt);
													mysqli_stmt_store_result($stmt);
													$hasRevisiKetua = mysqli_stmt_num_rows($stmt) > 0;
													mysqli_stmt_close($stmt);
												}
												?>
												<?php if ($hasRevisiKetua): ?>
													<a href="?pg=<?= htmlspecialchars($currentPg) ?>&view_revisi=<?= (int)$row['id'] ?>&role=ketua_yayasan" class="validation-icon pending" style="background: #f59e0b; color: white; cursor: pointer; text-decoration: none; display: inline-block; width: 22px; height: 22px; line-height: 22px; text-align: center; border-radius: 50%;" title="Klik untuk lihat catatan revisi dari Ketua Yayasan">✏️</a>
												<?php else: ?>
													<span class="validation-icon pending">⏳</span>
												<?php endif; ?>
											<?php endif; ?>
										</div>
									</td>
									<td>
										<?php if (!empty($row['dokumentasi_link'])): ?>
											<a href="<?= htmlspecialchars($row['dokumentasi_link']) ?>" target="_blank" class="file-link">
												<i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">link</i> Link
											</a>
										<?php else: ?>
											<?php if (isAllValidated($row)): ?>
												<span style="color: #f59e0b; font-size: 11px;">Belum</span>
											<?php else: ?>
												<span style="color: #9ca3af; font-size: 11px;">-</span>
											<?php endif; ?>
										<?php endif; ?>
									</td>
									<td>
										<?php if (!empty($row['file_lpj'])): ?>
											<a href="uploads/lpj/<?= htmlspecialchars($row['file_lpj']) ?>" target="_blank" class="file-link">
												<i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">description</i> LPJ
											</a>
										<?php else: ?>
											<?php if (isAllValidated($row)): ?>
												<span style="color: #f59e0b; font-size: 11px;">Belum</span>
											<?php else: ?>
												<span style="color: #9ca3af; font-size: 11px;">-</span>
											<?php endif; ?>
										<?php endif; ?>
									</td>
									<td>
									<?php 
									$allValidated = isAllValidated($row);
									$canEdit = !$isAdmin && ($row['pengaju_id'] == $userId);
									$canManageLpj = $allValidated && ($isAdmin || ($row['pengaju_id'] == $userId));
									$canDelete = $isAdmin || ($row['pengaju_id'] == $userId);
									?>
									
									<?php if ($canEdit): ?>
										<a href="?pg=<?= htmlspecialchars($currentPg) ?>&edit=<?= (int)$row['id'] ?>" class="icon-btn icon-edit" title="Edit Proposal"><i class="material-icons-outlined">edit</i></a>
									<?php endif; ?>

									<?php if ($canManageLpj): ?>
										<a href="?pg=<?= htmlspecialchars($currentPg) ?>&upload_lpj=<?= (int)$row['id'] ?>" class="icon-btn" style="background:#e0e7ff;border-color:#c7d2fe;color:#3730a3;" title="Input / Edit LPJ & Dokumentasi"><i class="material-icons-outlined">assignment_turned_in</i></a>
									<?php endif; ?>
									
									<?php if ($canDelete): ?>
										<a href="?pg=<?= htmlspecialchars($currentPg) ?>&hapus=<?= (int)$row['id'] ?>" class="icon-btn icon-del" onclick="return confirm('Hapus data proposal ini?')" title="Hapus"><i class="material-icons-outlined">delete</i></a>
									<?php endif; ?>
								</td>
								</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
				
				<?php if ($totalPages > 1): ?>
				<div style="display: flex; justify-content: center; gap: 8px; margin-top: 20px;">
					<?php 
					$baseUrl = '?pg=' . htmlspecialchars($currentPg);
					if (!empty($filterNama)) $baseUrl .= '&cari=' . urlencode($filterNama);
					if (!empty($filterUnit)) $baseUrl .= '&unit_sekolah=' . urlencode($filterUnit);
					if ($filterTahun > 0) $baseUrl .= '&tahun=' . $filterTahun;
					
					if ($page > 1): ?>
						<a href="<?= $baseUrl ?>&page=<?= $page - 1 ?>" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; color: #374151;">«</a>
					<?php else: ?>
						<span style="padding: 8px 12px; color: #9ca3af;">«</span>
					<?php endif; ?>
					
					<?php for ($i = 1; $i <= $totalPages; $i++): ?>
						<?php if ($i == $page): ?>
							<span style="padding: 8px 12px; background: var(--brand); color: white; border-radius: 6px; font-weight: 600;"><?= $i ?></span>
						<?php else: ?>
							<a href="<?= $baseUrl ?>&page=<?= $i ?>" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; color: #374151;"><?= $i ?></a>
						<?php endif; ?>
					<?php endfor; ?>
					
					<?php if ($page < $totalPages): ?>
						<a href="<?= $baseUrl ?>&page=<?= $page + 1 ?>" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; color: #374151;">»</a>
					<?php else: ?>
						<span style="padding: 8px 12px; color: #9ca3af;">»</span>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div id="proposalModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
	<div class="modal-content" style="background: white; border-radius: 12px; padding: 24px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
			<h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 600;">
				<?= $editingId !== null ? 'Edit Proposal' : 'Proposal Baru' ?>
			</h3>
			<button onclick="closeProposalModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
		</div>
		
		<form method="post" enctype="multipart/form-data" class="proposal-form">
			<input type="hidden" name="id" value="<?= $editingId !== null ? (int)$editingId : '' ?>">
			
			<?php 
			// Fix: Definisikan $allValidated dengan benar untuk mode baru dan edit
			$allValidated = $editingId !== null ? isAllValidated($editingData) : false;
			?>

			<?php if (!empty($pendingRevisis)): ?>
				<div style="background: #fffbebfb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 14px; margin-bottom: 20px;">
					<div style="font-weight: 700; color: #92400e; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
						<span style="font-size: 18px;">⚠️</span> Catatan Revisi yang Perlu Diperbaiki:
					</div>
					<?php foreach ($pendingRevisis as $pRev): ?>
						<div style="background: #ffffff; border: 1px solid #fef3c7; border-radius: 6px; padding: 10px; margin-top: 6px;">
							<div style="font-size: 12px; font-weight: 600; color: #b45309; margin-bottom: 4px;">
								Revisi dari <?= $pRev['revisi_role'] === 'kepsek' ? 'Kepala Sekolah' : ($pRev['revisi_role'] === 'ketua_yayasan' ? 'Ketua Yayasan' : ucfirst($pRev['revisi_role'])) ?> 
								<?= !empty($pRev['revisi_oleh_nama']) ? '(' . htmlspecialchars($pRev['revisi_oleh_nama']) . ')' : '' ?> - <?= date('d/m/Y H:i', strtotime($pRev['created_at'])) ?>:
							</div>
							<div style="font-size: 13px; color: #1f2937; white-space: pre-wrap;"><?= htmlspecialchars($pRev['keterangan_revisi']) ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			
			<div class="form-row">
				<div class="form-group half">
					<label for="nama_proposal">Nama Proposal *</label>
					<input type="text" id="nama_proposal" name="nama_proposal" value="<?= htmlspecialchars($editingData['nama_proposal'] ?? '') ?>" required>
				</div>
				<div class="form-group half">
					<label for="diajukan_oleh">Diajukan Oleh *</label>
					<input type="text" id="diajukan_oleh" name="diajukan_oleh" value="<?= htmlspecialchars($editingData['diajukan_oleh'] ?? $userName) ?>" readonly style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; width: 100%; background-color: #f9fafb; color: #6b7280;" required>
					<input type="hidden" name="diajukan_oleh_value" value="<?= htmlspecialchars($userName) ?>">
				</div>
			</div>
			
			<div class="form-row">
				<div class="form-group half">
					<label for="tanggal">Tanggal *</label>
					<input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($editingData['tanggal'] ?? date('Y-m-d')) ?>" min="<?= isset($editingData['tanggal']) ? htmlspecialchars($editingData['tanggal']) : date('Y-m-d') ?>" required>
				</div>
				<div class="form-group half">
					<label for="file_proposal">File Proposal (PDF) *</label>
					<?php 
					$kepsekAndKetuaValidated = $editingId !== null ? ($editingData['validasi_kepsek'] && $editingData['validasi_ketua_yayasan']) : false;
					?>
					<input type="file" id="file_proposal" name="file_proposal" accept=".pdf" <?= $editingId ? '' : 'required' ?> <?= $kepsekAndKetuaValidated ? 'disabled' : '' ?>>
					<?php if ($kepsekAndKetuaValidated): ?>
						<div style="margin-top: 4px; font-size: 11px; color: #ef4444;">File proposal tidak bisa diganti setelah divalidasi oleh Kepala Sekolah dan Ketua Yayasan</div>
					<?php endif; ?>
					<?php if (!empty($editingData['file_proposal'])): ?>
						<div style="margin-top: 6px; font-size: 12px;">
							File saat ini: <a href="uploads/proposal/<?= htmlspecialchars($editingData['file_proposal']) ?>" target="_blank" style="color: var(--brand);">Lihat file</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
			
			<?php if ($editingId): ?>
			<div class="validasi-section">
				<div class="validasi-title">Status Validasi</div>
				<div class="validasi-grid">
					<!-- 1. Keuangan -->
					<div class="validasi-item <?= $editingData['validasi_keuangan'] ? 'validated' : 'pending' ?>">
						<div class="validasi-icon <?= $editingData['validasi_keuangan'] ? 'validated' : 'pending' ?>">
							<?= $editingData['validasi_keuangan'] ? '✓' : '?' ?>
						</div>
						<div class="validasi-text">
							<div>1. Keuangan</div>
							<div class="validasi-action">
								<?php if ($editingData['validasi_keuangan']): ?>
									Validasi: <?= date('d/m/Y H:i', strtotime($editingData['validasi_keuangan_at'])) ?>
								<?php else: ?>
									Menunggu validasi
								<?php endif; ?>
							</div>
						</div>
					</div>

					<!-- 2. Kepala Sekolah -->
					<div class="validasi-item <?= $editingData['validasi_kepsek'] ? 'validated' : 'pending' ?>">
						<div class="validasi-icon <?= $editingData['validasi_kepsek'] ? 'validated' : 'pending' ?>">
							<?= $editingData['validasi_kepsek'] ? '✓' : '?' ?>
						</div>
						<div class="validasi-text">
							<div>2. Kepala Sekolah</div>
							<div class="validasi-action">
								<?php if ($editingData['validasi_kepsek']): ?>
									Validasi: <?= date('d/m/Y H:i', strtotime($editingData['validasi_kepsek_at'])) ?>
								<?php else: ?>
									Menunggu validasi
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<!-- 3. Ketua Yayasan -->
					<div class="validasi-item <?= $editingData['validasi_ketua_yayasan'] ? 'validated' : 'pending' ?>">
						<div class="validasi-icon <?= $editingData['validasi_ketua_yayasan'] ? 'validated' : 'pending' ?>">
							<?= $editingData['validasi_ketua_yayasan'] ? '✓' : '?' ?>
						</div>
						<div class="validasi-text">
							<div>3. Ketua Yayasan</div>
							<div class="validasi-action">
								<?php if ($editingData['validasi_ketua_yayasan']): ?>
									Validasi: <?= date('d/m/Y H:i', strtotime($editingData['validasi_ketua_yayasan_at'])) ?>
								<?php else: ?>
									Menunggu validasi
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
					
			<div class="proposal-actions">
				<button type="submit" name="simpan"><?= $editingId !== null ? 'Update' : 'Simpan' ?></button>
				<a href="?pg=<?= htmlspecialchars($currentPg) ?>" class="btn btn-secondary" style="padding: 9px 14px; border-radius: 8px; background: #f1f5f9; color: #374151; border: 1px solid #d1d5db; margin-left: 8px; text-decoration: none; display: inline-block;">Batal</a>
			</div>
		</form>
	</div>
</div>

<?php } // Tutup blok else untuk user biasa ?>

<!-- Modal Input Revisi Proposal (Untuk Validator) -->
<div id="revisiModal" class="modal-overlay" style="display: <?= isset($showRevisiModal) && $showRevisiModal ? 'block' : 'none' ?>; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
	<div class="modal-content" style="background: white; border-radius: 12px; padding: 24px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
			<h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 600;">
				Revisi Proposal
			</h3>
			<a href="?pg=<?= htmlspecialchars($currentPg) ?><?= isset($_GET['view']) && $_GET['view'] === 'validation' ? '&view=validation' : '' ?>" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280; text-decoration: none;">&times;</a>
		</div>
		
		<form method="post">
			<input type="hidden" name="revisi_proposal" value="<?= isset($revisiModalData['proposal_id']) ? (int)$revisiModalData['proposal_id'] : '' ?>">
			<input type="hidden" name="role" value="<?= isset($revisiModalData['role']) ? htmlspecialchars($revisiModalData['role']) : '' ?>">
			
			<div class="form-group">
				<label><strong>Proposal:</strong></label>
				<div style="padding: 8px; background: #f9fafb; border-radius: 6px; margin-top: 4px;">
					<?= isset($revisiModalData['proposal_name']) ? htmlspecialchars($revisiModalData['proposal_name']) : '' ?>
				</div>
			</div>
			
			<div class="form-group">
				<label><strong>Role:</strong></label>
				<div style="padding: 8px; background: #f9fafb; border-radius: 6px; margin-top: 4px;">
					<?= isset($revisiModalData['role_name']) ? htmlspecialchars($revisiModalData['role_name']) : '' ?>
				</div>
			</div>
			
			<div class="form-group">
				<label for="keterangan_revisi"><strong>Keterangan Revisi *</strong></label>
				<textarea id="keterangan_revisi" name="keterangan_revisi" rows="5" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; resize: vertical;" placeholder="Jelaskan apa yang perlu direvisi dari proposal ini..."></textarea>
			</div>
			
			<div class="proposal-actions">
				<button type="submit" style="background: #f59e0b; color: white;">Kirim Revisi</button>
				<a href="?pg=<?= htmlspecialchars($currentPg) ?><?= isset($_GET['view']) && $_GET['view'] === 'validation' ? '&view=validation' : '' ?>" style="background: #f1f5f9; color: #374151; border: 1px solid #d1d5db; margin-left: 8px; text-decoration: none; display: inline-block; padding: 9px 14px; border-radius: 8px;">Batal</a>
			</div>
		</form>
	</div>
</div>

<!-- Modal Detail Revisi Proposal (Untuk Pengaju & Validator) -->
<div id="detailRevisiModal" class="modal-overlay" style="display: <?= isset($showDetailRevisiModal) && $showDetailRevisiModal ? 'block' : 'none' ?>; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999999;">
	<div class="modal-content" style="background: white; border-radius: 12px; padding: 24px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000000;">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
			<h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 600;">
				Detail Revisi Proposal
			</h3>
			<a href="?pg=<?= htmlspecialchars($currentPg) ?><?= isset($_GET['view']) && $_GET['view'] === 'validation' ? '&view=validation' : '' ?>" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280; text-decoration: none;">&times;</a>
		</div>
		
		<?php if (isset($detailRevisiData)): ?>
			<div style="margin-bottom: 16px;">
				<strong>Proposal:</strong> <?= htmlspecialchars($detailRevisiData['proposal_name']) ?>
			</div>
			<div style="margin-bottom: 16px;">
				<strong>Status Revisi Oleh:</strong> <span style="background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 6px; font-weight: 600; font-size: 12px;"><?= htmlspecialchars($detailRevisiData['role_name']) ?></span>
			</div>
			<div style="margin-bottom: 16px;">
				<strong>Tanggal Catatan:</strong> <?= htmlspecialchars($detailRevisiData['created_at']) ?>
			</div>
			<div style="margin-bottom: 16px;">
				<strong>Status:</strong> 
				<span style="color: <?= $detailRevisiData['status'] === 'pending' ? '#f59e0b' : '#10b981' ?>; font-weight: bold;">
					<?= $detailRevisiData['status'] === 'pending' ? '⏳ Menunggu Perbaikan' : '✓ Sudah Diperbaiki' ?>
				</span>
			</div>
			<div style="margin-bottom: 16px;">
				<strong>Isi Catatan Revisi:</strong>
				<div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 14px; border-radius: 8px; margin-top: 8px; white-space: pre-wrap; font-size: 14px; color: #1f2937;">
					<?= htmlspecialchars($detailRevisiData['keterangan_revisi']) ?>
				</div>
			</div>
			<div style="margin-bottom: 16px;">
				<strong>Diberikan Oleh:</strong> <?= htmlspecialchars($detailRevisiData['revisi_oleh']) ?>
			</div>
		<?php endif; ?>
		
		<div class="proposal-actions" style="margin-top: 20px;">
			<a href="?pg=<?= htmlspecialchars($currentPg) ?><?= isset($_GET['view']) && $_GET['view'] === 'validation' ? '&view=validation' : '' ?>" style="background: #f1f5f9; color: #374151; border: 1px solid #d1d5db; text-decoration: none; display: inline-block; padding: 9px 14px; border-radius: 8px;">Tutup</a>
		</div>
	</div>
</div>

<!-- Modal Input / Edit LPJ & Dokumentasi -->
<div id="lpjDokModal" class="modal-overlay" style="display: <?= isset($showLpjModal) && $showLpjModal ? 'block' : 'none' ?>; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999999;">
	<div class="modal-content" style="background: white; border-radius: 12px; padding: 24px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000000;">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
			<h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 600;">
				Input LPJ &amp; Dokumentasi
			</h3>
			<a href="?pg=<?= htmlspecialchars($currentPg) ?>" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280; text-decoration: none;">&times;</a>
		</div>
		
		<?php if (isset($lpjModalData)): ?>
		<form method="post" enctype="multipart/form-data">
			<input type="hidden" name="proposal_id" value="<?= (int)$lpjModalData['id'] ?>">
			
			<div class="form-group" style="margin-bottom: 16px;">
				<label><strong>Nama Proposal:</strong></label>
				<div style="padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px; font-weight: 600; color: #1f2937;">
					<?= htmlspecialchars($lpjModalData['nama_proposal']) ?>
				</div>
			</div>
			
			<div class="form-group" style="margin-bottom: 16px;">
				<label for="dokumentasi_link"><strong>Link Dokumentasi</strong></label>
				<input type="url" id="dokumentasi_link" name="dokumentasi_link" value="<?= htmlspecialchars($lpjModalData['dokumentasi_link'] ?? '') ?>" placeholder="https://..." style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
			</div>
			
			<div class="form-group" style="margin-bottom: 20px;">
				<label for="file_lpj"><strong>File LPJ (PDF)</strong></label>
				<input type="file" id="file_lpj" name="file_lpj" accept=".pdf" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
				<?php if (!empty($lpjModalData['file_lpj'])): ?>
					<div style="margin-top: 6px; font-size: 12px;">
						File LPJ saat ini: <a href="uploads/lpj/<?= htmlspecialchars($lpjModalData['file_lpj']) ?>" target="_blank" style="color: var(--brand);">Lihat file LPJ</a>
					</div>
				<?php endif; ?>
			</div>
			
			<div class="proposal-actions" style="display: flex; gap: 8px;">
				<button type="submit" name="simpan_lpj_dok" value="1">Simpan LPJ &amp; Dokumentasi</button>
				<a href="?pg=<?= htmlspecialchars($currentPg) ?>" style="background: #f1f5f9; color: #374151; border: 1px solid #d1d5db; text-decoration: none; display: inline-block; padding: 9px 14px; border-radius: 8px;">Batal</a>
			</div>
		</form>
		<?php endif; ?>
	</div>
</div>

<script>
function openProposalModal() {
	var modal = document.getElementById('proposalModal');
	if (modal) {
		if (modal.parentNode !== document.body) document.body.appendChild(modal);
		modal.style.display = 'block';
	}
	document.body.style.overflow = 'hidden';
}

function closeProposalModal() {
	var modal = document.getElementById('proposalModal');
	if (modal) modal.style.display = 'none';
	document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function() {
	// Pindahkan seluruh modal langsung ke document.body agar tidak terpotong oleh overflow/z-index kontainer parent
	['proposalModal', 'revisiModal', 'detailRevisiModal', 'lpjDokModal'].forEach(function(id) {
		var el = document.getElementById(id);
		if (el && el.parentNode !== document.body) {
			document.body.appendChild(el);
		}
	});

<?php if (isset($editingId) && $editingId !== null): ?>
	openProposalModal();
<?php endif; ?>

<?php if (isset($showLpjModal) && $showLpjModal): ?>
	var lModal = document.getElementById('lpjDokModal');
	if (lModal) {
		lModal.style.display = 'block';
		document.body.style.overflow = 'hidden';
	}
<?php endif; ?>

<?php if (isset($showDetailRevisiModal) && $showDetailRevisiModal): ?>
	var dModal = document.getElementById('detailRevisiModal');
	if (dModal) {
		dModal.style.display = 'block';
		document.body.style.overflow = 'hidden';
	}
<?php endif; ?>

<?php if (isset($showRevisiModal) && $showRevisiModal): ?>
	var rModal = document.getElementById('revisiModal');
	if (rModal) {
		rModal.style.display = 'block';
		document.body.style.overflow = 'hidden';
	}
<?php endif; ?>
});
</script>