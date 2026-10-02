<?php
defined('APK') or exit('No access');

// Include Summernote CDN untuk Rich Text & Table Editor
echo '<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">';
echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi koneksi
global $koneksi;
if (!$koneksi) {
    include '../../koneksi.php';
}

// Variabel user
$userLevel = isset($user['level']) ? strtolower(trim($user['level'])) : (isset($_SESSION['user']['level']) ? strtolower(trim($_SESSION['user']['level'])) : 'admin');
$userLevel = str_replace(' ', '_', $userLevel);
$userId = isset($user['id_user']) ? (int)$user['id_user'] : (isset($_SESSION['user']['id_user']) ? (int)$_SESSION['user']['id_user'] : 0);
$userJabatan = isset($user['jabatan']) ? strtolower(trim($user['jabatan'])) : (isset($_SESSION['user']['jabatan']) ? strtolower(trim($_SESSION['user']['jabatan'])) : '');

$isAdmin = $userLevel === 'admin';
$isYayasan = $userLevel === 'yayasan';
$isStaffTU = in_array($userLevel, ['staff_tu', 'staff'], true) || strpos($userLevel, 'staff') !== false;

// Variabel unit sekolah untuk filtering
$user_unit_sekolah_sk = $user['unit_sekolah'] ?? $_SESSION['unit_sekolah'] ?? '';
$isGuru = $userLevel === 'guru';
$isKepsek = in_array($userLevel, ['kepsek', 'kepala_sekolah'], true);
$isWakakur = in_array($userLevel, ['wakakur', 'waka_kurikulum'], true);
$isWakasis = in_array($userLevel, ['wakasis', 'kesiswaan'], true);
$isKaprog = in_array($userLevel, ['kaprog', 'keprog'], true);

// Pengaju: guru biasa, wakakur, wakasis, kaprog, kepsek
$isPengaju = $isGuru || $isWakakur || $isWakasis || $isKaprog || $isKepsek;

// Variabel lain
$currentPg = isset($_GET['pg']) ? $_GET['pg'] : '';
$errors = [];
$successMessage = '';

if (isset($_SESSION['sk_errors'])) {
    $errors = $_SESSION['sk_errors'];
    unset($_SESSION['sk_errors']);
}
if (isset($_SESSION['sk_success'])) {
    $successMessage = $_SESSION['sk_success'];
    unset($_SESSION['sk_success']);
}

if (!function_exists('skNormalizeStatus')) {
    function skNormalizeStatus($status) {
        return strtolower(trim((string)$status));
    }
}

if (!function_exists('skStatusBadge')) {
    function skStatusBadge($status) {
        $status = skNormalizeStatus($status);
        $map = [
            'diajukan' => ['label' => 'Diajukan', 'class' => 'sk-status-diajukan'],
            'diproses' => ['label' => 'Diproses', 'class' => 'sk-status-diproses'],
            'selesai' => ['label' => 'Selesai', 'class' => 'sk-status-selesai'],
            'ditolak' => ['label' => 'Ditolak', 'class' => 'sk-status-ditolak'],
        ];
        $s = $map[$status] ?? ['label' => ucfirst((string)$status), 'class' => 'sk-status-diajukan'];
        return '<span class="sk-status-badge ' . $s['class'] . '">' . htmlspecialchars($s['label'], ENT_QUOTES, 'UTF-8') . '</span>';
    }
}

// Fungsi generate nomor surat otomatis
function generateNomorSurat($koneksi, $kategoriId, $tanggal) {
    $tahun = date('Y', strtotime($tanggal));
    
    $stmt = mysqli_prepare($koneksi, "SELECT kode FROM surat_keluar_kategori WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $kategoriId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $kategori = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$kategori) {
        return null;
    }
    
    $kodeKategori = $kategori['kode'];
    
    $stmt = mysqli_prepare($koneksi, "SELECT MAX(CAST(SUBSTRING_INDEX(nomor_surat, '/', 1) AS UNSIGNED)) as max_num FROM surat_keluar WHERE YEAR(tanggal_pengajuan) = ? AND nomor_surat IS NOT NULL AND nomor_surat != ''");
    mysqli_stmt_bind_param($stmt, 'i', $tahun);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    $lastNum = $data['max_num'] ? (int)$data['max_num'] : 0;
    $nomorUrut = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
    
    $bulan = date('n', strtotime($tanggal));
    $bulanRomawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$bulan];
    
    return "$nomorUrut/$kodeKategori/$bulanRomawi/$tahun";
}

if (!function_exists('generateDocxSurat')) {
    function generateDocxSurat($templatePath, $outputPath, $replacements) {
        if (!file_exists($templatePath)) return false;
        if (!copy($templatePath, $outputPath)) return false;
        
        $zip = new ZipArchive();
        if ($zip->open($outputPath) === TRUE) {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml === false) {
                $zip->close();
                return false;
            }
            foreach ($replacements as $search => $replace) {
                $escReplace = htmlspecialchars($replace, ENT_QUOTES, 'UTF-8');
                $escReplace = str_replace("\n", '</w:t><w:br/><w:t>', $escReplace);
                $xml = str_replace($search, $escReplace, $xml);
            }
            $zip->addFromString('word/document.xml', $xml);
            $zip->close();
            return true;
        }
        return false;
    }
}

