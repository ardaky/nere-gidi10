<?php

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    header("Location: index.php");
    exit;
}


$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$password = $_POST['password']; 
$company_id = $_POST['company_id']; 
$cinsiyet = isset($_POST['cinsiyet']) ? $_POST['cinsiyet'] : '';


if (empty($full_name) || empty($email) || empty($password) || empty($company_id) || empty($cinsiyet)) {
    $_SESSION['hata_mesaji'] = "Tüm alanların doldurulması zorunludur.";
    header("Location: firma_admin_ekle.php");
    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
     $_SESSION['hata_mesaji'] = "Lütfen geçerli bir e-posta adresi girin.";
     header("Location: firma_admin_ekle.php");
     exit;
}


if (strlen($password) < 6) {
     $_SESSION['hata_mesaji'] = "Şifre en az 6 karakter olmalıdır.";
     header("Location: firma_admin_ekle.php");
     exit;
}

$sifrelenmis_parola = password_hash($password, PASSWORD_BCRYPT);



try {
    $sorgu_check = $vt->prepare("SELECT COUNT(*) FROM User WHERE email = ?");
    $sorgu_check->execute([$email]);
    if ($sorgu_check->fetchColumn() > 0) {
        $_SESSION['hata_mesaji'] = "Bu e-posta adresi ('{$email}') zaten kullanılıyor.";
        header("Location: firma_admin_ekle.php");
        exit;
    }


    $kullanici_uuid = uuid_olustur();
    $rol = 'firma_admin'; 

    $sql = "INSERT INTO User (uuid, full_name, email, password, role, company_id, cinsiyet)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $sorgu = $vt->prepare($sql);

    $sonuc = $sorgu->execute([
        $kullanici_uuid,
        $full_name,
        $email,
        $sifrelenmis_parola, 
        $rol,
        $company_id,
        $cinsiyet
    ]);

    if ($sonuc) {
        $_SESSION['basari_mesaji'] = "'{$full_name}' adlı Firma Admin kullanıcısı başarıyla eklendi.";
        header("Location: admin_panel.php");
        exit;
    } else {
        throw new Exception("Firma Admin eklenirken bilinmeyen bir veritabanı hatası oluştu.");
    }

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Veritabanı hatası: " . $hata->getMessage();
    header("Location: firma_admin_ekle.php");
    exit;
} catch (Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: firma_admin_ekle.php");
    exit;
}

?>