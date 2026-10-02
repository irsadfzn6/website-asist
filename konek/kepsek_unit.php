<?php
require_once __DIR__ . '/unit_helper.php';

if (!function_exists('kepsek_unit_current')) {
    function kepsek_unit_current($koneksi, $user = null)
    {
        if ($user === null && !empty($_SESSION['id_user'])) {
            $idUser = (int) $_SESSION['id_user'];
            $res = mysqli_query($koneksi, "SELECT level, unit_sekolah AS unit FROM users WHERE id_user='{$idUser}' LIMIT 1");
            $user = $res ? mysqli_fetch_assoc($res) : null;
        }

        $level = strtolower((string)($user['level'] ?? ''));
        $unitScopedRoles = ['kepsek', 'waka_kurikulum', 'kesiswaan', 'admin'];
        if (!$user || !in_array($level, $unitScopedRoles, true)) {
            return '';
        }

        return unit_normalize($user['unit_sekolah'] ?? ($user['unit'] ?? ''));
    }

    function kepsek_unit_has_siswa_unit($koneksi)
    {
        $res = mysqli_query($koneksi, "SHOW COLUMNS FROM siswa LIKE 'unit_sekolah'");
        return $res && mysqli_num_rows($res) > 0;
    }

    function kepsek_unit_sql($koneksi, $alias = 's', $user = null)
    {
        $unitSekolah = kepsek_unit_current($koneksi, $user);
        if ($unitSekolah === '' || !kepsek_unit_has_siswa_unit($koneksi)) {
            return '';
        }

        $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
        $prefix = $alias !== '' ? "`{$alias}`." : '';
        $unitVariants = array_values(array_unique(array_filter([
            $unitSekolah,
            str_replace('_', '', $unitSekolah),
            function_exists('unit_label') ? unit_label($unitSekolah) : ''
        ])));
        $safeUnits = array_map(function($unit) use ($koneksi) {
            return "'" . mysqli_real_escape_string($koneksi, $unit) . "'";
        }, $unitVariants);
        return " AND {$prefix}`unit_sekolah` IN (" . implode(',', $safeUnits) . ")";
    }
}