// PROSES POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 0. Simpan Master Gambar Kop Surat (Admin Only)
    if (isset($_POST['simpan_master_kop']) && $isAdmin) {
        if (isset($_FILES['master_gambar_kop']) && $_FILES['master_gambar_kop']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__FILE__) . '/uploads/kop_surat/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $_FILES['master_gambar_kop'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
                $newName = 'master_kop_' . date('YmdHis') . '_' . rand(100,999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                    $userUnit = $user['unit_sekolah'] ?? $_SESSION['unit_sekolah'] ?? '';
                    if (!empty($userUnit)) {
                        mysqli_query($koneksi, "UPDATE surat_keluar_kategori SET gambar_kop = '$newName' WHERE unit_sekolah = '$userUnit'");
                    } else {
                        mysqli_query($koneksi, "UPDATE surat_keluar_kategori SET gambar_kop = '$newName'");
                    }
                    $_SESSION['sk_success'] = 'Gambar Kop Surat Resmi Sekolah berhasil diunggah & disimpan ke tabel kategori!';
                    echo "<script>window.location.href='?pg=$currentPg';</script>";
                    exit;
                } else {
                    $errors[] = 'Gagal menyimpan file Gambar Kop Surat.';
                }
            } else {
                $errors[] = 'Format file Gambar Kop Surat harus PNG, JPG, atau JPEG.';
            }
        } else {
            $errors[] = 'Pilih file gambar Kop Surat terlebih dahulu.';
        }
    }

    // 1. Simpan Kategori (Admin Only)
    if (isset($_POST['simpan_kategori']) && $isAdmin) {
        $nama = trim($_POST['nama_kategori'] ?? '');
        $kode = strtoupper(trim($_POST['kode_kategori'] ?? ''));
        $deskripsi = trim($_POST['deskripsi_kategori'] ?? '');
        $templateIsi = trim($_POST['template_isi'] ?? '');
        $kategoriId = isset($_POST['kategori_id']) && $_POST['kategori_id'] !== '' ? (int)$_POST['kategori_id'] : null;

        if ($nama === '') $errors[] = 'Nama kategori harus diisi.';
        if ($kode === '') $errors[] = 'Kode kategori harus diisi.';

        // Upload Gambar Kop Surat jika ada
        $gambarKopName = null;
        if (isset($_FILES['gambar_kop']) && $_FILES['gambar_kop']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__FILE__) . '/uploads/kop_surat/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $_FILES['gambar_kop'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
                $newName = 'kop_' . date('YmdHis') . '_' . rand(100,999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                    $gambarKopName = $newName;
                } else {
                    $errors[] = 'Gagal mengunggah file Gambar Kop Surat.';
                }
            } else {
                $errors[] = 'Format Gambar Kop Surat harus PNG, JPG, atau JPEG.';
            }
        }

        if (!$errors) {
            $katUnitSekolah = $user['unit_sekolah'] ?? $_SESSION['unit_sekolah'] ?? '';
            if ($kategoriId) {
                $updateFields = ["nama = ?", "kode = ?", "deskripsi = ?", "template_isi = ?", "unit_sekolah = ?"];
                $types = "sssss";
                $params = [$nama, $kode, $deskripsi, $templateIsi, $katUnitSekolah];

                if ($gambarKopName) {
                    $updateFields[] = "gambar_kop = ?";
                    $types .= "s";
                    $params[] = $gambarKopName;
                }

                $types .= "i";
                $params[] = $kategoriId;

                $sqlUpd = "UPDATE surat_keluar_kategori SET " . implode(", ", $updateFields) . " WHERE id = ?";
                $stmt = mysqli_prepare($koneksi, $sqlUpd);
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                $action = 'diperbarui';
            } else {
                $stmt = mysqli_prepare($koneksi, "INSERT INTO surat_keluar_kategori (nama, kode, deskripsi, template_isi, gambar_kop, unit_sekolah) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'ssssss', $nama, $kode, $deskripsi, $templateIsi, $gambarKopName, $katUnitSekolah);
                $action = 'ditambahkan';
            }

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['sk_success'] = "Kategori berhasil $action.";
                mysqli_stmt_close($stmt);
                echo "<script>window.location.href='?pg=$currentPg';</script>";
                exit;
            } else {
                $errors[] = 'Gagal menyimpan kategori: ' . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }

    // 2. Simpan Pengajuan Surat (Pengaju / Guru)
    if (isset($_POST['simpan_surat'])) {
        $kategoriId = (int)($_POST['kategori_id'] ?? 0);
        $perihal = trim($_POST['perihal'] ?? '');
        $tujuanSurat = trim($_POST['tujuan_surat'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');

        if ($kategoriId === 0) $errors[] = 'Kategori surat harus dipilih.';
        if ($perihal === '') $errors[] = 'Perihal surat harus diisi.';

        if (!$errors) {
            $tanggal = date('Y-m-d');
            $userUnitSekolah = $user['unit_sekolah'] ?? $_SESSION['unit_sekolah'] ?? '';
            $unitSekolahSql = !empty($userUnitSekolah) ? "'" . mysqli_real_escape_string($koneksi, $userUnitSekolah) . "'" : "NULL";

            $escKategori = (int)$kategoriId;
            $escUser = (int)$userId;
            $escPerihal = mysqli_real_escape_string($koneksi, $perihal);
            $escTujuan = mysqli_real_escape_string($koneksi, $tujuanSurat);
            $escCatatan = mysqli_real_escape_string($koneksi, $catatan);
            $escTanggal = mysqli_real_escape_string($koneksi, $tanggal);

            $sql = "INSERT INTO surat_keluar (kategori_id, user_id, perihal, tujuan_surat, catatan, status, tanggal_pengajuan, unit_sekolah) 
                    VALUES ($escKategori, $escUser, '$escPerihal', '$escTujuan', '$escCatatan', 'diajukan', '$escTanggal', $unitSekolahSql)";

            if (mysqli_query($koneksi, $sql)) {
                $_SESSION['sk_success'] = 'Surat berhasil diajukan. Staff TU akan memproses dan menerbitkan dokumen surat ini.';
                echo "<script>window.location.href='?pg=$currentPg';</script>";
                exit;
            } else {
                $errors[] = 'Gagal mengajukan surat: ' . mysqli_error($koneksi);
            }
        }
    }

    // 3. Simpan Pemrosesan & Pembuatan Isi Surat (Staff TU)
    if (isset($_POST['simpan_proses_surat']) && $isStaffTU) {
        $suratId = (int)($_POST['surat_id'] ?? 0);
        $nomorSurat = trim($_POST['nomor_surat'] ?? '');
        $tglKeluar = trim($_POST['tanggal_keluar'] ?? date('Y-m-d'));
        $kotaTanggal = trim($_POST['kota_tanggal'] ?? 'Kab. Bogor');
        $tujuanSurat = trim($_POST['tujuan_surat'] ?? '');
        $lampiran = trim($_POST['lampiran'] ?? '-');
        $perihal = trim($_POST['perihal'] ?? '');
        $isiSurat = trim($_POST['isi_surat'] ?? '');
        $ttdNama = trim($_POST['penandatangan_nama'] ?? '');
        $ttdJabatan = trim($_POST['penandatangan_jabatan'] ?? '');
        $ttdNip = trim($_POST['penandatangan_nip'] ?? '');
        $tembusan = trim($_POST['tembusan'] ?? '');

        if ($suratId <= 0) $errors[] = 'ID Surat tidak valid.';
        if ($nomorSurat === '') $errors[] = 'Nomor Surat harus diisi.';
        if ($perihal === '') $errors[] = 'Perihal harus diisi.';

        if (!$errors) {
            $escNomor = mysqli_real_escape_string($koneksi, $nomorSurat);
            $escTgl = mysqli_real_escape_string($koneksi, $tglKeluar);
            $escKotaTgl = mysqli_real_escape_string($koneksi, $kotaTanggal);
            $escTujuan = mysqli_real_escape_string($koneksi, $tujuanSurat);
            $escLamp = mysqli_real_escape_string($koneksi, $lampiran);
            $escPerihal = mysqli_real_escape_string($koneksi, $perihal);
            $escIsi = mysqli_real_escape_string($koneksi, $isiSurat);
            $escNama = mysqli_real_escape_string($koneksi, $ttdNama);
            $escJab = mysqli_real_escape_string($koneksi, $ttdJabatan);
            $escNip = mysqli_real_escape_string($koneksi, $ttdNip);
            $escTembusan = mysqli_real_escape_string($koneksi, $tembusan);

            // Cek apakah file_ttd sudah diupload sebelumnya
            $qChk = mysqli_query($koneksi, "SELECT file_ttd FROM surat_keluar WHERE id = $suratId LIMIT 1");
            $currFileTtd = ($qChk && ($rC = mysqli_fetch_assoc($qChk))) ? ($rC['file_ttd'] ?? '') : '';
            $statusSet = !empty($currFileTtd) ? 'selesai' : 'diproses';

            $sql = "UPDATE surat_keluar SET 
                    nomor_surat = '$escNomor',
                    tanggal_keluar = '$escTgl',
                    kota_tanggal = '$escKotaTgl',
                    tujuan_surat = '$escTujuan',
                    lampiran = '$escLamp',
                    perihal = '$escPerihal',
                    isi_surat = '$escIsi',
                    penandatangan_nama = '$escNama',
                    penandatangan_jabatan = '$escJab',
                    penandatangan_nip = '$escNip',
                    tembusan = '$escTembusan',
                    status = '$statusSet',
                    updated_at = NOW()
                    WHERE id = $suratId";

            if (mysqli_query($koneksi, $sql)) {
                $_SESSION['sk_success'] = 'Form surat berhasil diproses! Silakan cetak draf PDF untuk dimintakan TTD Kepsek, lalu upload hasil scan-nya.';
                echo "<script>window.open('surat_keluar/skeluar_print.php?id=$suratId', '_blank'); window.location.href='?pg=$currentPg';</script>";
                exit;
            } else {
                $errors[] = 'Gagal memproses surat: ' . mysqli_error($koneksi);
            }
        }
    }

    // 4. Upload File Scan TTD (Staff TU)
    if (isset($_POST['upload_file_ttd']) && $isStaffTU) {
        $suratId = (int)($_POST['surat_id'] ?? 0);
        if ($suratId <= 0) $errors[] = 'ID Surat tidak valid.';

        if (isset($_FILES['file_ttd']) && $_FILES['file_ttd']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__FILE__) . '/uploads/surat_ttd/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $_FILES['file_ttd'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'], true)) {
                $newName = 'surat_ttd_' . $suratId . '_' . date('YmdHis') . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                    mysqli_query($koneksi, "UPDATE surat_keluar SET file_ttd = '$newName', status = 'selesai', updated_at = NOW() WHERE id = $suratId");
                    $_SESSION['sk_success'] = 'File Hasil Scan TTD berhasil diunggah! Status surat kini telah SELESAI.';
                    echo "<script>window.location.href='?pg=$currentPg';</script>";
                    exit;
                } else {
                    $errors[] = 'Gagal menyimpan file Surat TTD.';
                }
            } else {
                $errors[] = 'Format file Surat TTD harus PDF, PNG, JPG, atau JPEG.';
            }
        } else {
            $errors[] = 'Pilih file Surat TTD yang sudah ditandatangani terlebih dahulu.';
        }
    }

    // 5. Tolak Surat (Staff TU)
    if (isset($_POST['tolak_surat']) && $isStaffTU) {
        $suratId = (int)$_POST['surat_id'];
        $sql = "UPDATE surat_keluar SET status = 'ditolak' WHERE id = $suratId";
        if (mysqli_query($koneksi, $sql)) {
            $_SESSION['sk_success'] = 'Pengajuan surat telah ditolak.';
            echo "<script>window.location.href='?pg=$currentPg';</script>";
            exit;
        }
    }
}

