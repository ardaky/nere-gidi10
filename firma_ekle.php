<?php

include 'header.php';


if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}

$hata_mesaji = $_SESSION['hata_mesaji'] ?? null; unset($_SESSION['hata_mesaji']);
?>

<h2 class="mb-4">Yeni Otobüs Firması Ekle</h2>

<?php if ($hata_mesaji): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="islem_firma_ekle.php" method="POST" enctype="multipart/form-data"> <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Firma Adı</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    <div class="form-text">Otobüs firmasının tam adı.</div>
                </div>

                <div class="col-md-6">
                    <label for="logo" class="form-label">Firma Logosu (Opsiyonel)</label>
                    <input class="form-control" type="file" id="logo" name="logo">
                    <div class="form-text">Logoyu kare formatında yüklemeniz önerilir (örn: .png, .jpg).</div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Firmayı Kaydet</button>
                    <a href="admin_panel.php" class="btn btn-secondary">İptal</a>
                </div>
            </div> </form>
    </div> </div> </div> <footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>