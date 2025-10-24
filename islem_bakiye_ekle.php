<?php
require 'config.php';


if (!isset($_SESSION['kullanici_uuid'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $kullanici_uuid = $_SESSION['kullanici_uuid'];
    

    $eklenecek_tutar = isset($_POST['eklenecek_tutar']) ? (int)$_POST['eklenecek_tutar'] : 0;
    

    $sefer_uuid = isset($_POST['sefer_uuid']) ? $_POST['sefer_uuid'] : '';
    $koltuklar = isset($_POST['koltuklar']) ? (array)$_POST['koltuklar'] : [];
    $kupon_kodu = isset($_POST['kupon_kodu']) ? $_POST['kupon_kodu'] : '';


    if (empty($sefer_uuid) || empty($koltuklar)) {
         $_SESSION['hata_mesaji'] = "Bakiye eklenirken sefer veya koltuk bilgisi kayboldu. Lütfen tekrar deneyin.";
         header("Location: bilet_al.php?sefer_id=" . $sefer_uuid);
         exit;
    }

    if ($eklenecek_tutar > 0) {
        try {
            $sql = "UPDATE User SET balance = balance + ? WHERE uuid = ?";
            $sorgu = $vt->prepare($sql);
            $sorgu->execute([$eklenecek_tutar, $kullanici_uuid]);
            
            $_SESSION['basari_mesaji'] = "$eklenecek_tutar TL bakiye başarıyla eklendi.";
            
        } catch (Exception $hata) {
            $_SESSION['hata_mesaji'] = "Bakiye eklenirken bir hata oluştu: " . $hata->getMessage();
        }
    } else {
        $_SESSION['hata_mesaji'] = "Lütfen geçerli bir bakiye miktarı girin.";
    }

    echo "<form id='geriYonlendirmeFormu' action='onay.php' method='POST'>";
    echo "<input type='hidden' name='sefer_uuid' value='" . htmlspecialchars($sefer_uuid) . "'>";
    echo "<input type='hidden' name='kupon_kodu' value='" . htmlspecialchars($kupon_kodu) . "'>";
    foreach ($koltuklar as $koltuk) {
        echo "<input type='hidden' name='koltuklar[]' value='" . htmlspecialchars($koltuk) . "'>";
    }
    echo "</form>";
    echo "<script type='text/javascript'>document.getElementById('geriYonlendirmeFormu').submit();</script>";
    exit;
    
} else {
    header("Location: index.php");
    exit;
}
?>