<?php
include 'header.php';

try {
    $sql_cities = "SELECT DISTINCT departure_city AS sehir FROM Trips
                   UNION
                   SELECT DISTINCT destination_city AS sehir FROM Trips
                   ORDER BY sehir ASC";
    $sehir_sorgu = $vt->query($sql_cities);
    $tum_sehirler = $sehir_sorgu->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $hata) {
    $tum_sehirler = [];
}

try {
    $sql_random_trips = "SELECT
                            T.uuid AS trip_uuid,
                            T.departure_city,
                            T.destination_city,
                            T.price,
                            BC.name AS firma_adi
                        FROM Trips T
                        JOIN Bus_Company BC ON T.company_id = BC.uuid
                        WHERE T.departure_time > datetime('now', '+3 hour')
                        ORDER BY RANDOM()
                        LIMIT 3";
    $sorgu_random_trips = $vt->query($sql_random_trips);
    $rastgele_seferler = $sorgu_random_trips->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $hata) {
    $rastgele_seferler = [];
}

function generate_image_filename($city_name) {
    if ($city_name === null) return 'default.jpg';
    $turkish = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç', ' '];
    $english = ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c', ''];
    $city_name_lower = mb_strtolower(trim($city_name), 'UTF-8');
    $filename_base = str_replace($turkish, $english, $city_name_lower);
    $filename_base = preg_replace('/[^a-z0-9]/', '', $filename_base);
    if (empty($filename_base)) return 'default.jpg';
    return $filename_base . '.jpg';
}
?>

<style>
    .popular-routes .card-img-top,
    .random-trips .card-img-top {
        height: 200px;
        object-fit: cover;
    }
    .hero-section {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/hero-bg.jpg') center center/cover no-repeat;
        min-height: 400px;
        display: flex;
        align-items: center;
    }
    .route-card-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<div class="bg-dark text-light p-5 mb-4 rounded-3 shadow hero-section">
    <div class="container-fluid text-center">
        <h1 class="display-3 fw-bold">Türkiye'yi Keşfedin</h1>
        <p class="fs-4 col-md-8 mx-auto">Nere Gidi10 ile konforlu ve uygun fiyatlı otobüs yolculuğunun keyfini çıkarın.</p>
    </div>
</div>

