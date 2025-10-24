<?php

include 'header.php';


if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}


try {
    $sorgu_firmalar = $vt->query("SELECT uuid, name FROM Bus_Company ORDER BY name ASC");
    $firmalar = $sorgu_firmalar->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $hata) {
    echo '<div class="alert alert-danger">Firmalar yüklenirken bir hata oluştu: ' . $hata->getMessage() . '</div>';
    $firmalar = []; 
}



$hata_mesaji = $_SESSION['hata_mesaji'] ?? null; unset($_SESSION['hata_mesaji']);
?>

<h2 class="mb-4">Yeni Firma Admin Kullanıcısı Ekle</h2>

<?php if ($hata_mesaji): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="islem_firma_admin_ekle.php" method="POST">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="full_name" class="form-label">Ad Soyad</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">E-posta Adresi</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="form-text">Bu e-posta adresi ile giriş yapacak.</div>
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Şifre</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="form-text">Güçlü bir şifre belirleyin.</div>
                </div>

                <div class="col-md-6">
                    <label for="company_id" class="form-label">Atanacak Firma</label>
                    <select id="company_id" name="company_id" class="form-select" required>
                        <option value="" disabled selected>-- Firma Seçin --</option>
                        <?php foreach ($firmalar as $firma): ?>
                            <option value="<?php echo htmlspecialchars($firma['uuid']); ?>">
                                <?php echo htmlspecialchars($firma['name']); ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                    <div class="form-text">Bu kullanıcı hangi firmanın seferlerini yönetecek?</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cinsiyet</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cinsiyet" id="cinsiyet_erkek" value="erkek" required>
                            <label class="form-check-label" for="cinsiyet_erkek">Erkek</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cinsiyet" id="cinsiyet_kadin" value="kadin" required>
                            <label class="form-check-label" for="cinsiyet_kadin">Kadın</label>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Firma Admin'i Kaydet</button>
                    <a href="admin_panel.php" class="btn btn-secondary">İptal</a>
                </div>
            </div> </form>
    </div> </div> </div> <footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>