<?php
defined('APK') or define('APK', 'e-SANDIK');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$koneksi = mysqli_connect('localhost', 'root', '', 'db_asist');
if (!$koneksi) {
    die('Koneksi database gagal');
}
mysqli_set_charset($koneksi, 'utf8');

require_once __DIR__ . '/../konek/function.php';

$suratId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($suratId <= 0 && isset($_GET['s'])) {
    $suratId = (int)dekripsi($_GET['s']);
}

if ($suratId <= 0) {
    die('ID Surat tidak valid.');
}

// Fetch surat_keluar data
$sql = "SELECT sk.*, skk.nama as nama_kategori, skk.kode as kode_kategori, skk.gambar_kop as kategori_gambar_kop, u.nama as nama_pengaju 
        FROM surat_keluar sk 
        LEFT JOIN surat_keluar_kategori skk ON sk.kategori_id = skk.id 
        LEFT JOIN users u ON sk.user_id = u.id_user 
        WHERE sk.id = $suratId LIMIT 1";

$res = mysqli_query($koneksi, $sql);
if (!$res || mysqli_num_rows($res) == 0) {
    die('Data surat keluar tidak ditemukan.');
}

$surat = mysqli_fetch_assoc($res);

if (($surat['status'] ?? '') === 'ditolak') {
    die('<div style="font-family:sans-serif; text-align:center; padding:50px; color:#ef4444;"><h2>❌ Surat Ini Telah Ditolak</h2><p>Surat yang telah ditolak tidak dapat diproses atau dicetak.</p></div>');
}

// Master Gambar Kop Surat dari tabel surat_keluar_kategori
$gambarKopFile = $surat['kategori_gambar_kop'] ?? '';

// Fallback: Jika kategori surat ini belum terisi, cari kop surat aktif di tabel surat_keluar_kategori
if (empty($gambarKopFile)) {
    $unitSekolahEsc = mysqli_real_escape_string($koneksi, $surat['unit_sekolah'] ?? '');
    $qKop = mysqli_query($koneksi, "SELECT gambar_kop FROM surat_keluar_kategori WHERE (unit_sekolah = '$unitSekolahEsc' OR '$unitSekolahEsc' = '') AND gambar_kop IS NOT NULL AND gambar_kop != '' ORDER BY id DESC LIMIT 1");
    if ($qKop && ($rKop = mysqli_fetch_assoc($qKop))) {
        $gambarKopFile = $rKop['gambar_kop'];
    }
}

$gambarKopPath = '';
if (!empty($gambarKopFile)) {
    if (file_exists(__DIR__ . '/uploads/kop_surat/' . $gambarKopFile)) {
        $gambarKopPath = 'uploads/kop_surat/' . $gambarKopFile;
    } elseif (file_exists(__DIR__ . '/../images/' . $gambarKopFile)) {
        $gambarKopPath = '../images/' . $gambarKopFile;
    }
}

// Determine unit sekolah settings
$unitKode = $surat['unit_sekolah'] ?? '';
$namaSekolah = $setting['sekolah'] ?? 'SMA NEGARA CONTOH';

if ($unitKode === 'wb_1') {
    $namaSekolah = 'SMA NEGARA CONTOH 1';
} elseif ($unitKode === 'wb_2') {
    $namaSekolah = 'SMA NEGARA CONTOH 2';
} elseif ($unitKode === 'wb_3') {
    $namaSekolah = 'SMA NEGARA CONTOH 3';
} elseif ($unitKode === 'wira_buana' || empty($unitKode)) {
    $namaSekolah = 'YAYASAN NEGARA CONTOH';
}

$tglKeluarRaw = !empty($surat['tanggal_keluar']) ? $surat['tanggal_keluar'] : ($surat['tanggal_pengajuan'] ?? date('Y-m-d'));
$tglKeluarIndo = buat_tanggal('d F Y', $tglKeluarRaw);

$tujuanSurat = !empty($surat['tujuan_surat']) ? $surat['tujuan_surat'] : 'Bapak/Ibu / Saudara/i';
$lampiran = !empty($surat['lampiran']) ? $surat['lampiran'] : '-';
$perihal = !empty($surat['perihal']) ? $surat['perihal'] : '-';
$nomorSurat = !empty($surat['nomor_surat']) ? $surat['nomor_surat'] : '-';

$isiSurat = !empty($surat['isi_surat']) ? $surat['isi_surat'] : ($surat['catatan'] ?? '');