<div class="container" style="margin-top: -80px;">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <form action="seferler.php" method="GET" class="bg-light p-4 rounded shadow-lg">
                 <div class="row g-3 align-items-end justify-content-center">
                    <div class="col-md text-start">
                        <label for="kalkis_select" class="form-label fw-bold">Nereden</label>
                        <select id="kalkis_select" name="kalkis_sehri" class="form-select form-select-lg" required>
                            <option value="" disabled selected>-- Seçiniz --</option>
                            <?php foreach ($tum_sehirler as $sehir): ?>
                                <option value="<?php echo htmlspecialchars($sehir['sehir']); ?>"><?php echo htmlspecialchars($sehir['sehir']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md text-start">
                        <label for="varis_select" class="form-label fw-bold">Nereye</label>
                        <select id="varis_select" name="varis_sehri" class="form-select form-select-lg" required>
                            <option value="" disabled selected>-- Seçiniz --</option>
                            <?php foreach ($tum_sehirler as $sehir): ?>
                                <option value="<?php echo htmlspecialchars($sehir['sehir']); ?>"><?php echo htmlspecialchars($sehir['sehir']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-auto d-grid">
                         <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-lg px-4"><i class="bi bi-search"></i> Bul</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container my-5 pt-4 popular-routes">
    <h2 class="text-center mb-4">Gözde Rotalarımız</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card h-100 shadow-sm overflow-hidden">
                <?php
                    $dest_city_1 = 'Ankara';
                    $img_filename_1 = generate_image_filename($dest_city_1);
                    $img_path_1 = 'images/' . $img_filename_1;
                    if (!file_exists($img_path_1)) { $img_path_1 = 'images/default.jpg'; }
                ?>
                <img src="<?php echo htmlspecialchars($img_path_1); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($dest_city_1); ?>">
                <div class="card-body">
                    <h5 class="card-title route-card-title">İstanbul <i class="bi bi-arrow-right"></i> <?php echo htmlspecialchars($dest_city_1); ?></h5>
                    <a href="seferler.php?kalkis_sehri=Istanbul&varis_sehri=<?php echo htmlspecialchars($dest_city_1); ?>" class="btn btn-sm btn-outline-primary mt-2">Seferleri Gör</a>
                </div>
            </div>
        </div>
        <div class="col">
             <div class="card h-100 shadow-sm overflow-hidden">
                 <?php
                    $dest_city_2 = 'Istanbul';
                    $img_filename_2 = generate_image_filename($dest_city_2);
                    $img_path_2 = 'images/' . $img_filename_2;
                    if (!file_exists($img_path_2)) { $img_path_2 = 'images/default.jpg'; }
                ?>
                <img src="<?php echo htmlspecialchars($img_path_2); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($dest_city_2); ?>">
                <div class="card-body">
                    <h5 class="card-title route-card-title">İzmir <i class="bi bi-arrow-right"></i> <?php echo htmlspecialchars($dest_city_2); ?></h5>
                    <a href="seferler.php?kalkis_sehri=Izmir&varis_sehri=<?php echo htmlspecialchars($dest_city_2); ?>" class="btn btn-sm btn-outline-primary mt-2">Seferleri Gör</a>
                </div>
            </div>
        </div>
        <div class="col">
             <div class="card h-100 shadow-sm overflow-hidden">
                 <?php
                    $dest_city_3 = 'Antalya';
                    $img_filename_3 = generate_image_filename($dest_city_3);
                    $img_path_3 = 'images/' . $img_filename_3;
                    if (!file_exists($img_path_3)) { $img_path_3 = 'images/default.jpg'; }
                ?>
                <img src="<?php echo htmlspecialchars($img_path_3); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($dest_city_3); ?>">
                <div class="card-body">
                    <h5 class="card-title route-card-title">Ankara <i class="bi bi-arrow-right"></i> <?php echo htmlspecialchars($dest_city_3); ?></h5>
                     <a href="#" class="btn btn-sm btn-outline-primary disabled mt-2">Yakında</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5 random-trips">
    <h2 class="text-center mb-4">Sizin İçin Seçtiklerimiz</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if (empty($rastgele_seferler)): ?>
            <div class="col-12">
                <p class="text-center text-muted">Şu anda önerilecek aktif sefer bulunamadı.</p>
            </div>
        <?php else: ?>
            <?php foreach ($rastgele_seferler as $sefer): ?>
                <?php
                    $image_filename = generate_image_filename($sefer['destination_city']);
                    $image_path = 'images/' . $image_filename;
                    if (!file_exists($image_path)) {
                        $image_path = 'images/default.jpg';
                    }
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm overflow-hidden">
                        <img src="<?php echo htmlspecialchars($image_path); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($sefer['destination_city']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title route-card-title">
                                <?php echo htmlspecialchars($sefer['departure_city']); ?>
                                <i class="bi bi-arrow-right"></i>
                                <?php echo htmlspecialchars($sefer['destination_city']); ?>
                            </h5>
                            <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($sefer['firma_adi']); ?></p>
                            <p class="card-text fw-bold text-danger fs-5 mb-auto"><?php echo htmlspecialchars($sefer['price']); ?> TL</p>
                            <a href="biletal.php?sefer_id=<?php echo $sefer['trip_uuid']; ?>" class="btn btn-sm btn-primary mt-2">Bilet Al</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</div>
<footer class="text-center text-muted mt-5 mb-4">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

<script>
    document.addEventListener('DOMContentLoaded',function(){
        const k=document.getElementById('kalkis_select'), v=document.getElementById('varis_select');
        function u(){
            if(!k||!v) return;
            const a=k.value, b=v.value;
            for(const o of v.options) o.disabled=(o.value===a && o.value!=="");
            for(const o of k.options) o.disabled=(o.value===b && o.value!=="");
        }
        if(k&&v){
            u();
            k.addEventListener('change',u);
            v.addEventListener('change',u);
        }
    });
</script>

</body>
</html>