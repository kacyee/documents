<?php
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

echo "<h2>Test drukowania:</h2>";
echo "<p>Możesz przetestować drukarkę komendą:</p>";
echo "<p><code>echo 'Test' | lpr -P Brother_QL_820NWB -o media=17x54mm</code></p>";
echo "<p>Rozmiar taśmy: 17x54mm</p>";

echo "<h2>Informacje o systemie:</h2>";
echo "<p>System: " . php_uname() . "</p>";
echo "<p>Użytkownik PHP: " . get_current_user() . "</p>";
echo "<p>Dostęp do lpr: " . (shell_exec('which lpr') ? 'Tak' : 'Nie') . "</p>";