$ttdNama = !empty($surat['penandatangan_nama']) ? $surat['penandatangan_nama'] : ($setting['kepsek'] ?? 'Kepala Sekolah');
$ttdJabatan = !empty($surat['penandatangan_jabatan']) ? $surat['penandatangan_jabatan'] : 'Kepala Sekolah';
$ttdNip = !empty($surat['penandatangan_nip']) ? $surat['penandatangan_nip'] : '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Keluar - <?= htmlspecialchars($nomorSurat) ?></title>
    <style>
        @page {
            size: A4;
            margin: 0mm 0mm 15mm 0mm;
        }
        * {
            box-sizing: border-box;
        }
        body, .page-container, .page-container *, .surat-body, .surat-body * {
            font-family: "Times New Roman", Times, serif !important;
        }
        body {
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        .page-container {
            background: white;
            width: 210mm;
            min-height: 297mm;
            padding: 0 20mm 20mm 20mm;
            margin: 0 auto 20px auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        /* Kop Surat Header Full Flush at Top */
        .kop-header-full {
            margin-left: -20mm;
            margin-right: -20mm;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .kop-header-full img {
            width: 100%;
            display: block;
        }

        .kop-container {
            padding: 10mm 20mm 0 20mm;
            margin-left: -20mm;
            margin-right: -20mm;
            margin-top: 0;
        }
        .kop-surat-grid {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .kop-logo-left {
            width: 85px;
            height: 85px;
            object-fit: contain;
        }
        .kop-logo-right {
            width: 85px;
            height: 85px;
            object-fit: contain;
        }
        .kop-text-center {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }
        .kop-text-center h4 {
            margin: 0;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-text-center h2 {
            margin: 2px 0 3px 0;
            font-size: 21px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .kop-text-center p {
            margin: 1px 0;
            font-size: 11px;
            line-height: 1.3;
        }
        .kop-divider-blue {
            height: 3px;
            background-color: #2563eb;
            margin-top: 8px;
            margin-bottom: 20px;
        }

        /* Date Top Right */
        .date-top-right {
            text-align: right;
            font-size: 14px;
            margin-bottom: 10px;
        }

        /* Surat Info Table */
        .surat-meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 14px;
            line-height: 1.5;
        }
        .surat-meta-table td {
            vertical-align: top;
        }

        /* Recipient */
        .recipient-block {
            margin: 15px 0 20px 0;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Body Content Spacing Optimization */
        .surat-body {
            font-size: 14px;
            line-height: 1.45;
            text-align: justify;
            margin-bottom: 25px;
            min-height: 150px;
        }
        .surat-body p {
            margin-top: 0;
            margin-bottom: 5px;
            text-indent: 30px;
        }
        .surat-body p.no-indent, .surat-body p:first-child {
            text-indent: 0;
        }

        /* Styling for Tables inside Body (e.g. Raport Table) */
        .surat-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .surat-body table, .surat-body th, .surat-body td {
            border: 1px solid #000;
        }
        .surat-body th {
            padding: 8px 10px;
            text-align: center;
            font-weight: bold;
            background-color: #f8fafc;
        }
        .surat-body td {
            padding: 6px 10px;
            font-size: 13px;
        }

        /* Signature Block */
        .ttd-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            font-size: 14px;
            page-break-inside: avoid;
        }
        .ttd-box {
            width: 260px;
            text-align: center;
            position: relative;
        }
        .ttd-space {
            height: 70px;
            position: relative;
        }
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Tembusan Block (Pinned at Absolute Bottom Footer) */
        .tembusan-block {
            position: absolute;
            bottom: 15mm;
            left: 20mm;
            font-size: 11px;
            line-height: 1.4;
            color: #64748b;
            opacity: 0.85;
        }
        .tembusan-block p {
            margin: 0;
            font-weight: 600;
            text-decoration: underline;
            color: #475569;
        }

        /* Action bar for screen */
        .action-bar {
            width: 210mm;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .btn-print {
            background: #2563eb;
            color: white;
            border: none;
            padding: 9px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }
        .btn-back {
            background: #64748b;
            color: white;
            text-decoration: none;
            padding: 9px 18px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 6px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }
            html, body {
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                background: white !important;
            }
            .action-bar {
                display: none !important;
            }
            .page-container {
                box-shadow: none !important;
                width: 210mm !important;
                height: 297mm !important;
                max-height: 297mm !important;
                padding: 0 20mm 15mm 20mm !important;
                margin: 0 !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                page-break-inside: avoid !important;
                position: relative !important;
                overflow: hidden !important;
            }
            .kop-header-full {
                margin-left: -20mm !important;
                margin-right: -20mm !important;
                margin-top: 0 !important;
            }
            .tembusan-block {
                position: absolute !important;
                bottom: 12mm !important;
                left: 20mm !important;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <div>
            <a href="javascript:window.close();" class="btn-back">← Tutup</a>
        </div>
        <div>
            <button onclick="window.print()" class="btn-print">
                🖨️ Cetak PDF / Print Surat
            </button>
        </div>
    </div>

    <div class="page-container">
        <!-- KOP SURAT HEADER (FULL AT TOP) -->
        <?php if (!empty($gambarKopPath)): ?>
            <div class="kop-header-full">
                <img src="<?= htmlspecialchars($gambarKopPath) ?>" alt="Kop Surat">
            </div>
        <?php else: ?>
            <div class="kop-container">
                <div class="kop-surat-grid">
                    <div>
                        <img src="../images/logo.png" alt="Logo" class="kop-logo-left" onerror="this.style.visibility='hidden';">
                    </div>
                    <div class="kop-text-center">
                        <h4>YAYASAN PENDIDIKAN HAJI NOOR HASYIM</h4>
                        <h2><?= htmlspecialchars($namaSekolah) ?></h2>
                        <p style="font-weight:bold;">NPSN : <?= htmlspecialchars($setting['npsn'] ?? '00000000') ?> – NSS : <?= htmlspecialchars($setting['nss'] ?? '000000000000') ?></p>
                        <p style="font-weight:bold;">TERAKREDITASI A</p>
                        <p>Teknik Otomotif (Teknik Kendaraan Ringan - Teknik Sepeda Motor)</p>
                        <p>Teknik Informatika (Rekayasa Perangkat Lunak - Teknik Komputer Jaringan)</p>
                        <p><?= htmlspecialchars($setting['header'] ?? 'Alamat Sekolah') ?></p>
                        <p>Kontak: -(isi nomor telepon & email sekolah)</p>
                    </div>
                    <div>
                        <img src="../images/logo_yayasan.png" alt="Logo Yayasan" class="kop-logo-right" onerror="this.style.visibility='hidden';">
                    </div>
                </div>
                <div class="kop-divider-blue"></div>
            </div>
        <?php endif; ?>

        <!-- TANGGAL KOTA TOP RIGHT -->
        <div class="date-top-right">
            <?= htmlspecialchars($surat['kota_tanggal'] ?? 'Kab. Bogor') ?>, <?= htmlspecialchars($tglKeluarIndo) ?>
        </div>

        <!-- SURAT META TABLE -->
        <table class="surat-meta-table">
            <tr>
                <td style="width: 90px;">Nomor</td>
                <td style="width: 15px;">:</td>
                <td><strong><?= htmlspecialchars($nomorSurat) ?></strong></td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td><?= htmlspecialchars($lampiran) ?></td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong><?= htmlspecialchars($perihal) ?></strong></td>
            </tr>
        </table>

        <!-- RECIPIENT -->
        <div class="recipient-block">
            <p style="margin:0 0 4px 0;">Kepada Yth</p>
            <p style="margin:0 0 4px 0; font-weight: bold;"><?= nl2br(htmlspecialchars($tujuanSurat)) ?></p>
            <p style="margin:0;">Di Tempat</p>
        </div>

        <!-- BODY CONTENT -->
        <div class="surat-body">
            <?php 
            if (!empty($isiSurat)) {
                if (strip_tags($isiSurat) !== $isiSurat) {
                    echo $isiSurat;
                } else {
                    $paragraphs = explode("\n\n", trim($isiSurat));
                    foreach ($paragraphs as $p) {
                        echo '<p>' . nl2br(htmlspecialchars(trim($p))) . '</p>';
                    }
                }
            } else {
                echo '<p class="no-indent">Assalamualaikum Warahmatullahi Wabarakatuh</p>';
                echo '<p>Teriring salam dan do’a semoga Bapak/Ibu selalu dalam lindungan Allah SWT, sehat wal’afiat serta selalu sukses dalam menjalankan tugas sehari-hari. Aamiin.</p>';
                echo '<p>Sehubungan dengan pelaksanaan kegiatan administrasi di lingkungan ' . htmlspecialchars($namaSekolah) . ', bersama surat ini kami sampaikan perihal mengenai <strong>' . htmlspecialchars($perihal) . '</strong>.</p>';
                echo '<p>Demikian surat ini kami sampaikan. Atas perhatian dan kerja sama yang baik dari bapak/ibu orang tua/wali, kami sampaikan terima kasih.</p>';
            }
            ?>
        </div>

        <!-- SIGNATURE BLOCK -->
        <div class="ttd-wrapper">
            <div class="ttd-box">
                <p style="margin:0 0 4px 0;">Mengetahui,</p>
                <p style="margin:0 0 4px 0; font-weight:600;"><?= htmlspecialchars($ttdJabatan) ?></p>
                <div class="ttd-space"></div>
                <p class="ttd-nama" style="margin:0;"><?= htmlspecialchars($ttdNama) ?></p>
                <?php if (!empty($ttdNip)): ?>
                    <p style="margin:2px 0 0 0; font-size:12px;">NIP. <?= htmlspecialchars($ttdNip) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- TEMBUSAN BLOCK -->
        <?php if (!empty($surat['tembusan'])): ?>
            <div class="tembusan-block">
                <p style="margin:0; font-weight:bold; text-decoration:underline;">Tembusan :</p>
                <div style="margin-left: 10px; margin-top: 4px;">
                    <?= nl2br(htmlspecialchars($surat['tembusan'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
