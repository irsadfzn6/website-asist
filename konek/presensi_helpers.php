<?php
/**
 * HELPER FUNCTIONS UNTUK SISTEM PRESENSI
 * File: konek/presensi_helpers.php
 * 
 * Fungsi-fungsi utility untuk menangani logika presensi,
 * terutama untuk cek surat tugas aktif
 */

function presensiTableExists($koneksi, $table)
{
    $table = mysqli_real_escape_string($koneksi, $table);
    $result = mysqli_query($koneksi, "SHOW TABLES LIKE '{$table}'");
    return $result && mysqli_num_rows($result) > 0;
}

function ensureSuratTugasPengajuanUserColumn($koneksi)
{
    if (!presensiTableExists($koneksi, 'surat_tugas_pengajuan')) {
        return 'id_user';
    }

    $colUser = mysqli_query($koneksi, "SHOW COLUMNS FROM surat_tugas_pengajuan LIKE 'id_user'");
    if ($colUser && mysqli_num_rows($colUser) > 0) {
        $column = 'id_user';
    } else {
        $colPeg = mysqli_query($koneksi, "SHOW COLUMNS FROM surat_tugas_pengajuan LIKE 'id_user_peg'");
        if ($colPeg && mysqli_num_rows($colPeg) > 0) {
            mysqli_query($koneksi, "ALTER TABLE surat_tugas_pengajuan CHANGE COLUMN id_user_peg id_user INT(11) NULL AFTER id_siswa");
            $column = 'id_user';
        } else {
            mysqli_query($koneksi, "ALTER TABLE surat_tugas_pengajuan ADD COLUMN id_user INT(11) NULL AFTER id_siswa");
            $column = 'id_user';
        }
    }

    $colLevel = mysqli_query($koneksi, "SHOW COLUMNS FROM surat_tugas_pengajuan LIKE 'level_pemohon'");
    if (!$colLevel || mysqli_num_rows($colLevel) === 0) {
        mysqli_query($koneksi, "ALTER TABLE surat_tugas_pengajuan ADD COLUMN level_pemohon VARCHAR(30) NULL AFTER id_user");
    }

    $colOldPeg = mysqli_query($koneksi, "SHOW COLUMNS FROM surat_tugas_pengajuan LIKE 'id_pegawai'");
    if ($colOldPeg && mysqli_num_rows($colOldPeg) > 0) {
        mysqli_query($koneksi, "ALTER TABLE surat_tugas_pengajuan DROP COLUMN id_pegawai");
    }

    return $column;
}

/**
 * Cek apakah user memiliki surat tugas yang sudah diapprove
 * (User upload dari presensi dan di-approve admin presensi)
 * 
 * @param string $id_siswa - ID siswa
 * @param string|null $tanggal - Tanggal check (default: hari ini)
 * @return array|null - Data surat jika aktif, NULL jika tidak ada
 */
