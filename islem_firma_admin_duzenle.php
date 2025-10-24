<?php

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    header("Location: index.php");
    exit;
}


$admin_uuid = $_POST['admin_uuid'];
$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$password = $_POST['password']; 
$company_id = $_POST['company_id'];
$cinsiyet = isset($_POST['cinsiyet']) ? $_POST['cinsiyet'] : '';


if (empty($admin_uuid) || empty($full_name) || empty($email) || empty($company_id) || empty($cinsiyet)) {
    $_SESSION['hata_mesaji'] = "Gerekli alanlar (Ad, E-posta, Firma, Cinsiyet) boş bırakılamaz.";
    header("Location: firma_admin_duzenle.php?admin_uuid=" . $admin_uuid);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
     $_SESSION['hata_mesaji'] = "Lütfen geçerli bir e-posta adresi girin.";
     header("Location: firma_admin_duzenle.php?admin_uuid=" . $admin_uuid);
     exit;
}

if (!empty($password) && strlen($password) < 6) {
     $_SESSION['hata_mesaji'] = "Yeni şifre en az 6 karakter olmalıdır.";
     header("Location: firma_admin_duzenle.php?admin_uuid=" . $admin_uuid);
     exit;
}

try {
    
    $sorgu_check = $vt->prepare("SELECT COUNT(*) FROM User WHERE email = ? AND uuid != ?");
    $sorgu_check->execute([$email, $admin_uuid]);
    if ($sorgu_check->fetchColumn() > 0) {
        $_SESSION['hata_mesaji'] = "Bu e-posta adresi ('{$email}') başka bir kullanıcı tarafından kullanılıyor.";
        header("Location: firma_admin_duzenle.php?admin_uuid=" . $admin_uuid);
        exit;
    }

    $sql_parts = [
        "full_name = ?",
        "email = ?",
        "company_id = ?",
        "cinsiyet = ?"
    ];
    $params = [$full_name, $email, $company_id, $cinsiyet];

    if (!empty($password)) {
        $sifrelenmis_parola = password_hash($password, PASSWORD_BCRYPT);
        $sql_parts[] = "password = ?";
        $params[] = $sifrelenmis_parola;
    }


    $params[] = $admin_uuid;


    $sql = "UPDATE User SET " . implode(', ', $sql_parts) . " WHERE uuid = ? AND role = 'firma_admin'"; 

    $sorgu = $vt->prepare($sql);
    $sonuc = $sorgu->execute($params);


    if ($sonuc) {
        $_SESSION['basari_mesaji'] = "'{$full_name}' adlı Firma Admin kullanıcısı başarıyla güncellendi.";
        header("Location: admin_panel.php");
        exit;
    } else {
        throw new Exception("Firma Admin güncellenirken bilinmeyen bir veritabanı hatası oluştu.");
    }

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Veritabanı hatası: " . $hata->getMessage();
    header("Location: firma_admin_duzenle.php?admin_uuid=" . $admin_uuid);
    exit;
} catch (Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: firma_admin_duzenle.php?admin_uuid=" . $admin_uuid);
    exit;
}

?>