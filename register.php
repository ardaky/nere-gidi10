<?php

require 'config.php';

$mesaj = '';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tam_ad = $_POST['tam_ad'];
    $eposta = $_POST['eposta'];
    $sifre = $_POST['sifre'];

    $cinsiyet = isset($_POST['cinsiyet']) ? $_POST['cinsiyet'] : '';

    if (empty($cinsiyet)) {
        $mesaj = "Lütfen cinsiyetinizi seçin.";
    } else {
        $sifrelenmis_parola = password_hash($sifre, PASSWORD_BCRYPT);

        $kullanici_uuid = uuid_olustur();

        try {
            $sql = "INSERT INTO User (uuid, full_name, email, password, cinsiyet) VALUES (?, ?, ?, ?, ?)";
            
            $sorgu = $vt->prepare($sql);

            $sorgu->execute([
                $kullanici_uuid,
                $tam_ad,
                $eposta,
                $sifrelenmis_parola,
                $cinsiyet
            ]);

            $_SESSION['basari_mesaji'] = 'Kayıt başarılı! Lütfen giriş yapın.';
            header("Location: login.php");
            exit;

        } catch (PDOException $hata) {
            if (str_contains($hata->getMessage(), 'UNIQUE constraint failed: User.email')) {
                $mesaj = "Bu e-posta adresi zaten kayıtlı!";
            } else {
                $mesaj = "Bir hata oluştu: " . $hata->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="style.css" rel="stylesheet"> 
    
</head>
<body class="arkaplan-gri">

    <div class="card ortalanmis-kart shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h2 class="card-title text-center mb-4">Yeni Hesap Oluştur</h2>
            
            <?php if (!empty($mesaj)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $mesaj; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label for="tam_ad" class="form-label">Ad Soyad</label>
                    <input type="text" class="form-control" id="tam_ad" name="tam_ad" required>
                </div>
                
                <div class="mb-3">
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
                <div class="mb-3">
                    <label for="eposta" class="form-label">E-posta Adresi</label>
                    <input type="email" class="form-control" id="eposta" name="eposta" required>
                </div>
                <div class="mb-3">
                    <label for="sifre" class="form-label">Şifre</label>
                    <input type="password" class="form-control" id="sifre" name="sifre" required>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Kayıt Ol</button>
                </div>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <p>Zaten bir hesabın var mı? <a href="login.php">Giriş Yap</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>