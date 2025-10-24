<?php

include 'header.php';

if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'firma_admin' || !isset($_SESSION['firma_uuid'])) {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}

$firma_uuid = $_SESSION['firma_uuid']; 

$hata_mesaji = $_SESSION['hata_mesaji'] ?? null; unset($_SESSION['hata_mesaji']);
?>

<h2 class="mb-4">Yeni İndirim Kuponu Ekle</h2>

<?php if ($hata_mesaji): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="islem_kupon_ekle.php" method="POST">

            <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($firma_uuid); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="code" class="form-label">Kupon Kodu</label>
                    <input type="text" class="form-control" id="code" name="code" placeholder="Örn: INDIRIM20" required>
                    <div class="form-text">Kullanıcıların gireceği benzersiz kod (Sadece harf ve rakam önerilir).</div>
                </div>

                <div class="col-md-6">
                    <label for="discount" class="form-label">İndirim Oranı (%)</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="discount" name="discount" min="1" max="100" step="1" placeholder="Örn: 10" required>
                        <span class="input-group-text">%</span>
                    </div>
                     <div class="form-text">Uygulanacak indirim yüzdesi (1 ile 100 arası).</div>
                </div>

                <div class="col-md-6">
                    <label for="usage_limit" class="form-label">Toplam Kullanım Limiti</label>
                    <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" value="100" required>
                     <div class="form-text">Bu kuponun toplamda kaç defa kullanılabileceği.</div>
                </div>

                <div class="col-md-6">
                    <label for="expire_date" class="form-label">Son Kullanma Tarihi</label>
                    <input type="date" class="form-control" id="expire_date" name="expire_date" required>
                    <div class="form-text">Bu tarihten sonra kupon geçersiz olacaktır.</div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Kuponu Kaydet</button>
                    <a href="firma_kupon_yonetimi.php" class="btn btn-secondary">İptal</a>
                </div>
            </div> </form>
    </div> </div> </div> <footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>