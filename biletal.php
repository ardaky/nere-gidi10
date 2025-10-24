<?php
include 'header.php';

if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] !== 'user') {
    // Admin/Firma Admin buraya düşer
    $_SESSION['hata_mesaji'] = "Sadece Yolcu (User) rolündeki kullanıcılar bilet satın alabilir.";
    header("Location: index.php");
    exit;
}

$sefer_uuid = isset($_GET['sefer_id']) ? htmlspecialchars($_GET['sefer_id']) : '';

if (empty($sefer_uuid)) {
    echo '<div class="alert alert-danger">Geçersiz sefer ID\'si.</div>';
    exit;
}

try {
    $sql_sefer = "SELECT Trips.*, Bus_Company.name AS firma_adi, Bus_Company.logo_path
                  FROM Trips
                  JOIN Bus_Company ON Trips.company_id = Bus_Company.uuid
                  WHERE Trips.uuid = ?";
    $sorgu_sefer = $vt->prepare($sql_sefer);
    $sorgu_sefer->execute([$sefer_uuid]);
    $sefer = $sorgu_sefer->fetch(PDO::FETCH_ASSOC);

    if (!$sefer) {
        echo '<div class="alert alert-danger">Sefer bulunamadı.</div>';
        exit;
    }

    $sql_koltuklar = "SELECT Booked_Seats.seat_number, User.cinsiyet
                      FROM Booked_Seats
                      JOIN Tickets ON Booked_Seats.ticket_id = Tickets.uuid
                      JOIN User ON Tickets.user_id = User.uuid
                      WHERE Tickets.trip_id = ? AND Tickets.status = 'active'";

    $sorgu_koltuklar = $vt->prepare($sql_koltuklar);
    $sorgu_koltuklar->execute([$sefer_uuid]);
    $dolu_koltuk_map = $sorgu_koltuklar->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $hata) {
    echo '<div class="alert alert-danger">Veritabanı hatası: ' . $hata->getMessage() . '</div>';
    exit;
}

function render_seat($seat_num, $dolu_koltuk_map) {
    $is_dolu = array_key_exists((string)$seat_num, $dolu_koltuk_map);
    $disabled_attr = $is_dolu ? 'disabled' : '';
    $label_class = 'seat-empty';
    if ($is_dolu) {
        $cinsiyet = $dolu_koltuk_map[(string)$seat_num];
        if ($cinsiyet == 'erkek') { $label_class = 'seat-male'; }
        elseif ($cinsiyet == 'kadin') { $label_class = 'seat-female'; }
        else { $label_class = 'seat-disabled'; }
    }
    echo "<div class='seat'>";
    echo "<input type='checkbox' class='btn-check' id='koltuk_{$seat_num}' name='koltuklar[]' value='{$seat_num}' {$disabled_attr}>";
    echo "<label class='btn seat-label {$label_class}' for='koltuk_{$seat_num}'>{$seat_num}</label>";
    echo "</div>";
}
?>

<style>
    .bus-container { border: 2px solid #6c757d; border-radius: 15px 15px 5px 5px; background: #f8f9fa; padding: 15px; max-width: 320px; margin: auto; }
    .bus-front { height: 50px; border-radius: 10px 10px 0 0; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #495057; background: #ced4da; }
    .bus-row { display: flex; justify-content: center; margin-bottom: 8px; }
    .seat { margin: 0 3px; }
    .aisle { width: 30px; }
    .seat-label { width: 40px; height: 35px; font-weight: bold; border: 2px solid #22C55E; color: #22C55E; background-color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
    .seat-label.seat-male { background-color: #00B4D8; border-color: #00B4D8; color: #fff; }
    .seat-label.seat-female { background-color: #F75990; border-color: #F75990; color: #fff; }
    .seat-label.seat-disabled { background-color: #6c757d; border-color: #6c757d; color: #fff; opacity: 0.7; }
    .seat input[type=checkbox]:checked + .seat-label { background-color: #E63946 !important; border-color: #E63946 !important; color: #fff !important; }
    .back-row { border-top: 1px dashed #6c757d; padding-top: 8px; }
    .firma-logo-small { height: 35px; max-width: 100px; object-fit: contain; } /* Logo için yeni stil */
</style>

<form action="onay.php" method="POST">
    <div class="row g-4">

        <div class="col-lg-4 order-lg-2">
            <div class="sticky-top" style="top: 20px;">

                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        <h4 class="mb-0">Sefer Bilgileri</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <?php
                                $logo_yolu = $sefer['logo_path'];
                                if (!empty($logo_yolu) && file_exists($logo_yolu)):
                            ?>
                                <img src="<?php echo htmlspecialchars($logo_yolu); ?>" alt="<?php echo htmlspecialchars($sefer['firma_adi']); ?> Logosu" class="me-2 firma-logo-small">
                            <?php endif; ?>
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($sefer['firma_adi']); ?></h5>
                        </div>
                        <p class="card-text">
                            <strong>Kalkış:</strong> <?php echo htmlspecialchars($sefer['departure_city']); ?><br>
                            <strong>Varış:</strong> <?php echo htmlspecialchars($sefer['destination_city']); ?>
                        </p>
                        <p class="card-text">
                            <strong>Tarih:</strong> <?php echo date('d M Y, H:i', strtotime($sefer['departure_time'])); ?>
                        </p>
                        <hr>
                        <h3 class="text-primary"><?php echo htmlspecialchars($sefer['price']); ?> TL</h3>
                        <p class="text-muted">(Koltuk Başına Fiyat)</p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Ödeme</h5>
                        <input type="hidden" name="sefer_uuid" value="<?php echo htmlspecialchars($sefer_uuid); ?>">
                        <div class="mb-3">
                            <label for="kupon_kodu" class="form-label">Kupon Kodu (Varsa)</label>
                            <input type="text" id="kupon_kodu" name="kupon_kodu" class="form-control form-control-lg">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Satın Al</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-lg-8 order-lg-1">
            <h2>Koltuk Seçimi</h2>
            <p>Lütfen koltuğunuzu seçin. (Standart 2+2 Düzen)</p>

            <div class="bus-container">
                <div class="bus-front">
                    <i class="bi bi-person-workspace"></i> &nbsp; ŞOFÖR
                </div>
                <?php
                $kapasite = $sefer['capacity'];
                $seat_number = 1;
                $rows = floor($kapasite / 4);
                for ($r = 1; $r <= $rows; $r++):
                    echo '<div class="bus-row">';
                    if($seat_number <= $kapasite) render_seat($seat_number++, $dolu_koltuk_map);
                    if($seat_number <= $kapasite) render_seat($seat_number++, $dolu_koltuk_map);
                    echo '<div class="aisle"></div>';
                    if($seat_number <= $kapasite) render_seat($seat_number++, $dolu_koltuk_map);
                    if($seat_number <= $kapasite) render_seat($seat_number++, $dolu_koltuk_map);
                    echo '</div>';
                endfor;
                if ($seat_number <= $kapasite) {
                    echo '<div class="bus-row back-row">';
                    while ($seat_number <= $kapasite) {
                        render_seat($seat_number++, $dolu_koltuk_map);
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</form>

</div>
<footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>