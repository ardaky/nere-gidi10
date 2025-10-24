<?php

include 'header.php';

if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'firma_admin' || !isset($_SESSION['firma_uuid'])) {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}


$firma_uuid = $_SESSION['firma_uuid']; 


try {
    $sorgu_firma = $vt->prepare("SELECT name FROM Bus_Company WHERE uuid = ?");
    $sorgu_firma->execute([$firma_uuid]);
    $firma = $sorgu_firma->fetch(PDO::FETCH_ASSOC);
    $firma_adi = $firma ? $firma['name'] : 'Bilinmeyen Firma';

    $sql_seferler = "SELECT * FROM Trips 
                     WHERE company_id = ? 
                     ORDER BY created_at DESC"; 
                     
    $sorgu_seferler = $vt->prepare($sql_seferler);
    $sorgu_seferler->execute([$firma_uuid]);
    $firma_seferleri = $sorgu_seferler->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $hata) {
    echo '<div class="alert alert-danger">Seferler yüklenirken bir hata oluştu: ' . $hata->getMessage() . '</div>';
    $firma_seferleri = []; 
    $firma_adi = 'Hata Oluştu';
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Firma Yönetim Paneli - <?php echo htmlspecialchars($firma_adi); ?></h2>
    <div>
        <a href="sefer_ekle.php" class="btn btn-primary me-2">
            <i class="bi bi-plus-circle"></i> Yeni Sefer Ekle
        </a>
        <a href="firma_kupon_yonetimi.php" class="btn btn-info">
            <i class="bi bi-tags"></i> Kuponları Yönet
        </a>
         </div>
</div>

<?php if (isset($_SESSION['basari_mesaji'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['basari_mesaji']; unset($_SESSION['basari_mesaji']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['hata_mesaji'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['hata_mesaji']; unset($_SESSION['hata_mesaji']); ?></div>
<?php endif; ?>


<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Mevcut Seferleriniz</h5>
    </div>
    <div class="card-body">
        <?php if (empty($firma_seferleri)): ?>
            <div class="alert alert-info">Henüz hiç sefer eklememişsiniz.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Güzergah</th>
                            <th>Kalkış Zamanı</th>
                            <th>Varış Zamanı</th>
                            <th>Fiyat (TL)</th>
                            <th>Kapasite</th>
                            <th>Eklenme Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($firma_seferleri as $sefer): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?php echo htmlspecialchars($sefer['departure_city']); ?>
                                    <i class="bi bi-arrow-right"></i>
                                    <?php echo htmlspecialchars($sefer['destination_city']); ?>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($sefer['departure_time'])); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($sefer['arrival_time'])); ?></td>
                                <td><?php echo htmlspecialchars($sefer['price']); ?></td>
                                <td><?php echo htmlspecialchars($sefer['capacity']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($sefer['created_at'])); ?></td>
                                <td>
                                    <a href="sefer_duzenle.php?sefer_uuid=<?php echo $sefer['uuid']; ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="bi bi-pencil-square"></i> Düzenle
                                    </a>
                                    <form action="islem_sefer_sil.php" method="POST" class="d-inline" onsubmit="return confirm('Bu seferi silmek istediğinize emin misiniz?');">
                                        <input type="hidden" name="sefer_uuid" value="<?php echo $sefer['uuid']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i> Sil
                                        </button>
                                    </form>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>


</div> <footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>