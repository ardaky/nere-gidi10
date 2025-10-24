<?php

include 'header.php';

if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'firma_admin' || !isset($_SESSION['firma_uuid'])) {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}

$firma_uuid = $_SESSION['firma_uuid'];

$sefer_uuid_duzenle = isset($_GET['sefer_uuid']) ? htmlspecialchars($_GET['sefer_uuid']) : '';

if (empty($sefer_uuid_duzenle)) {
    $_SESSION['hata_mesaji'] = "Düzenlenecek sefer ID'si belirtilmedi.";
    header("Location: firma_admin_panel.php");
    exit;
}

try {
    $sql = "SELECT * FROM Trips WHERE uuid = ? AND company_id = ?";
    $sorgu = $vt->prepare($sql);
    $sorgu->execute([$sefer_uuid_duzenle, $firma_uuid]);
    $sefer = $sorgu->fetch(PDO::FETCH_ASSOC);

    if (!$sefer) {
        $_SESSION['hata_mesaji'] = "Düzenlenecek sefer bulunamadı veya bu sefere erişim yetkiniz yok.";
        header("Location: firma_admin_panel.php");
        exit;
    }

    $sefer['departure_time_formatted'] = date('Y-m-d\TH:i', strtotime($sefer['departure_time']));
    $sefer['arrival_time_formatted'] = date('Y-m-d\TH:i', strtotime($sefer['arrival_time']));


} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Sefer bilgileri alınırken hata oluştu: " . $hata->getMessage();
    header("Location: firma_admin_panel.php");
    exit;
}

$hata_mesaji = $_SESSION['hata_mesaji'] ?? null; unset($_SESSION['hata_mesaji']);
?>

<h2 class="mb-4">Seferi Düzenle</h2>

<?php if ($hata_mesaji): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="islem_sefer_duzenle.php" method="POST">
            
            <input type="hidden" name="sefer_uuid" value="<?php echo htmlspecialchars($sefer['uuid']); ?>">
            <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($firma_uuid); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="departure_city" class="form-label">Kalkış Şehri</label>
                    <input type="text" class="form-control" id="departure_city" name="departure_city" value="<?php echo htmlspecialchars($sefer['departure_city']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="destination_city" class="form-label">Varış Şehri</label>
                    <input type="text" class="form-control" id="destination_city" name="destination_city" value="<?php echo htmlspecialchars($sefer['destination_city']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="departure_time" class="form-label">Kalkış Tarihi ve Saati</label>
                    <input type="datetime-local" class="form-control" id="departure_time" name="departure_time" value="<?php echo $sefer['departure_time_formatted']; ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="arrival_time" class="form-label">Varış Tarihi ve Saati</label>
                    <input type="datetime-local" class="form-control" id="arrival_time" name="arrival_time" value="<?php echo $sefer['arrival_time_formatted']; ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="price" class="form-label">Bilet Fiyatı (TL)</label>
                    <input type="number" class="form-control" id="price" name="price" min="1" step="0.01" value="<?php echo htmlspecialchars($sefer['price']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="capacity" class="form-label">Koltuk Kapasitesi</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" min="1" max="100" value="<?php echo htmlspecialchars($sefer['capacity']); ?>" required>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning">Değişiklikleri Kaydet</button> <a href="firma_admin_panel.php" class="btn btn-secondary">İptal</a>
                </div>
            </div> </form>
    </div> </div> </div> <footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>