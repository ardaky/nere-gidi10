<?php

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    header("Location: index.php");
    exit;
}


$firma_uuid_sil = $_POST['firma_uuid'];

if (empty($firma_uuid_sil)) {
    $_SESSION['hata_mesaji'] = "Silinecek firma ID'si belirtilmedi.";
    header("Location: admin_panel.php");
    exit;
}


try {

    $sorgu_firma = $vt->prepare("SELECT * FROM Bus_Company WHERE uuid = ?");
    $sorgu_firma->execute([$firma_uuid_sil]);
    $firma = $sorgu_firma->fetch(PDO::FETCH_ASSOC);
    $logo_path_sil = $firma ? $firma['logo_path'] : null;
    $firma_adi_sil = $firma ? $firma['name'] : 'Bilinmeyen Firma';

    $sql_admin_kaldir = "UPDATE User SET company_id = NULL WHERE company_id = ?";
    $vt->prepare($sql_admin_kaldir)->execute([$firma_uuid_sil]);


    $sql = "DELETE FROM Bus_Company WHERE uuid = ?";
    $sorgu = $vt->prepare($sql);
    $sonuc = $sorgu->execute([$firma_uuid_sil]);


    if ($sorgu->rowCount() > 0) {

        if ($logo_path_sil && file_exists($logo_path_sil)) {
            @unlink($logo_path_sil);
        }
        $_SESSION['basari_mesaji'] = "'{$firma_adi_sil}' firması (ve ilgili tüm verileri) başarıyla silindi.";
        header("Location: admin_panel.php");
        exit;
    } else {
        throw new Exception("Firma bulunamadı veya silinemedi.");
    }

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Veritabanı hatası: " . $hata->getMessage();
    header("Location: admin_panel.php");
    exit;
} catch (Exception $hata) {
    $_SESSION['hata_mesaji'] = "Bir hata oluştu: " . $hata->getMessage();
    header("Location: admin_panel.php");
    exit;
}

?>