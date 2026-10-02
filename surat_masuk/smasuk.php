<?php
// Halaman Surat Masuk (form di atas, tabel di bawah)
// Note: session_start() dan koneksi database sudah tersedia dari top.php

if (!function_exists('suratMasukBuildUserNameMap')) {
	/** @return array<string, string> username => nama */
	function suratMasukBuildUserNameMap($koneksi) {
		$map = [];
		$q = mysqli_query($koneksi, "SELECT username, nama FROM users WHERE TRIM(COALESCE(username,'')) <> ''");
		if ($q) {
			while ($r = mysqli_fetch_assoc($q)) {
				$u = (string)$r['username'];
				$nama = trim((string)($r['nama'] ?? ''));
				$map[$u] = $nama !== '' ? $nama : $u;
			}
		}
		return $map;
	}
}

if (!function_exists('suratMasukLabelAktor')) {
	function suratMasukLabelAktor($username, array $userNameMap) {
		$username = trim((string)$username);
		if ($username === '') {
			return '-';
		}
		if (isset($userNameMap[$username]) && $userNameMap[$username] !== '') {
			return $userNameMap[$username];
		}
		return $username;
	}
}

if (!function_exists('suratMasukNomorExists')) {
	function suratMasukNomorExists($koneksi, $nomorSurat, $unitId, $excludeId = 0) {
		$nomorEsc = mysqli_real_escape_string($koneksi, trim($nomorSurat));
		$unitId = max(1, (int)$unitId);
		$excludeId = (int)$excludeId;
		$excludeSql = $excludeId > 0 ? " AND id <> $excludeId" : '';
		$sql = "SELECT id FROM surat_masuk WHERE nomor_surat = '$nomorEsc' AND unit_id = $unitId" . $excludeSql . " LIMIT 1";
		$q = mysqli_query($koneksi, $sql);
		return $q && mysqli_num_rows($q) > 0;
	}
}

require_once __DIR__ . '/aktor_helper.php';

/**
 * Guru & staff untuk dropdown "Ditujukan Kepada" - Filter by unit sekolah
 * @return array<int, array{id_user:int,username:string,nama:string}>
 */
if (!function_exists('suratMasukGuruOptions')) {
	function suratMasukGuruOptions($koneksi, $selectedUsername = '', $unitSekolah = '', $isAdmin = false, $isYayasan = false) {
		$rows = [];
		$recipientCond = suratMasukRecipientCondSql();

		// Filter unit sekolah - hanya jika memiliki unit_sekolah (admin_yayasan & ketua_yayasan bernilai NULL/kosong sehingga bisa lihat semua)
		$unitFilter = '';
		if (!empty($unitSekolah)) {
			$unitFilter = " AND u.unit_sekolah = '" . mysqli_real_escape_string($koneksi, $unitSekolah) . "'";
		}

		$sql = "SELECT u.id_user, u.username, u.nama FROM users u
			WHERE TRIM(COALESCE(u.username,'')) <> ''
			AND $recipientCond
			$unitFilter
			ORDER BY u.nama ASC";
		$q = mysqli_query($koneksi, $sql);
		if ($q) {
			while ($r = mysqli_fetch_assoc($q)) {
				$rows[] = $r;
			}
		}

		$selectedUsername = is_string($selectedUsername) ? trim($selectedUsername) : '';
		if ($selectedUsername !== '') {
			$found = false;
			foreach ($rows as $r) {
				if (isset($r['username']) && $r['username'] === $selectedUsername) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$esc = mysqli_real_escape_string($koneksi, $selectedUsername);
				$q2 = mysqli_query($koneksi, "SELECT id_user, username, nama FROM users WHERE username = '$esc' OR TRIM(username) = '$esc' LIMIT 1");
				if ($q2 && ($r2 = mysqli_fetch_assoc($q2))) {
					$rows[] = $r2;
				} else {
					$rows[] = ['id_user' => 0, 'username' => $selectedUsername, 'nama' => $selectedUsername];
				}
			}
		}

		return $rows;
	}
}

// Ambil parameter pg untuk membangun tautan aksi
$currentPg = isset($_GET['pg']) ? $_GET['pg'] : '';

