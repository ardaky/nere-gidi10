<?php

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    header("Location: index.php");
    exit;
}


$admin_uuid_sil = $_POST['admin_uuid'];


if (empty($admin_uuid_sil)) {
    $_SESSION['hata_mesaji'] = "Silinecek Firma Admin ID'si belirtilmedi.";
    header("Location: admin_panel.php");
    exit;
}


if ($admin_uuid_sil === $_SESSION['kullanici_uuid']) {
    $_SESSION['hata_mesaji'] = "Kendi hesabınızı silemezsiniz.";
    header("Location: admin_panel.php");
    exit;
}

try {
    $sql = "DELETE FROM User WHERE uuid = ? AND role = 'firma_admin'";
    $sorgu = $vt->prepare($sql);
    $sonuc = $sorgu->execute([$admin_uuid_sil]);


    if ($sorgu->rowCount() > 0) {
        $_SESSION['basari_mesaji'] = "Firma Admin kullanıcısı başarıyla silindi.";
        header("Location: admin_panel.php");
        exit;
    } else {
        throw new Exception("Firma Admin kullanıcısı bulunamadı veya silinemedi.");
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