// PROSES GET (Set Status Diproses & Hapus)
if (isset($_GET['set_proses']) && $isStaffTU) {
    $sId = (int)$_GET['set_proses'];
    if ($sId > 0) {
        mysqli_query($koneksi, "UPDATE surat_keluar SET status = 'diproses' WHERE id = $sId AND status = 'diajukan'");
        $_SESSION['sk_success'] = 'Status surat berhasil diubah menjadi Diproses! Silakan klik Edit Form untuk melengkapi isi surat.';
    }
    echo "<script>window.location.href='?pg=$currentPg';</script>";
    exit;
}

if (isset($_GET['hapus_kategori']) && $isAdmin) {
    $hapusId = (int)$_GET['hapus_kategori'];
    $cek = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM surat_keluar WHERE kategori_id = $hapusId");
    $cnt = ($cek && $r = mysqli_fetch_assoc($cek)) ? (int)$r['cnt'] : 0;
    if ($cnt > 0) {
        $_SESSION['sk_errors'] = ["Kategori tidak bisa dihapus karena masih digunakan oleh $cnt surat keluar."];
    } else {
        mysqli_query($koneksi, "DELETE FROM surat_keluar_kategori WHERE id = $hapusId");
        $_SESSION['sk_success'] = "Kategori berhasil dihapus.";
    }
    echo "<script>window.location.href='?pg=$currentPg';</script>";
    exit;
}

