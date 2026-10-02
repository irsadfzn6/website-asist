<?php
defined('APK') or exit('No access');

/**
 * Periode KPI per semester (bukan per bulan).
 * Format simpan: 2025/2026-Ganjil | 2025/2026-Genap
 */

if (!function_exists('kpiPeriodeCurrent')) {
	function kpiPeriodeCurrent($at = null) {
		$at = $at ?? time();
		$month = (int)date('n', $at);
		$year = (int)date('Y', $at);
		if ($month >= 7) {
			return $year . '/' . ($year + 1) . '-Ganjil';
		}
		return ($year - 1) . '/' . $year . '-Genap';
	}
}

if (!function_exists('kpiPeriodeLabel')) {
	function kpiPeriodeLabel($code) {
		$code = trim((string)$code);
		if ($code === '') {
			return 'Semua periode';
		}
		if (preg_match('#^(\d{4}/\d{4})-(Ganjil|Genap)$#', $code, $m)) {
			$sem = $m[2] === 'Ganjil' ? 'Semester Ganjil' : 'Semester Genap';
			return $sem . ' TP ' . $m[1];
		}
		if (preg_match('#^\d{4}-\d{2}$#', $code)) {
			return $code . ' (periode bulanan lama)';
		}
		return $code;
	}
}

if (!function_exists('kpiPeriodeIsValid')) {
	function kpiPeriodeIsValid($code) {
		$code = trim((string)$code);
		if ($code === '') {
			return false;
		}
		return (bool)preg_match('#^(\d{4}/\d{4})-(Ganjil|Genap)$#', $code)
			|| (bool)preg_match('#^\d{4}-\d{2}$#', $code);
	}
}

if (!function_exists('kpiPeriodeStandardOptions')) {
	/** Opsi semester untuk dropdown (beberapa tahun terakhir) */
	function kpiPeriodeStandardOptions($yearsBack = 4) {
		$options = [];
		$cur = kpiPeriodeCurrent();
		if (!preg_match('#^(\d{4})/(\d{4})-(Ganjil|Genap)$#', $cur, $m)) {
			return $options;
		}
		$startYear = (int)$m[1] - $yearsBack;
		for ($y = $startYear; $y <= (int)$m[1] + 1; $y++) {
			$tp = $y . '/' . ($y + 1);
			$options[] = ['value' => $tp . '-Ganjil', 'label' => kpiPeriodeLabel($tp . '-Ganjil')];
			$options[] = ['value' => $tp . '-Genap', 'label' => kpiPeriodeLabel($tp . '-Genap')];
		}
		return $options;
	}
}

if (!function_exists('kpiPeriodeSortKey')) {
	function kpiPeriodeSortKey($code) {
		$code = trim((string)$code);
		if (preg_match('#^(\d{4})/(\d{4})-(Ganjil|Genap)$#', $code, $m)) {
			$sem = $m[3] === 'Ganjil' ? 2 : 1;
			return (int)$m[2] * 10 + $sem;
		}
		if (preg_match('#^(\d{4})-(\d{2})$#', $code, $m)) {
			return (int)$m[1] * 10 + (int)$m[2];
		}
		return 0;
	}
}

if (!function_exists('kpiPeriodeMergeOptions')) {
	/**
	 * Gabung opsi standar + periode yang sudah ada di database (Global).
	 * @return array<int, array{value: string, label: string}>
	 */
	function kpiPeriodeMergeOptions($koneksi, $tableEvaluation) {
		$map = [];
		foreach (kpiPeriodeStandardOptions() as $opt) {
			$map[$opt['value']] = $opt['label'];
		}
		if ($koneksi && $tableEvaluation !== '') {
            // Filter Unit dihapus agar menampilkan semua periode yang ada
			$q = mysqli_query($koneksi, "SELECT DISTINCT e.periode FROM `$tableEvaluation` e ORDER BY e.periode DESC");
			if ($q) {
				while ($row = mysqli_fetch_assoc($q)) {
					$v = trim((string)$row['periode']);
					if ($v !== '' && !isset($map[$v])) {
						$map[$v] = kpiPeriodeLabel($v);
					}
				}
				mysqli_free_result($q);
			}
		}
		$out = [];
		foreach ($map as $value => $label) {
			$out[] = ['value' => $value, 'label' => $label, '_sort' => kpiPeriodeSortKey($value)];
		}
		usort($out, function ($a, $b) {
			return $b['_sort'] <=> $a['_sort'];
		});
		foreach ($out as &$row) {
			unset($row['_sort']);
		}
		unset($row);
		return $out;
	}
}

if (!function_exists('kpiLatestPeriodeForUser')) {
    /**
     * Mencari periode evaluasi KPI terakhir untuk user tertentu.
     * Ini fungsi yang sebelumnya hilang dan menyebabkan halaman blank.
     */
    function kpiLatestPeriodeForUser($koneksi, $userId, $tableEvaluation) {
        $userId = (int)$userId;
        $q = mysqli_query($koneksi, "SELECT periode FROM `$tableEvaluation` WHERE user_id = $userId ORDER BY periode DESC LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            return $row['periode'];
        }
        return kpiPeriodeCurrent();
    }
}

