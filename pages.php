<?php if ($pg == '') : ?>
    <?php include 'home.php'; ?>
<?php elseif ($pg == enkripsi('master')) : ?>
    <?php include 'gtt/master.php'; ?>
<?php elseif ($pg == enkripsi('sk')) : ?>
    <?php include 'gtt/sk.php'; ?>
<?php elseif ($pg == enkripsi('cetak3')) : ?>
    <?php include 'gtt/cetak3.php'; ?>	
<?php elseif ($pg == enkripsi('sppd')) : ?>
    <?php include 'gtt/sppd.php'; ?>	
<?php elseif ($pg == enkripsi('cetak4')) : ?>
    <?php include 'gtt/cetak4.php'; ?>	
	
<?php elseif ($pg == enkripsi('cetak1')) : ?>
    <?php include 'gtt/cetak1.php'; ?>
<?php elseif ($pg == enkripsi('guru')) : ?>
    <?php include 'gtt/guru.php'; ?>
<?php elseif ($pg == enkripsi('tugas')) : ?>
    <?php include 'tugas/tugas.php'; ?>
<?php elseif ($pg == enkripsi('bagi')) : ?>
    <?php include 'tugas/guru.php'; ?>
<?php elseif ($pg == enkripsi('cetak2')) : ?>
    <?php include 'tugas/cetak2.php'; ?>

<!-- Mulai Rute yang ditambahkan dari file lama -->
<?php elseif ($pg == enkripsi('smasuk')) : ?>
    <?php include 'surat_masuk/smasuk.php'; ?>
<?php elseif ($pg == enkripsi('skeluar')) : ?>
    <?php include 'surat_keluar/skeluar.php'; ?>
<?php elseif ($pg == enkripsi('surat_masuk_staff')) : ?>
    <?php include 'surat_masuk/surat_masuk_staff.php'; ?>
<?php elseif ($pg == enkripsi('surat_masuk_kepsek')) : ?>
    <?php include 'surat_masuk/surat_masuk_kepsek.php'; ?>
<?php elseif ($pg == enkripsi('surat_masuk_guru')) : ?>
    <?php include 'surat_masuk/surat_masuk_guru.php'; ?>
<?php elseif ($pg == enkripsi('proposal')) : ?>
    <?php include 'kegiatan/proposal.php'; ?>
<?php elseif ($pg == enkripsi('kpi')) : ?>
    <?php include 'kpi/kpi.php'; ?>
<?php elseif ($pg == enkripsi('kpi_results')) : ?>
    <?php include 'kpi/kpi_results.php'; ?>
<?php elseif ($pg == enkripsi('kpi_user')) : ?>
    <?php include 'kpi/kpi_user.php'; ?>
<?php elseif ($pg == enkripsi('kpi_results_user')) : ?>
    <?php include 'kpi/kpi_results_user.php'; ?>
<?php elseif ($pg == enkripsi('kpi_validation')) : ?>
    <?php include 'kpi/kpi_validation.php'; ?>
<!-- Akhir Rute yang ditambahkan -->

<?php elseif ($pg == enkripsi('resetdata')) : ?>
    <?php include 'pengaturan/resetdata.php'; ?>	
<?php elseif ($pg == enkripsi('setcetak')) : ?>
    <?php include 'pengaturan/setcetak.php'; ?>		
<?php else : ?>
 <div class="app app-error align-content-stretch d-flex flex-wrap">
        <div class="app-error-info">
            <h5>Oops!</h5>
            <span>It seems that the page you are looking for no longer exists.<br>
                We will try our best to fix this soon.</span>
            <a href="." class="btn btn-dark">Go to dashboard</a>
        </div>
        <div class="app-error-background"></div>
    </div>

<?php endif ?>
<?php mysqli_query($koneksi,"DELETE FROM absensi_mapel WHERE tanggal<>'$tanggal'"); ?>