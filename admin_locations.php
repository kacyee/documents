<?php
require_once 'Auth.php';
require_once 'DocumentManager.php';

$auth = new Auth();
$auth->requireAdmin();

$manager = new DocumentManager();
$pageTitle = 'Zarządzanie miejscami';
$currentPage = 'locations';

$message = $_GET['message'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        $locationCode = trim($_POST['location_code'] ?? '');
        $shelfType = $_POST['shelf_type'] ?? '';

        if (empty($locationCode) || empty($shelfType)) {
            $error = 'Wszystkie pola są wymagane';
        } else {
            try {
                if ($manager->addLocation($locationCode, $shelfType)) {
                    header('Location: admin_locations.php?message=' . urlencode('Miejsce zostało dodane pomyślnie'));
                    exit();
                } else {
                    $error = 'Błąd podczas dodawania miejsca';
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

$locations = $manager->getAllLocations();

require_once 'includes/header.php';
?>

<div class="header">
    <h1>Zarządzanie miejscami</h1>
    <div class="user-info">
        Zalogowany jako: <?= htmlspecialchars($_SESSION['username']) ?>
        <a href="logout.php" class="logout">Wyloguj</a>
    </div>
</div>

<div class="nav">
    <a href="index.php">Strona główna</a>
    <a href="admin_locations.php" class="active">Zarządzanie miejscami</a>
    <a href="admin_documents.php">Zarządzanie dokumentami</a>
    <a href="admin_archive.php">Archiwum</a>
    <a href="search.php">Wyszukiwanie</a>
    <a href="barcode_scan.php">Skanowanie kodu</a>
</div>

<div class="container">
    <div class="back-link">
        <a href="index.php">← Powrót do strony głównej</a>
    </div>

    <div class="card">
        <h2>Dodaj nowe miejsce</h2>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label for="location_code">Kod miejsca:</label>
                <input type="text" id="location_code" name="location_code" required>
            </div>

            <div class="form-group">
                <label for="shelf_type">Typ regału:</label>
                <select id="shelf_type" name="shelf_type" required>
                    <option value="">Wybierz typ regału</option>
                    <option value="civil">Sprawy cywilne</option>
                    <option value="criminal">Sprawy karne</option>
                </select>
            </div>

            <button type="submit">Dodaj miejsce</button>
        </form>
    </div>

    <div class="card">
        <h2>Lista miejsc</h2>

        <div class="tabs">
            <button class="tab all active" onclick="showTab('all')">Wszystkie</button>
            <button class="tab civil" onclick="showTab('civil')">Sprawy cywilne</button>
            <button class="tab criminal" onclick="showTab('criminal')">Sprawy karne</button>
        </div>

        <div id="all" class="tab-content active">
            <h3>Wszystkie miejsca</h3>
            <table>
                <thead>
                    <tr>
                        <th>Kod miejsca</th>
                        <th>Typ regału</th>
                        <th>Aktywne akta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $location): ?>
                        <tr>
                            <td><?= htmlspecialchars($location['location_code']) ?></td>
                            <td><?= $location['shelf_type'] == 'civil' ? 'Sprawy cywilne' : 'Sprawy karne' ?></td>
                            <td>
                                <span class="status <?= $location['active_documents_count'] > 0 ? 'occupied' : 'available' ?>">
                                    <?= $location['active_documents_count'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="civil" class="tab-content">
            <h3>Miejsca dla spraw cywilnych</h3>
            <table>
                <thead>
                    <tr>
                        <th>Kod miejsca</th>
                        <th>Aktywne akta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $location): ?>
                        <?php if ($location['shelf_type'] == 'civil'): ?>
                            <tr>
                                <td><?= htmlspecialchars($location['location_code']) ?></td>
                                <td>
                                    <span class="status <?= $location['active_documents_count'] > 0 ? 'occupied' : 'available' ?>">
                                        <?= $location['active_documents_count'] ?> akt
                                    </span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="criminal" class="tab-content">
            <h3>Miejsca dla spraw karnych</h3>
            <table>
                <thead>
                    <tr>
                        <th>Kod miejsca</th>
                        <th>Aktywne akta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $location): ?>
                        <?php if ($location['shelf_type'] == 'criminal'): ?>
                            <tr>
                                <td><?= htmlspecialchars($location['location_code']) ?></td>
                                <td>
                                    <span class="status <?= $location['active_documents_count'] > 0 ? 'occupied' : 'available' ?>">
                                        <?= $location['active_documents_count'] ?> akt
                                    </span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
</script>

<?php require_once 'includes/footer.php'; ?>