if (!function_exists('kpiRenderPeriodeSelect')) {
	function kpiRenderPeriodeSelect($name, $selected, array $options, $includeAll = true, $attrs = '') {
		$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
		echo '<select name="' . $name . '" ' . $attrs . '>';
		if ($includeAll) {
			$sel = $selected === '' ? ' selected' : '';
			echo '<option value=""' . $sel . '>— Semua periode —</option>';
		}
		foreach ($options as $opt) {
			$v = $opt['value'];
			$sel = ($selected === $v) ? ' selected' : '';
			echo '<option value="' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '"' . $sel . '>'
				. htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8') . '</option>';
		}
		echo '</select>';
	}
}

if (!function_exists('kpiGetValidationRecord')) {
	/** @return array<string,mixed>|null */
	function kpiGetValidationRecord($koneksi, $tableValidation, $userId, $periode) {
		if (!$koneksi || $tableValidation === '') {
			return null;
		}
		$userId = (int)$userId;
		$periodeEsc = mysqli_real_escape_string($koneksi, trim((string)$periode));
		if ($userId <= 0 || $periodeEsc === '') {
			return null;
		}
		$q = mysqli_query($koneksi, "SELECT * FROM `$tableValidation` WHERE user_id = $userId AND periode = '$periodeEsc' LIMIT 1");
		if ($q && ($row = mysqli_fetch_assoc($q))) {
			mysqli_free_result($q);
			return $row;
		}
		return null;
	}
}

if (!function_exists('kpiFormatUnitSqlVal')) {
	function kpiFormatUnitSqlVal($koneksi, $unit) {
		$unit = trim((string)$unit);
		if (function_exists('unit_normalize') && !empty($unit)) {
			$unit = unit_normalize($unit);
		}
		if ($unit === '') {
			return "NULL";
		}
		if ($koneksi) {
			$esc = mysqli_real_escape_string($koneksi, $unit);
			$chk = mysqli_query($koneksi, "SELECT kode FROM unit_sekolah WHERE kode = '$esc' LIMIT 1");
			if ($chk && mysqli_num_rows($chk) > 0) {
				mysqli_free_result($chk);
				return "'$esc'";
			}
			$chk2 = mysqli_query($koneksi, "SELECT kode FROM unit_sekolah WHERE nama = '$esc' LIMIT 1");
			if ($chk2 && ($r2 = mysqli_fetch_assoc($chk2))) {
				$matchedCode = mysqli_real_escape_string($koneksi, $r2['kode']);
				mysqli_free_result($chk2);
				return "'$matchedCode'";
			}
		}
		return "NULL";
	}
}

if (!function_exists('kpiCanEditEvaluation')) {
	function kpiCanEditEvaluation($validationStatus) {
		$status = strtolower(trim((string)($validationStatus ?? '')));
		return $status === '' || $status === 'pending' || $status === 'rejected';
	}
}

if (!function_exists('kpiValidationStatusLabel')) {
	function kpiValidationStatusLabel($validationStatus) {
		$status = strtolower(trim((string)($validationStatus ?? '')));
		if ($status === 'approved') {
			return 'Disetujui';
		}
		if ($status === 'rejected') {
			return 'Ditolak';
		}
		if ($status === 'pending') {
			return 'Menunggu Validasi';
		}
		return 'Belum Diajukan';
	}
}

if (!function_exists('kpiSubmitValidation')) {
	function kpiSubmitValidation($koneksi, $tableValidation, $userId, $periode, $unitSekolah = '') {
		if (!$koneksi || $tableValidation === '') {
			return false;
		}
		$userId = (int)$userId;
		$periodeEsc = mysqli_real_escape_string($koneksi, trim((string)$periode));
		if ($userId <= 0 || $periodeEsc === '') {
			return false;
		}
		$unitVal = kpiFormatUnitSqlVal($koneksi, $unitSekolah);
		$existing = kpiGetValidationRecord($koneksi, $tableValidation, $userId, $periodeEsc);
		if ($existing) {
			return (bool)mysqli_query(
				$koneksi,
				"UPDATE `$tableValidation` SET status = 'pending', validator_id = NULL, validated_at = NULL, unit_sekolah = $unitVal, updated_at = NOW() WHERE user_id = $userId AND periode = '$periodeEsc'"
			);
		}
		return (bool)mysqli_query(
			$koneksi,
			"INSERT INTO `$tableValidation` (user_id, periode, status, unit_sekolah) VALUES ($userId, '$periodeEsc', 'pending', $unitVal)"
		);
	}
}

if (!function_exists('kpiEnsureLegacyPendingValidation')) {
	/**
	 * Data lama: sudah ada evaluasi tapi belum ada record validasi → kunci sebagai pending.
	 */
	function kpiEnsureLegacyPendingValidation($koneksi, $tableValidation, $tableEvaluation, $userId, $periode, $unitSekolah = '') {
		if (!$koneksi || kpiGetValidationRecord($koneksi, $tableValidation, $userId, $periode)) {
			return kpiGetValidationRecord($koneksi, $tableValidation, $userId, $periode);
		}
		$userId = (int)$userId;
		$periodeEsc = mysqli_real_escape_string($koneksi, trim((string)$periode));
		$q = mysqli_query($koneksi, "SELECT COUNT(*) AS cnt FROM `$tableEvaluation` WHERE user_id = $userId AND periode = '$periodeEsc'");
		$cnt = ($q && ($row = mysqli_fetch_assoc($q))) ? (int)$row['cnt'] : 0;
		if ($cnt > 0) {
			kpiSubmitValidation($koneksi, $tableValidation, $userId, $periodeEsc, $unitSekolah);
		}
		return kpiGetValidationRecord($koneksi, $tableValidation, $userId, $periode);
	}
}