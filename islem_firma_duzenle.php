<?php

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    header("Location: index.php");
    exit;
}

$firma_uuid = $_POST['firma_uuid'];
$name = trim($_POST['name']);
$logoyu_kaldir = isset($_POST['logoyu_kaldir']) ? true : false;

if (empty($firma_uuid) || empty($name)) {
    $_SESSION['hata_mesaji'] = "Firma ID'si veya adı eksik.";
    header("Location: admin_panel.php");
    exit;
}

try {
    $sorgu_mevcut = $vt->prepare("SELECT * FROM Bus_Company WHERE uuid = ?");
    $sorgu_mevcut->execute([$firma_uuid]);
    $mevcut_firma = $sorgu_mevcut->fetch(PDO::FETCH_ASSOC);

    if (!$mevcut_firma) {
        $_SESSION['hata_mesaji'] = "Güncellenecek firma bulunamadı.";
        header("Location: admin_panel.php");
        exit;
    }
    $mevcut_logo_path = $mevcut_firma['logo_path'];
    $yeni_logo_path = $mevcut_logo_path;

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Mevcut firma bilgileri alınamadı: " . $hata->getMessage();
    header("Location: firma_duzenle.php?firma_uuid=" . $firma_uuid);
    exit;
}


$eski_logoyu_sil = false;


if ($logoyu_kaldir) {
    $yeni_logo_path = null; 
    if ($mevcut_logo_path && file_exists($mevcut_logo_path)) {
        $eski_logoyu_sil = true; 
    }
}

elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/logos/';
    $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('logo_', true) . '.' . strtolower($file_extension);
    $target_file = $upload_dir . $new_filename;
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 2 * 1024 * 1024; 

    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

    if (!in_array(strtolower($file_extension), $allowed_types)) {
        $_SESSION['hata_mesaji'] = "Sadece JPG, JPEG, PNG, GIF formatı geçerlidir.";
        header("Location: firma_duzenle.php?firma_uuid=" . $firma_uuid); exit;
    }
    if ($_FILES['logo']['size'] > $max_size) {
        $_SESSION['hata_mesaji'] = "Logo dosyası en fazla 2MB olabilir.";
        header("Location: firma_duzenle.php?firma_uuid=" . $firma_uuid); exit;
    }

    if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
        $yeni_logo_path = $target_file; 
        if ($mevcut_logo_path && file_exists($mevcut_logo_path)) {
            $eski_logoyu_sil = true; 
        }
    } else {
        $_SESSION['hata_mesaji'] = "Yeni logo yüklenirken hata oluştu.";
        header("Location: firma_duzenle.php?firma_uuid=" . $firma_uuid); exit;
    }
}


try {
    $sorgu_check = $vt->prepare("SELECT COUNT(*) FROM Bus_Company WHERE name = ? AND uuid != ?");
    $sorgu_check->execute([$name, $firma_uuid]);
    if ($sorgu_check->fetchColumn() > 0) {
        $_SESSION['hata_mesaji'] = "Bu firma adı ('{$name}') başka bir firma tarafından kullanılıyor.";
        header("Location: firma_duzenle.php?firma_uuid=" . $firma_uuid);
        exit;
    }


    $sql = "UPDATE Bus_Company SET name = ?, logo_path = ? WHERE uuid = ?";
    $sorgu = $vt->prepare($sql);
    $sonuc = $sorgu->execute([$name, $yeni_logo_path, $firma_uuid]);

    if ($sonuc && $eski_logoyu_sil) {
        @unlink($mevcut_logo_path); 
    }

    if ($sonuc) {
        $_SESSION['basari_mesaji'] = "'{$name}' firması başarıyla güncellendi.";
        header("Location: admin_panel.php");
        exit;
    } else {
        throw new Exception("Firma güncellenirken bilinmeyen bir veritabanı hatası oluştu.");
    }

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Veritabanı hatası: " . $hata->getMessage();
    header("Location: firma_duzenle.php?firma_uuid=" . $firma_uuid);
    exit;
} catch (Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: firma_duzenle.php?firma_uuid=" . $firma_uuid);
    exit;
}

?>