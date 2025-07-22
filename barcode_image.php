<?php


require_once 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

$code = $_GET['code'] ?? '';
$code = trim($code);
if ($code === '') {
    http_response_code(400);
    exit('Brak kodu');
}

$generator = new BarcodeGeneratorPNG();
$image = $generator->getBarcode($code, $generator::TYPE_CODE_128, 3, 100);

header('Content-Type: image/png');
echo $image;
