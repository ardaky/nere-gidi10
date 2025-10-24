<?php
ob_start();

require 'config.php';

$basari_mesaji_header = $_SESSION['basari_mesaji'] ?? null;
$hata_mesaji_header = $_SESSION['hata_mesaji'] ?? null;

unset($_SESSION['basari_mesaji']);
unset($_SESSION['hata_mesaji']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nere Gidi10</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; }
        .navbar-brand { font-weight: 600; }
        .navbar-nav .nav-link.active { font-weight: 500; }
        .navbar-brand, .navbar-nav .nav-link { transition: transform 0.2s ease-in-out, opacity 0.2s ease-in-out; }
        .navbar-brand:hover { transform: scale(1.03); }
        .navbar-nav .nav-link.active:hover { transform: scale(1.05); opacity: 0.85; }
        .navbar-nav .nav-link:not(.active):hover { opacity: 0.85; }
        .navbar-nav .nav-link.dropdown-toggle:hover { transform: scale(1.03); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3 rounded">
    <div class="container"> <a class="navbar-brand fs-4 me-lg-4" href="index.php">
            <span class="text-primary"><i class="bi bi-bus-front-fill"></i></span>
            Nere Gidi<span class="text-primary">10</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active fs-5 ms-3" aria-current="page" href="index.php">Ana Sayfa</a></li>
                <li class="nav-item"><a class="nav-link fs-5 ms-4 active" href="hakkimizda.php">Hakkımızda</a></li>
                <li class="nav-item"><a class="nav-link fs-5 ms-4 active" href="firmalarimiz.php">Firmalarımız</a></li>
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <?php if (isset($_SESSION['kullanici_uuid'])): ?>
                    <li class="nav-item me-lg-3 mb-2 mb-lg-0">
                         <span class="navbar-text">
                             <i class="bi bi-wallet2"></i> Bakiye:
                             <strong class="text-success"><?php echo isset($_SESSION['kullanici_bakiyesi']) ? htmlspecialchars($_SESSION['kullanici_bakiyesi']) : 'N/A'; ?> TL</strong>
                         </span>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                           <?php if ($_SESSION['kullanici_rolu'] == 'admin'): ?><li><a class="dropdown-item" href="admin_panel.php">Admin Paneli</a></li><?php endif; ?>
                           <?php if ($_SESSION['kullanici_rolu'] == 'firma_admin'): ?><li><a class="dropdown-item" href="firma_admin_panel.php">Firma Paneli</a></li><?php endif; ?>
                           <?php if ($_SESSION['kullanici_rolu'] == 'user'): ?><li><a class="dropdown-item" href="hesabim.php">Hesabım / Biletlerim</a></li><?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Çıkış Yap</a></li>
                        </ul>
                    </li>
                 <?php else: ?>
                    <li class="nav-item me-2"><a class="btn btn-outline-light" href="login.php">Giriş Yap</a></li>
                    <li class="nav-item"><a class="btn btn-primary" href="register.php">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container mt-4">
    <?php if ($basari_mesaji_header): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $basari_mesaji_header; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($hata_mesaji_header): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $hata_mesaji_header; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>