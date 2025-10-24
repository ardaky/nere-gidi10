<?php

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'firma_admin' || !isset($_SESSION['firma_uuid'])) {
    header("Location: index.php");
    exit;
}

$kupon_uuid_sil = $_POST['kupon_uuid'];
$firma_uuid = $_SESSION['firma_uuid']; 

if (empty($kupon_uuid_sil)) {
    $_SESSION['hata_mesaji'] = "Silinecek kupon ID'si belirtilmedi.";
    header("Location: firma_kupon_yonetimi.php");
    exit;
}

try {
    $sql = "DELETE FROM Coupons WHERE uuid = ? AND company_id = ?";
    $sorgu = $vt->prepare($sql);
    $sonuc = $sorgu->execute([$kupon_uuid_sil, $firma_uuid]);

    if ($sorgu->rowCount() > 0) {
        $_SESSION['basari_mesaji'] = "Kupon başarıyla silindi.";
        header("Location: firma_kupon_yonetimi.php");
        exit;
    } else {
        throw new Exception("Kupon bulunamadı veya silme yetkiniz yok.");
    }

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Veritabanı hatası: " . $hata->getMessage();
    header("Location: firma_kupon_yonetimi.php");
    exit;
} catch (Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: firma_kupon_yonetimi.php");
    exit;
}

?>