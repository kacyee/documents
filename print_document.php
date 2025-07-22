<?php
require_once 'Auth.php';
require_once 'DocumentManager.php';

$auth = new Auth();
$auth->requireAdmin();

$manager = new DocumentManager();
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $documentId = $_POST['document_id'] ?? '';

    if (empty($documentId)) {
        $response['message'] = 'Brak ID dokumentu';
    } else {
        try {
            $document = $manager->getDocumentById($documentId);

            if (!$document) {
                $response['message'] = 'Dokument nie istnieje';
            } else {
                $printerStatus = $manager->checkPrinterStatus();

                if ($printerStatus === 'busy') {
                    $manager->clearPrinterQueue();
                    sleep(1);
                }

                $success = $manager->printBarcodeImageOnly($document['id']);

                if ($success) {
                    $response['success'] = true;
                    $response['message'] = 'Kod kreskowy został wysłany do drukarki';
                } else {
                    $response['message'] = 'Błąd podczas drukowania - sprawdź status drukarki';
                    $response['debug'] = 'Drukowanie nie powiodło się. Sprawdź logi PHP dla szczegółów.';
                }
            }
        } catch (Exception $e) {
            $response['message'] = 'Błąd: ' . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
