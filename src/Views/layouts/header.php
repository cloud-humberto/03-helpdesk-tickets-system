<?php
declare(strict_types=1);
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'HelpDesk Pro — Enterprise Ticket Management' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-container">
            <a href="/tickets" class="nav-brand">
                <span class="brand-icon">🎫</span>
                <span class="brand-title">HelpDesk<strong>Pro</strong></span>
                <span class="badge-role <?= ($currentUser['role'] ?? '') === 'admin' ? 'role-admin' : 'role-client' ?>">
                    <?= ($currentUser['role'] ?? '') === 'admin' ? 'Technician Portal' : 'Customer Portal' ?>
                </span>
            </a>

            <div class="nav-actions">
                <?php if (isset($currentUser)): ?>
                    <span class="user-greeting">Signed in as <strong><?= htmlspecialchars($currentUser['name']) ?></strong></span>
                    <a href="/logout" class="btn btn-outline btn-sm">Log out</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-primary btn-sm">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main-body">
        <div class="container">
            <?php if ($flashSuccess): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
            <?php endif; ?>
