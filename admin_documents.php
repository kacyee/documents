<?php
require_once 'Auth.php';
require_once 'DocumentManager.php';

$auth = new Auth();
$auth->requireAdmin();

$manager = new DocumentManager();
$pageTitle = 'Zarządzanie dokumentami';
$currentPage = 'documents';

$message = $_GET['message'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        $locationId = $_POST['location_id'] ?? '';
        $defendantName = trim($_POST['defendant_name'] ?? '');
        $plaintiffName = trim($_POST['plaintiff_name'] ?? '');
        $caseNumber = trim($_POST['case_number'] ?? '');
        $caseType = $_POST['case_type'] ?? '';

        if (empty($locationId) || empty($defendantName) || empty($plaintiffName) || empty($caseType)) {
            $error = 'Wszystkie pola są wymagane (oprócz sygnatury akt)';
        } else {
            try {
                $barcode = $manager->addDocument($locationId, $defendantName, $plaintiffName, $caseNumber, $caseType);
                header('Location: admin_documents.php?message=' . urlencode('Dokument został dodany pomyślnie.'));
                exit();
            } catch (Exception $e) {
                $error = 'Błąd podczas dodawania dokumentu: ' . $e->getMessage();
            }
        }
    } elseif ($action == 'archive') {
        $documentId = $_POST['document_id'] ?? '';

        if (empty($documentId)) {
            $error = 'Nie wybrano dokumentu';
        } else {
            try {
                if ($manager->archiveDocument($documentId)) {
                    header('Location: admin_documents.php?message=' . urlencode('Dokument został zarchiwizowany pomyślnie'));
                    exit();
                } else {
                    $error = 'Błąd podczas archiwizacji dokumentu';
                }
            } catch (Exception $e) {
                $error = 'Błąd podczas archiwizacji: ' . $e->getMessage();
            }
        }
    } elseif ($action == 'edit') {
        $documentId = $_POST['document_id'] ?? '';
        $locationId = $_POST['location_id'] ?? '';
        $defendantName = trim($_POST['defendant_name'] ?? '');
        $plaintiffName = trim($_POST['plaintiff_name'] ?? '');
        $caseNumber = trim($_POST['case_number'] ?? '');
        $caseType = $_POST['case_type'] ?? '';

        if (empty($documentId) || empty($locationId) || empty($defendantName) || empty($plaintiffName) || empty($caseType)) {
            $error = 'Wszystkie pola są wymagane (oprócz sygnatury akt)';
        } else {
            try {
                if ($manager->updateDocument($documentId, $locationId, $defendantName, $plaintiffName, $caseNumber, $caseType)) {
                    header('Location: admin_documents.php?message=' . urlencode('Dokument został zaktualizowany pomyślnie'));
                    exit();
                } else {
                    $error = 'Błąd podczas aktualizacji dokumentu';
                }
            } catch (Exception $e) {
                $error = 'Błąd podczas aktualizacji: ' . $e->getMessage();
            }
        }
    }
}

$civilLocations = $manager->getAvailableLocations('civil');
$criminalLocations = $manager->getAvailableLocations('criminal');
$civilDocuments = $manager->getDocumentsByShelfType('civil');
$criminalDocuments = $manager->getDocumentsByShelfType('criminal');

require_once 'includes/header.php';
?>

<div class="header">
    <h1>Zarządzanie dokumentami</h1>
    <div class="user-info">
        Zalogowany jako: <?= htmlspecialchars($_SESSION['username']) ?>
        <a href="logout.php" class="logout">Wyloguj</a>
    </div>
</div>

<div class="nav">
    <a href="index.php">Strona główna</a>
    <a href="admin_locations.php">Zarządzanie miejscami</a>
    <a href="admin_documents.php" class="active">Zarządzanie dokumentami</a>
    <a href="admin_archive.php">Archiwum</a>
    <a href="search.php">Wyszukiwanie</a>
    <a href="barcode_scan.php">Skanowanie kodu</a>
</div>