function redirectSuratMasuk($currentPg)
{
	$target = 'index.php?pg=' . rawurlencode($currentPg);
	if (!headers_sent()) {
		header('Location: ' . $target);
		exit;
	}
	echo '<script>window.location.href=' . json_encode($target) . ';</script>';
	exit;
}

// Role untuk akses penuh surat masuk (sama seperti admin)
$userLevel = isset($user['level']) ? strtolower((string)$user['level']) : (isset($_SESSION['user']['level']) ? strtolower((string)$_SESSION['user']['level']) : 'admin');
$isAdmin = $userLevel === 'admin';
$isStaff = $userLevel === 'staff' || $userLevel === 'staff_tu';
$suratMasukFullAccess = $isAdmin || $isStaff;

// Hapus data jika ada parameter hapus (hanya admin / staff TU)
if (isset($_GET['hapus']) && isset($koneksi) && $koneksi && $suratMasukFullAccess) {
    $hapusId = (int)$_GET['hapus'];
    if ($hapusId > 0) {
		$query = mysqli_query($koneksi, "DELETE FROM surat_masuk WHERE id = '$hapusId'");
		if ($query) {
			$_SESSION['surat_masuk_flash'] = 'Data surat masuk berhasil dihapus.';
		} else {
			$_SESSION['surat_masuk_flash'] = 'Gagal menghapus data.';
		}
		redirectSuratMasuk($currentPg);
    }
}

// Jika ada parameter edit, siapkan data untuk prefilling
$editingId = null;
$editingData = null;
if (isset($_GET['edit']) && isset($koneksi) && $koneksi) {
    $editingId = (int)$_GET['edit'];
    if ($editingId > 0) {
        $query = mysqli_query($koneksi, "SELECT * FROM surat_masuk WHERE id = '$editingId'");
        if ($query && mysqli_num_rows($query) > 0) {
            $editingData = mysqli_fetch_array($query);
        } else {
            $editingId = null;
            $editingData = null;
        }
    } else {
        $editingId = null;
        $editingData = null;
    }
}

$isKepsek = $userLevel === 'kepsek';

// Fungsi untuk handle upload file
function uploadFileSurat($file, $id_surat = null) {
	$uploadDir = 'uploads/surat_masuk/';
	if (!file_exists($uploadDir)) {
		mkdir($uploadDir, 0777, true);
	}
	
	if (isset($file) && $file['error'] == 0) {
		$maxSize = 5 * 1024 * 1024; // 5MB
		
		if ($file['size'] > $maxSize) {
			return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB.'];
		}
		
		$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if ($extension !== 'pdf') {
			return ['success' => false, 'message' => 'Format file tidak diizinkan. Hanya file PDF yang diperbolehkan.'];
		}
		$fileName = 'surat_' . ($id_surat ? $id_surat . '_' : '') . time() . '_' . uniqid() . '.' . $extension;
		$filePath = $uploadDir . $fileName;
		
		if (move_uploaded_file($file['tmp_name'], $filePath)) {
			return ['success' => true, 'filename' => $fileName];
		} else {
			return ['success' => false, 'message' => 'Gagal mengupload file.'];
		}
	}
	return ['success' => false, 'message' => 'Tidak ada file yang diupload.'];
}

