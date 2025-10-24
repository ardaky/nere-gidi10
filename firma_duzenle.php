<?php
include 'header.php';


if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}

$firma_uuid_duzenle = isset($_GET['firma_uuid']) ? htmlspecialchars($_GET['firma_uuid']) : '';

if (empty($firma_uuid_duzenle)) {
    $_SESSION['hata_mesaji'] = "Düzenlenecek firma ID'si belirtilmedi.";
    header("Location: admin_panel.php");
    exit;
}

try {
    $sql = "SELECT * FROM Bus_Company WHERE uuid = ?";
    $sorgu = $vt->prepare($sql);
    $sorgu->execute([$firma_uuid_duzenle]);
    $firma = $sorgu->fetch(PDO::FETCH_ASSOC);

    if (!$firma) {
        $_SESSION['hata_mesaji'] = "Düzenlenecek firma bulunamadı.";
        header("Location: admin_panel.php");
        exit;
    }
} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Firma bilgileri alınırken hata oluştu: " . $hata->getMessage();
    header("Location: admin_panel.php");
    exit;
}

$hata_mesaji = $_SESSION['hata_mesaji'] ?? null; unset($_SESSION['hata_mesaji']);
?>

<h2 class="mb-4">Firmayı Düzenle: <?php echo htmlspecialchars($firma['name']); ?></h2>

<?php if ($hata_mesaji): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="islem_firma_duzenle.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="firma_uuid" value="<?php echo htmlspecialchars($firma['uuid']); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Firma Adı</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($firma['name']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="logo" class="form-label">Firma Logosu (Değiştirmek için yenisini seçin)</label>
                    <?php if (!empty($firma['logo_path']) && file_exists($firma['logo_path'])): ?>
                        <div class="mb-2">
                            <img src="<?php echo htmlspecialchars($firma['logo_path']); ?>" alt="Mevcut Logo" height="50">
                            <input type="checkbox" name="logoyu_kaldir" id="logoyu_kaldir" value="1" class="form-check-input ms-2">
                            <label for="logoyu_kaldir" class="form-check-label">Logoyu Kaldır</label>
                        </div>
                    <?php endif; ?>
                    <input class="form-control" type="file" id="logo" name="logo">
                    <div class="form-text">Yeni logo yüklerseniz eskisi silinir.</div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning">Değişiklikleri Kaydet</button>
                    <a href="admin_panel.php" class="btn btn-secondary">İptal</a>
                </div>
            </div> </form>
    </div> </div> </div> <footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>