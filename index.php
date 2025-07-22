<?php
require_once 'Auth.php';

$auth = new Auth();
$auth->requireLogin();

session_start();
$isAdmin = $_SESSION['is_admin'] ?? false;

$pageTitle = 'System Zarządzania Dokumentami';
include 'includes/header.php';
?>

<div class="header">
    <h1>System Zarządzania Dokumentami</h1>
    <div class="user-info">
        Zalogowany jako: <?= htmlspecialchars($_SESSION['username']) ?>
        <a href="logout.php" class="logout">Wyloguj</a>
    </div>
</div>

<div class="nav">
    <a href="index.php" class="active">Strona główna</a>
    <?php if ($isAdmin): ?>
        <a href="admin_locations.php">Zarządzanie miejscami</a>
        <a href="admin_documents.php">Zarządzanie dokumentami</a>
        <a href="admin_archive.php">Archiwum</a>
    <?php endif; ?>
    <a href="search.php">Wyszukiwanie</a>
    <a href="barcode_scan.php">Skanowanie kodu</a>
</div>

<div class="container">
    <div class="welcome">
        <h2>Witaj w systemie zarządzania dokumentami!</h2>
        <p>Wybierz opcję z menu poniżej, aby rozpocząć pracę z systemem.</p>
    </div>

    <div class="menu-grid">
        <?php if ($isAdmin): ?>
            <div class="menu-item">
                <h3>Zarządzanie miejscami</h3>
                <p>Dodawaj i zarządzaj miejscami na półkach</p>
                <a href="admin_locations.php" class="btn">Przejdź</a>
            </div>

            <div class="menu-item">
                <h3>Zarządzanie dokumentami</h3>
                <p>Dodawaj nowe dokumenty i generuj kody kreskowe</p>
                <a href="admin_documents.php" class="btn">Przejdź</a>
            </div>

            <div class="menu-item">
                <h3>Archiwum</h3>
                <p>Zarządzaj zarchiwizowanymi dokumentami</p>
                <a href="admin_archive.php" class="btn">Przejdź</a>
            </div>
        <?php endif; ?>

        <div class="menu-item">
            <h3>Wyszukiwanie</h3>
            <p>Wyszukuj dokumenty po nazwach osób lub sygnaturach</p>
            <a href="search.php" class="btn">Przejdź</a>
        </div>

        <div class="menu-item">
            <h3>Skanowanie kodu</h3>
            <p>Skanuj kody kreskowe i wyświetl informacje o dokumentach</p>
            <a href="barcode_scan.php" class="btn">Przejdź</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>