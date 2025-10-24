<?php
require 'config.php';

if (!isset($_SESSION['kullanici_uuid'])) {
    $_SESSION['hata_mesaji'] = "İşlem yapmak için giriş yapmalısınız.";
    header("Location: login.php");
    exit;
}

$kullanici_uuid = $_SESSION['kullanici_uuid'];
$sefer_uuid = isset($_POST['sefer_uuid']) ? $_POST['sefer_uuid'] : '';
$secilen_koltuklar = isset($_POST['koltuklar']) ? (array)$_POST['koltuklar'] : [];
$kupon_kodu = isset($_POST['kupon_kodu']) ? trim(strtoupper($_POST['kupon_kodu'])) : '';

if (empty($sefer_uuid) || empty($secilen_koltuklar)) {
    $_SESSION['hata_mesaji'] = "Koltuk veya sefer bilgisi eksik.";
    header("Location: bilet_al.php?sefer_id=" . $sefer_uuid);
    exit;
}

$kupon_gecerli = false;
$kupon_indirim_orani = 0;
$kupon_uuid = null;

try {
    $vt->beginTransaction();

    $sorgu_bilgi = $vt->prepare("SELECT price, company_id FROM Trips WHERE uuid = ?");
    $sorgu_bilgi->execute([$sefer_uuid]);
    $sefer = $sorgu_bilgi->fetch(PDO::FETCH_ASSOC);

    $sorgu_bakiye = $vt->prepare("SELECT balance FROM User WHERE uuid = ?");
    $sorgu_bakiye->execute([$kullanici_uuid]);
    $kullanici = $sorgu_bakiye->fetch(PDO::FETCH_ASSOC);

    if (!$sefer || !$kullanici) {
        throw new Exception("Sefer veya kullanıcı bulunamadı.");
    }

    $koltuk_fiyati = $sefer['price'];
    $kullanici_bakiyesi = $kullanici['balance'];
    $toplam_fiyat = $koltuk_fiyati * count($secilen_koltuklar);
    $odenecek_fiyat = $toplam_fiyat;

    if (!empty($kupon_kodu)) {
        $sorgu_kupon = $vt->prepare("SELECT * FROM Coupons WHERE code = ?");
        $sorgu_kupon->execute([$kupon_kodu]);
        $kupon = $sorgu_kupon->fetch(PDO::FETCH_ASSOC);

        if (!$kupon) {
            $_SESSION['hata_mesaji'] = "Geçersiz kupon kodu: '{$kupon_kodu}'.";
            header("Location: bilet_al.php?sefer_id=" . $sefer_uuid); exit;
        }

        if (strtotime($kupon['expire_date']) < time()) {
            $_SESSION['hata_mesaji'] = "'{$kupon_kodu}' kuponunun süresi dolmuş.";
            header("Location: bilet_al.php?sefer_id=" . $sefer_uuid); exit;
        }

        if ($kupon['company_id'] !== null && $kupon['company_id'] !== $sefer['company_id']) {
             $_SESSION['hata_mesaji'] = "'{$kupon_kodu}' kuponu bu firma için geçerli değil.";
             header("Location: bilet_al.php?sefer_id=" . $sefer_uuid); exit;
        }

        $sorgu_kullanim_sayisi = $vt->prepare("SELECT COUNT(*) FROM User_Coupons WHERE coupon_id = ?");
        $sorgu_kullanim_sayisi->execute([$kupon['uuid']]);
        $kullanim_sayisi = $sorgu_kullanim_sayisi->fetchColumn();

        if ($kullanim_sayisi >= $kupon['usage_limit']) {
            $_SESSION['hata_mesaji'] = "'{$kupon_kodu}' kuponunun kullanım limitine ulaşılmış.";
            header("Location: bilet_al.php?sefer_id=" . $sefer_uuid); exit;
        }

        $kupon_gecerli = true;
        $kupon_indirim_orani = $kupon['discount'];
        $kupon_uuid = $kupon['uuid'];

        $indirim_tutari = $toplam_fiyat * $kupon_indirim_orani;
        $odenecek_fiyat = $toplam_fiyat - $indirim_tutari;
        if ($odenecek_fiyat < 0) { $odenecek_fiyat = 0; }
    }

    if ($kullanici_bakiyesi < $odenecek_fiyat) {
        $_SESSION['hata_mesaji'] = "Yetersiz bakiye! (Gereken: $odenecek_fiyat TL, Bakiye: $kullanici_bakiyesi TL)";
        header("Location: bilet_al.php?sefer_id=" . $sefer_uuid); exit;
    }

    $sql_musaitlik = "SELECT seat_number FROM Booked_Seats
                      JOIN Tickets ON Booked_Seats.ticket_id = Tickets.uuid
                      WHERE Tickets.trip_id = ? AND Tickets.status = 'active'
                      AND Booked_Seats.seat_number IN (";
    $sql_musaitlik .= implode(',', array_fill(0, count($secilen_koltuklar), '?'));
    $sql_musaitlik .= ")";
    $sorgu_musaitlik = $vt->prepare($sql_musaitlik);
    $sorgu_musaitlik->execute(array_merge([$sefer_uuid], $secilen_koltuklar));
    $dolu_koltuklar = $sorgu_musaitlik->fetchAll(PDO::FETCH_COLUMN, 0);

    if (count($dolu_koltuklar) > 0) {
        $_SESSION['hata_mesaji'] = "Üzgünüz, siz onaylarken " . implode(', ', $dolu_koltuklar) . " numaralı koltuk(lar) alındı.";
        header("Location: bilet_al.php?sefer_id=" . $sefer_uuid); exit;
    }

    $sql_bakiye_dus = "UPDATE User SET balance = balance - ? WHERE uuid = ?";
    $vt->prepare($sql_bakiye_dus)->execute([$odenecek_fiyat, $kullanici_uuid]);

    $bilet_uuid = uuid_olustur();
    $sql_bilet_ekle = "INSERT INTO Tickets (uuid, trip_id, user_id, status, total_price) VALUES (?, ?, ?, 'active', ?)";
    $vt->prepare($sql_bilet_ekle)->execute([$bilet_uuid, $sefer_uuid, $kullanici_uuid, $odenecek_fiyat]);

    $sql_koltuk_ekle = "INSERT INTO Booked_Seats (uuid, ticket_id, seat_number) VALUES (?, ?, ?)";
    $sorgu_koltuk = $vt->prepare($sql_koltuk_ekle);
    foreach ($secilen_koltuklar as $koltuk) {
        $koltuk_uuid = uuid_olustur();
        $sorgu_koltuk->execute([$koltuk_uuid, $bilet_uuid, $koltuk]);
    }

    if ($kupon_gecerli && $kupon_uuid !== null) {
        $kullanim_uuid = uuid_olustur();
        $sql_kupon_kullan = "INSERT INTO User_Coupons (uuid, coupon_id, user_id) VALUES (?, ?, ?)";
        $vt->prepare($sql_kupon_kullan)->execute([$kullanim_uuid, $kupon_uuid, $kullanici_uuid]);
    }

    $vt->commit();
    
    $sorgu_yeni_bakiye = $vt->prepare("SELECT balance FROM User WHERE uuid = ?");
    $sorgu_yeni_bakiye->execute([$kullanici_uuid]);
    $yeni_bakiye = $sorgu_yeni_bakiye->fetchColumn();
    $_SESSION['kullanici_bakiyesi'] = $yeni_bakiye;

    $basari_ek_mesaj = $kupon_gecerli ? " ('{$kupon_kodu}' kuponu ile ".($kupon_indirim_orani*100)."% indirim uygulandı)" : "";
    $_SESSION['basari_mesaji'] = "Biletiniz başarıyla satın alındı! (Koltuklar: " . implode(', ', $secilen_koltuklar) . ")" . $basari_ek_mesaj;

    header("Location: basari.php?bilet_id=" . $bilet_uuid);
    exit;

} catch (Exception $hata) {
    if ($vt->inTransaction()) {
        $vt->rollBack();
    }
    $_SESSION['hata_mesaji'] = "Kritik bir hata oluştu: " . $hata->getMessage();
    header("Location: bilet_al.php?sefer_id=" . $sefer_uuid);
    exit;
}
?>