<?php
include 'includes/header.php';

// Check if author parameter is provided
if (empty($author)) {
    header("Location: /authors");
    exit();
}

$author_info = null;
$books = [];

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get author information by name (using URL-safe name)
$query = "SELECT * FROM authors WHERE url_name = :author_name OR name = :author_name";
$stmt = $db->prepare($query);
$stmt->bindParam(":author_name", $author);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $author_info = $stmt->fetch(PDO::FETCH_ASSOC);
    $author_id = $author_info['author_id'];

    // Get author's books
    $query = "SELECT * FROM books WHERE author_id = :author_id ORDER BY title";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":author_id", $author_id);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $books[] = $row;
    }
} else {
    header("Location: /authors");
    exit();
}
?>

<div class="container">
    <?php if ($author_info): ?>
        <div class="author-header">
            <h1><?php echo htmlspecialchars($author_info['name']); ?></h1>
            <p class="author-years">
                <?php echo ($author_info['birth_year'] ?: '?'); ?> -
                <?php echo ($author_info['death_year'] ?: '?'); ?>
            </p>
        </div>

        <div class="card">
            <h2 class="card-title">Biographie</h2>
            <div class="text-content">
                <?php echo nl2br(htmlspecialchars($author_info['biography'])); ?>
            </div>
        </div>

        <h2>Œuvres</h2>

        <?php if (count($books) > 0): ?>
            <div class="book-list">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <h3><a href="/authors/<?php echo urlencode($author); ?>/books/<?php echo urlencode($book['url_name'] ?? $book['book_id']); ?>"><?php echo htmlspecialchars($book['title']); ?></a></h3>
                        <p>Année: <?php echo ($book['publication_year'] ?: 'Inconnue'); ?></p>
                        <p>Langue: <?php echo htmlspecialchars($book['language']); ?></p>
                        <p><?php echo (strlen($book['description']) > 100 ? substr(htmlspecialchars($book['description']), 0, 100) . '...' : htmlspecialchars($book['description'])); ?></p>
                        <a href="/authors/<?php echo urlencode($author); ?>/books/<?php echo urlencode($book['url_name'] ?? $book['book_id']); ?>" class="btn">En savoir plus</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Aucun livre trouvé.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>Auteur non trouvé.</p>
    <?php endif; ?>
</div>