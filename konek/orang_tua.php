<?php

if (!function_exists('ensure_siswa_parent_auth_columns')) {
    function ensure_siswa_parent_auth_columns($koneksi)
    {
        static $ensured = false;

        if ($ensured) {
            return;
        }

        $columns = [
            'username_ortu' => "ALTER TABLE siswa ADD COLUMN username_ortu VARCHAR(100) NULL AFTER username",
            'password_ortu' => "ALTER TABLE siswa ADD COLUMN password_ortu VARCHAR(100) NULL AFTER password",
        ];

        foreach ($columns as $column => $sql) {
            $check = mysqli_query($koneksi, "SHOW COLUMNS FROM siswa LIKE '" . mysqli_real_escape_string($koneksi, $column) . "'");
            if ($check && mysqli_num_rows($check) === 0) {
                @mysqli_query($koneksi, $sql);
            }
        }

        $ensured = true;
    }

    function siswa_get_login_role()
    {
        return $_SESSION['login_siswa_role'] ?? 'siswa';
    }

    function siswa_is_orang_tua_login()
    {
        return siswa_get_login_role() === 'orang_tua';
    }

    function siswa_dashboard_page_allowed($page)
    {
        if (!siswa_is_orang_tua_login()) {
            return true;
        }

        return in_array((string) $page, ['', 'pembayaran', 'detail_transaksi'], true);
    }

    function siswa_payment_tab_allowed($tab)
    {
        $tab = (string) $tab;

        if (!siswa_is_orang_tua_login()) {
            return in_array($tab, ['pembayaran', 'riwayat', 'tagihan'], true);
        }

        return in_array($tab, ['riwayat', 'tagihan'], true);
    }

    function siswa_dashboard_home_url()
    {
        if (siswa_is_orang_tua_login()) {
            return '?pg=pembayaran&tab=tagihan';
        }

        return './';
    }

    function siswa_dashboard_display_name(array $siswa)
    {
        if (!siswa_is_orang_tua_login()) {
            return $siswa['nama'] ?? 'Siswa';
        }

        $parentName = trim((string) ($siswa['ayah'] ?? ''));
        if ($parentName === '') {
            $parentName = trim((string) ($siswa['ibu'] ?? ''));
        }

        if ($parentName === '') {
            $parentName = 'Orang Tua';
        }

        $studentName = trim((string) ($siswa['nama'] ?? 'Siswa'));
        return $parentName . ' / ' . $studentName;
    }
}
