<?php

include 'header.php';


if (!isset($_SESSION['kullanici_uuid']) || $_SESSION['kullanici_rolu'] != 'admin') {
    $_SESSION['hata_mesaji'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: index.php");
    exit;
}


$admin_uuid_duzenle = isset($_GET['admin_uuid']) ? htmlspecialchars($_GET['admin_uuid']) : '';

if (empty($admin_uuid_duzenle)) {
    $_SESSION['hata_mesaji'] = "Düzenlenecek Firma Admin ID'si belirtilmedi.";
    header("Location: admin_panel.php");
    exit;
}


try {
    $sql_admin = "SELECT uuid, full_name, email, company_id, cinsiyet FROM User WHERE uuid = ? AND role = 'firma_admin'";
    $sorgu_admin = $vt->prepare($sql_admin);
    $sorgu_admin->execute([$admin_uuid_duzenle]);
    $admin = $sorgu_admin->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        $_SESSION['hata_mesaji'] = "Düzenlenecek Firma Admin kullanıcısı bulunamadı.";
        header("Location: admin_panel.php");
        exit;
    }

    
    $sorgu_firmalar = $vt->query("SELECT uuid, name FROM Bus_Company ORDER BY name ASC");
    $firmalar = $sorgu_firmalar->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $hata) {
    $_SESSION['hata_mesaji'] = "Kullanıcı veya firma bilgileri alınırken hata oluştu: " . $hata->getMessage();
    header("Location: admin_panel.php");
    exit;
}


$hata_mesaji = $_SESSION['hata_mesaji'] ?? null; unset($_SESSION['hata_mesaji']);
?>

<h2 class="mb-4">Firma Admin Kullanıcısını Düzenle</h2>

<?php if ($hata_mesaji): ?>
    <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="islem_firma_admin_duzenle.php" method="POST">

            <input type="hidden" name="admin_uuid" value="<?php echo htmlspecialchars($admin['uuid']); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="full_name" class="form-label">Ad Soyad</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">E-posta Adresi</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Yeni Şifre (Değiştirmek istemiyorsanız boş bırakın)</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <div class="form-text">Boş bırakılırsa mevcut şifre değişmez.</div>
                </div>

                <div class="col-md-6">
                    <label for="company_id" class="form-label">Atanacak Firma</label>
                    <select id="company_id" name="company_id" class="form-select" required>
                        <option value="" disabled>-- Firma Seçin --</option>
                        <?php foreach ($firmalar as $firma): ?>
                            <option value="<?php echo htmlspecialchars($firma['uuid']); ?>" <?php if ($firma['uuid'] == $admin['company_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($firma['name']); ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cinsiyet</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cinsiyet" id="cinsiyet_erkek" value="erkek" <?php if ($admin['cinsiyet'] == 'erkek') echo 'checked'; ?> required>
                            <label class="form-check-label" for="cinsiyet_erkek">Erkek</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cinsiyet" id="cinsiyet_kadin" value="kadin" <?php if ($admin['cinsiyet'] == 'kadin') echo 'checked'; ?> required>
                            <label class="form-check-label" for="cinsiyet_kadin">Kadın</label>
                        </div>
                    </div>
                </div>


                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning">Değişiklikleri Kaydet</button>
                    <a href="admin_panel.php" class="btn btn-secondary">İptal</a>
                </div>
            </div> </form>
    </div> </div> </div> <footer class="text-center text-muted mt-5 mb-3">
    &copy; <?php echo date("Y"); ?> Nere Gidi10. Tüm hakları saklıdır.
</footer>

</body>
</html>