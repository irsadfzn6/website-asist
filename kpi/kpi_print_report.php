<?php

// Build URL to print handler (myadm/kpi/kpi_print.php)
if (!function_exists('kpiPrintReportUrl')) {
    function kpiPrintReportUrl($pgKey, $kpiType, $userId, $periode, $context = '') {
        unset($pgKey, $context);
        return 'kpi/kpi_print_render.php?type=' . rawurlencode($kpiType)
            . '&user_id=' . (int)$userId
            . '&periode=' . rawurlencode($periode);
    }
}

// Validation helpers (lightweight)
if (!function_exists('kpiFetchValidationRow')) {
    function kpiFetchValidationRow($koneksi, $tableValidation, $userId, $periode, $unitFilterSql = '1=1') {
        if (!$koneksi || !$tableValidation || !$periode) return null;
        $periodeEsc = mysqli_real_escape_string($koneksi, $periode);
        $q = mysqli_query($koneksi, "SELECT * FROM `$tableValidation` WHERE user_id=".(int)$userId." AND periode='$periodeEsc' LIMIT 1");
        return ($q && ($row = mysqli_fetch_assoc($q))) ? $row : null;
    }
}

if (!function_exists('kpiValidationStatusLabel')) {
    function kpiValidationStatusLabel($status, $hasRow) {
        if (!$hasRow) return 'Belum Diajukan';
        switch ($status) {
            case 'approved': return 'Disetujui';
            case 'rejected': return 'Ditolak';
            default: return 'Menunggu Validasi';
        }
    }
}

if (!function_exists('kpiCanPrintReport')) {
    function kpiCanPrintReport($koneksi, $tableValidation, $userId, $periode, $unitFilterSql) {
        if (!$koneksi || !$userId || !$periode) return false;
        $pEsc = mysqli_real_escape_string($koneksi, $periode);
        $q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM `kpi_evaluations` WHERE user_id = " . (int)$userId . " AND periode = '$pEsc'");
        if ($q && ($r = mysqli_fetch_assoc($q))) {
            return (int)$r['cnt'] > 0;
        }
        return false;
    }
}

if (!function_exists('kpiPrintDeniedMessage')) {
    function kpiPrintDeniedMessage($koneksi, $tableValidation, $userId, $periode, $unitFilterSql) {
        return 'Penilaian KPI belum diinput oleh evaluator. Cetak PDF tersedia setelah dinilai oleh atasan.';
    }
}

