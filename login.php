<?php

require 'config.php';

$mesaj = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eposta = $_POST['eposta'];
    $sifre = $_POST['sifre'];

    try {
        $sql = "SELECT * FROM User WHERE email = ?";
        $sorgu = $vt->prepare($sql);
        $sorgu->execute([$eposta]);
        
        $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

        if ($kullanici && password_verify($sifre, $kullanici['password'])) {
            

            $_SESSION['kullanici_uuid'] = $kullanici['uuid'];
            $_SESSION['kullanici_adi'] = $kullanici['full_name'];
            $_SESSION['kullanici_rolu'] = $kullanici['role'];
            $_SESSION['kullanici_bakiyesi'] = $kullanici['balance'];
            

            if ($kullanici['role'] == 'firma_admin') {
                $_SESSION['firma_uuid'] = $kullanici['company_id'];
            }

            header("Location: index.php");
            exit;

        } else {
            $mesaj = "E-posta veya şifre hatalı!";
        }

    } catch (PDOException $hata) {
        $mesaj = "Veritabanı hatası: " . $hata->getMessage();
    }
}

$basari_mesaji = '';
if (isset($_SESSION['basari_mesaji'])) {
    $basari_mesaji = $_SESSION['basari_mesaji'];
    unset($_SESSION['basari_mesaji']);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="style.css" rel="stylesheet"> 
    
</head>
<body class="arkaplan-gri">

    <div class="card ortalanmis-kart shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h2 class="card-title text-center mb-4">Giriş Yap</h2>
            
            <?php if (!empty($mesaj)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $mesaj; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($basari_mesaji)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $basari_mesaji; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="eposta" class="form-label">E-posta Adresi</label>
                    <input type="email" class="form-control" id="eposta" name="eposta" required>
                </div>
                <div class="mb-3">
                    <label for="sifre" class="form-label">Şifre</label>
                    <input type="password" class="form-control" id="sifre" name="sifre" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Giriş Yap</button>
                </div>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <p>Hesabın yok mu? <a href="register.php">Hemen Kayıt Ol</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>