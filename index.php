<?php include"top.php"; ?>
<body>
<?php if($sidebar==''): ?>
<div class="app align-content-stretch d-flex flex-wrap" >
<?php else : ?>
<div class="app menu-off-canvas align-content-stretch d-flex flex-wrap">	
	<?php endif; ?>
        <div class="app-sidebar">
            <?php 
            $logoImg = !empty($setting['logo']) && file_exists(__DIR__ . '/images/' . $setting['logo']) ? $setting['logo'] : 'logo683.png';
            ?>
            <div class="logo" style="height:70px">
			 <div class="d-flex justify-content-between align-items-center mb-0" style="padding: 4px 0;">
                    <div class="text-center">
                     <img src="<?= $baseurl ?>/images/<?= $logoImg ?>" style="max-width:42px; height:auto; border-radius:50%;" alt="Logo Sekolah" >                      
                    </div>
                    <div class="hidden-on-mobile">
                      <strong><?= htmlspecialchars($user['nama']) ?></strong>
					  <p style="color:#10b981; font-weight:600; font-size:11px; margin:0;">online</p>
                    </div>
                    <div class="hidden-on-mobile text-end">         
                      <span style="font-weight:700; font-size:12px; color:#1e293b;"><?= htmlspecialchars($setting['sekolah']) ?></span>
					   <p style="color:#2563eb; font-weight:600; font-size:11px; margin:0;"><?= htmlspecialchars($setting['npsn']) ?></p>
                    </div>                    
                  </div>
            </div>
			
            <?php include"menu.php"; ?>
			 <div class="logo" style="height:70px;text-align:center">
			 SISTEM ADMINISTRASI
		  </div>
		 </div>
        <div class="app-container">
            <?php include"nav.php"; ?>
            <div class="app-content">
              <?php include"pages.php"; ?>	
             </div>
            </div>
        </div>
    </div>
   <?php include"footer.php"; ?>
</body>
</html>