// Render button/link
if (!function_exists('kpiRenderPrintButton')) {
    function kpiRenderPrintButton($koneksi, $tableValidation, $userId, $periode, $unitFilterSql, $printUrl, $variant = 'inline', $label = 'PDF') {
        if ($periode === '' || (function_exists('kpiPeriodeIsValid') && !kpiPeriodeIsValid($periode))) return '';
        $allowed = kpiCanPrintReport($koneksi, $tableValidation, $userId, $periode, $unitFilterSql);
        $tip = $allowed ? '' : htmlspecialchars(kpiPrintDeniedMessage($koneksi, $tableValidation, $userId, $periode, $unitFilterSql), ENT_QUOTES, 'UTF-8');
        if ($allowed) {
            if ($variant === 'myapp') {
                return '<a href="'.htmlspecialchars($printUrl, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener" class="kpi-print-pdf-btn" title="Cetak PDF"><i class="material-icons" style="font-size:18px;vertical-align:middle;">print</i> '.$label.'</a>';
            }
            if ($variant === 'bootstrap' || $variant === 'bootstrap-lg') {
                return '<a href="'.htmlspecialchars($printUrl, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> '.$label.'</a>';
            }
            return '<a href="'.htmlspecialchars($printUrl, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary ml-1" title="Cetak PDF"><i class="fas fa-print"></i></a>';
        }
        // Disabled look
        if ($variant === 'myapp') {
            return '<span class="kpi-print-pdf-btn disabled" style="opacity:.5;cursor:not-allowed;" title="'.$tip.'"><i class="material-icons" style="font-size:18px;vertical-align:middle;">print</i> '.$label.'</span>';
        }
        return '<span class="btn btn-sm btn-secondary disabled ml-1" style="opacity:.55;cursor:not-allowed;" title="'.$tip.'"><i class="fas fa-print"></i></span>';
    }
}

if (!function_exists('kpi__avg_by_perspective')) {
    function kpi__avg_by_perspective($koneksi, $expr, $tableEvaluation, $tableIndicator, $userId, $periode, $perspectiveId, $unit = '') {
        if (!$koneksi) return 0;
        $periodeEsc = mysqli_real_escape_string($koneksi, $periode);
        $condUnit = $unit !== '' ? (" AND e.unit_sekolah = '" . mysqli_real_escape_string($koneksi, $unit) . "'") : '';
        $sql = "SELECT AVG($expr) AS avg_val FROM `$tableEvaluation` e
                JOIN `$tableIndicator` i ON e.indicator_id = i.id
                WHERE e.user_id = " . (int)$userId . " AND e.periode = '$periodeEsc' AND i.perspective_id = " . (int)$perspectiveId . $condUnit;
        $res = mysqli_query($koneksi, $sql);
        if ($res && ($row = mysqli_fetch_assoc($res))) return (float)($row['avg_val'] ?? 0);
        return 0;
    }
}

if (!function_exists('kpiCalculatePerspectiveWeight')) {
    function kpiCalculatePerspectiveWeight($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator, $unit = '') {
        return round(kpi__avg_by_perspective($koneksi, 'e.kpi_range', $tableEvaluation, $tableIndicator, $userId, $periode, $perspectiveId, $unit), 1);
    }
}

if (!function_exists('kpiCalculatePerspectiveRating')) {
    function kpiCalculatePerspectiveRating($koneksi, $userId, $periode, $perspectiveId, $tableEvaluation, $tableIndicator, $unit = '') {
        return round(kpi__avg_by_perspective($koneksi, 'e.rating', $tableEvaluation, $tableIndicator, $userId, $periode, $perspectiveId, $unit), 2);
    }
}

if (!function_exists('kpiCalculateTotalFinalRating')) {
    function kpiCalculateTotalFinalRating($koneksi, $userId, $periode, $tablePerspective, $tableEvaluation, $tableIndicator, $unit = '') {
        if (!$koneksi || !$periode) return 0;
        $periodeEsc = mysqli_real_escape_string($koneksi, $periode);
        $condUnit = $unit !== '' ? (" AND e.unit_sekolah = '" . mysqli_real_escape_string($koneksi, $unit) . "'") : '';
        $q = mysqli_query($koneksi, "SELECT AVG(e.rating) AS total_rating FROM `$tableEvaluation` e WHERE e.user_id = " . (int)$userId . " AND e.periode = '$periodeEsc'" . $condUnit);
        if ($q && ($row = mysqli_fetch_assoc($q))) return round((float)($row['total_rating'] ?? 0), 2);
        return 0;
    }
}

if (!function_exists('kpiPrintReportFetchData')) {
	function kpiPrintReportFetchData($koneksi, $userId, $periode, $tablePerspective, $tableIndicator, $tableEvaluation, $kpiUnitFilter, $kpiUnitFilterP, $kpiUnitFilterE, $targetRole = '') {
		$userId = (int)$userId;
		$periodeEsc = mysqli_real_escape_string($koneksi, $periode);
		$user = null;
		$qU = mysqli_query($koneksi, "SELECT id_user, nama, username, unit_sekolah, level FROM users WHERE id_user = $userId LIMIT 1");
		if ($qU && ($user = mysqli_fetch_assoc($qU))) {
			mysqli_free_result($qU);
		}
		if (!$user) {
			return null;
		}

		$targetUnit = trim((string)($user['unit_sekolah'] ?? ''));
		$roleWhere = $targetRole !== '' ? "target_role = '" . mysqli_real_escape_string($koneksi, $targetRole) . "' AND " : "";
		$roleJoin  = $targetRole !== '' ? "p.target_role = '" . mysqli_real_escape_string($koneksi, $targetRole) . "' AND " : "";

		$pWhere = $targetUnit !== ''
			? ($roleWhere . "unit_sekolah = '" . mysqli_real_escape_string($koneksi, $targetUnit) . "'")
			: ($roleWhere . ($kpiUnitFilter ?: '1=1'));
		$pJoinFilter = $targetUnit !== ''
			? ($roleJoin . "p.unit_sekolah = '" . mysqli_real_escape_string($koneksi, $targetUnit) . "'")
			: ($roleJoin . ($kpiUnitFilterP ?: '1=1'));
		$eFilter = $targetUnit !== ''
			? ("e.unit_sekolah = '" . mysqli_real_escape_string($koneksi, $targetUnit) . "'")
			: ($kpiUnitFilterE ?: '1=1');

		$perspectives = [];
		$qP = mysqli_query($koneksi, "SELECT * FROM `$tablePerspective` WHERE $pWhere ORDER BY urutan");
		if ($qP) {
			while ($p = mysqli_fetch_assoc($qP)) {
				$perspectives[(int)$p['id']] = [
					'info' => $p,
					'indicators' => [],
					'weight' => 0,
					'rating' => 0,
				];
			}
			mysqli_free_result($qP);
		}

		$sql = "SELECT i.id AS indicator_id, i.nama AS indicator_nama, i.deskripsi AS indicator_desk, i.urutan AS indicator_urutan,
			p.id AS perspective_id, p.nama AS perspective_nama,
			e.kpi_range, e.rating
			FROM `$tableIndicator` i
			INNER JOIN `$tablePerspective` p ON i.perspective_id = p.id
			LEFT JOIN `$tableEvaluation` e ON e.indicator_id = i.id
				AND e.user_id = $userId
				AND e.periode = '$periodeEsc'
				AND $eFilter
			WHERE $pJoinFilter
			ORDER BY p.urutan, i.urutan";
		$qI = mysqli_query($koneksi, $sql);
		if ($qI) {
			while ($row = mysqli_fetch_assoc($qI)) {
				$pid = (int)$row['perspective_id'];
				if (!isset($perspectives[$pid])) {
					$perspectives[$pid] = [
						'info' => ['id' => $pid, 'nama' => $row['perspective_nama'], 'deskripsi' => '', 'urutan' => 0],
						'indicators' => [],
						'weight' => 0,
						'rating' => 0,
					];
				}
				$perspectives[$pid]['indicators'][] = [
					'id' => (int)$row['indicator_id'],
					'nama' => $row['indicator_nama'],
					'deskripsi' => $row['indicator_desk'] ?? '',
					'kpi_range' => $row['kpi_range'] !== null ? (float)$row['kpi_range'] : null,
					'rating' => $row['rating'] !== null ? (int)$row['rating'] : null,
				];
			}
			mysqli_free_result($qI);
		}

		foreach ($perspectives as $pid => &$block) {
			$block['weight'] = kpiCalculatePerspectiveWeight($koneksi, $userId, $periode, $pid, $tableEvaluation, $tableIndicator, $targetUnit);
			$block['rating'] = kpiCalculatePerspectiveRating($koneksi, $userId, $periode, $pid, $tableEvaluation, $tableIndicator, $targetUnit);
		}
		unset($block);

		$totalFinal = kpiCalculateTotalFinalRating($koneksi, $userId, $periode, $tablePerspective, $tableEvaluation, $tableIndicator, $targetUnit);

		return [
			'user' => $user,
			'perspectives' => $perspectives,
			'total_final' => $totalFinal,
		];
	}
}

if (!function_exists('kpiPrintReportOutput')) {
	function kpiPrintReportOutput($koneksi, array $kpiConfig, $kpiType, $userId, $periode, $tablePerspective, $tableIndicator, $tableEvaluation, $kpiUnitFilter, $kpiUnitFilterP, $kpiUnitFilterE, $unitLabel, $tableValidation = null) {
		if ($periode === '') {
			echo '<p>Periode harus dipilih untuk cetak (pilih semester tertentu, bukan semua periode).</p>';
			return;
		}
		$data = kpiPrintReportFetchData($koneksi, $userId, $periode, $tablePerspective, $tableIndicator, $tableEvaluation, $kpiUnitFilter, $kpiUnitFilterP, $kpiUnitFilterE, $kpiType);
		if (!$data) {
			echo '<p>Data pegawai / evaluasi tidak ditemukan.</p>';
			return;
		}

		global $setting;
		$sekolah = $setting['sekolah'] ?? 'Sekolah';
		$kepsek = $setting['kepsek'] ?? '';
		$validation = $tableValidation
			? kpiFetchValidationRow($koneksi, $tableValidation, (int)$userId, $periode, $kpiUnitFilter)
			: null;
		$statusLabel = kpiValidationStatusLabel($validation['status'] ?? null, $validation !== null);

		$periodeLabel = function_exists('kpiPeriodeLabel') ? kpiPeriodeLabel($periode) : $periode;
		$printedAt = date('d/m/Y H:i');

		header('Content-Type: text/html; charset=utf-8');
		?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<title>Cetak KPI — <?= htmlspecialchars($data['user']['nama'], ENT_QUOTES, 'UTF-8') ?></title>
	<style>
		* { box-sizing: border-box; }
		html, body {
			margin: 0;
			padding: 0;
			width: 100%;
			background: #fff;
		}
		body {
			font-family: "Segoe UI", Tahoma, Arial, sans-serif;
			font-size: 11pt;
			color: #111;
			line-height: 1.4;
			max-width: 210mm;
			margin: 0 auto;
			padding: 16px 18px 24px;
		}
		.report-page { width: 100%; margin: 0 auto; }
		.no-print { margin-bottom: 16px; text-align: center; }
		.no-print button { padding: 10px 24px; margin: 0 6px; font-size: 14px; cursor: pointer; border-radius: 6px; border: 1px solid #ccc; background: #4f46e5; color: #fff; }
		.no-print button.secondary { background: #fff; color: #333; }
		.report-header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 12px; margin-bottom: 16px; }
		.report-header h1 { margin: 0 0 4px; font-size: 16pt; }
		.report-header h2 { margin: 0 0 8px; font-size: 13pt; font-weight: 600; }
		.report-header p { margin: 2px 0; font-size: 10pt; }
		.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; margin-bottom: 16px; font-size: 10.5pt; }
		.perspective-block { margin-bottom: 18px; page-break-inside: avoid; }
		.perspective-title { background: #eef2ff; padding: 8px 10px; font-weight: 700; font-size: 11pt; border: 1px solid #cbd5e1; margin: 0 0 0; }
		table.data { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 10pt; }
		table.data th, table.data td { border: 1px solid #94a3b8; padding: 6px 8px; vertical-align: top; }
		table.data th { background: #f1f5f9; font-weight: 600; text-align: left; }
		table.data td.num { text-align: center; width: 50px; }
		table.data td.pct { text-align: center; width: 70px; }
		.pers-summary { background: #f8fafc; font-weight: 600; }
		table.summary { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 10.5pt; }
		table.summary th, table.summary td { border: 1px solid #94a3b8; padding: 8px; }
		table.summary th { background: #e2e8f0; }
		.total-box { text-align: center; padding: 14px; border: 2px solid #059669; background: #ecfdf5; margin-top: 12px; }
		.total-box .label { font-size: 11pt; margin-bottom: 4px; }
		.total-box .value { font-size: 28pt; font-weight: 700; color: #047857; }
		.footer-note { margin-top: 20px; font-size: 9pt; color: #64748b; text-align: center; }
		@media print {
			html, body {
				width: 100%;
				max-width: none;
				margin: 0;
				padding: 0;
			}
			@page {
				size: A4 portrait;
				margin: 12mm 14mm;
			}
			body { padding: 0; }
			.no-print { display: none !important; }
			.perspective-block { page-break-inside: avoid; }
		}
	</style>
</head>
<body>
	<div class="report-page">
	<div class="no-print">
		<button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
		<button type="button" class="secondary" onclick="window.close()">Tutup</button>
	</div>

	<div class="report-header">
		<h1><?= htmlspecialchars($sekolah, ENT_QUOTES, 'UTF-8') ?></h1>
		<h2>Hasil Evaluasi KPI — <?= htmlspecialchars($kpiConfig['title'], ENT_QUOTES, 'UTF-8') ?></h2>
		<p><?= htmlspecialchars($unitLabel, ENT_QUOTES, 'UTF-8') ?></p>
	</div>

	<div class="info-grid">
		<div><strong>Nama:</strong> <?= htmlspecialchars($data['user']['nama'], ENT_QUOTES, 'UTF-8') ?></div>
		<div><strong>Jabatan:</strong> <?= htmlspecialchars(trim((string)($data['user']['jabatan'] ?? '')) !== '' ? ($data['user']['jabatan'] ?? '-') : (ucfirst(str_replace('_', ' ', (string)($data['user']['level'] ?? '-')))), ENT_QUOTES, 'UTF-8') ?></div>
		<div><strong>Periode:</strong> <?= htmlspecialchars($periodeLabel, ENT_QUOTES, 'UTF-8') ?></div>
		<div><strong>Status validasi:</strong> <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></div>
		<div><strong>Total Final Rating:</strong> <?= htmlspecialchars((string)$data['total_final'], ENT_QUOTES, 'UTF-8') ?></div>
		<div><strong>Dicetak:</strong> <?= htmlspecialchars($printedAt, ENT_QUOTES, 'UTF-8') ?></div>
	</div>

	<?php foreach ($data['perspectives'] as $block): ?>
	<div class="perspective-block">
		<div class="perspective-title"><?= htmlspecialchars($block['info']['nama'], ENT_QUOTES, 'UTF-8') ?></div>
		<table class="data">
			<thead>
				<tr>
					<th class="num">No</th>
					<th>Indikator</th>
					<th class="pct">KPI Range (%)</th>
					<th class="pct">Rating</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($block['indicators'])): ?>
				<tr><td colspan="4" style="text-align:center;color:#64748b;">Tidak ada indikator</td></tr>
				<?php else: ?>
				<?php $no = 1; foreach ($block['indicators'] as $ind): ?>
				<tr>
					<td class="num"><?= $no++ ?></td>
					<td><?= htmlspecialchars($ind['nama'], ENT_QUOTES, 'UTF-8') ?></td>
					<td class="pct"><?= $ind['kpi_range'] !== null ? number_format($ind['kpi_range'], 2) : '—' ?></td>
					<td class="pct"><?= $ind['rating'] !== null ? (int)$ind['rating'] : '—' ?></td>
				</tr>
				<?php endforeach; ?>
				<tr class="pers-summary">
					<td colspan="2" style="text-align:right;">Rata-rata perspektif (Weight / Rating)</td>
					<td class="pct"><?= number_format($block['weight'], 1) ?>%</td>
					<td class="pct"><?= round($block['rating']) ?></td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php endforeach; ?>

	<table class="summary">
		<thead>
			<tr>
				<th>Perspektif</th>
				<th style="text-align:center;width:100px;">Weight (%)</th>
				<th style="text-align:center;width:80px;">Rating</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data['perspectives'] as $block): ?>
			<tr>
				<td><?= htmlspecialchars($block['info']['nama'], ENT_QUOTES, 'UTF-8') ?></td>
				<td style="text-align:center;"><?= number_format($block['weight'], 1) ?>%</td>
				<td style="text-align:center;"><?= round($block['rating']) ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="total-box">
		<div class="label">Total Final Rating</div>
		<div class="value"><?= round($data['total_final']) ?></div>
	</div>

	<p class="footer-note">Dokumen ini dicetak dari sistem Smart WB. Gunakan "Cetak" di browser lalu pilih "Simpan sebagai PDF" jika diperlukan.</p>
	</div>

	<script>
		// Opsional: langsung buka dialog cetak
		// window.onload = function() { setTimeout(function() { window.print(); }, 400); };
	</script>
</body>
</html>
		<?php
	}
}