// Jika tombol simpan ditekan, tambahkan/ubah di database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan']) && isset($koneksi) && $koneksi) {
	$nomor_surat = mysqli_real_escape_string($koneksi, trim($_POST['nomor_surat'] ?? ''));
	$tanggal_masuk = mysqli_real_escape_string($koneksi, trim($_POST['tanggal_masuk'] ?? ''));
	$today = date('Y-m-d');
	if (empty($_POST['id_edit']) && $tanggal_masuk < $today) {
		$_SESSION['surat_masuk_flash'] = 'Tanggal surat masuk tidak boleh backdate (lebih awal dari hari ini).';
		redirectSuratMasuk($currentPg);
	}
	$perihal = mysqli_real_escape_string($koneksi, trim($_POST['perihal'] ?? ''));
	$nama_instansi = mysqli_real_escape_string($koneksi, trim($_POST['nama_instansi'] ?? ''));
	$disposisi = mysqli_real_escape_string($koneksi, trim($_POST['disposisi'] ?? ''));
	$aktorRaw = trim($_POST['aktor_tujuan'] ?? '');
	$aktorUsername = suratMasukResolveAktorUsername($koneksi, $aktorRaw);
	if ($aktorUsername === '') {
		$_SESSION['surat_masuk_flash'] = 'Penerima surat (Ditujukan Kepada) harus dipilih.';
		redirectSuratMasuk($currentPg);
	}
	$aktor_tujuan = mysqli_real_escape_string($koneksi, $aktorUsername);
	$file_surat = null;
	
	// Handle upload file jika ada
	if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] == 0) {
		$uploadResult = uploadFileSurat($_FILES['file_surat']);
		if ($uploadResult['success']) {
			$file_surat = mysqli_real_escape_string($koneksi, $uploadResult['filename']);
		} else {
			$_SESSION['surat_masuk_flash'] = $uploadResult['message'];
			redirectSuratMasuk($currentPg);
		}
	}
	
	if (isset($_POST['id_edit']) && $_POST['id_edit'] !== '') {
		// Update
		$id_edit = (int)$_POST['id_edit'];
		if ($nomor_surat !== '' && suratMasukNomorExists($koneksi, $nomor_surat, 1, $id_edit)) {
			$_SESSION['surat_masuk_flash'] = 'Nomor surat "' . htmlspecialchars($nomor_surat, ENT_QUOTES, 'UTF-8') . '" sudah ada. Gunakan nomor lain atau edit data yang sudah ada.';
			redirectSuratMasuk($currentPg);
		}
		if ($isKepsek && !$suratMasukFullAccess) {
			// Kepsek hanya boleh ubah disposisi + aktor_tujuan
			$query = mysqli_query($koneksi, "UPDATE surat_masuk SET disposisi = '$disposisi', aktor_tujuan = '$aktor_tujuan' WHERE id = '$id_edit'");
		} else {
			$fileUpdate = $file_surat ? ", file_surat = '$file_surat'" : "";
			$query = mysqli_query($koneksi, "UPDATE surat_masuk SET 
				nomor_surat = '$nomor_surat',
				tanggal_masuk = '$tanggal_masuk',
				perihal = '$perihal',
				nama_instansi = '$nama_instansi',
				disposisi = '$disposisi',
				aktor_tujuan = '$aktor_tujuan'
				$fileUpdate
				WHERE id = '$id_edit'");
		}
		if ($query) {
			$_SESSION['surat_masuk_flash'] = 'Data surat masuk berhasil diperbarui.';
		} else {
			$_SESSION['surat_masuk_flash'] = 'Gagal memperbarui data: ' . mysqli_error($koneksi);
		}
		$editingId = null;
		$editingData = null;
	} else {
		// Insert baru
		if ($nomor_surat !== '' && suratMasukNomorExists($koneksi, $nomor_surat, 1)) {
			$_SESSION['surat_masuk_flash'] = 'Nomor surat "' . htmlspecialchars($nomor_surat, ENT_QUOTES, 'UTF-8') . '" sudah terdaftar. Cek daftar surat atau gunakan nomor berbeda.';
			redirectSuratMasuk($currentPg);
		}
		$fileColumn = $file_surat ? ", file_surat" : "";
		$fileValue = $file_surat ? ", '$file_surat'" : "";
		
		// Ambil unit sekolah dari user yang input (pengirim/operator)
		$inputUnitSekolah = $user['unit_sekolah'] ?? $_SESSION['unit_sekolah'] ?? '';
		$unitSekolahCol = !empty($inputUnitSekolah) ? ", unit_sekolah" : "";
		$unitSekolahVal = !empty($inputUnitSekolah) ? ", '" . mysqli_real_escape_string($koneksi, $inputUnitSekolah) . "'" : "";
		
		$query = mysqli_query($koneksi, "INSERT INTO surat_masuk (nomor_surat, tanggal_masuk, perihal, nama_instansi, disposisi, aktor_tujuan$unitSekolahCol$fileColumn) 
			VALUES ('$nomor_surat', '$tanggal_masuk', '$perihal', '$nama_instansi', '$disposisi', '$aktor_tujuan'$unitSekolahVal$fileValue)");
		if ($query) {
			$_SESSION['surat_masuk_flash'] = 'Data surat masuk berhasil disimpan.';
		} else {
			$_SESSION['surat_masuk_flash'] = 'Gagal menyimpan data: ' . mysqli_error($koneksi);
		}
	}
	redirectSuratMasuk($currentPg);
}

// Ambil data dari database dengan pagination
$suratMasuk = [];
$totalSurat = 0;
$years = [];
$perPage = 8; // jumlah baris per halaman
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$totalPages = 1;
$offset = ($currentPage - 1) * $perPage;

if (isset($koneksi) && $koneksi) {
	// Filter pencarian & tahun
	$searchQuery = trim($_GET['q'] ?? '');
	$filterYear = trim($_GET['tahun'] ?? '');
	$searchEsc = mysqli_real_escape_string($koneksi, $searchQuery);
	$yearInt = (ctype_digit($filterYear) ? (int)$filterYear : 0);

	$where = [];
	if ($searchQuery !== '') {
		$where[] = "(
			nomor_surat LIKE '%$searchEsc%' OR
			perihal LIKE '%$searchEsc%' OR
			nama_instansi LIKE '%$searchEsc%' OR
			disposisi LIKE '%$searchEsc%' OR
			aktor_tujuan LIKE '%$searchEsc%'
		)";
	}
	if ($yearInt > 0) {
		$where[] = "YEAR(tanggal_masuk) = $yearInt";
	}
	
	// Filter unit sekolah - yayasan & user tanpa unit khusus melihat semua sekolah
	$userLevelSm = strtolower(trim((string)($user['level'] ?? $_SESSION['user']['level'] ?? '')));
	$userRawUnitSm = trim((string)($user['unit_sekolah'] ?? ''));
	$isYayasanSm = in_array($userLevelSm, ['yayasan', 'ketua_yayasan', 'pembina_yayasan', 'keuangan'], true);
	$seeAllSchoolsSm = ($userRawUnitSm === '' || $userRawUnitSm === 'wira_buana' || $isYayasanSm);
	
	// Check if unit_sekolah column exists
	$hasUnitColSm = false;
	$checkColSm = mysqli_query($koneksi, "SHOW COLUMNS FROM surat_masuk LIKE 'unit_sekolah'");
	if ($checkColSm && mysqli_num_rows($checkColSm) > 0) {
		$hasUnitColSm = true;
	}
	
	if ($hasUnitColSm && !$seeAllSchoolsSm && !empty($userRawUnitSm)) {
		$escUnitSm = mysqli_real_escape_string($koneksi, $userRawUnitSm);
		$where[] = "unit_sekolah = '$escUnitSm'";
	}
	
	$whereSql = !empty($where) ? (" WHERE " . implode(" AND ", $where)) : "";

	// List tahun untuk dropdown (berdasarkan data yang ada)
	$years = [];
	$yearRes = mysqli_query($koneksi, "SELECT DISTINCT YEAR(tanggal_masuk) AS y FROM surat_masuk ORDER BY y DESC");
	if ($yearRes) {
		while ($yr = mysqli_fetch_assoc($yearRes)) {
			if (!empty($yr['y'])) $years[] = (int)$yr['y'];
		}
	}

	// Hitung total data
	$countQuery = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM surat_masuk" . $whereSql);
	if ($countQuery) {
		$countRow = mysqli_fetch_assoc($countQuery);
		$totalSurat = (int)($countRow['total'] ?? 0);
		if ($totalSurat > 0) {
			$totalPages = (int)ceil($totalSurat / $perPage);
		}
	}

	// Ambil data sesuai halaman
	$query = mysqli_query($koneksi, "SELECT * FROM surat_masuk" . $whereSql . " ORDER BY tanggal_masuk DESC, id DESC LIMIT $perPage OFFSET $offset");
	if ($query) {
		while ($row = mysqli_fetch_array($query)) {
			$suratMasuk[] = $row;
		}
	}
}

