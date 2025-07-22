<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../DocumentManager.php';

$auth = new Auth();
$manager = new DocumentManager();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'System Zarządzania Dokumentami' ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>