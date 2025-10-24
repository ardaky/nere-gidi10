<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['kullanici_uuid'])) {
    $_SESSION['hata_mesaji'] = "İşlem yapmak için giriş yapmalısınız.";
    header("Location: login.php");
    exit;
}

$kullanici_uuid = $_SESSION['kullanici_uuid'];
$bilet_uuid = isset($_POST['bilet_uuid']) ? $_POST['bilet_uuid'] : '';

if (empty($bilet_uuid)) {
    $_SESSION['hata_mesaji'] = "Geçersiz bilet ID'si.";
    header("Location: hesabim.php");
    exit;
}

try {
    $vt->beginTransaction();

    $sql_bilet = "SELECT T.uuid AS bilet_uuid, T.total_price, Tr.departure_time
                  FROM Tickets T
                  JOIN Trips Tr ON T.trip_id = Tr.uuid
                  WHERE T.uuid = ? AND T.user_id = ? AND T.status = 'active'";
    $sorgu_bilet = $vt->prepare($sql_bilet);
    $sorgu_bilet->execute([$bilet_uuid, $kullanici_uuid]);
    $bilet = $sorgu_bilet->fetch(PDO::FETCH_ASSOC);

    if (!$bilet) {
        throw new Exception("İptal edilecek aktif bilet bulunamadı veya bu bilete erişim izniniz yok.");
    }

    $iade_tutari = $bilet['total_price'];

    $kalkis_zamani = new DateTime($bilet['departure_time']);
    $iptal_son_saniye = new DateTime('now', new DateTimeZone('Europe/Istanbul'));
    $iptal_son_saniye->modify('+1 hour');

    if ($iptal_son_saniye > $kalkis_zamani) {
        throw new Exception("Seferin kalkışına 1 saatten az kaldığı için bilet iptal edilemez.");
    }

    $sql_bilet_iptal = "UPDATE Tickets SET status = 'cancelled' WHERE uuid = ?";
    $vt->prepare($sql_bilet_iptal)->execute([$bilet_uuid]);

    $sql_koltuk_sil = "DELETE FROM Booked_Seats WHERE ticket_id = ?";
    $vt->prepare($sql_koltuk_sil)->execute([$bilet_uuid]);

    $sql_bakiye_iade = "UPDATE User SET balance = balance + ? WHERE uuid = ?";
    $vt->prepare($sql_bakiye_iade)->execute([$iade_tutari, $kullanici_uuid]);
    

    $sorgu_yeni_bakiye = $vt->prepare("SELECT balance FROM User WHERE uuid = ?");
    $sorgu_yeni_bakiye->execute([$kullanici_uuid]);
    $yeni_bakiye = $sorgu_yeni_bakiye->fetchColumn();
    $_SESSION['kullanici_bakiyesi'] = $yeni_bakiye;

    $vt->commit();

    $_SESSION['basari_mesaji'] = "Biletiniz başarıyla iptal edildi. " . $iade_tutari . " TL bakiyenize iade edildi.";
    header("Location: hesabim.php");
    exit;

} catch (Exception $hata) {
    if ($vt->inTransaction()) {
        $vt->rollBack();
    }
    $_SESSION['hata_mesaji'] = "Bilet iptal edilirken bir hata oluştu: " . $hata->getMessage();
    header("Location: hesabim.php");
    exit;
}
?>