<?php

include 'header.php';

try {
    $sql_firmalar = "SELECT * FROM Bus_Company ORDER BY name ASC"; 
    $sorgu_firmalar = $vt->query($sql_firmalar);
    $firmalar = $sorgu_firmalar->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $hata) {
    echo '<div class="alert alert-danger">Firmalar yüklenirken bir hata oluştu: ' . $hata->getMessage() . '</div>';
    $firmalar = []; 
}
?>

<div class="text-center border-bottom pb-3 mb-4">
    <h1 class="display-5 fw-bold">Çalıştığımız Otobüs Firmaları</h1>
    <p class="lead text-muted">Türkiye'nin önde gelen firmalarıyla güvenli yolculuk.</p>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4"> <?php if (empty($firmalar)): ?>
        <div class="col-12">
            <div class="alert alert-info">Görüntülenecek firma bulunamadı.</div>
        </div>
    <?php else: ?>
        <?php foreach ($firmalar as $firma): ?>
            <div class="col">
                <div class="card h-100 shadow-sm text-center"> <?php
                        $logo_yolu = $firma['logo_path'];
              
                        if (!empty($logo_yolu) && file_exists($logo_yolu)) {
                           
                            echo '<img src="' . htmlspecialchars($logo_yolu) . '" class="card-img-top p-3" alt="' . htmlspecialchars($firma['name']) . ' Logosu" style="max-height: 150px; object-fit: contain;">'; // contain: resmi kırpmadan sığdır
                        } else {
                            
                            echo '<div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 150px;"><i class="bi bi-image-alt display-1 text-muted"></i></div>'; // Gri kutu ve ikon
                        }
                    ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($firma['name']); ?></h5>
                        </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


</div> <footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>