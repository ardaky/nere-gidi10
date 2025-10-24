<?php

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    header("Location: index.php");
    exit;
}

$kupon_uuid_sil = $_POST['kupon_uuid'];


if (empty($kupon_uuid_sil)) {
    $_SESSION['hata_mesaji'] = "Silinecek kupon ID'si belirtilmedi.";
    header("Location: admin_kupon_yonetimi.php");
    exit;
}

try {
   
    $sql = "DELETE FROM Coupons WHERE uuid = ? AND company_id IS NULL"; 
    $sorgu = $vt->prepare($sql);
    $sonuc = $sorgu->execute([$kupon_uuid_sil]);

  
    if ($sorgu->rowCount() > 0) {
        $_SESSION['basari_mesaji'] = "Genel kupon başarıyla silindi.";
        header("Location: admin_kupon_yonetimi.php");
        exit;
    } else {
        throw new Exception("Genel kupon bulunamadı veya silinemedi.");
    }

} catch (PDOException | Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: admin_kupon_yonetimi.php");
    exit;
}
?>