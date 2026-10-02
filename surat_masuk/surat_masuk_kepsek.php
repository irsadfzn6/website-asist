<?php
defined('APK') or exit('No accsess');

// Kepsek melihat SEMUA surat masuk di sekolahnya (dengan filter unit_sekolah)

// Ambil data user kepsek
$userLevel = strtolower(trim((string)($user['level'] ?? $_SESSION['user']['level'] ?? '')));
$user_unit_sekolah = $user['unit_sekolah'] ?? $_SESSION['unit_sekolah'] ?? '';

$isYayasan = ($userLevel === 'yayasan');

// Filter unit sekolah - yayasan bisa lihat semua, kepsek hanya unit sendiri
$unitFilter = '';
if (!$isYayasan && !empty($user_unit_sekolah)) {
	$escUnit = mysqli_real_escape_string($koneksi, $user_unit_sekolah);
	$unitFilter = " WHERE unit_sekolah = '$escUnit'";
}

// Ambil semua surat masuk di sekolah kepsek
$suratMasuk = [];
if (isset($koneksi) && $koneksi) {
	$query = mysqli_query($koneksi, "SELECT * FROM surat_masuk $unitFilter ORDER BY tanggal_masuk DESC, id DESC");
	if ($query) {
		while ($row = mysqli_fetch_array($query)) {
			$suratMasuk[] = $row;
		}
	}
}

?>

<style>
	:root {
		--card-bg: #ffffff;
		--card-border: #e6e9ef;
		--muted-text: #6b7280;
		--brand: #4f46e5;
		--brand-dark: #4338ca;
		--table-header: #f8fafc;
	}
</style>

<div class="row">
	<div class="col-12">
		<div class="card card-primary card-outline">
			<div class="card-header">
				<h3 class="card-title" style="color: var(--brand); font-weight: 600;">
					<i class="fas fa-envelope-open-text"></i> Surat Masuk - Semua Surat Sekolah
				</h3>
			</div>
			<div class="card-body">
				<div class="alert alert-info">
					<strong>Username Anda:</strong> <?= htmlspecialchars($user['username'] ?? '-') ?><br>
					<small>Daftar seluruh surat masuk di sekolah Anda.</small>
				</div>

				<div class="table-responsive">
					<table class="table table-hover" id="tabelSuratMasukKepsek">
						<thead style="background: var(--table-header);">
							<tr>
								<th style="width: 40px;">No</th>
								<th>Nomor Surat</th>
								<th>Tanggal Masuk</th>
								<th>Perihal</th>
								<th>Nama Instansi</th>
								<th>Ditujukan Kepada</th>
								<th>Disposisi</th>
								<th>File</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($suratMasuk)): ?>
								<?php foreach ($suratMasuk as $i => $sm): ?>
									<tr>
										<td><?= $i + 1 ?></td>
										<td><?= htmlspecialchars($sm['nomor_surat']) ?></td>
										<td><?= htmlspecialchars($sm['tanggal_masuk']) ?></td>
										<td><?= htmlspecialchars($sm['perihal']) ?></td>
										<td><?= htmlspecialchars($sm['nama_instansi']) ?></td>
										<td><?= htmlspecialchars($sm['aktor_tujuan']) ?></td>
										<td><?= htmlspecialchars($sm['disposisi']) ?></td>
										<td>
											<?php if (!empty($sm['file_surat'])): ?>
												<a href="uploads/surat_masuk/<?= htmlspecialchars($sm['file_surat']) ?>" target="_blank" class="btn btn-sm btn-success">
													<i class="fas fa-download"></i> Unduh
												</a>
											<?php else: ?>
												<span class="text-muted">-</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="8" class="text-center text-muted py-4">Tidak ada surat masuk di sekolah Anda.</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="alert alert-light mt-3">
					<strong>Total:</strong> <?= count($suratMasuk) ?> surat masuk
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	$('#tabelSuratMasukKepsek').DataTable({
		paging: true,
		searching: true,
		ordering: true,
		info: true,
		order: [[2, 'desc']]
	});
});
</script>
