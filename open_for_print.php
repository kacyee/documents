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
                $success = $manager->openBarcodeForPrinting($document['id']);

                if ($success) {
                    $response['success'] = true;
                    $response['message'] = 'Etykieta została otwarta. Kliknij CTRL+P aby wydrukować.';
                } else {
                    $response['message'] = 'Błąd podczas otwierania etykiety';
                }
            }
        } catch (Exception $e) {
            $response['message'] = 'Błąd: ' . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
