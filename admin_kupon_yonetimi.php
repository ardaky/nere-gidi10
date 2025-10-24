<?php

include 'header.php';


if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}


try {
    
    $sql_kuponlar = "SELECT * FROM Coupons
                     WHERE company_id IS NULL
                     ORDER BY created_at DESC";
    $sorgu_kuponlar = $vt->query($sql_kuponlar);
    $genel_kuponlar = $sorgu_kuponlar->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $hata) {
    echo '<div class="alert alert-danger">Genel kuponlar yüklenirken bir hata oluştu: ' . $hata->getMessage() . '</div>';
    $genel_kuponlar = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Genel Kupon Yönetimi (Tüm Firmalar)</h2>
    <div>
        <a href="admin_kupon_ekle.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Yeni Genel Kupon Ekle
        </a>
         <a href="admin_panel.php" class="btn btn-secondary">
             <i class="bi bi-arrow-left"></i> Admin Paneline Dön
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
        <h5 class="mb-0">Tüm Firmalarda Geçerli Kuponlar</h5>
    </div>
    <div class="card-body">
        <?php if (empty($genel_kuponlar)): ?>
            <div class="alert alert-info">Henüz hiç genel kupon oluşturulmamış.</div>
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
                        <?php foreach ($genel_kuponlar as $kupon): ?>
                            <tr>
                                <td class="fw-bold text-danger"><?php echo htmlspecialchars($kupon['code']); ?></td>
                                <td><?php echo htmlspecialchars($kupon['discount'] * 100); ?>%</td>
                                <td><?php echo htmlspecialchars($kupon['usage_limit']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($kupon['expire_date'])); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($kupon['created_at'])); ?></td>
                                <td>
                                    <a href="admin_kupon_duzenle.php?kupon_uuid=<?php echo $kupon['uuid']; ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="bi bi-pencil-square"></i> Düzenle
                                    </a>
                                    <form action="islem_admin_kupon_sil.php" method="POST" class="d-inline" onsubmit="return confirm('Bu genel kuponu silmek istediğinize emin misiniz?');">
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

</div>
<footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>
</body>
</html>