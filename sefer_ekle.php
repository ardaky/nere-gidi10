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

<h2 class="mb-4">Yeni Sefer Ekle</h2>

<?php if ($hata_mesaji): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="islem_sefer_ekle.php" method="POST">
            
            <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($firma_uuid); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="departure_city" class="form-label">Kalkış Şehri</label>
                    <input type="text" class="form-control" id="departure_city" name="departure_city" required>
                </div>
                <div class="col-md-6">
                    <label for="destination_city" class="form-label">Varış Şehri</label>
                    <input type="text" class="form-control" id="destination_city" name="destination_city" required>
                </div>

                <div class="col-md-6">
                    <label for="departure_time" class="form-label">Kalkış Tarihi ve Saati</label>
                    <input type="datetime-local" class="form-control" id="departure_time" name="departure_time" required>
                </div>
                <div class="col-md-6">
                    <label for="arrival_time" class="form-label">Varış Tarihi ve Saati</label>
                    <input type="datetime-local" class="form-control" id="arrival_time" name="arrival_time" required>
                </div>

                <div class="col-md-6">
                    <label for="price" class="form-label">Bilet Fiyatı (TL)</label>
                    <input type="number" class="form-control" id="price" name="price" min="1" step="0.01" required> </div>
                <div class="col-md-6">
                    <label for="capacity" class="form-label">Koltuk Kapasitesi</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" min="1" max="100" value="45" required> </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Seferi Kaydet</button>
                    <a href="firma_admin_panel.php" class="btn btn-secondary">İptal</a>
                </div>
            </div> </form>
    </div> </div> </div> <footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>