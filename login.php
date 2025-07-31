<?php
require_once 'Auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($auth->login($username, $password)) {
        header('Location: index.php');
        exit();
    } else {
        $error = 'Nieprawidłowa nazwa użytkownika lub hasło';
    }
}

$pageTitle = 'Logowanie - System Zarządzania Dokumentami';
include 'includes/header.php';
?>

<div class="login-container">
    <h1>System Zarządzania Dokumentami</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="password">Hasło:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Zaloguj się</button>
        
    </form>

</div>

<?php include 'includes/footer.php'; ?>