function getSuratTugasPengajuanAktif($koneksi, $id_siswa, $tanggal = null)
{
    if (!$tanggal)
        $tanggal = date('Y-m-d');

    if (!presensiTableExists($koneksi, 'surat_tugas_pengajuan')) {
        return null;
    }

    // Query dari table surat_tugas_pengajuan (user upload, admin presensi approve)
    $stmt = $koneksi->prepare("
        SELECT 
            id, 
            nomor_surat,
            tanggal_berlaku,
            durasi_hari,
            lokasi_kegiatan,
            ket_kegiatan,
            file_surat,
            status_approval
        FROM surat_tugas_pengajuan
        WHERE id_siswa = ? 
        AND status_approval = 'approved'
        AND tanggal_berlaku IS NOT NULL 
        AND ? >= tanggal_berlaku 
        AND ? <= DATE_ADD(tanggal_berlaku, INTERVAL (durasi_hari - 1) DAY)
        ORDER BY tanggal_berlaku DESC
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('sss', $id_siswa, $tanggal, $tanggal);
    $stmt->execute();
    $result = $stmt->get_result();
    $surat = $result->fetch_assoc();
    $stmt->close();

    return $surat;
}

function getSuratTugasPengajuanTerbaru($koneksi, $id_siswa)
{
    if (!presensiTableExists($koneksi, 'surat_tugas_pengajuan')) {
        return null;
    }

    $stmt = $koneksi->prepare("
        SELECT
            id,
            nomor_surat,
            tanggal_berlaku,
            durasi_hari,
            lokasi_kegiatan,
            ket_kegiatan,
            file_surat,
            status_approval,
            notes_approval,
            created_at,
            approved_at,
            DATE_ADD(tanggal_berlaku, INTERVAL (durasi_hari - 1) DAY) AS tanggal_selesai
        FROM surat_tugas_pengajuan
        WHERE id_siswa = ?
        ORDER BY created_at DESC, id DESC
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $id_siswa);
    $stmt->execute();
    $result = $stmt->get_result();
    $surat = $result->fetch_assoc();
    $stmt->close();

    return $surat;
}

function getSuratTugasPengajuanPegawaiAktif($koneksi, $id_user, $tanggal = null)
{
    if (!$tanggal) {
        $tanggal = date('Y-m-d');
    }

    if (!presensiTableExists($koneksi, 'surat_tugas_pengajuan')) {
        return null;
    }

    ensureSuratTugasPengajuanUserColumn($koneksi);

    $stmt = $koneksi->prepare("
        SELECT *,
            DATE_ADD(tanggal_berlaku, INTERVAL (durasi_hari - 1) DAY) AS tanggal_selesai
        FROM surat_tugas_pengajuan
        WHERE id_user = ?
        AND status_approval = 'approved'
        AND tanggal_berlaku IS NOT NULL
        AND durasi_hari IS NOT NULL AND durasi_hari > 0
        AND ? >= tanggal_berlaku
        AND ? <= DATE_ADD(tanggal_berlaku, INTERVAL (durasi_hari - 1) DAY)
        ORDER BY tanggal_berlaku DESC, id DESC
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('iss', $id_user, $tanggal, $tanggal);
    $stmt->execute();
    $result = $stmt->get_result();
    $surat = $result->fetch_assoc();
    $stmt->close();

    return $surat;
}

function getSuratTugasPengajuanPegawaiTerbaru($koneksi, $id_user)
{
    if (!presensiTableExists($koneksi, 'surat_tugas_pengajuan')) {
        return null;
    }

    ensureSuratTugasPengajuanUserColumn($koneksi);

    $stmt = $koneksi->prepare("
        SELECT *,
            DATE_ADD(tanggal_berlaku, INTERVAL (durasi_hari - 1) DAY) AS tanggal_selesai
        FROM surat_tugas_pengajuan
        WHERE id_user = ?
        ORDER BY created_at DESC, id DESC
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $surat = $result->fetch_assoc();
    $stmt->close();

    return $surat;
}

function isSuratTugasExpired($surat, $tanggal = null)
{
    if (!$surat || ($surat['status_approval'] ?? '') !== 'approved') {
        return false;
    }

    if (!$tanggal) {
        $tanggal = date('Y-m-d');
    }

    $tanggal_berlaku = $surat['tanggal_berlaku'] ?? null;
    $durasi_hari = (int) ($surat['durasi_hari'] ?? 0);
    if (!$tanggal_berlaku || $durasi_hari < 1) {
        return false;
    }

    $tanggal_selesai = $surat['tanggal_selesai'] ?? date('Y-m-d', strtotime($tanggal_berlaku . ' + ' . ($durasi_hari - 1) . ' days'));
    return $tanggal > $tanggal_selesai;
}

/**
 * Cek surat tugas aktif untuk presensi.
 * Sumber valid hanya surat yang diupload siswa dan di-approve admin presensi.
 * 
 * @param string $id_siswa - ID siswa
 * @param string|null $tanggal - Tanggal check (default: hari ini)
 * @return array|null - Data surat aktif (dari source manapun)
 */
function getSuratTugasAktif($koneksi, $id_siswa, $tanggal = null, $kelas = null)
{
    $surat_pengajuan = getSuratTugasPengajuanAktif($koneksi, $id_siswa, $tanggal);
    if ($surat_pengajuan) {
        $surat_pengajuan['source'] = 'pengajuan';
        return $surat_pengajuan;
    }

    // Tidak ada surat aktif
    return null;
}

/**
 * Cek apakah user memiliki pengajuan surat yang pending
 * 
 * @param string $id_siswa - ID siswa
 * @return array|null - Data pengajuan jika ada
 */
function getSuratTugasPengajuanPending($koneksi, $id_siswa)
{
    if (!presensiTableExists($koneksi, 'surat_tugas_pengajuan')) {
        return null;
    }

    $stmt = $koneksi->prepare("
        SELECT 
            id, 
            nomor_surat,
            tanggal_berlaku,
            durasi_hari,
            lokasi_kegiatan,
            ket_kegiatan,
            status_approval,
            created_at
        FROM surat_tugas_pengajuan
        WHERE id_siswa = ? 
        AND status_approval = 'pending'
        ORDER BY created_at DESC
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $id_siswa);
    $stmt->execute();
    $result = $stmt->get_result();
    $surat = $result->fetch_assoc();
    $stmt->close();

    return $surat;
}

/**
 * Cek apakah user memiliki pengajuan surat yang ditolak
 * 
 * @param string $id_siswa - ID siswa
 * @return array|null - Data pengajuan jika ada
 */
function getSuratTugasPengajuanRejected($koneksi, $id_siswa)
{
    if (!presensiTableExists($koneksi, 'surat_tugas_pengajuan')) {
        return null;
    }

    $stmt = $koneksi->prepare("
        SELECT 
            id, 
            nomor_surat,
            tanggal_berlaku,
            durasi_hari,
            lokasi_kegiatan,
            ket_kegiatan,
            status_approval,
            notes_approval,
            created_at
        FROM surat_tugas_pengajuan
        WHERE id_siswa = ? 
        AND status_approval = 'rejected'
        ORDER BY created_at DESC
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $id_siswa);
    $stmt->execute();
    $result = $stmt->get_result();
    $surat = $result->fetch_assoc();
    $stmt->close();

    return $surat;
}

/**
 * Format tanggal berlaku surat untuk display
 * 
 * @param string $tanggal_berlaku - Tanggal mulai berlaku (Y-m-d)
 * @param int $durasi_hari - Durasi dalam hari
 * @return string - Format: "20 Mei 2026 - 22 Mei 2026 (3 hari)"
 */
function formatTanggalBerlakuSurat($tanggal_berlaku, $durasi_hari)
{
    $tanggal_selesai = date('Y-m-d', strtotime($tanggal_berlaku . " + " . ($durasi_hari - 1) . " days"));

    $mulai = date('d M Y', strtotime($tanggal_berlaku));
    $selesai = date('d M Y', strtotime($tanggal_selesai));

    return "{$mulai} - {$selesai} ({$durasi_hari} hari)";
}

function presensiRecordLabel($record)
{
    $source = $record['source'] ?? '';
    $ket = strtoupper((string) ($record['ket'] ?? ''));

    if ($source === 'absen_luar' || $source === 'absen_luar_pegawai') {
        return 'Absen Luar';
    }

    $labels = [
        'H' => 'Hadir',
        'T' => 'Terlambat',
        'I' => 'Izin',
        'S' => 'Sakit',
        'A' => 'Alpha',
    ];

    return $labels[$ket] ?? 'Absensi';
}

function presensiHasSiswaDailyRecord($koneksi, $id_siswa, $tanggal = null)
{
    $id_siswa = (int) $id_siswa;
    if ($id_siswa <= 0) {
        return null;
    }

    if (!$tanggal) {
        $tanggal = date('Y-m-d');
    }
    $tanggal = mysqli_real_escape_string($koneksi, $tanggal);

    if (presensiTableExists($koneksi, 'absensi')) {
        $q = mysqli_query($koneksi, "SELECT id, ket FROM absensi WHERE idsiswa='$id_siswa' AND tanggal='$tanggal' LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            $row['source'] = 'absensi';
            $row['label'] = presensiRecordLabel($row);
            return $row;
        }
    }

    if (presensiTableExists($koneksi, 'absen_luar')) {
        $q = mysqli_query($koneksi, "SELECT id FROM absen_luar WHERE id_siswa='$id_siswa' AND tanggal='$tanggal' LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            $row['source'] = 'absen_luar';
            $row['label'] = presensiRecordLabel($row);
            return $row;
        }
    }

    return null;
}

function presensiHasSiswaSuratRequest($koneksi, $id_siswa, $tanggal = null)
{
    $id_siswa = (int) $id_siswa;
    if ($id_siswa <= 0) {
        return null;
    }

    if (!$tanggal) {
        $tanggal = date('Y-m-d');
    }
    $tanggal = mysqli_real_escape_string($koneksi, $tanggal);

    if (presensiTableExists($koneksi, 'surat')) {
        $q = mysqli_query($koneksi, "SELECT id, ket, status FROM surat WHERE idsiswa='$id_siswa' AND tanggal='$tanggal' AND status<>'-1' LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            $row['source'] = 'surat';
            $row['label'] = 'Pengajuan Surat';
            return $row;
        }
    }

    return null;
}

function presensiHasUserDailyRecord($koneksi, $id_user, $tanggal = null)
{
    $id_user = (int) $id_user;
    if ($id_user <= 0) {
        return null;
    }

    if (!$tanggal) {
        $tanggal = date('Y-m-d');
    }
    $tanggal = mysqli_real_escape_string($koneksi, $tanggal);

    if (presensiTableExists($koneksi, 'absensi')) {
        $q = mysqli_query($koneksi, "SELECT id, ket FROM absensi WHERE idpeg='$id_user' AND tanggal='$tanggal' LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            $row['source'] = 'absensi';
            $row['label'] = presensiRecordLabel($row);
            return $row;
        }
    }

    if (presensiTableExists($koneksi, 'absen_luar')) {
        $q = mysqli_query($koneksi, "SELECT id FROM absen_luar WHERE idpeg='$id_user' AND tanggal='$tanggal' LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            $row['source'] = 'absen_luar';
            $row['label'] = presensiRecordLabel($row);
            return $row;
        }
    }

    if (presensiTableExists($koneksi, 'absen_luar_pegawai')) {
        $q = mysqli_query($koneksi, "SELECT id FROM absen_luar_pegawai WHERE id_pegawai='$id_user' AND tanggal='$tanggal' LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            $row['source'] = 'absen_luar_pegawai';
            $row['label'] = presensiRecordLabel($row);
            return $row;
        }
    }

    return null;
}

function presensiHasUserSuratRequest($koneksi, $id_user, $tanggal = null)
{
    $id_user = (int) $id_user;
    if ($id_user <= 0) {
        return null;
    }

    if (!$tanggal) {
        $tanggal = date('Y-m-d');
    }
    $tanggal = mysqli_real_escape_string($koneksi, $tanggal);

    if (presensiTableExists($koneksi, 'surat')) {
        $q = mysqli_query($koneksi, "SELECT id, ket, status FROM surat WHERE idpeg='$id_user' AND tanggal='$tanggal' AND status<>'-1' LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            $row['source'] = 'surat';
            $row['label'] = 'Pengajuan Surat';
            return $row;
        }
    }

    return null;
}

function presensiDailyBlockMessage($record, $subject = 'Anda')
{
    $label = $record['label'] ?? presensiRecordLabel($record);
    return $subject . ' sudah memiliki presensi hari ini (' . $label . '), sehingga tidak bisa melakukan presensi lagi.';
}

function presensiSuratRequestBlockMessage($record, $subject = 'Anda')
{
    $label = $record['label'] ?? 'Pengajuan Surat';
    return $subject . ' sudah memiliki ' . $label . ' hari ini, sehingga tidak bisa melakukan presensi.';
}

?>
