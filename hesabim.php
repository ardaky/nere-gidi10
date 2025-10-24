<?php
include 'header.php';

if (!isset($_SESSION['kullanici_uuid'])) {
    $_SESSION['hata_mesaji'] = "Bu sayfayı görmek için giriş yapmalısınız.";
    header("Location: login.php");
    exit;
}
$kullanici_uuid = $_SESSION['kullanici_uuid'];

$basari_mesaji = $_SESSION['basari_mesaji'] ?? null;
$hata_mesaji = $_SESSION['hata_mesaji'] ?? null;

$aktif_biletler = [];
$gecmis_biletler = [];
$fmt = null;

try {
    $sql = "SELECT
                T.uuid AS bilet_uuid, T.total_price, T.created_at AS satin_alma_tarihi, T.status AS bilet_durumu,
                Tr.departure_city, Tr.destination_city, Tr.departure_time,
                BC.name AS firma_adi, BC.logo_path
            FROM Tickets T
            JOIN Trips Tr ON T.trip_id = Tr.uuid
            JOIN Bus_Company BC ON Tr.company_id = BC.uuid
            WHERE T.user_id = ?
            ORDER BY Tr.departure_time DESC";
    $sorgu = $vt->prepare($sql);
    $sorgu->execute([$kullanici_uuid]);
    $tum_biletler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

    $simdiki_zaman_unix = time();
    foreach ($tum_biletler as $bilet) {
        $kalkis_zamani_unix = strtotime($bilet['departure_time']);
        if ($bilet['bilet_durumu'] == 'active' && $kalkis_zamani_unix > $simdiki_zaman_unix) {
            $aktif_biletler[] = $bilet;
        } else {
            $gecmis_biletler[] = $bilet;
        }
    }
    usort($aktif_biletler, function($a, $b) {
        return strtotime($a['departure_time']) <=> strtotime($b['departure_time']);
    });

    if (class_exists('IntlDateFormatter')) {
        $fmt = new IntlDateFormatter(
            'tr_TR',
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT,
            'Europe/Istanbul',
            IntlDateFormatter::GREGORIAN,
            'dd MMMM yyyy, HH:mm'
        );
    }

} catch (PDOException $hata) {
    $aktif_biletler = [];
    $gecmis_biletler = [];
}
?>

<h2 class="mb-4">Hesabım</h2>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0">Şifre Değiştir</h5>
    </div>
    <div class="card-body">
        <form action="islem_sifre_degistir.php" method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="mevcut_sifre" class="form-label">Mevcut Şifre</label>
                    <input type="password" class="form-control" id="mevcut_sifre" name="mevcut_sifre" required>
                </div>
                <div class="col-md-4">
                    <label for="yeni_sifre" class="form-label">Yeni Şifre</label>
                    <input type="password" class="form-control" id="yeni_sifre" name="yeni_sifre" minlength="6" required>
                    <div class="form-text">En az 6 karakter olmalıdır.</div>
                </div>
                <div class="col-md-4">
                    <label for="yeni_sifre_tekrar" class="form-label">Yeni Şifre (Tekrar)</label>
                    <input type="password" class="form-control" id="yeni_sifre_tekrar" name="yeni_sifre_tekrar" required>
                </div>
                <div class="col-12 mt-3 text-end">
                    <button type="submit" class="btn btn-warning">Şifreyi Güncelle</button>
                </div>
            </div>
        </form>
    </div>
</div>

<h3 class="mb-3">Biletlerim</h3>

<ul class="nav nav-tabs mb-3" id="biletTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="aktif-tab" data-bs-toggle="tab" data-bs-target="#aktif-biletler" type="button" role="tab" aria-controls="aktif-biletler" aria-selected="true">
        Aktif Biletlerim (<?php echo count($aktif_biletler); ?>)
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="gecmis-tab" data-bs-toggle="tab" data-bs-target="#gecmis-biletler" type="button" role="tab" aria-controls="gecmis-biletler" aria-selected="false">
        Geçmiş Biletlerim (<?php echo count($gecmis_biletler); ?>)
    </button>
  </li>
</ul>

