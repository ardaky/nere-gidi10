<?php

include 'header.php';

if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}


$hata_mesaji = $_SESSION['hata_mesaji'] ?? null; unset($_SESSION['hata_mesaji']);
?>

<h2 class="mb-4">Yeni Genel Kupon Ekle (Tüm Firmalar)</h2>

<?php if ($hata_mesaji): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="islem_admin_kupon_ekle.php" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="code" class="form-label">Kupon Kodu</label>
                    <input type="text" class="form-control" id="code" name="code" placeholder="Örn: HOSGELDIN15" required>
                    <div class="form-text">Kullanıcıların gireceği benzersiz kod.</div>
                </div>

                <div class="col-md-6">
                    <label for="discount" class="form-label">İndirim Oranı (%)</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="discount" name="discount" min="1" max="100" step="1" placeholder="Örn: 15" required>
                        <span class="input-group-text">%</span>
                    </div>
                     <div class="form-text">Uygulanacak indirim yüzdesi (1 ile 100 arası).</div>
                </div>

                <div class="col-md-6">
                    <label for="usage_limit" class="form-label">Toplam Kullanım Limiti</label>
                    <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" value="1000" required> <div class="form-text">Bu kuponun toplamda kaç defa kullanılabileceği.</div>
                </div>

                <div class="col-md-6">
                    <label for="expire_date" class="form-label">Son Kullanma Tarihi</label>
                    <input type="date" class="form-control" id="expire_date" name="expire_date" required>
                    <div class="form-text">Bu tarihten sonra kupon geçersiz olacaktır.</div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Genel Kuponu Kaydet</button>
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