<?php
require("konek/koneksi.php");

if (!$koneksi) {
    die('Koneksi database gagal');
}

$username = mysqli_real_escape_string($koneksi, $_POST['username'] ?? '');
$password = mysqli_real_escape_string($koneksi, $_POST['password'] ?? '');
$level    = mysqli_real_escape_string($koneksi, $_POST['level'] ?? '');

$query = mysqli_query($koneksi, "
    SELECT * FROM users
    WHERE username = '$username'
    AND password = '$password'
    AND level = '$level'
    LIMIT 1
");

if ($query && mysqli_num_rows($query) > 0) {
    $user = mysqli_fetch_assoc($query);

    $_SESSION['id_user'] = $user['id_user'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['level'] = $user['level'];
    $_SESSION['unit_sekolah'] = 'asist';

    echo 'ok';
} else {
    echo 'td';
}