$suratMasukUserMap = [];
if (isset($koneksi) && $koneksi && $suratMasukFullAccess) {
	$suratMasukUserMap = suratMasukBuildUserNameMap($koneksi);
}
?>

<style>
	:root {
		--card-bg: #ffffff;
		--card-border: #e6e9ef;
		--muted-text: #6b7280;
		--brand: #4f46e5; /* indigo */
		--brand-dark: #4338ca;
		--table-header: #f8fafc;
	}
	.surat-container { width: 100%; max-width: 100%; margin: 0; padding: 0; font-size: 14px; box-sizing: border-box; }
	.surat-layout { display: flex; gap: 18px; align-items: flex-start; width: 100%; }
	.surat-main { flex: 1 1 0; min-width: 0; max-width: 100%; }
	.surat-side { flex: 0 0 360px; width: 360px; max-width: 38%; min-width: 280px; position: sticky; top: 12px; align-self: flex-start; }
	.surat-card { background: var(--card-bg); border: 1px solid var(--card-border); padding: 14px; border-radius: 10px; box-shadow: 0 4px 14px rgba(15,23,42,.06); }
	.surat-title { display:flex; align-items:center; gap:10px; margin: 0 0 12px; font-weight:700; font-size:16px; color:#0f172a; }
	.surat-title .dot { width:10px; height:10px; border-radius:50%; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); display:inline-block; }

	.surat-form .form-row { display: flex; flex-wrap: wrap; gap: 10px; }
	.surat-form .form-group { flex: 1 1 260px; display: flex; flex-direction: column; }
	.surat-form label { font-weight: 600; margin-bottom: 6px; color:#111827; }
	.surat-form input[type="text"],
	.surat-form input[type="date"],
	.surat-form input[type="file"],
	.surat-form select,
	.surat-form textarea { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; background:#ffffff; transition: all .15s ease; }
	.surat-form input[type="text"]:focus,
	.surat-form input[type="date"]:focus,
	.surat-form select:focus,
	.surat-form textarea:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(79,70,229,.15); }
	.surat-form textarea { min-height: 96px; resize: vertical; }
	.surat-actions { margin-top: 10px; }
	.surat-actions button { padding: 9px 14px; border: 1px solid var(--brand); background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; border-radius: 8px; cursor: pointer; font-weight:600; }
	.surat-actions button:hover { filter: brightness(.95); }

	.surat-table-wrap { overflow-x: auto; overflow-y: visible; max-width: 100%; border:1px solid var(--card-border); border-radius:10px; background:#fff; }
	.surat-table { width: 100%; min-width: 720px; border-collapse: separate; border-spacing: 0; }
	.surat-table thead th { position: sticky; top:0; background: var(--table-header); z-index:1; }
	.surat-table th, .surat-table td { border-bottom: 1px solid var(--card-border); padding: 10px 12px; vertical-align: top; }
	.surat-table tbody tr:hover { background:#fafafa; }
	.surat-table th { text-align: left; color:#111827; font-weight:700; }
	.surat-table td { color:#111827; }
	.surat-table tbody tr:nth-child(odd) { background:#fcfcff; }
	.btn-sm { padding: 6px 10px; border: 1px solid #c7c9d1; background: #f3f4f6; border-radius: 8px; cursor: pointer; font-size: 12px; color:#111827; }
	.btn-sm:hover { background: #e5e7eb; }
	.btn-danger { border-color:#fecaca; background:#fee2e2; color:#b91c1c; }
	.btn-danger:hover { background:#fecaca; }
	/* Icon buttons for actions */
	.icon-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid transparent; text-decoration:none; }
	.icon-edit { background:#e8f7ee; border-color:#bbf7d0; color:#047857; }
	.icon-edit:hover { background:#d1fae5; }
	.icon-del { background:#fee2e2; border-color:#fecaca; color:#b91c1c; }
	.icon-del:hover { background:#fecaca; }
	.icon-btn i { font-size:18px; line-height:1; }
	.pagination { display:flex; gap:6px; padding:10px 12px; align-items:center; justify-content:flex-end; flex-wrap:wrap; }
	.pagination a,
	.pagination span { padding:6px 10px; border-radius:8px; border:1px solid #e5e7eb; font-size:12px; text-decoration:none; color:#111827; background:#f9fafb; }
	.pagination a:hover { background:#e5e7eb; }
	.pagination .active { background: var(--brand); border-color: var(--brand-dark); color:#fff; }
	.pagination .disabled { color:#9ca3af; background:#f9fafb; border-style:dashed; }
	@media (max-width: 992px) {
		.surat-layout { flex-direction: column; }
		.surat-main { flex: 1 1 auto; width: 100%; }
		.surat-side { position: static; flex: 1 1 auto; width: 100%; max-width: 100%; min-width: 0; }
	}
</style>

<div class="surat-container">
	<div class="surat-layout">
		<div class="surat-main">
			<div class="surat-card">
				<?php if (!empty($_SESSION['surat_masuk_flash'])): ?>
				<div class="alert alert-success" style="margin-bottom:12px; padding:10px 14px; border-radius:8px; background:#ecfdf5; border:1px solid #bbf7d0; color:#047857;">
					<?= htmlspecialchars($_SESSION['surat_masuk_flash']); ?>
				</div>
				<?php unset($_SESSION['surat_masuk_flash']); endif; ?>
				<h3 class="surat-title"><span class="dot"></span>Data Surat Masuk</h3>
				<form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; margin: 0 0 12px;">
					<input type="hidden" name="pg" value="<?= htmlspecialchars($currentPg) ?>">
					<div style="flex:1 1 260px; min-width: 220px;">
						<label style="font-weight:600; display:block; margin-bottom:6px; color:#111827;">Pencarian</label>
						<input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Cari nomor surat, perihal, instansi, tujuan, disposisi, ditujukan kepada..." style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;">
					</div>
					<div style="flex:0 0 180px; min-width: 160px;">
						<label style="font-weight:600; display:block; margin-bottom:6px; color:#111827;">Tahun</label>
						<select name="tahun" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff;">
							<option value="">Semua</option>
							<?php
							$selectedYear = $_GET['tahun'] ?? '';
							if (!empty($years)) {
								foreach ($years as $y) {
									$sel = ((string)$y === (string)$selectedYear) ? 'selected' : '';
									echo "<option value=\"" . (int)$y . "\" $sel>" . (int)$y . "</option>";
								}
							}
							?>
						</select>
					</div>
					<div style="display:flex; gap:8px;">
						<button type="submit" class="btn-sm" style="padding:10px 14px; border-radius:8px; border:1px solid #c7c9d1; background:#f3f4f6;">Filter</button>
						<a class="btn-sm" href="?pg=<?= urlencode($currentPg) ?>" style="padding:10px 14px; border-radius:8px; border:1px solid #c7c9d1; background:#fff; text-decoration:none; display:inline-flex; align-items:center;">Reset</a>
					</div>
				</form>
				<div class="surat-table-wrap">
				<table class="surat-table">
					<thead>
						<tr>
							<th>No</th>
							<th>Nomor Surat</th>
							<th>Tanggal Masuk</th>
							<th>Perihal</th>
							<th>Nama Instansi</th>
							<th>Disposisi</th>
							<?php if ($suratMasukFullAccess): ?>
							<th>Ditujukan Kepada</th>
							<?php endif; ?>
							<th>File</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($suratMasuk)): ?>
						<tr>
							<td colspan="<?= $suratMasukFullAccess ? 9 : 8 ?>" style="text-align: center; padding: 20px; color: #6b7280;">
								<?php if (!empty($_GET['q'] ?? '') || !empty($_GET['tahun'] ?? '')): ?>
									Data tidak ditemukan untuk filter yang dipilih.
								<?php else: ?>
									Belum ada data surat masuk. Silakan tambahkan data baru menggunakan form di samping.
								<?php endif; ?>
							</td>
						</tr>
						<?php else: ?>
						<?php $no = $offset + 1; foreach ($suratMasuk as $item): ?>
						<tr>
							<td><?= $no++; ?></td>
							<td><?= htmlspecialchars($item['nomor_surat']); ?></td>
							<td><?= htmlspecialchars($item['tanggal_masuk']); ?></td>
							<td><?= htmlspecialchars($item['perihal']); ?></td>
							<td><?= htmlspecialchars($item['nama_instansi']); ?></td>
							<td><?= htmlspecialchars($item['disposisi']); ?></td>
							<?php if ($suratMasukFullAccess): ?>
							<td><?= htmlspecialchars(suratMasukLabelAktor($item['aktor_tujuan'] ?? '', $suratMasukUserMap), ENT_QUOTES, 'UTF-8'); ?></td>
							<?php endif; ?>
							<td>
								<?php if (!empty($item['file_surat'])): ?>
								<a href="uploads/surat_masuk/<?= htmlspecialchars($item['file_surat']) ?>" target="_blank" class="icon-btn icon-edit" title="Download File" style="text-decoration: none;">
									<i class="material-icons-outlined">download</i>
								</a>
								<?php else: ?>
								<span style="color: #9ca3af; font-size: 12px;">-</span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ($suratMasukFullAccess || $isKepsek): ?>
								<a class="icon-btn icon-edit" title="Edit" href="?pg=<?= urlencode($currentPg) ?>&page=<?= (int)$currentPage ?>&q=<?= urlencode($_GET['q'] ?? '') ?>&tahun=<?= urlencode($_GET['tahun'] ?? '') ?>&edit=<?= (int)($item['id']??0) ?>">
									<i class="material-icons-outlined">edit</i>
								</a>
								<?php endif; ?>
								<?php if ($suratMasukFullAccess): ?>
								<a class="icon-btn icon-del" title="Hapus" href="?pg=<?= urlencode($currentPg) ?>&page=<?= (int)$currentPage ?>&q=<?= urlencode($_GET['q'] ?? '') ?>&tahun=<?= urlencode($_GET['tahun'] ?? '') ?>&hapus=<?= (int)($item['id']??0) ?>" onclick="return confirm('Hapus data ini?');">
									<i class="material-icons-outlined">delete</i>
								</a>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
				</div>
				<?php if ($totalPages > 1): ?>
				<div class="pagination">
					<?php
					$baseUrl = '?pg=' . urlencode($currentPg) . '&q=' . urlencode($_GET['q'] ?? '') . '&tahun=' . urlencode($_GET['tahun'] ?? '');
					$prevPage = $currentPage - 1;
					$nextPage = $currentPage + 1;
					?>
					<span class="<?= $currentPage <= 1 ? 'disabled' : '' ?>">
						<?php if ($currentPage > 1): ?>
							<a href="<?= $baseUrl . '&page=' . (int)$prevPage ?>">« Sebelumnya</a>
						<?php else: ?>
							<span class="disabled">« Sebelumnya</span>
						<?php endif; ?>
					</span>
					<?php for ($i = 1; $i <= $totalPages; $i++): ?>
						<?php if ($i == $currentPage): ?>
							<span class="active"><?= $i ?></span>
						<?php else: ?>
							<a href="<?= $baseUrl . '&page=' . (int)$i ?>"><?= $i ?></a>
						<?php endif; ?>
					<?php endfor; ?>
					<span class="<?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
						<?php if ($currentPage < $totalPages): ?>
							<a href="<?= $baseUrl . '&page=' . (int)$nextPage ?>">Berikutnya »</a>
						<?php else: ?>
							<span class="disabled">Berikutnya »</span>
						<?php endif; ?>
					</span>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="surat-side">
			<div class="surat-card">
				<h3 class="surat-title"><span class="dot"></span>Input Surat Masuk</h3>
				<form class="surat-form" method="post" enctype="multipart/form-data">
					<input type="hidden" name="id_edit" value="<?= $editingId !== null ? (int)$editingId : '' ?>">
					<div class="form-row">
						<div class="form-group">
							<label for="nomor_surat">Nomor Surat</label>
							<input type="text" id="nomor_surat" name="nomor_surat" value="<?= $editingData['nomor_surat'] ?? '' ?>" required>
						</div>
						<div class="form-group">
							<label for="tanggal_masuk">Tanggal Surat Masuk</label>
							<input type="date" id="tanggal_masuk" name="tanggal_masuk" value="<?= htmlspecialchars($editingData['tanggal_masuk'] ?? date('Y-m-d')) ?>" min="<?= isset($editingData['tanggal_masuk']) ? htmlspecialchars($editingData['tanggal_masuk']) : date('Y-m-d') ?>" required>
						</div>
						<div class="form-group">
							<label for="perihal">Perihal</label>
							<input type="text" id="perihal" name="perihal" value="<?= $editingData['perihal'] ?? '' ?>" required>
						</div>
						<div class="form-group">
							<label for="nama_instansi">Nama Instansi</label>
							<input type="text" id="nama_instansi" name="nama_instansi" value="<?= $editingData['nama_instansi'] ?? '' ?>" required>
						</div>
												<div class="form-group" style="flex:1 1 100%">
							<label for="disposisi">Disposisi</label>
							<textarea id="disposisi" name="disposisi" required><?= $editingData['disposisi'] ?? '' ?></textarea>
						</div>
						<div class="form-group" style="flex:1 1 100%">
							<label for="aktor_tujuan">Ditujukan Kepada</label>
							<select id="aktor_tujuan" name="aktor_tujuan" required>
								<?php
								$opt = isset($editingData['aktor_tujuan']) ? (string)$editingData['aktor_tujuan'] : '';
								$guruOptions = [];
								if (isset($koneksi) && $koneksi) {
									$userUnitSekolahSm = $user['unit_sekolah'] ?? $_SESSION['unit_sekolah'] ?? '';
								$userLevelSm = strtolower($user['level'] ?? $_SESSION['user']['level'] ?? '');
								$isAdminSm = ($userLevelSm === 'admin');
								$isYayasanSm = ($userLevelSm === 'yayasan');
								$guruOptions = suratMasukGuruOptions($koneksi, $opt, $userUnitSekolahSm, $isAdminSm, $isYayasanSm);
								}
								$usersLoaded = !empty($guruOptions);
								if ($usersLoaded) {
									foreach ($guruOptions as $userData) {
										$uid = (int)($userData['id_user'] ?? 0);
										$uname = trim((string)($userData['username'] ?? ''));
										$nama = !empty($userData['nama']) ? $userData['nama'] : $uname;
										$selected = ($opt !== '' && $opt === $uname) ? 'selected' : '';
										$valueAttr = $uid > 0 ? (string)$uid : htmlspecialchars($uname, ENT_QUOTES, 'UTF-8');
										echo '<option value="' . htmlspecialchars($valueAttr, ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>'
											. htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') . '</option>';
									}
								} elseif ($opt !== '') {
									echo '<option value="' . htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') . '" selected>' . htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') . '</option>';
								} else {
									echo '<option value="">Pilih guru / staff</option>';
								}
								?>
							</select>
							<small style="color: #6b7280; font-size: 12px; margin-top: 6px; display: block;">
								Menampilkan semua guru dan staff.
							</small>
						</div>
						<div class="form-group" style="flex:1 1 100%">
							<label for="file_surat">Upload File Surat (PDF)</label>
							<input type="file" id="file_surat" name="file_surat" accept=".pdf" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; background:#ffffff;">
							<small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">
								Format: PDF (Maks. 5MB)
							</small>
							<?php if (isset($editingData['file_surat']) && !empty($editingData['file_surat'])): ?>
							<div style="margin-top: 8px; padding: 8px; background: #f0f9ff; border-radius: 6px; font-size: 12px;">
								<strong>File saat ini:</strong> 
								<a href="uploads/surat_masuk/<?= htmlspecialchars($editingData['file_surat']) ?>" target="_blank" style="color: #0369a1;">
									<?= htmlspecialchars($editingData['file_surat']) ?>
								</a>
								<span style="color: #6b7280;"> (Upload file baru untuk mengganti)</span>
							</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="surat-actions">
						<button type="submit" name="simpan" value="1"><?= $editingId !== null ? 'Update' : 'Simpan' ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>