<div class="tab-content" id="biletTabContent">

  <div class="tab-pane fade show active" id="aktif-biletler" role="tabpanel" aria-labelledby="aktif-tab">
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($aktif_biletler)): ?>
                <div class="alert alert-info mb-0">Görüntülenecek aktif biletiniz bulunmamaktadır.</div>
                <a href="index.php" class="btn btn-primary mt-3">Hemen Bilet Al</a>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Firma</th><th>Güzergah</th><th>Kalkış Zamanı</th><th>Fiyat</th><th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aktif_biletler as $bilet): ?>
                                <?php
                                    $kalkis_zamani_unix = strtotime($bilet['departure_time']);
                                    $simdiki_zaman_unix = time();
                                    $iptal_edilebilir = ($kalkis_zamani_unix - $simdiki_zaman_unix) > 3600;
                                    $formatted_departure_time = $fmt ? $fmt->format($kalkis_zamani_unix) : date('d M Y, H:i', $kalkis_zamani_unix);
                                ?>
                                <tr>
                                    <td>
                                        <?php $logo_yolu_aktif = $bilet['logo_path']; if (!empty($logo_yolu_aktif) && file_exists($logo_yolu_aktif)): ?>
                                            <img src="<?php echo htmlspecialchars($logo_yolu_aktif); ?>" alt="" class="me-1 align-middle" style="height: 20px; max-width: 50px; object-fit: contain;">
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($bilet['firma_adi']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($bilet['departure_city']); ?> <i class="bi bi-arrow-right"></i> <?php echo htmlspecialchars($bilet['destination_city']); ?>
                                    </td>
                                    <td><?php echo $formatted_departure_time; ?></td>
                                    <td><?php echo htmlspecialchars($bilet['total_price']); ?> TL</td>
                                    <td>
                                        <a href="bilet_pdf.php?bilet_id=<?php echo $bilet['bilet_uuid']; ?>" class="btn btn-success btn-sm" target="_blank" title="PDF İndir"><i class="bi bi-file-earmark-pdf"></i></a>
                                        <form action="islem_bilet_iptal.php" method="POST" class="d-inline" onsubmit="return confirm('Bileti iptal etmek istediğinize emin misiniz?');">
                                            <input type="hidden" name="bilet_uuid" value="<?php echo $bilet['bilet_uuid']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="İptal Et" <?php if (!$iptal_edilebilir) echo 'disabled'; ?>><i class="bi bi-trash"></i></button>
                                            <?php if (!$iptal_edilebilir): ?><small class="text-muted d-block">İptal süresi geçti.</small><?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
  </div>

  <div class="tab-pane fade" id="gecmis-biletler" role="tabpanel" aria-labelledby="gecmis-tab">
     <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($gecmis_biletler)): ?>
                <div class="alert alert-info mb-0">Görüntülenecek geçmiş biletiniz bulunmamaktadır.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Firma</th><th>Güzergah</th><th>Kalkış Zamanı</th><th>Durum</th><th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gecmis_biletler as $bilet): ?>
                                <?php
                                    $durum_text = ''; $durum_class = '';
                                    if ($bilet['bilet_durumu'] == 'cancelled') {
                                        $durum_text = 'İptal Edildi'; $durum_class = 'text-danger fw-bold';
                                    } elseif (strtotime($bilet['departure_time']) <= time()) {
                                         $durum_text = 'Tamamlandı'; $durum_class = 'text-success fw-bold';
                                    } else {
                                        $durum_text = ucfirst($bilet['bilet_durumu']); $durum_class = 'text-warning';
                                    }
                                    $formatted_departure_time_gecmis = $fmt ? $fmt->format(strtotime($bilet['departure_time'])) : date('d M Y, H:i', strtotime($bilet['departure_time']));
                                ?>
                                <tr>
                                    <td>
                                        <?php $logo_yolu_gecmis = $bilet['logo_path']; if (!empty($logo_yolu_gecmis) && file_exists($logo_yolu_gecmis)): ?>
                                            <img src="<?php echo htmlspecialchars($logo_yolu_gecmis); ?>" alt="" class="me-1 align-middle" style="height: 20px; max-width: 50px; object-fit: contain;">
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($bilet['firma_adi']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($bilet['departure_city']); ?> <i class="bi bi-arrow-right"></i> <?php echo htmlspecialchars($bilet['destination_city']); ?>
                                    </td>
                                    <td><?php echo $formatted_departure_time_gecmis; ?></td>
                                    <td><span class="<?php echo $durum_class; ?>"><?php echo $durum_text; ?></span></td>
                                    <td>
                                        <a href="bilet_pdf.php?bilet_id=<?php echo $bilet['bilet_uuid']; ?>" class="btn btn-secondary btn-sm" target="_blank" title="PDF İndir"><i class="bi bi-file-earmark-pdf"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
  </div>

</div>

</div>
<footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>
</body>
</html>