<?php
session_start();
require_once 'includes/utils.php';

// Check if user is logged in
$loggedIn = isset($_SESSION['user']);
$isAdmin = $loggedIn && isset($_SESSION['user']) && $_SESSION['user']['is_admin'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corpus Digitale</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Load this before everything else to ensure the dark mode is applied immediately -->
    <script>
        // Récupérer la préférence utilisateur depuis localStorage
        const darkMode = localStorage.getItem('darkMode') === 'true';
        // Applique immédiatement la classe au document
        if (darkMode) {
            document.documentElement.classList.add('dark-mode');
        } else if (localStorage.getItem('darkMode') === null &&
            window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            // Si pas de préférence enregistrée et que le système préfère le thème sombre
            document.documentElement.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'true');
        }
    </script>
    <script src="/assets/js/dark-mode.js">
    </script>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><a onclick='window.location.href="/"'>Corpus Digitale</a></h1>
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
                        <li><a onclick='window.location.href="/suggestions/suggest"'>Suggérer du contenu</a></li>
                        <?php if ($isAdmin): ?>
                            <li><a onclick='window.location.href="/admin/dashboard"'>Admin</a></li>
                        <?php endif; ?>
                        <li><a onclick='window.location.href="/account"'><?php echo htmlspecialchars($_SESSION['user']["username"]); ?></a></li>
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
