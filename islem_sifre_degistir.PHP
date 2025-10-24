<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid'])) {
    header("Location: index.php");
    exit;
}

$kullanici_uuid = $_SESSION['kullanici_uuid'];
$mevcut_sifre = $_POST['mevcut_sifre'];
$yeni_sifre = $_POST['yeni_sifre'];
$yeni_sifre_tekrar = $_POST['yeni_sifre_tekrar'];

if (empty($mevcut_sifre) || empty($yeni_sifre) || empty($yeni_sifre_tekrar)) {
    $_SESSION['hata_mesaji'] = "Tüm şifre alanları doldurulmalıdır.";
    header("Location: hesabim.php"); exit;
}

if ($yeni_sifre !== $yeni_sifre_tekrar) {
    $_SESSION['hata_mesaji'] = "Yeni şifreler eşleşmiyor.";
    header("Location: hesabim.php"); exit;
}

if (strlen($yeni_sifre) < 6) {
    $_SESSION['hata_mesaji'] = "Yeni şifre en az 6 karakter olmalıdır.";
    header("Location: hesabim.php"); exit;
}

try {
    $sorgu_mevcut = $vt->prepare("SELECT password FROM User WHERE uuid = ?");
    $sorgu_mevcut->execute([$kullanici_uuid]);
    $kullanici = $sorgu_mevcut->fetch(PDO::FETCH_ASSOC);

    if (!$kullanici) {
         throw new Exception("Kullanıcı bulunamadı.");
    }
    $mevcut_hash = $kullanici['password'];

    if (!password_verify($mevcut_sifre, $mevcut_hash)) {
        $_SESSION['hata_mesaji'] = "Mevcut şifreniz yanlış.";
        header("Location: hesabim.php"); exit;
    }

    $yeni_hash = password_hash($yeni_sifre, PASSWORD_BCRYPT);

    $sql_update = "UPDATE User SET password = ? WHERE uuid = ?";
    $sorgu_update = $vt->prepare($sql_update);
    $sonuc = $sorgu_update->execute([$yeni_hash, $kullanici_uuid]);

    if ($sonuc) {
        $_SESSION['basari_mesaji'] = "Şifreniz başarıyla güncellendi.";
        header("Location: hesabim.php"); exit;
    } else {
         throw new Exception("Şifre güncellenirken bir veritabanı hatası oluştu.");
    }

} catch (PDOException | Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: hesabim.php"); exit;
}
?>