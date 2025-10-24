<?php

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    header("Location: index.php");
    exit;
}


$code = trim(strtoupper($_POST['code']));
$discount_percent = $_POST['discount'];
$usage_limit = $_POST['usage_limit'];
$expire_date = $_POST['expire_date'];


if (empty($code) || empty($discount_percent) || empty($usage_limit) || empty($expire_date)) {
    $_SESSION['hata_mesaji'] = "Tüm alanların doldurulması zorunludur.";
    header("Location: admin_kupon_ekle.php"); exit;
}
if (!ctype_alnum($code)) {
    $_SESSION['hata_mesaji'] = "Kupon kodu sadece harf ve rakam içermelidir.";
    header("Location: admin_kupon_ekle.php"); exit;
}
if (!is_numeric($discount_percent) || $discount_percent < 1 || $discount_percent > 100) {
    $_SESSION['hata_mesaji'] = "İndirim oranı %1 ile %100 arasında olmalıdır.";
    header("Location: admin_kupon_ekle.php"); exit;
}
$discount_decimal = $discount_percent / 100.0;
if (!ctype_digit($usage_limit) || $usage_limit < 1) {
    $_SESSION['hata_mesaji'] = "Kullanım limiti geçerli bir pozitif tam sayı olmalıdır.";
    header("Location: admin_kupon_ekle.php"); exit;
}
$bugunun_tarihi = date('Y-m-d');
if ($expire_date <= $bugunun_tarihi) {
     $_SESSION['hata_mesaji'] = "Son kullanma tarihi bugünden sonraki bir tarih olmalıdır.";
     header("Location: admin_kupon_ekle.php"); exit;
}

try {
    $sorgu_check = $vt->prepare("SELECT COUNT(*) FROM Coupons WHERE code = ?");
    $sorgu_check->execute([$code]);
    if ($sorgu_check->fetchColumn() > 0) {
        $_SESSION['hata_mesaji'] = "Bu kupon kodu ('{$code}') zaten kullanılıyor.";
        header("Location: admin_kupon_ekle.php"); exit;
    }

    $kupon_uuid = uuid_olustur();
    $company_id = null; 

    $sql = "INSERT INTO Coupons (uuid, code, discount, company_id, usage_limit, expire_date)
            VALUES (?, ?, ?, ?, ?, ?)";
    $sorgu = $vt->prepare($sql);
    $sonuc = $sorgu->execute([
        $kupon_uuid, $code, $discount_decimal, $company_id, $usage_limit, $expire_date
    ]);

 
    if ($sonuc) {
        $_SESSION['basari_mesaji'] = "Genel kupon ('{$code}') başarıyla eklendi.";
        header("Location: admin_kupon_yonetimi.php"); exit;
    } else {
        throw new Exception("Genel kupon eklenirken bilinmeyen bir veritabanı hatası oluştu.");
    }
} catch (PDOException | Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: admin_kupon_ekle.php"); exit;
}
?>