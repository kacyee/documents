<?php
require_once 'Database.php';
require_once 'DocumentManager.php';
require_once 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;



$id = $_GET['id'] ?? '';
$id = trim($id);

if ($id === '') {
    http_response_code(400);
    exit('Brak ID dokumentu');
}

try {
    $generator = new BarcodeGeneratorPNG();
    $image = $generator->getBarcode($id, $generator::TYPE_CODE_128, 2, 40);
    
    // Konwertuj obrazek do base64
    $base64Image = base64_encode($image);
    
    // Zwróć stronę HTML z CSS do drukowania
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kod kreskowy - ID: <?= htmlspecialchars($id) ?></title>
    <link rel="stylesheet" href="print_barcode.css">
</head>
<body>
    <img src="data:image/png;base64,<?= $base64Image ?>" alt="Kod kreskowy ID: <?= htmlspecialchars($id) ?>">
</body>
</html>
    <?php
    
} catch (Exception $e) {
    http_response_code(500);
    exit('Błąd generowania kodu kreskowego');
}
