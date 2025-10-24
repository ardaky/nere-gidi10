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

    $sql_kuponlar = "SELECT * FROM Coupons
                     WHERE company_id = ?
                     ORDER BY created_at DESC";
    $sorgu_kuponlar = $vt->prepare($sql_kuponlar);
    $sorgu_kuponlar->execute([$firma_uuid]);
    $firma_kuponlari = $sorgu_kuponlar->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $hata) {
    echo '<div class="alert alert-danger">Kuponlar yüklenirken bir hata oluştu: ' . $hata->getMessage() . '</div>';
    $firma_kuponlari = [];
    $firma_adi = 'Hata Oluştu';
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Kupon Yönetimi - <?php echo htmlspecialchars($firma_adi); ?></h2>
    <div>
        <a href="kupon_ekle.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Yeni Kupon Ekle
        </a>
        <a href="firma_admin_panel.php" class="btn btn-secondary">
             <i class="bi bi-arrow-left"></i> Seferlere Dön
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
        <h5 class="mb-0">Firmanıza Ait Kuponlar</h5>
    </div>
    <div class="card-body">
        <?php if (empty($firma_kuponlari)): ?>
            <div class="alert alert-info">Henüz hiç kupon oluşturmamışsınız.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Kupon Kodu</th>
                            <th>İndirim Oranı (%)</th>
                            <th>Kullanım Limiti</th>
                            <th>Son Kullanma Tarihi</th>
                            <th>Oluşturulma Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($firma_kuponlari as $kupon): ?>
                            <tr>
                                <td class="fw-bold text-danger"><?php echo htmlspecialchars($kupon['code']); ?></td>
                                <td><?php echo htmlspecialchars($kupon['discount'] * 100); ?>%</td> <td><?php echo htmlspecialchars($kupon['usage_limit']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($kupon['expire_date'])); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($kupon['created_at'])); ?></td>
                                <td>
                                    <a href="kupon_duzenle.php?kupon_uuid=<?php echo $kupon['uuid']; ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="bi bi-pencil-square"></i> Düzenle
                                    </a>
                                    <form action="islem_kupon_sil.php" method="POST" class="d-inline" onsubmit="return confirm('Bu kuponu silmek istediğinize emin misiniz?');">
                                        <input type="hidden" name="kupon_uuid" value="<?php echo $kupon['uuid']; ?>">
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