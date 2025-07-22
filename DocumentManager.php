<?php
require_once 'Database.php';
require_once 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

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
        $generator = new BarcodeGeneratorPNG();
        $barcodeImage = $generator->getBarcode($documentId, $generator::TYPE_CODE_128, 3, 100);

        $tempFile = tempnam(sys_get_temp_dir(), 'barcode_') . '.png';
        file_put_contents($tempFile, $barcodeImage);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "powershell -Command \"Start-Process -FilePath '" . escapeshellarg($tempFile) . "' -Verb Print\"";
            $output = shell_exec($command . " 2>&1");

            if (empty($output)) {
                unlink($tempFile);
                return true;
            }

            $command = "rundll32 shimgvw.dll,ImageView_PrintTo \"" . PRINTER_NAME . "\" \"" . $tempFile . "\"";
            $output = shell_exec($command . " 2>&1");

            if (empty($output)) {
                unlink($tempFile);
                return true;
            }

            $command = "copy \"" . $tempFile . "\" \"" . PRINTER_NAME . "\"";
            $output = shell_exec($command . " 2>&1");

            if (empty($output)) {
                unlink($tempFile);
                return true;
            }

            $command = "rundll32 shimgvw.dll,ImageView_PrintTo \"Microsoft Print to PDF\" \"" . $tempFile . "\"";
            $output = shell_exec($command . " 2>&1");

            if (empty($output)) {
                unlink($tempFile);
                return true;
            }

            $command = "rundll32 shimgvw.dll,ImageView_PrintTo \"\" \"" . $tempFile . "\"";
            $output = shell_exec($command . " 2>&1");

            unlink($tempFile);
            return empty($output);
        } else {
            $command = "lp -d " . PRINTER_NAME . " -o media=" . PRINTER_MEDIA_SIZE . " " . escapeshellarg($tempFile);
            $output = shell_exec($command . " 2>&1");

            unlink($tempFile);

            if (empty($output)) {
                return true;
            }

            if (strpos($output, 'request id is') !== false) {
                return true;
            }

            if (strpos($output, 'error') === false && strpos($output, 'Error') === false) {
                return true;
            }

            return false;
        }
    }

    public function checkPrinterStatus()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "wmic printer where name=\"" . PRINTER_NAME . "\" get PrinterStatus /value 2>&1";
            $output = shell_exec($command);

            if (strpos($output, '3') !== false) {
                return 'ready';
            } elseif (strpos($output, '4') !== false) {
                return 'busy';
            } elseif (strpos($output, '5') !== false) {
                return 'error';
            } else {
                return 'unknown';
            }
        } else {
            $command = "lpstat -p " . PRINTER_NAME . " 2>&1";
            $output = shell_exec($command);

            if (strpos($output, 'idle') !== false) {
                return 'ready';
            } elseif (strpos($output, 'processing') !== false) {
                return 'busy';
            } else {
                return 'error';
            }
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
}
