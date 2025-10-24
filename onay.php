<?php

include 'header.php';

if (!isset($_SESSION['kullanici_uuid'])) {
    $_SESSION['hata_mesaji'] = "İşlem yapmak için giriş yapmalısınız.";
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] !== 'user') {
    $_SESSION['hata_mesaji'] = "Yetkiniz yok. Sadece Yolcu (User) rolündeki kullanıcılar bilet satın alabilir.";
    header("Location: index.php");
    exit;
}

$hata_mesaji_onceki = $_SESSION['hata_mesaji'] ?? null; unset($_SESSION['hata_mesaji']);
$basari_mesaji_onceki = $_SESSION['basari_mesaji'] ?? null; unset($_SESSION['basari_mesaji']);

$kullanici_uuid = $_SESSION['kullanici_uuid'];
$sefer_uuid = isset($_POST['sefer_uuid']) ? htmlspecialchars($_POST['sefer_uuid']) : '';
$secilen_koltuklar = isset($_POST['koltuklar']) ? array_map('htmlspecialchars', (array)$_POST['koltuklar']) : [];
$kupon_kodu = isset($_POST['kupon_kodu']) ? trim(htmlspecialchars($_POST['kupon_kodu'])) : '';

if (empty($secilen_koltuklar)) {
    $_SESSION['hata_mesaji'] = "Hiç koltuk seçmediniz! Lütfen en az bir koltuk seçin.";
    header("Location: bilet_al.php?sefer_id=" . $sefer_uuid);
    exit;
}

try {
    $sql_sefer = "SELECT t.price, t.departure_city, t.destination_city, t.departure_time, bc.name AS firma_adi 
                  FROM Trips t
                  JOIN Bus_Company bc ON t.company_id = bc.uuid
                  WHERE t.uuid = ?";
    $sorgu_sefer = $vt->prepare($sql_sefer);
    $sorgu_sefer->execute([$sefer_uuid]);
    $sefer = $sorgu_sefer->fetch(PDO::FETCH_ASSOC);

    $sorgu_bakiye = $vt->prepare("SELECT balance FROM User WHERE uuid = ?");
    $sorgu_bakiye->execute([$kullanici_uuid]);
    $kullanici = $sorgu_bakiye->fetch(PDO::FETCH_ASSOC);

    if (!$sefer || !$kullanici) {
        throw new Exception("Sefer veya kullanıcı bulunamadı.");
    }
    
    $koltuk_fiyati = $sefer['price'];
    $kullanici_bakiyesi = $kullanici['balance'];
    $toplam_fiyat = $koltuk_fiyati * count($secilen_koltuklar);
    
    $indirim_tutari = 0;
    $son_fiyat = $toplam_fiyat - $indirim_tutari;

} catch (Exception $hata) {
    $hata_mesaji_simdiki = "Bir hata oluştu: " . $hata->getMessage();
    $sefer = ['departure_city'=>'Bilinmiyor', 'destination_city'=>'Bilinmiyor', 'departure_time'=>'', 'firma_adi'=>'Bilinmiyor', 'price'=>0];
    $kullanici = ['balance'=>0];
    $son_fiyat = 0;
}

$bakiye_yeterli_mi = ($kullanici_bakiyesi >= $son_fiyat);
?>

<h2 class="mb-4">Satın Alma Onayı</h2>

<?php if (!empty($basari_mesaji_onceki)): ?>
    <div class="alert alert-success"><?php echo $basari_mesaji_onceki; ?></div>
<?php endif; ?>
<?php if (!empty($hata_mesaji_onceki)): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji_onceki; ?></div>
<?php endif; ?>
<?php if (!empty($hata_mesaji_simdiki)): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji_simdiki; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-lg-5"> <div class="row g-5"> <div class="col-lg-7">
                <h4 class="mb-3">Bilet Detayları</h4>
                <div class="border rounded p-3 mb-3 bg-light"> <p class="mb-1"><strong>Firma:</strong> <?php echo htmlspecialchars($sefer['firma_adi']); ?></p>
                    <h5 class="my-2">
                        <span class="text-primary fw-bold"><?php echo htmlspecialchars($sefer['departure_city']); ?></span> 
                        <i class="bi bi-arrow-right mx-1"></i> 
                        <span class="text-primary fw-bold"><?php echo htmlspecialchars($sefer['destination_city']); ?></span>
                    </h5>
                    <p class="mb-0"><strong>Tarih:</strong> <?php echo date('d M Y, H:i', strtotime($sefer['departure_time'])); ?></p>
                </div>
                
                <h5 class="mb-2">Seçilen Koltuklar:</h5>
                <p>
                    <?php foreach ($secilen_koltuklar as $koltuk): ?>
                        <span class="badge bg-danger fs-5 me-1"><?php echo $koltuk; ?></span>
                    <?php endforeach; ?>
                </p>
            </div>

            <div class="col-lg-5">
                <h4 class="mb-3">Ödeme Bilgileri</h4>
                <div class="border rounded p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Mevcut Bakiyeniz:</span>
                        <span class="fw-bold text-success"><?php echo $kullanici_bakiyesi; ?> TL</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Toplam Tutar:</span>
                        <span class="fw-bold text-danger fs-5"><?php echo $son_fiyat; ?> TL</span>
                    </div>
                    
                    <hr>

                    <?php if ($bakiye_yeterli_mi): ?>
                        <p class="text-success mb-3"><i class="bi bi-check-circle-fill"></i> Bakiyeniz bu işlem için yeterli.</p>
                        
                        <form action="islem_bilet_al.php" method="POST">
                            <input type="hidden" name="sefer_uuid" value="<?php echo htmlspecialchars($sefer_uuid); ?>">
                            <input type="hidden" name="kupon_kodu" value="<?php echo htmlspecialchars($kupon_kodu); ?>">
                            <?php foreach ($secilen_koltuklar as $koltuk): ?>
                                <input type="hidden" name="koltuklar[]" value="<?php echo htmlspecialchars($koltuk); ?>">
                            <?php endforeach; ?>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Onaylıyorum ve Satın Al</button>
                            </div>
                        </form>
                        
                    <?php else: ?>
                        <p class="text-danger mb-3"><i class="bi bi-exclamation-triangle-fill"></i> <strong>Bakiyeniz yetersiz!</strong> <?php echo ($son_fiyat - $kullanici_bakiyesi); ?> TL daha gereklidir.</p>
                        
                        <form action="islem_bakiye_ekle.php" method="POST" class="row g-2 align-items-center">
                            <input type="hidden" name="sefer_uuid" value="<?php echo htmlspecialchars($sefer_uuid); ?>">
                            <input type="hidden" name="kupon_kodu" value="<?php echo htmlspecialchars($kupon_kodu); ?>">
                            <?php foreach ($secilen_koltuklar as $koltuk): ?>
                                <input type="hidden" name="koltuklar[]" value="<?php echo htmlspecialchars($koltuk); ?>">
                            <?php endforeach; ?>

                            <div class="col">
                                <label for="eklenecek_tutar" class="visually-hidden">Eklenecek Miktar</label>
                                <input type="number" id="eklenecek_tutar" name="eklenecek_tutar" class="form-control" placeholder="Eklenecek Tutar (TL)" min="1" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success">Bakiye Ekle</button>
                            </div>
                        </form>
                        
                    <?php endif; ?>
                </div> </div> </div> </div> </div> </div> <footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> BiletProjesi. Tüm hakları saklıdır.
</footer>
</body>
</html>