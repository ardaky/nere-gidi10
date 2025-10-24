<?php
ob_start();

require 'config.php';
require_once('tcpdf/tcpdf.php');

if (!isset($_SESSION['kullanici_uuid'])) {
    ob_end_clean(); 
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); $pdf->SetCreator(PDF_CREATOR); $pdf->SetTitle('Hata'); $pdf->AddPage(); $pdf->SetFont('dejavusans', 'B', 14); $pdf->Cell(0, 10, 'PDF olusturmak icin giris yapmalisiniz.', 0, 1, 'C'); $pdf->Output('hata.pdf', 'I'); exit;
}

$bilet_uuid = isset($_GET['bilet_id']) ? htmlspecialchars($_GET['bilet_id']) : '';
$kullanici_uuid = $_SESSION['kullanici_uuid'];

if (empty($bilet_uuid)) { ob_end_clean(); die("Gecersiz bilet ID'si."); }

try {
    $sql = "SELECT
                T.total_price, T.created_at AS satin_alma_tarihi, T.status AS bilet_durumu,
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
    $bilet_detaylari = $sorgu->fetch(PDO::FETCH_ASSOC);

    if (!$bilet_detaylari) { ob_end_clean(); die("Bilet bulunamadi veya bu bilete erisim izniniz yok."); }
} catch (PDOException $hata) { ob_end_clean(); die("Veritabani hatasi: " . $hata->getMessage()); }

try {
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Nere Gidi10');
    $pdf->SetTitle('Yolcu Bileti - ' . $bilet_detaylari['yolcu_adi']);
    $pdf->SetSubject('Otobus Bileti');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();

    $primary_red = [230, 57, 70];
    $gray_text = [108, 117, 125];
    $light_gray_bg = [248, 249, 250];

    $pdf->SetFillColor($primary_red[0], $primary_red[1], $primary_red[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('dejavusans', 'B', 18);
    $pdf->Cell(0, 15, ' Nere Gidi10', 0, 1, 'L', 1, '', 0, false, 'T', 'M');
    $pdf->Ln(8);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(120, 8, 'Yolcu: ' . $bilet_detaylari['yolcu_adi'], 0, 0, 'L');

    $pdf->SetFont('dejavusans', '', 11);
    $currentY = $pdf->GetY();
    if (!empty($bilet_detaylari['logo_path']) && file_exists($bilet_detaylari['logo_path'])) {
        $image_type = pathinfo($bilet_detaylari['logo_path'], PATHINFO_EXTENSION);
        @$pdf->Image($bilet_detaylari['logo_path'], 160, $currentY + 1, 0, 10, strtoupper($image_type), '', 'T', false, 300, 'R', false, false, 0, false, false, false);
        $pdf->SetXY(150, $currentY + 12);
        $pdf->Cell(45, 8, 'Firma: ' . $bilet_detaylari['firma_adi'], 0, 1, 'R');
    } else {
        $pdf->Cell(60, 8, 'Firma: ' . $bilet_detaylari['firma_adi'], 0, 1, 'R');
    }
    $pdf->SetY($currentY + 22);

    $pdf->SetFillColor($light_gray_bg[0], $light_gray_bg[1], $light_gray_bg[2]);
    $pdf->SetDrawColor($primary_red[0], $primary_red[1], $primary_red[2]);
    $pdf->SetLineWidth(0.5);
    $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 180, 25, 2, '1111', 'DF');
    $pdf->Ln(1);

    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(85, 10, 'Kalkis Yeri', 0, 0, 'L');
    $pdf->Cell(10, 10, '', 0, 0, 'C');
    $pdf->Cell(85, 10, 'Varis Yeri', 0, 1, 'R');

    $pdf->SetFont('dejavusans', 'B', 18);
    $pdf->Cell(85, 10, $bilet_detaylari['departure_city'], 0, 0, 'L');
    $pdf->Cell(10, 10, html_entity_decode('&#8594;', ENT_HTML5, 'UTF-8'), 0, 0, 'C');
    $pdf->Cell(85, 10, $bilet_detaylari['destination_city'], 0, 1, 'R');
    $pdf->Ln(12);

    $pdf->SetFont('dejavusans', '', 10);
    $pdf->SetTextColor($gray_text[0], $gray_text[1], $gray_text[2]);
    $pdf->Cell(60, 8, 'Kalkis Zamani', 0, 0, 'L');
    $pdf->Cell(60, 8, 'Koltuk Numarasi', 0, 0, 'C');
    $pdf->Cell(60, 8, 'Odenen Tutar', 0, 1, 'R');

    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(60, 8, date('d.m.Y H:i', strtotime($bilet_detaylari['departure_time'])), 0, 0, 'L');

    $koltuklar_gosterim = $bilet_detaylari['koltuk_numaralari'] ? $bilet_detaylari['koltuk_numaralari'] : 'N/A';
    $pdf->Cell(60, 8, $koltuklar_gosterim, 0, 0, 'C');

    $pdf->SetTextColor($primary_red[0], $primary_red[1], $primary_red[2]);
    $pdf->Cell(60, 8, $bilet_detaylari['total_price'] . ' TL', 0, 1, 'R');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(10);

    if ($bilet_detaylari['bilet_durumu'] == 'cancelled') {
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', 'B', 16);
        $pdf->SetTextColor(154, 3, 30);
        $pdf->Cell(0, 10, 'BU BILET IPTAL EDILMISTIR', 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(5);
    }

    $pdf->SetFont('dejavusans', 'I', 8);
    $pdf->SetTextColor($gray_text[0], $gray_text[1], $gray_text[2]);
    $pdf->Cell(0, 5, 'Bu bilet Nere Gidi10 tarafindan ' . date('d.m.Y H:i:s', strtotime($bilet_detaylari['satin_alma_tarihi'])) . ' tarihinde olusturulmustur.', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Bilet UUID: ' . $bilet_uuid, 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(0, 5, 'Iyi yolculuklar dileriz!', 0, 1, 'C');

    ob_end_clean();

    $pdf->Output('Bilet-' . $bilet_uuid . '.pdf', 'I');
    exit;

} catch (Exception $e) { 
    ob_end_clean(); 
    die("PDF olusturulurken bir hata olustu: " . $e->getMessage());
}
?>