<?php
ob_start();
include 'header.php';

if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}

try {
    $sorgu_firmalar = $vt->query("SELECT * FROM Bus_Company ORDER BY name ASC");
    $firmalar = $sorgu_firmalar->fetchAll(PDO::FETCH_ASSOC);

    $sql_firma_adminler = "SELECT User.*, Bus_Company.name AS firma_adi
                           FROM User
                           LEFT JOIN Bus_Company ON User.company_id = Bus_Company.uuid
                           WHERE User.role = 'firma_admin'
                           ORDER BY User.created_at DESC";
    $sorgu_firma_adminler = $vt->query($sql_firma_adminler);
    $firma_adminler = $sorgu_firma_adminler->fetchAll(PDO::FETCH_ASSOC);

    $sql_genel_kuponlar = "SELECT * FROM Coupons
                           WHERE company_id IS NULL
                           ORDER BY created_at DESC
                           LIMIT 5";
    $sorgu_genel_kuponlar = $vt->query($sql_genel_kuponlar);
    $genel_kuponlar = $sorgu_genel_kuponlar->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $hata) {
    echo '<div class="alert alert-danger">Veriler yüklenirken bir hata oluştu: ' . $hata->getMessage() . '</div>';
    $firmalar = [];
    $firma_adminler = [];
    $genel_kuponlar = [];
}
?>
<style>
    .list-group-item strong {
        line-height: 25px;
        height: 25px;
        display: inline-block;
        margin-bottom: 0 !important;
        margin-top: 0 !important;
    }
    .list-group-item img {
        vertical-align: middle;
    }
</style>
<h2 class="mb-4">Admin Paneli</h2>

<?php if (isset($_SESSION['basari_mesaji'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['basari_mesaji']; unset($_SESSION['basari_mesaji']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['hata_mesaji'])): ?>
     <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['hata_mesaji']; unset($_SESSION['hata_mesaji']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">

    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Otobüs Firmaları</h5>
                <a href="firma_ekle.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Yeni Firma Ekle
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($firmalar)): ?>
                    <div class="p-3"><div class="alert alert-info mb-0">Henüz hiç firma eklenmemiş.</div></div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($firmalar as $firma): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="d-flex align-items-center">
                                    <?php if (!empty($firma['logo_path']) && file_exists($firma['logo_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($firma['logo_path']); ?>" alt="<?php echo htmlspecialchars($firma['name']); ?>" height="25" class="me-3">
                                    <?php endif; ?>
                                    <strong><?php echo htmlspecialchars($firma['name']); ?></strong>
                                </span>
                                <div class="text-nowrap">
                                    <a href="firma_duzenle.php?firma_uuid=<?php echo $firma['uuid']; ?>" class="btn btn-warning btn-sm me-1 py-0 px-1 align-middle" title="Düzenle">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="islem_firma_sil.php" method="POST" class="d-inline" onsubmit="return confirm('Bu firmayı silmek istediğinize emin misiniz? (Bu firmaya ait TÜM seferler de silinecektir!)');">
                                        <input type="hidden" name="firma_uuid" value="<?php echo $firma['uuid']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm py-0 px-1 align-middle" title="Sil">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
             <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Firma Admin Kullanıcıları</h5>
                <a href="firma_admin_ekle.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-plus"></i> Yeni Firma Admin Ekle
                </a>
            </div>
            <div class="card-body p-0">
                 <?php if (empty($firma_adminler)): ?>
                    <div class="p-3"><div class="alert alert-info mb-0">Henüz hiç Firma Admin kullanıcısı eklenmemiş.</div></div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($firma_adminler as $admin): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold"><?php echo htmlspecialchars($admin['full_name']); ?></span>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($admin['email']); ?></small>
                                </div>
                                <span class="badge bg-secondary rounded-pill mx-2 flex-shrink-0">
                                    <?php echo $admin['firma_adi'] ? htmlspecialchars($admin['firma_adi']) : 'Atanmamış'; ?>
                                </span>
                                <div class="text-nowrap">
                                     <a href="firma_admin_duzenle.php?admin_uuid=<?php echo $admin['uuid']; ?>" class="btn btn-warning btn-sm me-1 py-0 px-1 align-middle" title="Düzenle">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="islem_firma_admin_sil.php" method="POST" class="d-inline" onsubmit="return confirm('Bu Firma Admin kullanıcısını silmek istediğinize emin misiniz?');">
                                        <input type="hidden" name="admin_uuid" value="<?php echo $admin['uuid']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm py-0 px-1 align-middle" title="Sil">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 mt-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Genel İndirim Kuponları (Tüm Firmalar)</h5>
                 <a href="admin_kupon_yonetimi.php" class="btn btn-info btn-sm">
                     <i class="bi bi-tags"></i> Genel Kuponları Yönet
                 </a>
            </div>
             <div class="card-body">
                <?php if (empty($genel_kuponlar)): ?>
                    <p class="text-muted mb-0">Henüz hiç genel kupon oluşturulmamış.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($genel_kuponlar as $kupon): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>
                                    <strong class="text-danger"><?php echo htmlspecialchars($kupon['code']); ?></strong>
                                    <small class="text-muted ms-2">(<?php echo htmlspecialchars($kupon['discount'] * 100); ?>%)</small>
                                </span>
                                <small class="text-muted">
                                    Son Kullanma: <?php echo date('d.m.Y', strtotime($kupon['expire_date'])); ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                        <?php
                            $toplam_genel_kupon_sorgu = $vt->query("SELECT COUNT(*) FROM Coupons WHERE company_id IS NULL");
                            $toplam_genel_kupon = $toplam_genel_kupon_sorgu->fetchColumn();
                            if ($toplam_genel_kupon > 5):
                        ?>
                            <li class="list-group-item text-center px-0 pt-3">
                                <a href="admin_kupon_yonetimi.php">Tüm Genel Kuponları Gör (<?php echo $toplam_genel_kupon; ?>)</a>
                            </li>
                        <?php endif; ?>
                    </ul>
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