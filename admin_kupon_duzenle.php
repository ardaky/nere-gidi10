<?php

include 'header.php';

if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}


$kupon_uuid_duzenle = isset($_GET['kupon_uuid']) ? htmlspecialchars($_GET['kupon_uuid']) : '';

if (empty($kupon_uuid_duzenle)) {
    $_SESSION['hata_mesaji'] = "Düzenlenecek kupon ID'si belirtilmedi.";
    header("Location: admin_kupon_yonetimi.php");
    exit;
}


try {
    $sql = "SELECT * FROM Coupons WHERE uuid = ? AND company_id IS NULL"; 
    $sorgu = $vt->prepare($sql);
    $sorgu->execute([$kupon_uuid_duzenle]);
    $kupon = $sorgu->fetch(PDO::FETCH_ASSOC);

    if (!$kupon) {
        $_SESSION['hata_mesaji'] = "Düzenlenecek genel kupon bulunamadı.";
        header("Location: admin_kupon_yonetimi.php");
        exit;
    }

    
    $kupon['discount_percent'] = $kupon['discount'] * 100;
    $kupon['expire_date_formatted'] = date('Y-m-d', strtotime($kupon['expire_date']));

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Kupon bilgileri alınırken hata oluştu: " . $hata->getMessage();
    header("Location: admin_kupon_yonetimi.php");
    exit;
}


$hata_mesaji = $_SESSION['hata_mesaji'] ?? null; unset($_SESSION['hata_mesaji']);
?>

<h2 class="mb-4">Genel Kuponu Düzenle: <?php echo htmlspecialchars($kupon['code']); ?></h2>

<?php if ($hata_mesaji): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="islem_admin_kupon_duzenle.php" method="POST">
            <input type="hidden" name="kupon_uuid" value="<?php echo htmlspecialchars($kupon['uuid']); ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="code" class="form-label">Kupon Kodu</label>
                    <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($kupon['code']); ?>" readonly disabled>
                    <div class="form-text text-muted">Kupon kodu değiştirilemez.</div>
                </div>
                <div class="col-md-6">
                    <label for="discount" class="form-label">İndirim Oranı (%)</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="discount" name="discount" min="1" max="100" step="1" value="<?php echo htmlspecialchars($kupon['discount_percent']); ?>" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="usage_limit" class="form-label">Toplam Kullanım Limiti</label>
                    <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" value="<?php echo htmlspecialchars($kupon['usage_limit']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="expire_date" class="form-label">Son Kullanma Tarihi</label>
                    <input type="date" class="form-control" id="expire_date" name="expire_date" value="<?php echo $kupon['expire_date_formatted']; ?>" required>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning">Değişiklikleri Kaydet</button>
                    <a href="admin_kupon_yonetimi.php" class="btn btn-secondary">İptal</a>
                </div>
            </div>
        </form>
    </div>
</div>

</div>
<footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>
</body>
</html>