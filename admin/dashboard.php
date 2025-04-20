<?php include 'includes/header.php';

// Check if the user is logged in and is an admin
ensureLoggedIn();
ensureAdmin();

// Fetch all counts (users, authors, books, chapters and suggestions)
$pdo = getDbConnection();
$stmt = $pdo->prepare("
    SELECT 
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT COUNT(*) FROM authors) AS total_authors,
    (SELECT COUNT(*) FROM books) AS total_books,
    (SELECT COUNT(*) FROM chapters) AS total_chapters,
    (SELECT COUNT(*) FROM suggestions) AS total_suggestions
");
$stmt->execute();
$counts = $stmt->fetch(PDO::FETCH_ASSOC);
$totalUsers = $counts['total_users'];
$totalAuthors = $counts['total_authors'];
$totalBooks = $counts['total_books'];
$totalChapters = $counts['total_chapters'];
$totalSuggestions = $counts['total_suggestions'];
?>

    <div class="container">
        <div class="card">
            <h1 class="card-title">Tableau de bord</h1>
            <p>Bienvenue sur le tableau de bord administratif.</p>
            <p>Vous pouvez gérer les utilisateurs, les suggestions et d'autres fonctionnalités administratives.</p>
            <a href="/admin/users" class="btn">Gérer les utilisateurs</a>
            <a href="/admin/suggestions" class="btn">Gérer les suggestions</a>
        </div>
        <div class="card">
            <!-- Show some information about the site -->
            <h2 class="card-title">Statistiques du site</h2>
            <p>Nombre total d'utilisateurs : <strong><?php echo $totalUsers; ?></strong></p>
            <p>Nombre total d'auteurs : <strong><?php echo $totalAuthors; ?></strong></p>
            <p>Nombre total de livres : <strong><?php echo $totalBooks; ?></strong></p>
            <p>Nombre total de chapitres : <strong><?php echo $totalChapters; ?></strong></p>
            <p>Nombre total de suggestions : <strong><?php echo $totalSuggestions; ?></strong></p>
            <p>Nombre total de suggestions en attente : <strong>
                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM suggestions WHERE status = 'pending'");
                $stmt->execute();
                $pendingCount = $stmt->fetchColumn();
                echo $pendingCount;
                ?>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>