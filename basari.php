<?php
include 'header.php';

if (!isset($_SESSION['kullanici_uuid'])) {
    header("Location: login.php");
    exit;
}

$bilet_uuid = isset($_GET['bilet_id']) ? htmlspecialchars($_GET['bilet_id']) : '';
$kullanici_uuid = $_SESSION['kullanici_uuid'];

if (empty($bilet_uuid)) {
    echo '<div class="alert alert-danger">Geçersiz bilet ID\'si.</div>';
    exit;
}

try {
    $sql = "SELECT
                T.total_price, T.created_at AS satin_alma_tarihi,
                Tr.departure_city, Tr.destination_city, Tr.departure_time, Tr.arrival_time,
                BC.name AS firma_adi, BC.logo_path,
                U.full_name AS yolcu_adi,
                GROUP_CONCAT(BS.seat_number) AS koltuk_numaralari
            FROM Tickets T
            JOIN Trips Tr ON T.trip_id = Tr.uuid
            JOIN Bus_Company BC ON Tr.company_id = BC.uuid
            JOIN User U ON T.user_id = U.uuid
            LEFT JOIN Booked_Seats BS ON BS.ticket_id = T.uuid
            WHERE T.uuid = ? AND T.user_id = ?
            GROUP BY T.uuid";
    $sorgu = $vt->prepare($sql);
    $sorgu->execute([$bilet_uuid, $kullanici_uuid]);
    $bilet = $sorgu->fetch(PDO::FETCH_ASSOC);

    if (!$bilet) {
        echo '<div class="alert alert-danger">Başarı detayı görüntülenemedi veya bu bilete erişim izniniz yok.</div>';
        exit;
    }

    $sql_koltuklar = "SELECT seat_number FROM Booked_Seats WHERE ticket_id = ?";
    $sorgu_koltuklar = $vt->prepare($sql_koltuklar);
    $sorgu_koltuklar->execute([$bilet_uuid]);
    $alinan_koltuklar = $sorgu_koltuklar->fetchAll(PDO::FETCH_COLUMN, 0);

} catch (PDOException $hata) {
    echo '<div class="alert alert-danger">Bilet bilgileri alınırken bir hata oluştu: ' . $hata->getMessage() . '</div>';
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="text-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-check-circle-fill text-success mb-2" viewBox="0 0 16 16">
              <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>
            <h1 class="display-6 text-success">İşlem Başarılı!</h1>
            <p class="lead">Biletiniz başarıyla oluşturuldu. İyi yolculuklar dileriz!</p>
        </div>
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Bilet Detaylarınız</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-7 border-end mb-3 mb-md-0 pb-3 pb-md-0">
                         <div class="d-flex align-items-center mb-2">
                            <?php
                                $logo_yolu_basari = $bilet['logo_path'];
                                if (!empty($logo_yolu_basari) && file_exists($logo_yolu_basari)):
                            ?>
                                <img src="<?php echo htmlspecialchars($logo_yolu_basari); ?>" alt="" class="me-2 align-middle" style="height: 30px; max-width: 80px; object-fit: contain;">
                            <?php endif; ?>
                            <p class="mb-0"><strong>Firma:</strong> <?php echo htmlspecialchars($bilet['firma_adi']); ?></p>
                         </div>
                         <h5>
                            <span class="text-primary fw-bold"><?php echo htmlspecialchars($bilet['departure_city']); ?></span>
                            <i class="bi bi-arrow-right mx-1"></i>
                            <span class="text-primary fw-bold"><?php echo htmlspecialchars($bilet['destination_city']); ?></span>
                        </h5>
                        <p class="mb-0"><strong>Kalkış Zamanı:</strong> <?php echo date('d M Y, H:i', strtotime($bilet['departure_time'])); ?></p>
                    </div>
                    <div class="col-md-5 ps-md-4">
                         <p class="mb-2"><strong>Seçilen Koltuklar:</strong></p>
                         <p>
                            <?php if (!empty($alinan_koltuklar)): ?>
                                <?php foreach ($alinan_koltuklar as $koltuk): ?>
                                    <span class="badge bg-danger fs-5 me-1"><?php echo $koltuk; ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                         </p>
                         <hr>
                         <p class="mb-2"><strong>Ödenen Tutar:</strong></p>
                         <p><span class="h4 text-success fw-bold"><?php echo htmlspecialchars($bilet['total_price']); ?> TL</span></p>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="bilet_pdf.php?bilet_id=<?php echo $bilet_uuid; ?>" class="btn btn-success me-2" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> PDF İndir
                        </a>
                        <a href="hesabim.php" class="btn btn-secondary">Tüm Biletlerim</a>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-primary">Ana Sayfaya Dön</a>
                    </div>
                </div>
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