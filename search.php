<?php
require_once 'Auth.php';
require_once 'DocumentManager.php';

$auth = new Auth();
$auth->requireLogin();

$manager = new DocumentManager();
$searchResults = [];
$searchQuery = '';
$archivedOnly = false;

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['q'])) {
    $searchQuery = trim($_GET['q']);
    $archivedOnly = isset($_GET['archived']) && $_GET['archived'] == '1';
    if (!empty($searchQuery)) {
        $searchResults = $manager->searchDocuments($searchQuery, $archivedOnly);
    }
}

$pageTitle = 'Wyszukiwanie - System Zarządzania Dokumentami';
include 'includes/header.php';
?>

<div class="header">
    <h1>Wyszukiwanie dokumentów</h1>
</div>

<div class="nav">
    <a href="index.php">Strona główna</a>
    <?php if ($auth->isAdmin()): ?>
        <a href="admin_locations.php">Zarządzanie miejscami</a>
        <a href="admin_documents.php">Zarządzanie dokumentami</a>
        <a href="admin_archive.php">Archiwum</a>
    <?php endif; ?>
    <a href="search.php" class="active">Wyszukiwanie</a>
    <a href="barcode_scan.php">Skanowanie kodu</a>
</div>

<div class="container">
    <div class="back-link">
        <a href="index.php">← Powrót do strony głównej</a>
    </div>

    <div class="card">
        <h2>Wyszukaj dokumenty</h2>
        <p>Wprowadź nazwisko osoby, nazwę firmy lub sygnaturę akt, aby znaleźć dokumenty.</p>

        <form method="GET" class="search-form">
            <input type="text" name="q" placeholder="Wprowadź nazwisko, firmę lub sygnaturę..." value="<?= htmlspecialchars($searchQuery) ?>" required>
            <div class="checkbox-container">
                <label>
                    <input type="checkbox" name="archived" value="1" <?= $archivedOnly ? 'checked' : '' ?>>
                    Wyszukaj tylko w zarchiwizowanych dokumentach
                </label>
            </div>
            <button type="submit">Wyszukaj</button>
        </form>

        <?php if (!empty($searchQuery)): ?>
            <div class="search-info">
                <span><strong>Wyniki wyszukiwania dla:</strong> "<?= htmlspecialchars($searchQuery) ?>"</span>
                <br>
                <strong>Typ dokumentów:</strong> <?= $archivedOnly ? 'Zarchiwizowane' : 'Aktywne' ?>
                <br>
                <strong>Znaleziono:</strong> <?= count($searchResults) ?> dokument(ów)
            </div>

            <?php if (empty($searchResults)): ?>
                <div class="no-results">
                    <h3>Nie znaleziono dokumentów</h3>
                    <p>Spróbuj wprowadzić inne hasło wyszukiwania.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Miejsce</th>
                            <th>Pozwany</th>
                            <th>Powodowy</th>
                            <th style="width:10%">Sygnatura</th>
                            <th style="width:10%">Typ sprawy</th>
                            <th>Data utworzenia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searchResults as $doc): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($doc['location_code']) ?></strong></td>

                                <td><?= htmlspecialchars($doc['defendant_name']) ?></td>
                                <td><?= htmlspecialchars($doc['plaintiff_name']) ?></td>
                                <td style="width:10%"><?= htmlspecialchars($doc['case_number']) ?></td>
                                <td style="width:10%">
                                    <span class="case-type <?= $doc['case_type'] ?>">
                                        <?= $doc['case_type'] == 'civil' ? 'Cywilne' : 'Karne' ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($doc['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>