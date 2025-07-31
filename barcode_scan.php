<?php
require_once 'Auth.php';
require_once 'DocumentManager.php';

$auth = new Auth();
$auth->requireLogin();

$manager = new DocumentManager();
$document = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barcode = trim($_POST['barcode'] ?? '');

    if (empty($barcode)) {
        $error = 'Wprowadź kod kreskowy';
    } else {
        $document = $manager->getDocumentByBarcode($barcode);
        if (!$document) {
            $error = 'Nie znaleziono dokumentu o podanym kodzie kreskowym';
        }
    }
}

$pageTitle = 'Skanowanie kodu - System Zarządzania Dokumentami';
include 'includes/header.php';
?>

<div class="header">
    <h1>Skanowanie kodu kreskowego</h1>
    <div class="user-info">
        Zalogowany jako: <?= htmlspecialchars($_SESSION['username']) ?>
        <a href="logout.php" class="logout">Wyloguj</a>
    </div>
</div>

<div class="nav">
    <a href="index.php">Strona główna</a>
    <?php if ($auth->isAdmin()): ?>
        <a href="admin_locations.php">Zarządzanie miejscami</a>
        <a href="admin_documents.php">Zarządzanie dokumentami</a>
        <a href="admin_archive.php">Archiwum</a>
    <?php endif; ?>
    <a href="search.php">Wyszukiwanie</a>
    <a href="barcode_scan.php" class="active">Skanowanie kodu</a>
</div>

<div class="container">
    <div class="back-link">
        <a href="index.php">← Powrót do strony głównej</a>
    </div>

    <div class="card">
        <h2>Skanuj kod kreskowy</h2>
        <p>Wprowadź kod kreskowy (ID dokumentu) lub ID dokumentu, aby wyświetlić jego szczegóły.</p>

        <form method="POST" class="scan-form">
            <div class="form-group">
                <label for="barcode">Kod kreskowy:</label>
                <input type="text" id="barcode" name="barcode" placeholder="Wprowadź ID dokumentu..." value="" required autofocus>
            </div>
            <button type="submit">Skanuj</button>
        </form>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </div>
</div>

<?php if ($document): ?>
    <div id="documentModal" class="modal" style="display: block;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>

            <div class="modal-info-row">
                <span class="modal-case-type <?= $document['case_type'] ?>">
                    <?= $document['case_type'] == 'civil' ? 'Sprawa cywilna' : 'Sprawa karna' ?>
                </span>
            </div>

            <div class="modal-document-info">
                <div class="modal-info-row place">
                    <div class="modal-info-label">Miejsce na półce:</div>
                    <div class="modal-info-value">
                        <strong><?= htmlspecialchars($document['location_code']) ?></strong>
                    </div>
                </div>

                <div class="modal-info-row">
                    <div class="modal-info-label">Pozwany:</div>
                    <div class="modal-info-value"><?= htmlspecialchars($document['defendant_name']) ?></div>
                </div>

                <div class="modal-info-row">
                    <div class="modal-info-label">Powodowy:</div>
                    <div class="modal-info-value"><?= htmlspecialchars($document['plaintiff_name']) ?></div>
                </div>

                <div class="modal-info-row">
                    <div class="modal-info-label">Sygnatura:</div>
                    <div class="modal-info-value"><?= htmlspecialchars($document['case_number']) ?></div>
                </div>


                <div class="modal-info-row">
                    <div class="modal-info-label">Data utworzenia:</div>
                    <div class="modal-info-value"><?= date('d.m.Y H:i', strtotime($document['created_at'])) ?></div>
                </div>

                <?php if ($document['archived']): ?>
                    <div class="modal-info-row">
                        <div class="modal-info-label">Status:</div>
                        <div class="modal-info-value">
                            <span style="color: #dc3545; font-weight: bold;">Zarchiwizowany</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    function closeModal() {
        document.getElementById('documentModal').style.display = 'none';
        document.getElementById('barcode').value = '';
        document.getElementById('barcode').focus();
    }

    window.onclick = function(event) {
        const modal = document.getElementById('documentModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('barcode').value = '';
        document.getElementById('barcode').focus();
    });
</script>

<?php include 'includes/footer.php'; ?>