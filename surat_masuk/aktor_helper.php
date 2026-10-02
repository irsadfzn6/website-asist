<?php
/**
 * Helper penerima surat masuk (aktor_tujuan): resolve username & kondisi SQL inbox.
 */

if (!function_exists('suratMasukRecipientCondSql')) {
	function suratMasukRecipientCondSql() {
		// DITAMBAHKAN: keprog, waka_kurikulum, kesiswaan
		$levels = ['guru', 'staff', 'staff_tu', 'kepsek', 'kaprog', 'keprog', 'wakakur', 'waka_kurikulum', 'wakasis', 'kesiswaan', 'keuangan', 'ketua_yayasan', 'pembina_yayasan'];
		$escapedLevels = "'" . implode("','", $levels) . "'";
		
		// DITAMBAHKAN: waka kurikulum, kesiswaan
		return "(
			LOWER(TRIM(COALESCE(u.level,''))) IN ($escapedLevels)

		)";
	}
}

if (!function_exists('suratMasukResolveAktorUsername')) {
	function suratMasukResolveAktorUsername($koneksi, $raw) {
		$raw = trim((string)$raw);
		if ($raw === '' || !$koneksi) {
			return '';
		}
		if (ctype_digit($raw)) {
			$id = (int)$raw;
			$q = mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = $id AND TRIM(COALESCE(username,'')) <> '' LIMIT 1");
			if ($q && ($r = mysqli_fetch_assoc($q))) {
				return trim((string)$r['username']);
			}
		}
		$esc = mysqli_real_escape_string($koneksi, $raw);
		$q = mysqli_query($koneksi, "SELECT username FROM users WHERE TRIM(username) = '$esc' OR LOWER(TRIM(username)) = LOWER('$esc') LIMIT 1");
		if ($q && ($r = mysqli_fetch_assoc($q))) {
			return trim((string)$r['username']);
		}
		return $raw;
	}
}

if (!function_exists('suratMasukAktorMatchSql')) {
	function suratMasukAktorMatchSql($koneksi, $userId, $username, $alias = '') {
		$userId = (int)$userId;
		$username = trim((string)$username);
		$p = $alias !== '' ? $alias . '.' : '';
		$parts = [];
		if ($username !== '') {
			$esc = mysqli_real_escape_string($koneksi, $username);
			$parts[] = "TRIM({$p}aktor_tujuan) = '$esc'";
			$parts[] = "LOWER(TRIM({$p}aktor_tujuan)) = LOWER('$esc')";
		}
		if ($userId > 0) {
			$parts[] = "TRIM({$p}aktor_tujuan) = '$userId'";
			$qN = mysqli_query($koneksi, "SELECT nama FROM users WHERE id_user = $userId LIMIT 1");
			if ($qN && ($rn = mysqli_fetch_assoc($qN))) {
				$nama = trim((string)($rn['nama'] ?? ''));
				if ($nama !== '') {
					$nEsc = mysqli_real_escape_string($koneksi, $nama);
					$parts[] = "TRIM({$p}aktor_tujuan) = '$nEsc'";
				}
			}
		}
		if (empty($parts)) {
			return '0';
		}
		return '(' . implode(' OR ', $parts) . ')';
	}
}