<div class="container">
    <div class="back-link">
        <a href="index.php">← Powrót do strony głównej</a>
    </div>

    <div class="documents-layout">
        <div class="card add-document-card">
            <h2>Dodaj nowy dokument</h2>

            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="documentForm">
                <input type="hidden" name="action" value="add" id="formAction">
                <input type="hidden" name="document_id" value="" id="documentId">

                <div class="form-group">
                    <label for="case_type">Typ sprawy:</label>
                    <select id="case_type" name="case_type" required onchange="updateLocationOptions()">
                        <option value="">Wybierz typ sprawy</option>
                        <option value="civil">Sprawa cywilna</option>
                        <option value="criminal">Sprawa karna</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="location_id">Miejsce na półce:</label>
                    <select id="location_id" name="location_id" required>
                        <option value="">Najpierw wybierz typ sprawy</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="defendant_name">Imię i nazwisko osoby pozwanej / firmy:</label>
                    <input type="text" id="defendant_name" name="defendant_name" required>
                </div>

                <div class="form-group">
                    <label for="plaintiff_name">Imię i nazwisko osoby powodowej / firmy:</label>
                    <input type="text" id="plaintiff_name" name="plaintiff_name" required>
                </div>

                <div class="form-group">
                    <label for="case_number">Sygnatura akt (opcjonalne):</label>
                    <input type="text" id="case_number" name="case_number">
                </div>

                <button type="submit" id="submitBtn">Dodaj dokument</button>
                <button type="button" id="cancelBtn" onclick="cancelEdit()" style="display: none;">Anuluj edycję</button>
            </form>
        </div>

        <div class="card documents-list-card">
            <h2>Lista dokumentów</h2>

            <div class="tabs">
                <button class="tab all active" onclick="showTab('all')">Wszystkie</button>
                <button class="tab civil" onclick="showTab('civil')">Akta cywilne</button>
                <button class="tab criminal" onclick="showTab('criminal')">Akta karne</button>
            </div>

            <div id="all" class="tab-content active">
                <h3>Wszystkie dokumenty</h3>
                <?php
                $allDocuments = array_merge($civilDocuments, $criminalDocuments);
                usort($allDocuments, function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                if (empty($allDocuments)):
                ?>
                    <div class="empty-message">
                        <h4>Brak dokumentów</h4>
                        <p>Nie ma żadnych dokumentów.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Miejsce</th>
                                <th>Typ sprawy</th>
                                <th>Pozwany</th>
                                <th>Powodowy</th>
                                <th>Sygnatura</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allDocuments as $doc): ?>
                                <tr>
                                    <td  class="type <?= $doc['case_type'] === 'civil' ? 'occupied' : 'available' ?>"><?= htmlspecialchars($doc['location_code']) ?></td>
                                    <td  class="type <?= $doc['case_type'] === 'civil' ? 'occupied' : 'available' ?>">
                                        <span class="case-type <?= $doc['case_type'] ?>">
                                            <?= $doc['case_type'] == 'civil' ? 'Cywilne' : 'Karne' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($doc['defendant_name']) ?></td>
                                    <td><?= htmlspecialchars($doc['plaintiff_name']) ?></td>
                                    <td><?= htmlspecialchars($doc['case_number']) ?></td>
                                    <td>
                                        <button class="edit-btn" onclick="editDocument(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['defendant_name']) ?>', '<?= htmlspecialchars($doc['plaintiff_name']) ?>', '<?= htmlspecialchars($doc['case_number']) ?>', '<?= $doc['case_type'] ?>', <?= $doc['location_id'] ?>)">Edytuj</button>
                                        <button class="print-btn" onclick="printDocument(<?= $doc['id'] ?>)">Drukuj</button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="archive">
                                            <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                            <button type="submit" class="archive-btn" onclick="return confirm('Czy na pewno chcesz zarchiwizować ten dokument?')">Archiwizuj</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div id="civil" class="tab-content">
                <h3>Akta cywilne</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Miejsce</th>
                            <th>Typ sprawy</th>
                            <th>Pozwany</th>
                            <th>Powodowy</th>
                            <th>Sygnatura</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($civilDocuments as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['location_code']) ?></td>
                                <td><span class="case-type civil">Cywilne</span></td>
                                <td><?= htmlspecialchars($doc['defendant_name']) ?></td>
                                <td><?= htmlspecialchars($doc['plaintiff_name']) ?></td>
                                <td><?= htmlspecialchars($doc['case_number']) ?></td>
                                <td>
                                    <button class="edit-btn" onclick="editDocument(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['defendant_name']) ?>', '<?= htmlspecialchars($doc['plaintiff_name']) ?>', '<?= htmlspecialchars($doc['case_number']) ?>', '<?= $doc['case_type'] ?>', <?= $doc['location_id'] ?>)">Edytuj</button>
                                    <button class="print-btn" onclick="printDocument(<?= $doc['id'] ?>)">Drukuj</button>
                                    <form method="POST" style="display: inline; ">
                                        <input type="hidden" name="action" value="archive">
                                        <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                        <button type="submit" class="archive-btn" onclick="return confirm('Czy na pewno chcesz zarchiwizować ten dokument?')">Archiwizuj</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="criminal" class="tab-content">
                <h3>Akta karne</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Miejsce</th>
                            <th>Typ sprawy</th>
                            <th>Pozwany</th>
                            <th>Powodowy</th>
                            <th>Sygnatura</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($criminalDocuments as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['location_code']) ?></td>
                                <td><span class="case-type criminal">Karne</span></td>

                                <td><?= htmlspecialchars($doc['defendant_name']) ?></td>
                                <td><?= htmlspecialchars($doc['plaintiff_name']) ?></td>
                                <td><?= htmlspecialchars($doc['case_number']) ?></td>
                                <td>
                                    <button class="edit-btn" onclick="editDocument(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['defendant_name']) ?>', '<?= htmlspecialchars($doc['plaintiff_name']) ?>', '<?= htmlspecialchars($doc['case_number']) ?>', '<?= $doc['case_type'] ?>', <?= $doc['location_id'] ?>)">Edytuj</button>
                                    <button class="print-btn" onclick="printDocument(<?= $doc['id'] ?>)">Drukuj</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="archive">
                                        <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                        <button type="submit" class="archive-btn" onclick="return confirm('Czy na pewno chcesz zarchiwizować ten dokument?')">Archiwizuj</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const civilLocations = <?= json_encode($civilLocations) ?>;
        const criminalLocations = <?= json_encode($criminalLocations) ?>;

        function updateLocationOptions() {
            const caseType = document.getElementById('case_type').value;
            const locationSelect = document.getElementById('location_id');
            locationSelect.innerHTML = '<option value="">Wybierz miejsce</option>';

            if (caseType === 'civil') {
                civilLocations.forEach(location => {
                    const option = document.createElement('option');
                    option.value = location.id;
                    option.textContent = location.location_code + (location.active_documents_count > 0 ? ` (${location.active_documents_count} akt)` : '');
                    locationSelect.appendChild(option);
                });
            } else if (caseType === 'criminal') {
                criminalLocations.forEach(location => {
                    const option = document.createElement('option');
                    option.value = location.id;
                    option.textContent = location.location_code + (location.active_documents_count > 0 ? ` (${location.active_documents_count} akt)` : '');
                    locationSelect.appendChild(option);
                });
            }
        }

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
            button.textContent = 'Otwieranie...';

            const barcodeUrl = 'barcode_image.php?id=' + documentId;
            
            // Otwórz kod kreskowy w nowej karcie
            const newWindow = window.open(barcodeUrl, '_blank');
            
            if (newWindow) {
                // Poczekaj na załadowanie obrazu, a następnie wywołaj Ctrl+P
                newWindow.onload = function() {
                    setTimeout(() => {
                        try {
                            newWindow.print();
                        } catch (e) {
                            console.log('Automatyczne drukowanie nie działa, użytkownik musi nacisnąć Ctrl+P');
                        }
                    }, 500);
                };
                
                alert('Kod kreskowy został otwarty. Naciśnij Ctrl+P aby wydrukować.');
            } else {
                alert('Nie można otworzyć kodu kreskowego. Sprawdź blokadę wyskakujących okien.');
            }
            
            button.disabled = false;
            button.textContent = originalText;
        }



        function editDocument(documentId, defendantName, plaintiffName, caseNumber, caseType, locationId) {
            document.getElementById('formAction').value = 'edit';
            document.getElementById('documentId').value = documentId;
            document.getElementById('case_type').value = caseType;
            document.getElementById('defendant_name').value = defendantName;
            document.getElementById('plaintiff_name').value = plaintiffName;
            document.getElementById('case_number').value = caseNumber;

            updateLocationOptions();

            setTimeout(() => {
                document.getElementById('location_id').value = locationId;
            }, 100);

            document.getElementById('submitBtn').textContent = 'Zaktualizuj dokument';
            document.getElementById('cancelBtn').style.display = 'inline-block';

            document.querySelector('.add-document-card h2').textContent = 'Edytuj dokument';

            document.getElementById('documentForm').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function cancelEdit() {
            document.getElementById('formAction').value = 'add';
            document.getElementById('documentId').value = '';
            document.getElementById('case_type').value = '';
            document.getElementById('location_id').innerHTML = '<option value="">Najpierw wybierz typ sprawy</option>';
            document.getElementById('defendant_name').value = '';
            document.getElementById('plaintiff_name').value = '';
            document.getElementById('case_number').value = '';

            document.getElementById('submitBtn').textContent = 'Dodaj dokument';
            document.getElementById('cancelBtn').style.display = 'none';

            document.querySelector('.add-document-card h2').textContent = 'Dodaj nowy dokument';
        }
    </script>

    <?php require_once 'includes/footer.php'; ?>