<?php
require_once 'Database.php';
require_once 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;
use Talal\LabelPrinter\Printer;
use Talal\LabelPrinter\Mode\Escp;
use Talal\LabelPrinter\Command;

class DocumentManager
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function addLocation($locationCode, $shelfType)
    {
        $pdo = $this->db->getConnection();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM locations WHERE location_code = ? AND shelf_type = ?");
        $stmt->execute([$locationCode, $shelfType]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $shelfTypeName = $shelfType == 'civil' ? 'spraw cywilnych' : 'spraw karnych';
            throw new Exception("Miejsce o kodzie '$locationCode' dla $shelfTypeName już istnieje");
        }

        $stmt = $pdo->prepare("INSERT INTO locations (location_code, shelf_type) VALUES (?, ?)");
        return $stmt->execute([$locationCode, $shelfType]);
    }

    public function getAvailableLocations($shelfType = null)
    {
        $pdo = $this->db->getConnection();
        if ($shelfType) {
            $stmt = $pdo->prepare("
                SELECT l.*, 
                       COUNT(CASE WHEN d.is_archived = FALSE THEN 1 END) as active_documents_count
                FROM locations l 
                LEFT JOIN documents d ON l.id = d.location_id 
                WHERE l.shelf_type = ? 
                GROUP BY l.id, l.location_code, l.shelf_type, l.created_at
                ORDER BY l.location_code
            ");
            $stmt->execute([$shelfType]);
        } else {
            $stmt = $pdo->prepare("
                SELECT l.*, 
                       COUNT(CASE WHEN d.is_archived = FALSE THEN 1 END) as active_documents_count
                FROM locations l 
                LEFT JOIN documents d ON l.id = d.location_id 
                GROUP BY l.id, l.location_code, l.shelf_type, l.created_at
                ORDER BY l.location_code
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function getAllLocations()
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("
            SELECT l.*, 
                   COUNT(CASE WHEN d.is_archived = FALSE THEN 1 END) as active_documents_count,
                   COUNT(CASE WHEN d.is_archived = TRUE THEN 1 END) as archived_documents_count
            FROM locations l 
            LEFT JOIN documents d ON l.id = d.location_id 
            GROUP BY l.id, l.location_code, l.shelf_type, l.created_at
            ORDER BY l.location_code
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addDocument($locationId, $defendantName, $plaintiffName, $caseNumber, $caseType)
    {
        $pdo = $this->db->getConnection();

        $barcode = $this->generateBarcode($caseNumber, $defendantName);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO documents (location_id, barcode, defendant_name, plaintiff_name, case_number, case_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$locationId, $barcode, $defendantName, $plaintiffName, $caseNumber, $caseType]);

            $pdo->commit();
            return $barcode;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function archiveDocument($documentId)
    {
        $pdo = $this->db->getConnection();

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE documents SET is_archived = TRUE, archived_at = NOW() WHERE id = ?");
            $stmt->execute([$documentId]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function updateDocument($documentId, $locationId, $defendantName, $plaintiffName, $caseNumber, $caseType)
    {
        $pdo = $this->db->getConnection();

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE documents SET location_id = ?, defendant_name = ?, plaintiff_name = ?, case_number = ?, case_type = ? WHERE id = ?");
            $stmt->execute([$locationId, $defendantName, $plaintiffName, $caseNumber, $caseType, $documentId]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function searchDocuments($query, $archivedOnly = false)
    {
        $pdo = $this->db->getConnection();
        $searchTerm = "%$query%";

        $stmt = $pdo->prepare("
            SELECT d.*, l.location_code 
            FROM documents d 
            JOIN locations l ON d.location_id = l.id 
            WHERE (d.defendant_name LIKE ? OR d.plaintiff_name LIKE ? OR d.case_number LIKE ?) 
            AND d.is_archived = ? 
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $archivedOnly]);
        return $stmt->fetchAll();
    }

    public function getDocumentByBarcode($barcode)
    {
        $pdo = $this->db->getConnection();

        if (is_numeric($barcode)) {
            $stmt = $pdo->prepare("
                SELECT d.*, l.location_code 
                FROM documents d 
                JOIN locations l ON d.location_id = l.id 
                WHERE d.id = ?
            ");
            $stmt->execute([$barcode]);
        } else {
            $stmt = $pdo->prepare("
                SELECT d.*, l.location_code 
                FROM documents d 
                JOIN locations l ON d.location_id = l.id 
                WHERE d.barcode = ?
            ");
            $stmt->execute([$barcode]);
        }

        return $stmt->fetch();
    }

    public function getDocumentById($id)
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("
            SELECT d.*, l.location_code 
            FROM documents d 
            JOIN locations l ON d.location_id = l.id 
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getDocumentsByShelfType($shelfType)
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("
            SELECT d.*, l.location_code 
            FROM documents d 
            JOIN locations l ON d.location_id = l.id 
            WHERE l.shelf_type = ? AND d.is_archived = FALSE 
            ORDER BY l.location_code
        ");
        $stmt->execute([$shelfType]);
        return $stmt->fetchAll();
    }

    public function getArchivedDocumentsByShelfType($shelfType)
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("
            SELECT d.*, l.location_code 
            FROM documents d 
            JOIN locations l ON d.location_id = l.id 
            WHERE l.shelf_type = ? AND d.is_archived = TRUE 
            ORDER BY d.archived_at DESC
        ");
        $stmt->execute([$shelfType]);
        return $stmt->fetchAll();
    }

    private function generateBarcode($caseNumber, $defendantName)
    {
        $prefix = 'DOC';
        $timestamp = time();
        $hash = substr(md5($caseNumber . $defendantName), 0, 8);
        return $prefix . $timestamp . $hash;
    }

    public function printBarcodeImageOnly($documentId)
    {
        try {
            $document = $this->getDocumentById($documentId);
            
            if (!$document) {
                throw new Exception('Dokument nie istnieje');
            }
            
            $barcodeData = $document['barcode'] ?? $documentId;
            
            return $this->printLabelViaNotepad($documentId, $barcodeData);
            
        } catch (Exception $e) {
            error_log('Błąd drukowania: ' . $e->getMessage());
            return false;
        }
    }

    private function printLabelViaNotepad($documentId, $barcodeData)
    {
        try {
            $testFile = __DIR__ . '/temp_label.txt';
            
            $content = '';
            $content .= "ID: " . $documentId . "\r\n";
            $content .= $barcodeData . "\r\n";
            $content .= date('Y-m-d H:i:s') . "\r\n";
            $content .= "\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n"; // Extra spacing for 17x54mm label
            
            file_put_contents($testFile, $content);
            
            $command = 'notepad "' . $testFile . '"';
            shell_exec($command . ' 2>&1');
            
            return true;
            
        } catch (Exception $e) {
            error_log('Błąd drukowania przez notatnik: ' . $e->getMessage());
            return false;
        }
    }

    public function checkPrinterStatus()
    {
        try {
            $command = 'wmic printer where name="' . PRINTER_NAME . '" get PrinterStatus /value 2>&1';
            $output = shell_exec($command);
            
            if (strpos($output, '=3') !== false) {
                return 'ready';
            } elseif (strpos($output, '=4') !== false) {
                return 'busy';
            } elseif (strpos($output, '=5') !== false) {
                return 'error';
            } elseif (strpos($output, '=1') !== false || strpos($output, '=2') !== false) {
                return 'ready';
            } else {
                return 'unknown';
            }
        } catch (Exception $e) {
            error_log('Błąd sprawdzania statusu drukarki: ' . $e->getMessage());
            return 'error';
        }
    }

    public function clearPrinterQueue()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "net stop spooler 2>&1";
            shell_exec($command);
            sleep(2);
            $command = "net start spooler 2>&1";
            shell_exec($command);
        } else {
            $command = "lprm -P " . PRINTER_NAME . " - 2>&1";
            shell_exec($command);
        }
        return true;
    }

    public function openBarcodeForPrinting($documentId)
    {
        $generator = new BarcodeGeneratorPNG();
        $barcodeImage = $generator->getBarcode($documentId, $generator::TYPE_CODE_128, 3, 100);

        $tempFile = tempnam(sys_get_temp_dir(), 'barcode_') . '.png';
        file_put_contents($tempFile, $barcodeImage);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "start " . escapeshellarg($tempFile);
            shell_exec($command . " 2>&1");
            return true;
        } else {
            $command = "xdg-open " . escapeshellarg($tempFile);
            shell_exec($command . " 2>&1");
            return true;
        }
    }

    public function printBarcodeWithBrotherLibrary($documentId)
    {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $stream = fopen('usb://' . PRINTER_NAME, 'w+b');
                if (!$stream) {
                    throw new Exception("Nie można otworzyć połączenia z drukarką USB na Windows");
                }
            } else {
                $command = "lp -d " . PRINTER_NAME . " -o media=" . PRINTER_MEDIA_SIZE . " -";
                $descriptors = [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ];

                $process = proc_open($command, $descriptors, $pipes);
                if (!is_resource($process)) {
                    throw new Exception("Nie można uruchomić polecenia lp");
                }

                $printer = new Printer(new Escp($pipes[0]));
                $printer->addCommand(new Command\CharStyle(Command\CharStyle::NORMAL));
                $printer->addCommand(new Command\Align(Command\Align::CENTER));
                $printer->addCommand(new Command\Text($documentId));
                $printer->addCommand(new Command\Cut(Command\Cut::FULL));

                $printer->printLabel();
                fclose($pipes[0]);

                $returnValue = proc_close($process);
                return $returnValue === 0;
            }

            $printer = new Printer(new Escp($stream));
            $printer->addCommand(new Command\CharStyle(Command\CharStyle::NORMAL));
            $printer->addCommand(new Command\Align(Command\Align::CENTER));
            $printer->addCommand(new Command\Text($documentId));
            $printer->addCommand(new Command\Cut(Command\Cut::FULL));

            $printer->printLabel();
            fclose($stream);

            return true;
        } catch (Exception $e) {
            error_log("Błąd drukowania przez bibliotekę Brother: " . $e->getMessage());
            return false;
        }
    }
}
