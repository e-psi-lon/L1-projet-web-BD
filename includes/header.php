<?php
session_start();
// require_once 'config/database.php';

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);
$isAdmin = $loggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Litterae Aeternae</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="/assets/js/darkMode.js" defer></script>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><a onclick='window.location.href="/"'>Litterae Aeternae</a></h1>
            </div>
            <button class="menu-toggle" id="menuToggle" aria-expanded="false" aria-label="Menu">
                <i data-lucide="menu" class="icon"></i>
            </button>
            <nav id="mainNav">
                <ul>
                    <li><a onclick='window.location.href="/index"'>Accueil</a></li>
                    <li><a onclick='window.location.href="/authors"'>Auteurs</a></li>
                    <li><a onclick='window.location.href="/search"'>Recherche</a></li>
                    <?php if ($loggedIn): ?>
                        <li><a onclick='window.location.href="/suggest"'>Suggérer du contenu</a></li>
                        <?php if ($isAdmin): ?>
                            <li><a onclick='window.location.href="/admin/dashboard"'>Admin</a></li>
                        <?php endif; ?>
                        <li><a onclick='window.location.href="/auth/profile"'><?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                        <li><a onclick='window.location.href="/auth/logout"'>Déconnexion</a></li>
                    <?php else: ?>
                        <li><a onclick='window.location.href="/auth/login"'>Connexion</a></li>
                        <li><a onclick='window.location.href="/auth/register"'>Inscription</a></li>
                    <?php endif; ?>
                    <li class="dark-mode-container">
                        <label class="dark-mode-toggle">
                            <input type="checkbox" id="darkModeToggle">
                            <span class="slider">
                                <i data-lucide="sun" class="icon-light"></i>
                                <i data-lucide="moon" class="icon-dark"></i>
                            </span>
                        </label>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main>