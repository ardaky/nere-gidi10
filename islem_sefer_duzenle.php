<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'firma_admin' || !isset($_SESSION['firma_uuid'])) {
    header("Location: index.php");
    exit;
}

$sefer_uuid = $_POST['sefer_uuid'];
$company_id = $_POST['company_id'];
$departure_city = trim($_POST['departure_city']);
$destination_city = trim($_POST['destination_city']);
$departure_time = $_POST['departure_time'];
$arrival_time = $_POST['arrival_time'];
$price = $_POST['price'];
$capacity = $_POST['capacity'];

$departure_city = duzelt_sehir_adi($departure_city);
$destination_city = duzelt_sehir_adi($destination_city);

if ($company_id !== $_SESSION['firma_uuid']) {
     $_SESSION['hata_mesaji'] = "Yetkisiz işlem denemesi!";
     header("Location: firma_admin_panel.php");
     exit;
}
if (empty($sefer_uuid)) {
    $_SESSION['hata_mesaji'] = "Güncellenecek sefer ID'si bulunamadı.";
    header("Location: firma_admin_panel.php");
    exit;
}

if (empty($departure_city) || empty($destination_city) || empty($departure_time) || empty($arrival_time) || empty($price) || empty($capacity)) {
    $_SESSION['hata_mesaji'] = "Tüm alanların doldurulması zorunludur.";
    header("Location: sefer_duzenle.php?sefer_uuid=" . $sefer_uuid);
    exit;
}
if ($departure_city === $destination_city) {
    $_SESSION['hata_mesaji'] = "Kalkış ve varış şehri aynı olamaz.";
    header("Location: sefer_duzenle.php?sefer_uuid=" . $sefer_uuid);
    exit;
}
if (strtotime($arrival_time) <= strtotime($departure_time)) {
    $_SESSION['hata_mesaji'] = "Varış zamanı, kalkış zamanından sonra olmalıdır.";
    header("Location: sefer_duzenle.php?sefer_uuid=" . $sefer_uuid);
    exit;
}
if (!is_numeric($price) || !is_numeric($capacity) || $price <= 0 || $capacity <= 0) {
     $_SESSION['hata_mesaji'] = "Fiyat ve kapasite geçerli sayılar olmalıdır.";
     header("Location: sefer_duzenle.php?sefer_uuid=" . $sefer_uuid);
     exit;
}

try {
    $sql = "UPDATE Trips SET
                departure_city = ?,
                destination_city = ?,
                departure_time = ?,
                arrival_time = ?,
                price = ?,
                capacity = ?
            WHERE
                uuid = ? AND company_id = ?";

    $sorgu = $vt->prepare($sql);

    $sonuc = $sorgu->execute([
        $departure_city,
        $destination_city,
        $departure_time,
        $arrival_time,
        $price,
        $capacity,
        $sefer_uuid,
        $company_id
    ]);

    if ($sonuc) {
        $_SESSION['basari_mesaji'] = "Sefer başarıyla güncellendi.";
        header("Location: firma_admin_panel.php");
        exit;
    } else {
        throw new Exception("Sefer güncellenirken bilinmeyen bir veritabanı hatası oluştu.");
    }

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Veritabanı hatası: " . $hata->getMessage();
    header("Location: sefer_duzenle.php?sefer_uuid=" . $sefer_uuid);
    exit;
} catch (Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: sefer_duzenle.php?sefer_uuid=" . $sefer_uuid);
    exit;
}
?>