<?php
require_once 'Auth.php';
require_once 'DocumentManager.php';

$auth = new Auth();
$auth->requireAdmin();

$manager = new DocumentManager();
$pageTitle = 'Archiwum - System Zarządzania Dokumentami';

$civilDocuments = $manager->getArchivedDocumentsByShelfType('civil');
$criminalDocuments = $manager->getArchivedDocumentsByShelfType('criminal');

// Sortowanie po dacie archiwizacji malejąco
usort($civilDocuments, function ($a, $b) {
    return strtotime($b['archived_at']) - strtotime($a['archived_at']);
});
usort($criminalDocuments, function ($a, $b) {
    return strtotime($b['archived_at']) - strtotime($a['archived_at']);
});

require_once 'includes/header.php';
?>

<div class="header">
    <h1>Archiwum</h1>
    <div class="user-info">
        Zalogowany jako: <?= htmlspecialchars($_SESSION['username']) ?>
        <a href="logout.php" class="logout">Wyloguj</a>
    </div>
</div>

<div class="nav">
    <a href="index.php">Strona główna</a>
    <a href="admin_locations.php">Zarządzanie miejscami</a>
    <a href="admin_documents.php">Zarządzanie dokumentami</a>
    <a href="admin_archive.php" class="active">Archiwum</a>
    <a href="search.php">Wyszukiwanie</a>
    <a href="barcode_scan.php">Skanowanie kodu</a>
</div>

<div class="container">
    <div class="back-link">
        <a href="index.php">← Powrót do strony głównej</a>
    </div>

    <div class="card">
        <h2>Zarchiwizowane dokumenty</h2>
        <p>Poniżej znajdują się wszystkie zarchiwizowane dokumenty.</p>

        <div class="tabs">
            <button class="tab all active" onclick="showTab('all')">Wszystkie</button>
            <button class="tab civil" onclick="showTab('civil')">Akta cywilne</button>
            <button class="tab criminal" onclick="showTab('criminal')">Akta karne</button>
        </div>

        <div id="all" class="tab-content active">
            <h3>Wszystkie zarchiwizowane dokumenty</h3>
            <?php
            $allDocuments = array_merge($civilDocuments, $criminalDocuments);
            // Sortowanie po dacie archiwizacji malejąco
            usort($allDocuments, function ($a, $b) {
                return strtotime($b['archived_at']) - strtotime($a['archived_at']);
            });
            if (empty($allDocuments)):
            ?>
                <div class="empty-message">
                    <h4>Brak zarchiwizowanych dokumentów</h4>
                    <p>Nie ma żadnych zarchiwizowanych dokumentów.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Miejsce</th>
                            <th>Pozwany</th>
                            <th>Powodowy</th>
                            <th>Sygnatura</th>
                            <th style="width:10%">Typ sprawy</th>
                            <th>Data utworzenia</th>
                            <th style="width:15%">Data archiwizacji</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allDocuments as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['location_code']) ?></td>
                                <td><?= htmlspecialchars($doc['defendant_name']) ?></td>
                                <td><?= htmlspecialchars($doc['plaintiff_name']) ?></td>
                                <td><?= htmlspecialchars($doc['case_number']) ?></td>
                                <td style="width:10%">
                                    <span class="case-type <?= $doc['case_type'] ?>">
                                        <?= $doc['case_type'] == 'civil' ? 'Cywilne' : 'Karne' ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($doc['created_at'])) ?></td>
                                <td style="width:15%"><?= date('d.m.Y H:i', strtotime($doc['archived_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div id="civil" class="tab-content">
            <h3>Zarchiwizowane akta cywilne</h3>
            <?php if (empty($civilDocuments)): ?>
                <div class="empty-message">
                    <h4>Brak zarchiwizowanych dokumentów cywilnych</h4>
                    <p>Nie ma żadnych zarchiwizowanych dokumentów cywilnych.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Miejsce</th>
                            <th>Pozwany</th>
                            <th>Powodowy</th>
                            <th>Sygnatura</th>
                            <th style="width:10%">Typ sprawy</th>
                            <th>Data utworzenia</th>
                            <th style="width:15%">Data archiwizacji</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($civilDocuments as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['location_code']) ?></td>

                                <td><?= htmlspecialchars($doc['defendant_name']) ?></td>
                                <td><?= htmlspecialchars($doc['plaintiff_name']) ?></td>
                                <td><?= htmlspecialchars($doc['case_number']) ?></td>
                                <td style="width:10%">
                                    <span class="case-type civil">Cywilne</span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($doc['created_at'])) ?></td>
                                <td style="width:15%"><?= date('d.m.Y H:i', strtotime($doc['archived_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div id="criminal" class="tab-content">
            <h3>Zarchiwizowane akta karne</h3>
            <?php if (empty($criminalDocuments)): ?>
                <div class="empty-message">
                    <h4>Brak zarchiwizowanych dokumentów karnych</h4>
                    <p>Nie ma żadnych zarchiwizowanych dokumentów karnych.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Miejsce</th>
                            <th>Pozwany</th>
                            <th>Powodowy</th>
                            <th>Sygnatura</th>
                            <th style="width:10%">Typ sprawy</th>
                            <th>Data utworzenia</th>
                            <th style="width:15%">Data archiwizacji</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($criminalDocuments as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['location_code']) ?></td>
                                <td><?= htmlspecialchars($doc['defendant_name']) ?></td>
                                <td><?= htmlspecialchars($doc['plaintiff_name']) ?></td>
                                <td><?= htmlspecialchars($doc['case_number']) ?></td>
                                <td style="width:10%">
                                    <span class="case-type criminal">Karne</span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($doc['created_at'])) ?></td>
                                <td style="width:15%"><?= date('d.m.Y H:i', strtotime($doc['archived_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function showTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });

        document.getElementById(tabName).classList.add('active');
        event.target.classList.add('active');
    }

    function printDocument(documentId) {
        const button = event.target;
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = 'Drukowanie...';

        fetch('print_document.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'document_id=' + documentId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Kod kreskowy został wysłany do drukarki!');
                } else {
                    let errorMessage = 'Błąd: ' + data.message;
                    if (data.debug) {
                        errorMessage += '\n\nSzczegóły: ' + data.debug;
                    }
                    alert(errorMessage);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Błąd połączenia: ' + error.message);
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = originalText;
            });
    }

    function openForPrinting(documentId) {
        const button = event.target;
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = 'Otwieranie...';

        fetch('open_for_print.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'document_id=' + documentId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Etykieta została otwarta. Kliknij CTRL+P aby wydrukować.');
                } else {
                    alert('Błąd: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Błąd połączenia: ' + error.message);
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = originalText;
            });
    }
</script>

<?php require_once 'includes/footer.php'; ?>