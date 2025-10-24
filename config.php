<?php
date_default_timezone_set('Europe/Istanbul');
session_start();

$db_yolu = __DIR__ . '/database/database.db';

try {
  
    $vt = new PDO('sqlite:' . $db_yolu);

   
    $vt->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  
    $vt->exec('PRAGMA foreign_keys = ON;');

} catch (PDOException $hata) { 
   
    echo "Bağlantı hatası: " . $hata->getMessage();
    exit;
}


function uuid_olustur() { 
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function duzelt_sehir_adi($sehir) {
    $sehir = trim($sehir);
    $duzeltmeler = [
        'istanbul' => 'İstanbul',
        'i̇stanbul' => 'İstanbul',
        'izmir' => 'İzmir',
        'i̇zmir' => 'İzmir',
        'ankara' => 'Ankara',
        'gümüshane' => 'Gümüşhane',
        'gumushane' => 'Gümüşhane',
        'diyarbakir' => 'Diyarbakır',
        'erzincan' => 'Erzincan',
        'elazig' => 'Elazığ',
        'elaziğ' => 'Elazığ',
        'konya' => 'Konya',
        'karaman' => 'Karaman',
        'kayseri' => 'Kayseri',
        'karabuk' => 'Karabük',
        'karabük' => 'Karabük',
        'erzurum' => 'Erzurum',
        'bursa' => 'Bursa',
        'adana' => 'Adana',
        'antalya' => 'Antalya',
        'mersin' => 'Mersin'
    ];
    $sehir_kucuk = mb_strtolower($sehir, 'UTF-8');

    if (isset($duzeltmeler[$sehir_kucuk])) {
        return $duzeltmeler[$sehir_kucuk];
    }

    if(empty($sehir)) return '';
    $ilk_harf = mb_strtoupper(mb_substr($sehir, 0, 1, 'UTF-8'), 'UTF-8');
    $kalan = mb_strtolower(mb_substr($sehir, 1, null, 'UTF-8'), 'UTF-8');
    return $ilk_harf . $kalan;
}

?>