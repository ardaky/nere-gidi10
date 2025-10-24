<?php

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    header("Location: index.php");
    exit;
}

$name = trim($_POST['name']);
$logo_path = null; 

if (empty($name)) {
    $_SESSION['hata_mesaji'] = "Firma adı boş bırakılamaz.";
    header("Location: firma_ekle.php");
    exit;
}

if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/logos/';
    $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('logo_', true) . '.' . strtolower($file_extension);
    $target_file = $upload_dir . $new_filename;
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 2 * 1024 * 1024;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!in_array(strtolower($file_extension), $allowed_types)) {
        $_SESSION['hata_mesaji'] = "Sadece JPG, JPEG, PNG, GIF formatında logolar yüklenebilir.";
        header("Location: firma_ekle.php");
        exit;
    }

    if ($_FILES['logo']['size'] > $max_size) {
        $_SESSION['hata_mesaji'] = "Logo dosyası en fazla 2MB olabilir.";
        header("Location: firma_ekle.php");
        exit;
    }

    if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
        $logo_path = $target_file; 
    } else {
        $_SESSION['hata_mesaji'] = "Logo yüklenirken bir hata oluştu.";
        header("Location: firma_ekle.php");
        exit;
    }
}

try {
    $sorgu_check = $vt->prepare("SELECT COUNT(*) FROM Bus_Company WHERE name = ?");
    $sorgu_check->execute([$name]);
    if ($sorgu_check->fetchColumn() > 0) {
        $_SESSION['hata_mesaji'] = "Bu firma adı ('{$name}') zaten kayıtlı.";
        header("Location: firma_ekle.php");
        exit;
    }

    $firma_uuid = uuid_olustur();

    $sql = "INSERT INTO Bus_Company (uuid, name, logo_path) VALUES (?, ?, ?)";
    $sorgu = $vt->prepare($sql);
    $sonuc = $sorgu->execute([$firma_uuid, $name, $logo_path]);

    if ($sonuc) {
        $_SESSION['basari_mesaji'] = "'{$name}' firması başarıyla eklendi.";
        header("Location: admin_panel.php");
        exit;
    } else {
        throw new Exception("Firma eklenirken bilinmeyen bir veritabanı hatası oluştu.");
    }

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Veritabanı hatası: " . $hata->getMessage();
    header("Location: firma_ekle.php");
    exit;
} catch (Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: firma_ekle.php");
    exit;
}

?>