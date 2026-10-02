<div class="app-menu">
    <ul class="accordion-menu">
        <li><a href="."><i class="material-icons-two-tone">home</i>Beranda</a></li>

        <?php if($user['level'] == 'admin'): ?>
        <li>
            <a href="#"><i class="material-icons-two-tone">menu</i>Master Surat<i class="material-icons has-sub-menu">keyboard_arrow_down</i></a>
            <ul class="sub-menu">
                <li><a href="?pg=<?= enkripsi('smasuk') ?>">Surat Masuk</a></li>
                <li><a href="?pg=<?= enkripsi('skeluar') ?>">Surat Keluar</a></li>
            </ul>
        </li>
        <?php endif; ?>

        <?php if($user['level'] == 'staff'): ?>
        <li>
            <a href="#"><i class="material-icons-two-tone">mail</i>Surat Masuk<i class="material-icons has-sub-menu">keyboard_arrow_down</i></a>
            <ul class="sub-menu">
                <li><a href="?pg=<?= enkripsi('smasuk') ?>">Input &amp; Data Surat Masuk</a></li>
                <li><a href="?pg=<?= enkripsi('surat_masuk_staff') ?>">Surat Saya</a></li>
            </ul>
        </li>
        <li>
            <a href="#"><i class="material-icons-two-tone">send</i>Surat Keluar<i class="material-icons has-sub-menu">keyboard_arrow_down</i></a>
            <ul class="sub-menu">
                <li><a href="?pg=<?= enkripsi('skeluar') ?>">Kelola Surat</a></li>
            </ul>
        </li>
        <?php endif; ?>

        <?php
        // PENYESUAIAN LEVEL BARU: Ditambahkan kesesuaian dengan database baru
        $guruLikeLevels = ['guru', 'wakasis', 'kesiswaan', 'wakakur', 'waka_kurikulum', 'kaprog', 'keprog'];
        $guruLikeJabatans = ['guru', 'Guru', 'Wakil Kesiswaan', 'Kesiswaan', 'Wakil Kurikulum', 'Waka Kurikulum', 'Kepala Program', 'kaprog', 'keprog'];
        $isGuruLike = in_array($user['level'], $guruLikeLevels) || in_array($user['jabatan'], $guruLikeJabatans);
        $isKepsekMenu = ($user['level'] == 'kepsek' || $user['jabatan'] == 'Kepala Sekolah');
        if ($isGuruLike || $isKepsekMenu):
        ?>
        <li>
            <a href="#"><i class="material-icons-two-tone">mail</i>Surat Masuk<i class="material-icons has-sub-menu">keyboard_arrow_down</i></a>
            <ul class="sub-menu">
                <?php if ($isKepsekMenu): ?>
                    <li><a href="?pg=<?= enkripsi('surat_masuk_kepsek') ?>">Surat Masuk Sekolah</a></li>
                <?php else: ?>
                    <li><a href="?pg=<?= enkripsi('surat_masuk_guru') ?>">Surat Saya</a></li>
                <?php endif; ?>
            </ul>
        </li>
        <li>
            <a href="#"><i class="material-icons-two-tone">send</i>Surat Keluar<i class="material-icons has-sub-menu">keyboard_arrow_down</i></a>
            <ul class="sub-menu">
                <li><a href="?pg=<?= enkripsi('skeluar') ?>">Ajukan Surat</a></li>
            </ul>
        </li>
        <?php endif; ?>

        <?php
        // PENYESUAIAN LEVEL BARU: Ditambahkan kesesuaian dengan database baru untuk menu Kegiatan
        $kegiatanLevels = ['admin', 'guru', 'kepsek', 'ketua_yayasan', 'keuangan', 'wakasis', 'kesiswaan', 'wakakur', 'waka_kurikulum', 'pembina_yayasan', 'kaprog', 'keprog', 'yayasan'];
        $kegiatanJabatans = ['guru', 'Guru', 'Kepala Sekolah', 'kepsek', 'Yayasan', 'Pembina Yayasan', 'Keuangan', 'Bendahara'];
        $canAccessKegiatan = in_array($user['level'], $kegiatanLevels) || in_array($user['jabatan'], $kegiatanJabatans);
        
        $isPembinaLevel = in_array($user['level'], ['pembina_yayasan']) || in_array($user['jabatan'], ['Pembina Yayasan', 'pembina_yayasan']);
        $isYayasanValidatorLevel = in_array($user['level'], ['yayasan', 'ketua_yayasan']) || in_array($user['jabatan'], ['Ketua Yayasan', 'ketua_yayasan', 'Yayasan']);
        $isKepsekLevel = in_array($user['level'], ['kepsek', 'kepala_sekolah']) || in_array($user['jabatan'], ['Kepala Sekolah', 'kepsek']);
        if ($canAccessKegiatan):
        ?>
        <li>
            <a href="#"><i class="material-icons-two-tone">event</i>Kegiatan<i class="material-icons has-sub-menu">keyboard_arrow_down</i></a>
            <ul class="sub-menu">
                <?php if ($isPembinaLevel): ?>
                    <li><a href="?pg=<?= enkripsi('proposal') ?>">Data Proposal</a></li>
                <?php elseif ($isYayasanValidatorLevel): ?>
                    <li><a href="?pg=<?= enkripsi('proposal') ?>&view=validation">Validasi Proposal</a></li>
                    <li><a href="?pg=<?= enkripsi('proposal') ?>">Data Proposal</a></li>
                <?php elseif ($isKepsekLevel): ?>
                    <li><a href="?pg=<?= enkripsi('proposal') ?>">Proposal Saya</a></li>
                    <li><a href="?pg=<?= enkripsi('proposal') ?>&view=validation">Validasi Proposal</a></li>
                <?php else: ?>
                    <li><a href="?pg=<?= enkripsi('proposal') ?>">Proposal</a></li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>

        <?php
        // KPI Admin Menu - for admin only (kepsek and yayasan now use validation only)
        $kpiAdminLevels = ['admin'];
        $canAccessKpiAdmin = in_array($user['level'], $kpiAdminLevels);
        if ($canAccessKpiAdmin):
        ?>
        <li>
            <a href="#"><i class="material-icons-two-tone">analytics</i>KPI<i class="material-icons has-sub-menu">keyboard_arrow_down</i></a>
            <ul class="sub-menu">
                <li><a href="?pg=<?= enkripsi('kpi') ?>">Master KPI</a></li>
                <li><a href="?pg=<?= enkripsi('kpi_results') ?>">Hasil Evaluasi KPI</a></li>
            </ul>
        </li>
        <?php endif; ?>

        <?php
        // KPI Manajemen Menu - Gabungan Hasil KPI Saya & Penilaian KPI Subordinat
        $kpiUserLevels = ['guru', 'keprog', 'kesiswaan', 'wakasis', 'waka_kurikulum', 'wakakur', 'kepsek'];
        $validatorLevels = ['keprog', 'waka_kurikulum', 'kepsek', 'yayasan', 'ketua_yayasan'];
        
        $canAccessKpiUser = in_array($user['level'], $kpiUserLevels) || in_array($user['jabatan'], ['Guru', 'Kepala Program', 'Wakil Kesiswaan', 'Wakil Kurikulum', 'Kepala Sekolah']);
        $isValidator = in_array($user['level'], $validatorLevels) || in_array($user['jabatan'], ['Kepala Program', 'kaprog', 'Wakil Kurikulum', 'wakakur', 'Kepala Sekolah', 'kepsek', 'Ketua Yayasan', 'ketua_yayasan']);
        
        if (($canAccessKpiUser || $isValidator) && !$canAccessKpiAdmin):
        ?>
        <li>
            <a href="#"><i class="material-icons-two-tone">assessment</i>Manajemen KPI<i class="material-icons has-sub-menu">keyboard_arrow_down</i></a>
            <ul class="sub-menu">
                <?php if ($canAccessKpiUser): ?>
                    <li><a href="?pg=<?= enkripsi('kpi_results_user') ?>">Hasil KPI Saya</a></li>
                <?php endif; ?>
                
                <?php if (in_array($user['level'], ['keprog']) || in_array($user['jabatan'], ['Kepala Program', 'kaprog'])): ?>
                    <li><a href="?pg=<?= enkripsi('kpi_validation') ?>&type=guru">Penilaian KPI Guru</a></li>
                <?php endif; ?>
                <?php if (in_array($user['level'], ['waka_kurikulum']) || in_array($user['jabatan'], ['Wakil Kurikulum', 'wakakur'])): ?>
                    <li><a href="?pg=<?= enkripsi('kpi_validation') ?>&type=kaprog">Penilaian KPI Kepala Program</a></li>
                <?php endif; ?>
                <?php if (in_array($user['level'], ['kepsek']) || in_array($user['jabatan'], ['Kepala Sekolah', 'kepsek'])): ?>
                    <li><a href="?pg=<?= enkripsi('kpi_validation') ?>&type=wakasis">Penilaian KPI Wakil Kesiswaan</a></li>
                    <li><a href="?pg=<?= enkripsi('kpi_validation') ?>&type=wakakur">Penilaian KPI Wakil Kurikulum</a></li>
                <?php endif; ?>
                <?php if (in_array($user['level'], ['yayasan', 'ketua_yayasan']) || in_array($user['jabatan'], ['Yayasan', 'Ketua Yayasan'])): ?>
                    <li><a href="?pg=<?= enkripsi('kpi_validation') ?>&type=kepsek">Penilaian KPI Kepala Sekolah</a></li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>
        
        <li><a href="logout.php"><i class="material-icons-two-tone">logout</i>Logout</a></li>
    </ul>
</div>