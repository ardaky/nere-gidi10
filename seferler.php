<?php
include 'header.php';

$kalkis_sehri = isset($_GET['kalkis_sehri']) ? htmlspecialchars($_GET['kalkis_sehri']) : '';
$varis_sehri = isset($_GET['varis_sehri']) ? htmlspecialchars($_GET['varis_sehri']) : '';

$kalkis_sehri_duzeltilmis = duzelt_sehir_adi($kalkis_sehri);
$varis_sehri_duzeltilmis = duzelt_sehir_adi($varis_sehri);

$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$limit = 5;

$hata_mesaji = '';
$seferler = [];
$toplam_sayfa = 0;
$toplam_sefer = 0;

if (empty($kalkis_sehri) || empty($varis_sehri)) {
    $hata_mesaji = 'Lütfen kalkış ve varış yeri seçerek arama yapın.';
} elseif ($kalkis_sehri === $varis_sehri) {
    $hata_mesaji = 'Kalkış ve varış şehri aynı olamaz. Lütfen farklı şehirler seçin.';
} else {
    try {
        $sql_count = "SELECT COUNT(*) FROM Trips
                      WHERE departure_city = ?
                      AND destination_city = ?
                      AND departure_time > datetime('now', '+3 hour')";
        $sorgu_count = $vt->prepare($sql_count);
        $sorgu_count->execute([$kalkis_sehri_duzeltilmis, $varis_sehri_duzeltilmis]);
        $toplam_sefer = $sorgu_count->fetchColumn();

        if ($toplam_sefer > 0) {
            $toplam_sayfa = ceil($toplam_sefer / $limit);

            if ($sayfa < 1) { $sayfa = 1; }
            if ($sayfa > $toplam_sayfa) { $sayfa = $toplam_sayfa; }

            $offset = ($sayfa - 1) * $limit;

            $sql = "SELECT Trips.*, Bus_Company.name AS firma_adi, Bus_Company.logo_path
                    FROM Trips
                    JOIN Bus_Company ON Trips.company_id = Bus_Company.uuid
                    WHERE departure_city = ?
                    AND destination_city = ?
                    AND departure_time > datetime('now', '+3 hour')
                    ORDER BY departure_time ASC
                    LIMIT ? OFFSET ?";

            $sorgu = $vt->prepare($sql);
            $sorgu->bindValue(1, $kalkis_sehri_duzeltilmis);
            $sorgu->bindValue(2, $varis_sehri_duzeltilmis);
            $sorgu->bindValue(3, $limit, PDO::PARAM_INT);
            $sorgu->bindValue(4, $offset, PDO::PARAM_INT);
            $sorgu->execute();
            $seferler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (PDOException $hata) {
        $hata_mesaji = "Veritabanı hatası: " . $hata->getMessage();
    }
}
?>

<h2 class="mb-4">
    Arama Sonuçları:
    <span class="text-primary"><?php echo $kalkis_sehri; ?></span>
    <i class="bi bi-arrow-right"></i>
    <span class="text-primary"><?php echo $varis_sehri; ?></span>
</h2>

<?php if (!empty($hata_mesaji)): ?>
    <div class="alert alert-danger" role="alert">
        <strong>Hata!</strong> <?php echo $hata_mesaji; ?>
    </div>
    <a href="index.php" class="btn btn-secondary">Geri Dön</a>

<?php elseif ($toplam_sefer > 0 && !empty($seferler)): ?>
    <?php
        $fmt = new IntlDateFormatter(
            'tr_TR',
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT,
            'Europe/Istanbul',
            IntlDateFormatter::GREGORIAN,
            'dd MMMM yyyy, HH:mm'
        );
    ?>
    <?php foreach ($seferler as $sefer): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 d-flex align-items-center">
                        <?php $logo_yolu = $sefer['logo_path']; if (!empty($logo_yolu) && file_exists($logo_yolu)): ?>
                            <img src="<?php echo htmlspecialchars($logo_yolu); ?>" alt="<?php echo htmlspecialchars($sefer['firma_adi']); ?> Logosu" class="me-2" style="height: 40px; max-width: 90px; object-fit: contain;">
                        <?php endif; ?>
                        <h5 class="card-title mb-0 ms-3"><?php echo htmlspecialchars($sefer['firma_adi']); ?></h5>
                    </div>
                    <div class="col-md-4">
                        <p class="card-text mb-0"><strong>Kalkış:</strong> <?php echo $fmt->format(strtotime($sefer['departure_time'])); ?></p>
                        <p class="card-text"><strong>Varış:</strong> <?php echo $fmt->format(strtotime($sefer['arrival_time'])); ?></p>
                    </div>
                    <div class="col-md-2">Kapasite: <?php echo htmlspecialchars($sefer['capacity']); ?></div>
                    <div class="col-md-3 text-end">
                        <h4 class="text-primary"><?php echo htmlspecialchars($sefer['price']); ?> TL</h4>
                        <?php if (isset($_SESSION['kullanici_uuid']) && $_SESSION['kullanici_rolu'] == 'user'): ?>
                            <a href="biletal.php?sefer_id=<?php echo $sefer['uuid']; ?>" class="btn btn-primary">Bilet Al</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-secondary" title="Bilet almak için Yolcu (User) rolüne sahip olmalısınız.">Satın Almak İçin Giriş Yap</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ($toplam_sayfa > 1): ?>
        <nav aria-label="Sefer Sayfaları">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if($sayfa <= 1){ echo 'disabled'; } ?>">
                    <a class="page-link" href="?kalkis_sehri=<?php echo urlencode($kalkis_sehri); ?>&varis_sehri=<?php echo urlencode($varis_sehri); ?>&sayfa=<?php echo $sayfa - 1; ?>">Önceki</a>
                </li>
                <?php for ($i = 1; $i <= $toplam_sayfa; $i++): ?>
                    <li class="page-item <?php if($sayfa == $i) {echo 'active'; } ?>">
                        <a class="page-link" href="?kalkis_sehri=<?php echo urlencode($kalkis_sehri); ?>&varis_sehri=<?php echo urlencode($varis_sehri); ?>&sayfa=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php if($sayfa >= $toplam_sayfa) { echo 'disabled'; } ?>">
                    <a class="page-link" href="?kalkis_sehri=<?php echo urlencode($kalkis_sehri); ?>&varis_sehri=<?php echo urlencode($varis_sehri); ?>&sayfa=<?php echo $sayfa + 1; ?>">Sonraki</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

<?php else: ?>
    <div class="alert alert-warning" role="alert">
        <strong>Üzgünüz!</strong> Belirttiğiniz kriterlere uygun ('<?php echo $kalkis_sehri; ?>' -> '<?php echo $varis_sehri; ?>') aktif bir sefer bulunamadı.
    </div>
<?php endif; ?>

</div>
<footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>