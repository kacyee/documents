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
                $status = str_replace('=', '', $status);
                $statusText = 'nieznany';
                $color = 'gray';

                switch ($status) {
                    case '1':
                        $statusText = 'gotowa (idle)';
                        $color = 'green';
                        break;
                    case '2':
                        $statusText = 'gotowa (idle)';
                        $color = 'green';
                        break;
                    case '3':
                        $statusText = 'gotowa (ready)';
                        $color = 'green';
                        break;
                    case '4':
                        $statusText = 'zajęta (printing)';
                        $color = 'orange';
                        break;
                    case '5':
                        $statusText = 'błąd (error)';
                        $color = 'red';
                        break;
                    default:
                        $statusText = 'gotowa (status: ' . $status . ')';
                        $color = 'green';
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
    echo "<p><strong>Uwaga:</strong> Jeśli drukowanie nie działa, sprawdź czy nazwa drukarki w config.php jest poprawna.</p>";
    echo "<p>Aby sprawdzić nazwy drukarek, uruchom w cmd: <code>wmic printer get name</code></p>";
    echo "<p>Aby sprawdzić szczegółowy status drukarki, uruchom w cmd: <code>wmic printer where name=\"" . PRINTER_NAME . "\" get PrinterStatus,WorkOffline,DetectedErrorState /value</code></p>";
    echo "<p><strong>Status drukarki:</strong> =3 oznacza gotowość, =4 oznacza drukowanie, =5 oznacza błąd</p>";
    echo "<p><strong>Rozmiar taśmy:</strong> Upewnij się, że w sterowniku drukarki jest ustawiony rozmiar 17x54mm</p>";
    echo "<p>Możesz przetestować drukarkę komendą:</p>";
    echo "<p><code>print test.txt</code></p>";
    echo "<p>lub</p>";
    echo "<p><code>powershell -Command \"Start-Process -FilePath 'test.txt' -Verb Print\"</code></p>";
    echo "<p>lub</p>";
    echo "<p><code>rundll32 shimgvw.dll,ImageView_PrintTo \"" . PRINTER_NAME . "\" test.txt</code></p>";
    echo "<p>lub</p>";
    echo "<p><code>copy test.txt \"" . PRINTER_NAME . "\"</code></p>";

    echo "<h3>Test drukowania obrazu:</h3>";
    echo "<p>Utwórz plik testowy i przetestuj drukowanie:</p>";
    echo "<p><code>echo 'Test drukowania' > test.txt</code></p>";
    echo "<p><code>print test.txt</code></p>";
    echo "<p>lub</p>";
    echo "<p><code>powershell -Command \"Start-Process -FilePath 'test.txt' -Verb Print\"</code></p>";

    echo "<h3>Test drukowania kodu kreskowego:</h3>";
    echo "<p><a href='barcode_image.php?code=TEST123' target='_blank'>Wyświetl kod kreskowy testowy</a></p>";
    echo "<p>Kliknij prawym przyciskiem myszy na obraz i wybierz 'Drukuj'</p>";
    echo "<p><strong>Alternatywnie:</strong> Skopiuj obraz i wklej do programu Brother P-touch Editor</p>";

    echo "<h3>Test drukowania przez system:</h3>";
    echo "<form method='POST' style='margin: 10px 0;'>";
    echo "<input type='hidden' name='test_print' value='1'>";
    echo "<button type='submit' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;'>Testuj drukowanie kodu kreskowego</button>";
    echo "</form>";

    if (isset($_POST['test_print'])) {
        echo "<h4>Wynik testu drukowania:</h4>";
        require_once 'DocumentManager.php';
        $manager = new DocumentManager();
        $success = $manager->printBarcodeImageOnly('TEST123');
        if ($success) {
            echo "<p style='color: green;'>✓ Drukowanie testowe powiodło się</p>";
            echo "<p><strong>Uwaga:</strong> Jeśli etykieta się wydrukowała, ale ma nieprawidłowy rozmiar, sprawdź ustawienia rozmiaru taśmy w sterowniku drukarki.</p>";
        } else {
            echo "<p style='color: red;'>✗ Drukowanie testowe nie powiodło się</p>";
            echo "<p><strong>Możliwe przyczyny:</strong></p>";
            echo "<ul>";
            echo "<li>Drukarka jest offline - sprawdź w Panel sterowania > Urządzenia i drukarki</li>";
            echo "<li>Niepoprawna nazwa drukarki w config.php</li>";
            echo "<li>Brak uprawnień do drukowania</li>";
            echo "<li>Drukarka nie obsługuje drukowania obrazów PNG</li>";
            echo "<li>Drukarka wymaga specjalnego oprogramowania (np. Brother P-touch Editor)</li>";
            echo "<li>Rozmiar taśmy nie jest ustawiony w sterowniku (ustaw 17x54mm)</li>";
            echo "</ul>";
            echo "<p><strong>Rozwiązanie:</strong> Spróbuj wydrukować kod kreskowy ręcznie przez przeglądarkę (kliknij prawym na obraz i wybierz 'Drukuj')</p>";
        }
    }
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
