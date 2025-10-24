<?php

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    header("Location: index.php");
    exit;
}


$kupon_uuid = $_POST['kupon_uuid'];
$discount_percent = $_POST['discount'];
$usage_limit = $_POST['usage_limit'];
$expire_date = $_POST['expire_date'];


if (empty($kupon_uuid) || empty($discount_percent) || empty($usage_limit) || empty($expire_date)) {
    $_SESSION['hata_mesaji'] = "Gerekli alanlar boş bırakılamaz.";
    header("Location: admin_kupon_duzenle.php?kupon_uuid=" . $kupon_uuid); exit;
}
if (!is_numeric($discount_percent) || $discount_percent < 1 || $discount_percent > 100) {
    $_SESSION['hata_mesaji'] = "İndirim oranı %1 ile %100 arasında olmalıdır.";
    header("Location: admin_kupon_duzenle.php?kupon_uuid=" . $kupon_uuid); exit;
}
$discount_decimal = $discount_percent / 100.0;
if (!ctype_digit($usage_limit) || $usage_limit < 1) {
    $_SESSION['hata_mesaji'] = "Kullanım limiti geçerli bir pozitif tam sayı olmalıdır.";
    header("Location: admin_kupon_duzenle.php?kupon_uuid=" . $kupon_uuid); exit;
}
$bugunun_tarihi = date('Y-m-d');
if ($expire_date <= $bugunun_tarihi) {
     $_SESSION['hata_mesaji'] = "Son kullanma tarihi bugünden sonraki bir tarih olmalıdır.";
     header("Location: admin_kupon_duzenle.php?kupon_uuid=" . $kupon_uuid); exit;
}

try {
   
    $sql = "UPDATE Coupons SET
                discount = ?,
                usage_limit = ?,
                expire_date = ?
            WHERE
                uuid = ? AND company_id IS NULL"; 

    $sorgu = $vt->prepare($sql);
    $sonuc = $sorgu->execute([
        $discount_decimal, $usage_limit, $expire_date, $kupon_uuid
    ]);

 
    if ($sonuc && $sorgu->rowCount() > 0) { 
        $_SESSION['basari_mesaji'] = "Genel kupon başarıyla güncellendi.";
        header("Location: admin_kupon_yonetimi.php"); exit;
    } elseif ($sonuc && $sorgu->rowCount() == 0) {
   
         throw new Exception("Güncellenecek genel kupon bulunamadı.");
    } else {
        throw new Exception("Genel kupon güncellenirken bilinmeyen bir veritabanı hatası oluştu.");
    }
} catch (PDOException | Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: admin_kupon_duzenle.php?kupon_uuid=" . $kupon_uuid); exit;
}
?>