if (isset($_GET['hapus_surat'])) {
    $hapusId = (int)$_GET['hapus_surat'];
    if ($isPengaju && !$isAdmin && !$isStaffTU) {
        mysqli_query($koneksi, "DELETE FROM surat_keluar WHERE id = $hapusId AND user_id = $userId AND status = 'diajukan'");
    } else {
        mysqli_query($koneksi, "DELETE FROM surat_keluar WHERE id = $hapusId");
    }
    $_SESSION['sk_success'] = "Surat berhasil dihapus.";
    echo "<script>window.location.href='?pg=$currentPg';</script>";
    exit;
}

// AMBIL DATA UNTUK TAMPILAN
// Filter unit sekolah
$userLevelSk = strtolower(trim((string)($user['level'] ?? $_SESSION['user']['level'] ?? 'admin')));
$userRawUnitSk = trim((string)($user['unit_sekolah'] ?? ''));
$isYayasanSk = in_array($userLevelSk, ['yayasan', 'ketua_yayasan', 'pembina_yayasan', 'keuangan'], true);
$seeAllSchoolsSk = ($userRawUnitSk === '' || $userRawUnitSk === 'wira_buana' || $isYayasanSk);

$selectedUnitSk = trim((string)($_GET['unit_sekolah'] ?? ''));
if (!$seeAllSchoolsSk && !empty($userRawUnitSk)) {
    $activeUnitSk = $userRawUnitSk;
} else {
    $activeUnitSk = $selectedUnitSk;
}

// Filter Kategori berdasarkan unit sekolah
$katWhere = [];
if (!empty($activeUnitSk)) {
    $escUnitKat = mysqli_real_escape_string($koneksi, $activeUnitSk);
    $katWhere[] = "(unit_sekolah = '$escUnitKat' OR unit_sekolah IS NULL OR unit_sekolah = '')";
}
$katWhereSql = !empty($katWhere) ? 'WHERE ' . implode(' AND ', $katWhere) : '';

$kategoriList = [];
$katQuery = "SELECT * FROM surat_keluar_kategori $katWhereSql ORDER BY nama ASC";
$katRes = mysqli_query($koneksi, $katQuery);
if ($katRes) {
    while ($r = mysqli_fetch_assoc($katRes)) {
        $kategoriList[] = $r;
    }
}

$skStatus = strtolower(trim($_GET['status'] ?? ''));
$skKategoriId = (int)($_GET['kategori'] ?? 0);
$skQ = trim($_GET['q'] ?? '');

