<?php
require_once 'config.php';

echo "<h2>Informacje o systemie:</h2>";
echo "<p>System: " . php_uname() . "</p>";
echo "<p>Użytkownik PHP: " . get_current_user() . "</p>";

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "<h2>Dostępne drukarki Windows:</h2>";

    $output = shell_exec('wmic printer get name,printerstatus /format:list 2>&1');
    if ($output) {
        $lines = explode("\n", trim($output));
        $currentPrinter = '';
        foreach ($lines as $line) {
            if (strpos($line, 'Name=') === 0) {
                $currentPrinter = trim(substr($line, 5));
            } elseif (strpos($line, 'PrinterStatus=') === 0) {
                $status = trim(substr($line, 13));
                $statusText = 'nieznany';
                $color = 'gray';

                switch ($status) {
                    case '3':
                        $statusText = 'gotowa';
                        $color = 'green';
                        break;
                    case '4':
                        $statusText = 'zajęta';
                        $color = 'orange';
                        break;
                    case '5':
                        $statusText = 'błąd';
                        $color = 'red';
                        break;
                }

                echo "<p style='color: $color;'>✓ $currentPrinter - $statusText</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>Nie można pobrać listy drukarek Windows</p>";
    }

    echo "<h2>Test drukowania Windows:</h2>";
    echo "<p>Konfigurowana drukarka: " . PRINTER_NAME . "</p>";
    echo "<p>Możesz przetestować drukarkę komendą:</p>";
    echo "<p><code>copy test.txt \"" . PRINTER_NAME . "\"</code></p>";
    echo "<p>lub</p>";
    echo "<p><code>powershell -Command \"Start-Process -FilePath 'test.txt' -Verb Print\"</code></p>";
} else {
    echo "<h2>Dostępne drukarki CUPS:</h2>";

    $output = shell_exec('lpstat -p 2>&1');
    if ($output) {
        $lines = explode("\n", trim($output));
        foreach ($lines as $line) {
            if (preg_match('/^printer\s+(\S+)\s+is\s+(.+)$/', $line, $matches)) {
                $printerName = $matches[1];
                $status = $matches[2];
                $isDefault = (strpos($status, 'default') !== false);
                $isIdle = (strpos($status, 'idle') !== false);

                $color = $isIdle ? 'green' : 'orange';
                $statusText = $isIdle ? 'gotowa' : 'zajęta';

                echo "<p style='color: $color;'>✓ $printerName - $statusText</p>";
                if ($isDefault) {
                    echo "<p style='color: blue;'>  - drukarka domyślna</p>";
                }
            }
        }
    } else {
        echo "<p style='color: red;'>Nie można pobrać listy drukarek CUPS</p>";
    }

    echo "<h2>Test drukowania Linux:</h2>";
    echo "<p>Konfigurowana drukarka: " . PRINTER_NAME . "</p>";
    echo "<p>Możesz przetestować drukarkę komendą:</p>";
    echo "<p><code>echo 'Test' | lpr -P " . PRINTER_NAME . " -o media=" . PRINTER_MEDIA_SIZE . "</code></p>";
    echo "<p>Rozmiar taśmy: " . PRINTER_MEDIA_SIZE . "</p>";
    echo "<p>Dostęp do lpr: " . (shell_exec('which lpr') ? 'Tak' : 'Nie') . "</p>";
}
