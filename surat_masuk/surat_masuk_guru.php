<?php
defined('APK') or exit('No accsess');

require_once __DIR__ . '/aktor_helper.php';

// Ambil user guru yang sedang login
$username_guru = isset($user['username']) ? trim((string)$user['username']) : '';
$id_guru = (int)($user['id_user'] ?? $_SESSION['user']['id_user'] ?? 0);

$userLevel = strtolower(trim((string)($user['level'] ?? $_SESSION['user']['level'] ?? $user['jabatan'] ?? $_SESSION['user']['jabatan'] ?? 'guru')));
$isAdmin = ($userLevel === 'admin');
$isKepsek = in_array($userLevel, ['kepala sekolah', 'kepsek', 'kepala_sekolah'], true);

// Ambil surat masuk yang ditujukan kepada guru ini (yang sudah didisposisi)
$suratMasuk = [];
if (isset($koneksi) && $koneksi && ($username_guru !== '' || $id_guru > 0)) {
	$aktorMatchSql = suratMasukAktorMatchSql($koneksi, $id_guru, $username_guru);

	if ($isKepsek) {
		$query = mysqli_query($koneksi, "SELECT * FROM surat_masuk WHERE disposisi IS NOT NULL AND disposisi != '' ORDER BY tanggal_masuk DESC, id DESC");
	} else {
		$query = mysqli_query($koneksi, "SELECT * FROM surat_masuk WHERE $aktorMatchSql AND disposisi IS NOT NULL AND disposisi != '' ORDER BY tanggal_masuk DESC, id DESC");
	}
	
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
	.surat-container { max-width: 1400px; margin: 14px auto; font-size: 14px; }
	.surat-card { background: var(--card-bg); border: 1px solid var(--card-border); padding: 20px; border-radius: 10px; box-shadow: 0 4px 14px rgba(15,23,42,.06); margin-bottom: 20px; }
	.surat-title { display:flex; align-items:center; gap:10px; margin: 0 0 20px; font-weight:700; font-size:18px; color:#0f172a; }
	.surat-title .dot { width:12px; height:12px; border-radius:50%; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); display:inline-block; }
	.surat-table-wrap { overflow:auto; border:1px solid var(--card-border); border-radius:10px; background:#fff; }
	.surat-table { width: 100%; border-collapse: separate; border-spacing: 0; }
	.surat-table thead th { position: sticky; top:0; background: var(--table-header); z-index:1; }
	.surat-table th, .surat-table td { border-bottom: 1px solid var(--card-border); padding: 12px 14px; vertical-align: top; }
	.surat-table tbody tr:hover { background:#fafafa; }
	.surat-table th { text-align: left; color:#111827; font-weight:700; font-size:13px; }
	.surat-table td { color:#111827; font-size:13px; }
	.surat-table tbody tr:nth-child(odd) { background:#fcfcff; }
	.info-box { background: #e8f4fd; border-left: 4px solid var(--brand); padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
	.info-box p { margin: 0; color: #1e40af; font-size: 14px; }
	.empty-state { text-align: center; padding: 40px 20px; color: #6b7280; }
	.empty-state i { font-size: 48px; color: #d1d5db; margin-bottom: 16px; }
</style>

<div class="surat-container">
	<div class="surat-card">
		<h3 class="surat-title">
			<span class="dot"></span>
			Surat Masuk - Ditujukan kepada Saya
		</h3>
		
		<?php if (!empty($username_guru)): ?>
		<div class="info-box">
			<p><strong>Username Anda:</strong> <?= htmlspecialchars($username_guru) ?></p>
			<p style="margin-top: 8px; font-size: 13px;">
				<?php if ($isKepsek): ?>
					Daftar seluruh surat masuk yang sudah didisposisi.
				<?php else: ?>
					Hanya surat yang ditujukan kepada Anda dan sudah didisposisi yang akan tampil di sini.
				<?php endif; ?>
			</p>
		</div>
		<?php endif; ?>
		
		<div class="surat-table-wrap">
			<table class="surat-table">
				<thead>
					<tr>
						<th width="5%">No</th>
						<th width="12%">Nomor Surat</th>
						<th width="10%">Tanggal Masuk</th>
						<th width="<?= $isKepsek ? '15%' : '20%' ?>">Perihal</th>
						<th width="<?= $isKepsek ? '12%' : '15%' ?>">Nama Instansi</th>
						<?php if ($isKepsek): ?>
						<th width="13%">Ditujukan Kepada</th>
						<?php endif; ?>
						<th width="<?= $isKepsek ? '20%' : '25%' ?>">Disposisi</th>
						<th width="13%">File</th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($suratMasuk)): ?>
					<tr>
						<td colspan="<?= $isKepsek ? 8 : 7 ?>" class="empty-state">
							<i class="material-icons-two-tone">mail_outline</i>
							<p style="font-size: 16px; margin-bottom: 8px;">Belum ada surat masuk yang ditujukan kepada Anda</p>
							<p style="font-size: 13px;">Surat masuk akan muncul di sini setelah admin menetapkan surat ditujukan kepada Anda dan sudah didisposisi.</p>
						</td>
					</tr>
					<?php else: ?>
					<?php $no = 1; foreach ($suratMasuk as $item): ?>
					<tr>
						<td><?= $no++; ?></td>
						<td><strong><?= htmlspecialchars($item['nomor_surat']); ?></strong></td>
						<td><?= htmlspecialchars($item['tanggal_masuk']); ?></td>
						<td><?= htmlspecialchars($item['perihal']); ?></td>
						<td><?= htmlspecialchars($item['nama_instansi']); ?></td>
						<?php if ($isKepsek): ?>
						<td><?= htmlspecialchars($item['aktor_tujuan'] ?? ''); ?></td>
						<?php endif; ?>
						<td><?= nl2br(htmlspecialchars($item['disposisi'])); ?></td>
						<td>
							<?php if (!empty($item['file_surat'])): ?>
							<a href="uploads/surat_masuk/<?= htmlspecialchars($item['file_surat']) ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: #e8f7ee; border: 1px solid #bbf7d0; color: #047857; text-decoration: none;" title="Download File">
								<i class="material-icons-two-tone" style="font-size: 18px;">download</i>
							</a>
							<?php else: ?>
							<span style="color: #9ca3af; font-size: 12px;">-</span>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		
		<?php if (!empty($suratMasuk)): ?>
		<div style="margin-top: 16px; padding: 12px; background: #f0f9ff; border-radius: 6px; font-size: 13px; color: #0369a1;">
			<strong>Total:</strong> <?= count($suratMasuk) ?> surat masuk
		</div>
		<?php endif; ?>
	</div>
</div>