$where = [];
if (!empty($activeUnitSk)) {
    $escUnit = mysqli_real_escape_string($koneksi, $activeUnitSk);
    $where[] = "(sk.unit_sekolah = '$escUnit' OR u.unit_sekolah = '$escUnit')";
}
if ($isPengaju && !$isAdmin && !$isStaffTU && !$isKepsek) {
    $where[] = "sk.user_id = $userId";
}
if (!empty($skStatus)) {
    $where[] = "sk.status = '" . mysqli_real_escape_string($koneksi, $skStatus) . "'";
}
if ($skKategoriId > 0) {
    $where[] = "sk.kategori_id = $skKategoriId";
}
if (!empty($skQ)) {
    $escQ = mysqli_real_escape_string($koneksi, $skQ);
    $where[] = "(sk.nomor_surat LIKE '%$escQ%' OR sk.perihal LIKE '%$escQ%' OR u.nama LIKE '%$escQ%' OR k.nama LIKE '%$escQ%')";
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$suratList = [];
$sqlList = "SELECT sk.*, k.nama as kategori_nama, k.kode as kategori_kode, k.template_isi as kategori_template_isi, u.nama as user_nama 
            FROM surat_keluar sk 
            LEFT JOIN surat_keluar_kategori k ON sk.kategori_id = k.id 
            LEFT JOIN users u ON sk.user_id = u.id_user 
            $whereSql 
            ORDER BY sk.tanggal_pengajuan DESC, sk.id DESC";

$resList = mysqli_query($koneksi, $sqlList);
if ($resList) {
    while ($r = mysqli_fetch_assoc($resList)) {
        if (empty($r['nomor_surat'])) {
            $r['auto_nomor_surat'] = generateNomorSurat($koneksi, $r['kategori_id'], $r['unit_sekolah']);
        } else {
            $r['auto_nomor_surat'] = $r['nomor_surat'];
        }
        $suratList[] = $r;
    }
}

// Edit Kategori data jika ada
$editKategori = null;
if (isset($_GET['edit_kategori']) && $isAdmin) {
    $eid = (int)$_GET['edit_kategori'];
    $qE = mysqli_query($koneksi, "SELECT * FROM surat_keluar_kategori WHERE id = $eid LIMIT 1");
    if ($qE && ($rE = mysqli_fetch_assoc($qE))) {
        $editKategori = $rE;
    }
}

// Ambil data setting sekolah
$settingKepsek = $setting['kepsek'] ?? 'Kepala Sekolah';

?>

<style>
    .sk-container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 20px; font-family: system-ui, -apple-system, sans-serif; }
    .sk-header { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: white; padding: 24px 30px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 4px 20px rgba(79,70,229,0.2); }
    .sk-header h1 { margin: 0 0 6px 0; font-size: 24px; font-weight: 700; }
    .sk-header p { margin: 0; opacity: 0.9; font-size: 14px; }
    .sk-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px; margin-bottom: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
    .sk-title { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; }
    .sk-table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 14px; }
    .sk-table th, .sk-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #f1f5f9; }
    .sk-table th { background: #f8fafc; font-weight: 700; color: #334155; }
    .sk-status-badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block; }
    .sk-status-diajukan { background: #fef3c7; color: #92400e; }
    .sk-status-diproses { background: #dbeafe; color: #1e40af; }
    .sk-status-selesai { background: #d1e7dd; color: #0f5132; }
    .sk-status-ditolak { background: #fee2e2; color: #991b1b; }
    .btn-action { padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
    .btn-primary { background: #4f46e5; color: white; }
    .btn-primary:hover { background: #4338ca; }
    .btn-success { background: #10b981; color: white; }
    .btn-success:hover { background: #059669; }
    .btn-danger { background: #ef4444; color: white; }
    .btn-secondary { background: #64748b; color: white; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 6px; color: #334155; font-size: 14px; }
    .form-control { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
    
    /* Modal Styling */
    .sk-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); z-index: 9999; justify-content: center; align-items: center; overflow-y: auto; padding: 20px; }
    .sk-modal.active { display: flex; }
    .sk-modal-content { background: white; width: 100%; max-width: 700px; border-radius: 16px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
    .sk-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; }
    .sk-modal-header h3 { margin: 0; font-size: 20px; color: #1e293b; }
    .sk-modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; }
</style>

<div class="sk-container">

    <!-- HEADER PAGE -->
    <div class="sk-header">
        <h1><i class="material-icons" style="vertical-align:middle;">send</i> Fitur Surat Keluar Otomatis</h1>
        <p>Pengajuan, pemrosesan form terstruktur, dan penerbitan PDF surat keluar dengan Kop Surat resmi.</p>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (!empty($successMessage)): ?>
        <div style="background:#d1e7dd; border:1px solid #badbcc; color:#0f5132; padding:14px 18px; border-radius:10px; margin-bottom:20px; font-weight:600;">
            ✓ <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:14px 18px; border-radius:10px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:20px;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- MODAL PROSES SURAT (STAFF TU / ADMIN) -->
    <div id="prosesModal" class="sk-modal">
        <div class="sk-modal-content">
            <div class="sk-modal-header">
                <h3 id="modalProsesTitle">Form Pemrosesan & Penerbitan Surat Keluar</h3>
                <button type="button" class="sk-modal-close" onclick="closeProsesModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="surat_id" id="proc_surat_id">
                
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>Nomor Surat Resmi (Otomatis)</label>
                        <input type="text" name="nomor_surat" id="proc_nomor_surat" class="form-control" readonly style="background:#f1f5f9; cursor:not-allowed; font-weight:700; color:#1e293b;" required title="Nomor Surat resmi di-generate otomatis oleh sistem dan tidak dapat diubah manual.">
                    </div>
                    <div class="form-group">
                        <label>Kota / Kabupaten Surat</label>
                        <input type="text" name="kota_tanggal" id="proc_kota_tanggal" class="form-control" value="Kab. Bogor" required placeholder="Contoh: Kab. Bogor / Bojong Gede">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Surat Keluar</label>
                        <input type="date" name="tanggal_keluar" id="proc_tanggal_keluar" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label>Tujuan / Kepada Yth. (Bisa Multi-baris)</label>
                        <textarea name="tujuan_surat" id="proc_tujuan_surat" class="form-control" rows="3" required placeholder="Contoh:&#10;Bapak/Ibu Orang Tua&#10;Siswa Kelas X, XI & XII&#10;Di Tempat"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Lampiran</label>
                        <input type="text" name="lampiran" id="proc_lampiran" class="form-control" value="-" placeholder="-">
                    </div>
                </div>

                <div class="form-group">
                    <label>Perihal Surat</label>
                    <input type="text" name="perihal" id="proc_perihal" class="form-control" required placeholder="Perihal surat">
                </div>

                <div class="form-group">
                    <label>Draf / Isi Teks & Tabel Surat Keluar (Rich Text & Table Editor)</label>
                    <textarea name="isi_surat" id="proc_isi_surat" class="form-control" rows="10" placeholder="Tuliskan isi paragraf / tabel surat di sini..."></textarea>
                    <small style="color:#64748b; margin-top:4px; display:block;">Gunakan toolbar di atas untuk menambah tabel (seperti tabel Raport/Kegiatan), cetak tebal, list poin, dll.</small>
                </div>

                <div class="form-group">
                    <label>Tembusan Surat (Opsional, 1 baris per tembusan)</label>
                    <textarea name="tembusan" id="proc_tembusan" class="form-control" rows="3" placeholder="Contoh:&#10;- Pengurus Yayasan&#10;- Kantin & Security&#10;- Arsip"></textarea>
                </div>

                <div style="background:#f8fafc; padding:16px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:16px;">
                    <label style="font-weight:700; color:#1e293b; margin-bottom:10px; display:block;">Info Penandatangan Surat</label>
                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                        <div class="form-group" style="margin:0;">
                            <label>Nama Penandatangan</label>
                            <input type="text" name="penandatangan_nama" id="proc_penandatangan_nama" class="form-control" value="<?= htmlspecialchars($settingKepsek) ?>" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Jabatan Penandatangan</label>
                            <input type="text" name="penandatangan_jabatan" id="proc_penandatangan_jabatan" class="form-control" value="Kepala Sekolah" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>NIP (Opsional)</label>
                            <input type="text" name="penandatangan_nip" id="proc_penandatangan_nip" class="form-control" placeholder="Contoh: 19800101 200501 1 001">
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn-action btn-secondary" onclick="closeProsesModal()">Batal</button>
                    <button type="submit" name="simpan_proses_surat" class="btn-action btn-success">
                        🖨️ Simpan & Terbitkan PDF Surat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN SECTION ACCORDING TO ROLE -->
    <?php if ($isAdmin): ?>
        <!-- ADMIN MASTER KOP SURAT UPLOAD CARD -->
        <div class="sk-card" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid #bfdbfe; margin-bottom: 24px;">
            <h3 class="sk-title" style="color: #1e40af;"><i class="material-icons">image</i> Upload Gambar Kop Surat Resmi Sekolah</h3>
            <p style="font-size: 13px; color: #475569; margin-top: -8px; margin-bottom: 16px;">
                Unggah file gambar Kop Surat resmi sekolah Anda (PNG/JPG resolusi tinggi) di sini. Begitu diunggah, seluruh cetakan surat keluar akan <strong>otomatis menggunakan Gambar Kop Surat resmi sekolah Anda</strong> menggantikan teks header bawaan sistem.
            </p>
            <form method="POST" enctype="multipart/form-data" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 280px; margin: 0;">
                    <label>Pilih File Gambar Kop Surat Resmi (PNG / JPG)</label>
                    <input type="file" name="master_gambar_kop" class="form-control" accept="image/png, image/jpeg" required>
                </div>
                <button type="submit" name="simpan_master_kop" class="btn-action btn-primary" style="padding: 10px 22px;">
                    📤 Simpan & Terapkan Kop Surat Resmi
                </button>
            </form>
            <?php 
            $qCurrentKop = mysqli_query($koneksi, "SELECT gambar_kop FROM surat_keluar_kategori WHERE gambar_kop IS NOT NULL AND gambar_kop != '' LIMIT 1");
            $currKop = ($qCurrentKop && ($rKop = mysqli_fetch_assoc($qCurrentKop))) ? $rKop['gambar_kop'] : '';
            if (!empty($currKop) && file_exists(dirname(__FILE__) . '/uploads/kop_surat/' . $currKop)):
            ?>
                <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #cbd5e1;">
                    <span style="font-size: 12px; color: #10b981; font-weight: 700;">✓ Gambar Kop Surat Resmi Aktif Saat Ini:</span><br>
                    <img src="surat_keluar/uploads/kop_surat/<?= htmlspecialchars($currKop) ?>" style="max-height: 90px; max-width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; margin-top: 6px; background: white; padding: 4px;">
                </div>
            <?php else: ?>
                <div style="margin-top: 10px;">
                    <span style="font-size: 12px; color: #eab308; font-weight: 600;">⚠️ Belum ada Gambar Kop Surat diunggah. Cetakan surat saat ini masih menggunakan format Teks Kop sementara.</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- ADMIN VIEW: KELOLA KATEGORI & DRAF TEMPLATE -->
        <div class="sk-card">
            <h3 class="sk-title"><i class="material-icons">folder_special</i> Kelola Kategori Surat & Draf Template Kosongan</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="kategori_id" value="<?= htmlspecialchars($editKategori['id'] ?? '') ?>">
                <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label>Nama Kategori Surat</label>
                        <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($editKategori['nama'] ?? '') ?>" required placeholder="Contoh: Surat Tugas / Surat Keterangan / Surat Undangan">
                    </div>
                    <div class="form-group">
                        <label>Kode Kategori (untuk Nomor Surat)</label>
                        <input type="text" name="kode_kategori" class="form-control" value="<?= htmlspecialchars($editKategori['kode'] ?? '') ?>" required placeholder="Contoh: SK-TUGAS / SK-GURU">
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi Kategori</label>
                    <input type="text" name="deskripsi_kategori" class="form-control" value="<?= htmlspecialchars($editKategori['deskripsi'] ?? '') ?>" placeholder="Deskripsi singkat jenis surat">
                </div>

                <div class="form-group">
                    <label>Draf Teks & Tabel Template Kosongan (Rich Text & Table Editor)</label>
                    <textarea name="template_isi" id="admin_template_isi" class="form-control"><?= htmlspecialchars($editKategori['template_isi'] ?? '') ?></textarea>
                    <small style="color:#64748b; margin-top:4px; display:block;">Gunakan editor di atas untuk membuat draf teks dan tabel kosongan. Draf ini akan otomatis terisi saat Staff TU memproses surat jenis ini.</small>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="simpan_kategori" class="btn-action btn-primary">
                        <?= $editKategori ? 'Update Kategori' : 'Tambah Kategori Surat' ?>
                    </button>
                    <?php if ($editKategori): ?>
                        <a href="?pg=<?= $currentPg ?>" class="btn-action btn-secondary">Batal Edit</a>
                    <?php endif; ?>
                </div>
            </form>

            <h4 style="margin-top:28px; font-weight:700; color:#1e293b;">Daftar Kategori Surat Keluar</h4>
            <table class="sk-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th>Draf Template Teks & Tabel</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kategoriList)): ?>
                        <tr><td colspan="5" style="text-align:center; color:#64748b;">Belum ada kategori surat.</td></tr>
                    <?php else: ?>
                        <?php $no=1; foreach ($kategoriList as $kat): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span style="background:#e0e7ff; color:#3730a3; padding:2px 8px; border-radius:6px; font-weight:700; font-size:12px;"><?= htmlspecialchars($kat['kode']) ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($kat['nama']) ?></strong><br>
                                    <small style="color:#64748b;"><?= htmlspecialchars($kat['deskripsi'] ?: '-') ?></small>
                                </td>
                                <td>
                                    <?= !empty($kat['template_isi']) ? '<span style="color:#10b981; font-weight:600;">✓ Ada Draf Teks & Tabel</span>' : '<span style="color:#94a3b8;">Kosong</span>' ?>
                                </td>
                                <td style="text-align:center;">
                                    <a href="?pg=<?= $currentPg ?>&edit_kategori=<?= $kat['id'] ?>" class="btn-action btn-primary" style="padding:4px 8px;">Edit</a>
                                    <a href="?pg=<?= $currentPg ?>&hapus_kategori=<?= $kat['id'] ?>" class="btn-action btn-danger" style="padding:4px 8px;" onclick="return confirm('Yakin hapus kategori ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($isPengaju): ?>
        <!-- PENGAJU VIEW: FORM PENGAJUAN SURAT BARU -->
        <div class="sk-card">
            <h3 class="sk-title"><i class="material-icons">note_add</i> Form Pengajuan Surat Keluar Baru</h3>
            <form method="POST">
                <div style="display:grid; grid-template-columns: 1fr 2fr; gap:14px;">
                    <div class="form-group">
                        <label>Pilih Kategori Surat</label>
                        <select name="kategori_id" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategoriList as $kat): ?>
                                <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?> (<?= htmlspecialchars($kat['kode']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tujuan / Kepada Yth.</label>
                        <input type="text" name="tujuan_surat" class="form-control" required placeholder="Contoh: Bapak/Ibu Wali Siswa / Kepala Dinas Pendidikan">
                    </div>
                </div>
                <div class="form-group">
                    <label>Perihal Surat</label>
                    <input type="text" name="perihal" class="form-control" required placeholder="Perihal pengajuan surat keluar">
                </div>
                <div class="form-group">
                    <label>Catatan Tambahan untuk Staff TU (Opsional)</label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan tambahan bagi staff..."></textarea>
                </div>
                <button type="submit" name="simpan_surat" class="btn-action btn-primary" style="padding:10px 20px;">
                    🚀 Ajukan Surat Keluar
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- DAFTAR SURAT KELUAR -->
    <?php if (!$isAdmin): ?>
    <div class="sk-card">
        <h3 class="sk-title"><i class="material-icons">list_alt</i> Daftar & Status Surat Keluar</h3>
        
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
            <input type="hidden" name="pg" value="<?= htmlspecialchars($currentPg) ?>">
            <input type="text" name="q" class="form-control" style="width:250px;" value="<?= htmlspecialchars($skQ) ?>" placeholder="Cari perihal / nomor / pengaju...">
            <select name="status" class="form-control" style="width:160px;">
                <option value="">Semua Status</option>
                <option value="diajukan" <?= $skStatus === 'diajukan' ? 'selected' : '' ?>>Diajukan</option>
                <option value="diproses" <?= $skStatus === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                <option value="selesai" <?= $skStatus === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="ditolak" <?= $skStatus === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
            </select>
            <button type="submit" class="btn-action btn-primary">Filter</button>
            <a href="?pg=<?= urlencode($currentPg) ?>" class="btn-action btn-secondary">Reset</a>
        </form>

        <table class="sk-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Nomor Surat</th>
                    <th>Pengaju</th>
                    <th>Kategori</th>
                    <th>Tujuan & Perihal</th>
                    <th>Status</th>
                    <th style="text-align:center;">Aksi / Dokumen</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suratList)): ?>
                    <tr><td colspan="8" style="text-align:center; color:#64748b; padding:24px;">Belum ada surat keluar.</td></tr>
                <?php else: ?>
                    <?php $no=1; foreach ($suratList as $s): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($s['tanggal_pengajuan'])) ?></td>
                            <td><strong><?= htmlspecialchars($s['nomor_surat'] ?: 'Belum dibuat') ?></strong></td>
                            <td><?= htmlspecialchars($s['user_nama']) ?></td>
                            <td><span style="background:#f1f5f9; padding:2px 8px; border-radius:6px; font-weight:600; font-size:12px;"><?= htmlspecialchars($s['kategori_nama']) ?></span></td>
                            <td>
                <strong>Kepada:</strong> <?= htmlspecialchars($s['tujuan_surat'] ?: '-') ?><br>
                                <small style="color:#64748b;">Perihal: <?= htmlspecialchars($s['perihal']) ?></small>
                            </td>
                            <td><?= skStatusBadge($s['status']) ?></td>
                            <td style="text-align:center;">
                                <?php if ($isStaffTU): ?>
                                    <?php if ($s['status'] === 'diajukan'): ?>
                                        <div style="display:flex; gap:6px; justify-content:center;">
                                            <a href="?pg=<?= urlencode($currentPg) ?>&set_proses=<?= $s['id'] ?>" class="btn-action btn-primary" style="padding:6px 14px;">
                                                ⚙️ Proses
                                            </a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Tolak surat ini?');">
                                                <input type="hidden" name="surat_id" value="<?= $s['id'] ?>">
                                                <button type="submit" name="tolak_surat" class="btn-action btn-danger" style="padding:6px 14px;">Tolak</button>
                                            </form>
                                        </div>
                                    <?php elseif ($s['status'] === 'ditolak'): ?>
                                        <span style="color:#ef4444; font-weight:600; font-size:13px;">❌ Surat Ditolak</span>
                                    <?php else: ?>
                                        <div style="display:flex; flex-direction:column; gap:6px; align-items:center;">
                                            <div style="display:flex; gap:4px; flex-wrap:wrap; justify-content:center;">
                                                <button type="button" class="btn-action btn-primary" style="padding:4px 8px; font-size:12px;" onclick='openProsesModal(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                    ✏️ Edit Form
                                                </button>
                                                <a href="surat_keluar/skeluar_print.php?id=<?= $s['id'] ?>" target="_blank" class="btn-action btn-success" style="padding:4px 8px; font-size:12px;">
                                                    🖨️ Cetak Draf PDF
                                                </a>
                                            </div>
                                            
                                            <!-- DIRECT SIMPLE PDF UPLOAD FORM -->
                                            <div style="margin-top:6px; padding:8px 10px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; width:100%; text-align:center; box-sizing:border-box;">
                                                <?php if (!empty($s['file_ttd']) && file_exists(dirname(__FILE__) . '/uploads/surat_ttd/' . $s['file_ttd'])): ?>
                                                    <div style="margin-bottom:6px;">
                                                        <span style="color:#059669; font-weight:700; font-size:11px; display:block;">✓ PDF TTD Terunggah</span>
                                                        <a href="surat_keluar/uploads/surat_ttd/<?= htmlspecialchars($s['file_ttd']) ?>" target="_blank" class="btn-action btn-success" style="background:#059669; padding:3px 8px; font-size:11px; margin-top:2px;">
                                                            👁️ Lihat PDF TTD
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:4px;">Upload File PDF TTD:</span>
                                                <?php endif; ?>
                                                
                                                <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:4px; align-items:center;">
                                                    <input type="hidden" name="surat_id" value="<?= $s['id'] ?>">
                                                    <input type="file" name="file_ttd" accept="application/pdf" required style="font-size:11px; max-width:180px;">
                                                    <button type="submit" name="upload_file_ttd" class="btn-action btn-primary" style="background:#7c3aed; padding:4px 10px; font-size:11px; margin-top:2px;">
                                                        📤 <?= !empty($s['file_ttd']) ? 'Ganti File PDF TTD' : 'Upload PDF TTD' ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- UNTUK PENGAJU / NON-STAFF TU -->
                                    <?php if ($s['status'] === 'ditolak'): ?>
                                        <span style="color:#ef4444; font-weight:600; font-size:13px;">❌ Surat Ditolak</span>
                                    <?php elseif (!empty($s['file_ttd']) && file_exists(dirname(__FILE__) . '/uploads/surat_ttd/' . $s['file_ttd'])): ?>
                                        <a href="surat_keluar/uploads/surat_ttd/<?= htmlspecialchars($s['file_ttd']) ?>" target="_blank" class="btn-action btn-success" style="padding:6px 14px;">
                                            📥 Download Surat TTD
                                        </a>
                                    <?php elseif ($s['status'] === 'diajukan' && $s['user_id'] == $userId): ?>
                                        <a href="?pg=<?= $currentPg ?>&hapus_surat=<?= $s['id'] ?>" class="btn-action btn-danger" onclick="return confirm('Batal & hapus pengajuan ini?');">
                                            Batal
                                        </a>
                                    <?php else: ?>
                                        <span style="background:#fef3c7; color:#b45309; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600;">⏳ Menunggu Scan TTD</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<script>
function openProsesModal(surat) {
    document.getElementById('proc_surat_id').value = surat.id || '';
    
    // Nomor surat (Otomatis & Readonly)
    if (surat.nomor_surat && surat.nomor_surat !== '') {
        document.getElementById('proc_nomor_surat').value = surat.nomor_surat;
    } else if (surat.auto_nomor_surat && surat.auto_nomor_surat !== '') {
        document.getElementById('proc_nomor_surat').value = surat.auto_nomor_surat;
    } else {
        document.getElementById('proc_nomor_surat').value = '001/' + (surat.kategori_kode || 'SK') + '/' + getBulanRomawi() + '/' + new Date().getFullYear();
    }
    
    // Tanggal keluar (Tidak boleh backdate)
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('proc_tanggal_keluar').min = today;
    if (!surat.tanggal_keluar || surat.tanggal_keluar < today) {
        document.getElementById('proc_tanggal_keluar').value = today;
    } else {
        document.getElementById('proc_tanggal_keluar').value = surat.tanggal_keluar;
    }
    
    document.getElementById('proc_kota_tanggal').value = surat.kota_tanggal || 'Kab. Bogor';
    document.getElementById('proc_tujuan_surat').value = surat.tujuan_surat || "Bapak/Ibu Orang Tua\nSiswa Kelas X, XI & XII\nDi Tempat";
    document.getElementById('proc_lampiran').value = surat.lampiran || '-';
    document.getElementById('proc_perihal').value = surat.perihal || '';
    document.getElementById('proc_tembusan').value = surat.tembusan || "- Pengurus Yayasan\n- Kantin & Security\n- Arsip";
    
    // Isi surat: jika kosong, ambil template dari kategori
    var defaultContent = '';
    if (surat.isi_surat && surat.isi_surat.trim() !== '') {
        defaultContent = surat.isi_surat;
    } else if (surat.kategori_template_isi && surat.kategori_template_isi.trim() !== '') {
        defaultContent = surat.kategori_template_isi;
    } else {
        defaultContent = '<p class="no-indent"><strong>Assalamualaikum Warahmatullahi Wabarakatuh</strong></p>' +
            '<p>Teriring salam dan do’a semoga Bapak/Ibu selalu dalam lindungan Allah SWT, sehat wal’afiat serta selalu sukses dalam menjalankan tugas sehari-hari. Aamiin.</p>' +
            '<p>Sesuai kalender pendidikan Tahun Pelajaran 2025/2026 dan selesainya kegiatan Sumatif Tengah Semester Genap dan PSAJ (Penilaian Sumatif Akhir Jenjang) di SMA Negara Contoh 2, maka dengan ini Kami memberitahukan perihal Pengambilan Rapor STS Genap dan Libur Lebaran:</p>' +
            '<table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; margin:15px 0;">' +
            '<thead><tr style="background:#f8fafc;">' +
            '<th style="text-align:center; width:50px;">No</th>' +
            '<th style="text-align:center;">Tanggal</th>' +
            '<th style="text-align:center;">Kegiatan</th>' +
            '<th style="text-align:center;">Kelas</th>' +
            '</tr></thead>' +
            '<tbody>' +
            '<tr><td style="text-align:center;">1</td><td>04 April 26</td><td>Ambil Raport STS Genap</td><td>X & XI Semua Jurusan</td></tr>' +
            '<tr><td style="text-align:center;">2</td><td>16 s.d 28 Maret 26</td><td>Libur Idul Fitri (Lebaran)</td><td>X, XI & XII Semua Jurusan</td></tr>' +
            '<tr><td style="text-align:center;">3</td><td>30 Maret 26</td><td>Masuk Sekolah</td><td>X, XI & XII Semua Jurusan</td></tr>' +
            '</tbody></table>' +
            '<p>Demikian surat pemberitahuan ini. Atas perhatian dan kerja sama yang baik dari bapak/ibu orang tua/wali, kami sampaikan terima kasih.</p>';
    }
    
    // Set Summernote content
    if (typeof $ !== 'undefined' && $.fn.summernote) {
        if ($('#proc_isi_surat').next('.note-editor').length) {
            $('#proc_isi_surat').summernote('code', defaultContent);
        } else {
            $('#proc_isi_surat').summernote({
                height: 250,
                toolbar: [
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph', 'align']],
                    ['table', ['table']],
                    ['insert', ['link', 'hr']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
            $('#proc_isi_surat').summernote('code', defaultContent);
        }
    } else {
        document.getElementById('proc_isi_surat').value = defaultContent;
    }
    
    document.getElementById('proc_penandatangan_nama').value = surat.penandatangan_nama || <?= json_encode($settingKepsek) ?>;
    document.getElementById('proc_penandatangan_jabatan').value = surat.penandatangan_jabatan || 'Kepala SMA Negara Contoh 2';
    document.getElementById('proc_penandatangan_nip').value = surat.penandatangan_nip || '';
    
    document.getElementById('prosesModal').classList.add('active');
}

function closeProsesModal() {
    document.getElementById('prosesModal').classList.remove('active');
}

function getBulanRomawi() {
    var bulan = new Date().getMonth() + 1;
    var romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    return romawi[bulan];
}

$(document).ready(function() {
    if ($('#admin_template_isi').length && $.fn.summernote) {
        $('#admin_template_isi').summernote({
            height: 220,
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph', 'align']],
                ['table', ['table']],
                ['insert', ['link', 'hr']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });
    }
});
</script>