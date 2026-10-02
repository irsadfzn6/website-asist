<?php
require("konek/koneksi.php");
if (!empty($_SESSION['id_user'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - ASIST</title>
    <!-- CSS Bootstrap Bawaan -->
    <link href="<?= $baseurl ?>/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tambahan Bootstrap Icons untuk Ikon Mata -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        /* Desain UI Kustom */
        body {
            /* Gradien Biru Modern */
            background: linear-gradient(135deg, #0d6efd 0%, #00d2ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            background-color: #ffffff;
        }
        .brand-title {
            color: #0d6efd;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            border-color: #86b7fe;
        }
        .input-group-text {
            background-color: transparent;
            border-left: none;
            cursor: pointer;
            border-radius: 0 8px 8px 0;
            color: #6c757d;
        }
        .password-input {
            border-right: none;
        }
        .password-input:focus + .input-group-text {
            border-color: #86b7fe;
        }
        .btn-login {
            background-color: #0d6efd;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        label {
            font-weight: 500;
            color: #495057;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card login-card mt-4 mb-4">
                <div class="card-body p-5">
                    
                    <div class="text-center mb-4">
                        <h3 class="brand-title mb-0">ASIST</h3>
                        <p class="text-muted small mt-1">Sistem E-Administrasi Terintegrasi</p>
                    </div>

                    <div id="pesan" class="alert alert-danger d-none" style="border-radius: 8px; font-size: 0.9rem;"></div>
                    
                    <form id="formLogin">
                        <div class="mb-3">
                            <label class="mb-1">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username..." required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="mb-1">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="inputPassword" class="form-control password-input" placeholder="Masukkan password..." required>
                                <span class="input-group-text" id="togglePassword">
                                    <i class="bi bi-eye-slash" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="mb-1">Hak Akses (Level)</label>
                            <select name="level" class="form-select form-control" required>
                                <option value="" disabled selected>-- Pilih Hak Akses --</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                                <option value="guru">Guru</option>
                                <option value="kepsek">Kepala Sekolah</option>
                                <option value="waka_kurikulum">Wakil Kurikulum</option>
                                <option value="kesiswaan">Kesiswaan</option>
                                <option value="keprog">Kepala Program</option>
                                <option value="keuangan">Keuangan</option>
                                <option value="yayasan">Ketua Yayasan</option>
                                <option value="pembina_yayasan">Pembina Yayasan</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login w-100 text-white">
                            Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS JQuery Bawaan -->
<script src="<?= $baseurl ?>/assets/plugins/jquery/jquery-3.5.1.min.js"></script>
<script>
// Script untuk toggle lihat/sembunyikan password
$('#togglePassword').on('click', function() {
    const passwordField = $('#inputPassword');
    const eyeIcon = $('#eyeIcon');
    
    if (passwordField.attr('type') === 'password') {
        passwordField.attr('type', 'text');
        eyeIcon.removeClass('bi-eye-slash').addClass('bi-eye');
    } else {
        passwordField.attr('type', 'password');
        eyeIcon.removeClass('bi-eye').addClass('bi-eye-slash');
    }
});

// Script Proses Login AJAX Bawaan
$('#formLogin').on('submit', function(e) {
    e.preventDefault();
    
    // Ganti teks tombol saat loading
    const btn = $(this).find('button[type="submit"]');
    const originalText = btn.html();
    btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
    btn.prop('disabled', true);

    $.post('ceklogin.php', $(this).serialize(), function(res) {
        res = res.trim();
        if (res === 'ok') {
            window.location.href = 'index.php';
        } else {
            $('#pesan').removeClass('d-none').html('<i class="bi bi-exclamation-triangle-fill me-2"></i>Username, password, atau level salah.');
            // Kembalikan tombol ke semula
            btn.html(originalText);
            btn.prop('disabled', false);
        }
    });
});
</script>
</body>
</html>