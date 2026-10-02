# ASIST — Sistem Administrasi Sekolah Terpadu

Aplikasi web PHP native untuk administrasi sekolah: surat masuk, surat keluar,
kegiatan & proposal, KPI, dan presensi. Dirancang untuk multiple unit sekolah
dengan filter data per unit.

> Repo ini adalah **versi demo/sanitized**. Identitas sekolah asli, kredensial,
> dan dokumen internal sudah diganti dengan placeholder sebelum di-commit.
> Lihat [Sanitasi Data](#sanitasi-data) di bawah.

## Kebutuhan Sistem

- PHP 8.0 atau lebih baru (diuji sampai PHP 8.3)
- MySQL 5.7+ / MariaDB 10.3+
- Ekstensi PHP: `mysqli`, `gd` (untuk manipulasi gambar), `mbstring`
- Web server dengan `mod_rewrite` (Apache) — diperlukan oleh `.htaccess`
- Composer **tidak** dipakai; semua library di-vendor-kan di dalam `assets/`

## Instalasi

1. Salin folder ke `htdocs/`:

   ```bash
   cp -r asist /opt/lampp/htdocs/
   ```

2. Buat database lalu import dump:

   ```bash
   mysql -u root -p -e "CREATE DATABASE db_asist CHARACTER SET utf8mb4;"
   mysql -u root -p db_asist < "database/db_asist (10).sql"
   ```

   Ada tiga file SQL tambahan di `konek/` yang *tidak* ikut tercakup di dump
   utama dan perlu dijalankan terpisah bila fitur terkait dipakai:

   ```bash
   mysql -u root -p db_asist < konek/kpi_create_tables.sql
   mysql -u root -p db_asist < konek/kpi_seed_units.sql
   mysql -u root -p db_asist < konek/update_users_units.sql
   ```

3. Sesuaikan kredensial DB di `konek/koneksi.php` (lihat di bawah).

4. Buka `http://localhost/asist/`.

## Login

Seluruh akun pada dump demo memakai password yang sama:

| Level | Username contoh | Password |
|---|---|---|
| Admin | `admin_wb1`, `admin_wb2`, `admin_wb3`, `admin_yayasan` | `demo1234` |
| Guru | `guru1`, `guru2`, `guru3` | `demo1234` |
| Kepala Sekolah | `kepsek_wb1` … `kepsek_wb3` | `demo1234` |
| Peran lain | `staff_wb*`, `keuangan*`, `yayasan`, `pembina_yayasan`, `wakakur_wb*`, `kesiswaan_wb*`, `keprog_wb*` | `demo1234` |

Level dipilih bersama username saat login — `ceklogin.php` mencocokkan
ketiganya sekaligus.

## Konfigurasi

Semua konfigurasi berada di `konek/koneksi.php`:

```php
$host     = 'localhost';
$username = 'root';
$password = '';              // default XAMPP lokal
$database = 'db_asist';
```

`$setting` di file yang sama menyimpan identitas sekolah yang dipakai pada
kop surat: nama sekolah, NPSN, NSS, nama kepala sekolah, dan alamat.
Semua sudah diganti placeholder — **isi ulang dengan data sekolah Anda**
sebelum dipakai untuk surat resmi.

Untuk instalasi di subfolder (mis. `http://localhost/asist/`), `$baseurl`
sudah dideteksi otomatis dari `DOCUMENT_ROOT`.

## Struktur Direktori

```
asist/
├── index.php, login.php, ceklogin.php   # autentikasi
├── home.php, menu.php, nav.php           # shell & navigasi
├── konek/                                # koneksi DB, helper, schema SQL
├── surat_masuk/  surat_keluar/           # surat & cetak
├── kegiatan/                             # proposal kegiatan & LPJ
├── kpi/                                  # KPI per unit
├── assets/                               # Bootstrap, jQuery, TinyMCE,
│                                         #   DataTables, ChartJS, dll.
├── images/  font/                        # aset statis
└── uploads/                              # dokumen unggahan (tidak di-commit)
```

## Sanitasi Data

Sebelum repo ini dipublikasikan, data berikut diganti:

| Yang diganti | Menjadi |
|---|---|
| Nama sekolah, NPSN, NSS, alamat, nama kepsek | `SMA NEGARA CONTOH`, `00000000`, dst. |
| Password 28 akun | `demo1234` |
| Nama pribadi pada tabel `users` | label peran (`Guru WB 1`, dst.) |
| `secret_key` pada tabel `unit_sekolah` | `CHANGE_ME_UNITn` |
| Nomor telepon & email sekolah | teks placeholder |
| Folder `uploads/` (11 MB dokumen asli) | **tidak di-commit** |

Folder `uploads/`, `surat_masuk/uploads/`, dan `surat_keluar/uploads/` sengaja
tidak dimasukkan ke repo karena berisi dokumen internal sekolah. Buat folder
itu secara manual dengan izin tulis web server setelah clone:

```bash
mkdir -p uploads/{lpj,proposal,surat_masuk}
chmod -R 775 uploads/
```

## Catatan Keamanan

Beberapa hal yang perlu diketahui sebelum dipakai di server publik:

- **Password user disimpan plaintext.** `ceklogin.php` membandingkan
  `AND password = '$password'` secara langsung, dan dump SQL berisi password
  polos. Ganti ke `password_hash()` / `password_verify()` sebelum deploy —
  saat ini siapa pun yang bisa membaca database tahu semua password.
- **Query dibangun dengan interpolasi string** di banyak tempat. Input
  `$username` di-`escape` dengan `mysqli_real_escape_string`, tapi pola ini
  rapuh; prepared statement lebih aman.
- `session_regenerate_id()` tidak dipanggil setelah login, sehingga ada risiko
  session fixation.
- Tidak ada rate limiting pada endpoint login.

## Known Issues

- **`absensi_mapel` tidak ada.** `pages.php` baris 66 menjalankan
  `DELETE FROM absensi_mapel WHERE tanggal<>'$tanggal'` sebagai efek samping pada
  setiap halaman. Tabel ini tidak ada di dump SQL mana pun yang disertakan.
  Halaman tetap tampil karena query dijalankan setelah `endif`, tetapi di
  server dengan `display_errors=On` (default XAMPP!) error ini muncul sebagai
  `Uncaught mysqli_sql_exception` di response. Buat tabelnya, atau hapus
  baris tersebut bila fitur absensi mapel memang tidak dipakai.
- **Tiga folder tidak ikut dalam arsip sumber:** `gtt/`, `tugas/`, dan
  `pengaturan/`. `pages.php` masih mereferensikannya, tapi tidak ada tautan
  menu yang menuju ke sana — fitur lama yang sudah tidak aktif. Halaman-halaman
  tersebut akan gagal dengan `include` error bila diakses langsung.
- Query `DELETE` di `pages.php:66` juga tidak meng-escape `$tanggal`.

## Lisensi

Belum ditentukan oleh pemilik repositori.