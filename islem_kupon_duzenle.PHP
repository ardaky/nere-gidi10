<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'firma_admin' || !isset($_SESSION['firma_uuid'])) {
    header("Location: index.php");
    exit;
}

$kupon_uuid = $_POST['kupon_uuid'];
$company_id = $_POST['company_id'];
$discount_percent = $_POST['discount'];
$usage_limit = $_POST['usage_limit'];
$expire_date = $_POST['expire_date'];

if ($company_id !== $_SESSION['firma_uuid']) {
     $_SESSION['hata_mesaji'] = "Yetkisiz işlem denemesi!";
     header("Location: firma_kupon_yonetimi.php");
     exit;
}
if (empty($kupon_uuid)) {
    $_SESSION['hata_mesaji'] = "Güncellenecek kupon ID'si bulunamadı.";
    header("Location: firma_kupon_yonetimi.php");
    exit;
}

if (empty($discount_percent) || empty($usage_limit) || empty($expire_date)) {
    $_SESSION['hata_mesaji'] = "Tüm alanların doldurulması zorunludur.";
    header("Location: kupon_duzenle.php?kupon_uuid=" . $kupon_uuid);
    exit;
}
if (!is_numeric($discount_percent) || $discount_percent < 1 || $discount_percent > 100) {
    $_SESSION['hata_mesaji'] = "İndirim oranı %1 ile %100 arasında olmalıdır.";
    header("Location: kupon_duzenle.php?kupon_uuid=" . $kupon_uuid);
    exit;
}
$discount_decimal = $discount_percent / 100.0; 

if (!ctype_digit($usage_limit) || $usage_limit < 1) {
    $_SESSION['hata_mesaji'] = "Kullanım limiti geçerli bir pozitif tam sayı olmalıdır.";
    header("Location: kupon_duzenle.php?kupon_uuid=" . $kupon_uuid);
    exit;
}
$bugunun_tarihi = date('Y-m-d');
if ($expire_date <= $bugunun_tarihi) {
     $_SESSION['hata_mesaji'] = "Son kullanma tarihi bugünden sonraki bir tarih olmalıdır.";
     header("Location: kupon_duzenle.php?kupon_uuid=" . $kupon_uuid);
     exit;
}

try {

    $sql = "UPDATE Coupons SET
                discount = ?,
                usage_limit = ?,
                expire_date = ?
            WHERE
                uuid = ? AND company_id = ?";

    $sorgu = $vt->prepare($sql);

    $sonuc = $sorgu->execute([
        $discount_decimal, 
        $usage_limit,
        $expire_date,
        $kupon_uuid,       
        $company_id        
    ]);


    if ($sonuc) {
        $_SESSION['basari_mesaji'] = "Kupon başarıyla güncellendi.";
        header("Location: firma_kupon_yonetimi.php");
        exit;
    } else {
        throw new Exception("Kupon güncellenirken bilinmeyen bir veritabanı hatası oluştu.");
    }

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Veritabanı hatası: " . $hata->getMessage();
    header("Location: kupon_duzenle.php?kupon_uuid=" . $kupon_uuid);
    exit;
} catch (Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: kupon_duzenle.php?kupon_uuid=" . $kupon_uuid);
    exit;
}

?>