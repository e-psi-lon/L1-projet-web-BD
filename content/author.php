<?php
include 'includes/header.php';

// Check if the author parameter is provided
if (empty($author)) {
    header("Location: /authors");
    exit();
}

$author_info = null;
$books = [];

// Get database connection
$db = getDbConnection();

// Get author information by name (using URL-safe name)
$query = "SELECT * FROM authors WHERE authors.url_name = :author_name";
$stmt = $db->prepare($query);
$stmt->bindParam(":author_name", $author);
$stmt->execute();
$author_info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($author_info) {
    $author_id = $author_info['author_id'];
    $stmt = null;
    // Get author's books
    $query = "SELECT * FROM books WHERE author_id = :author_id ORDER BY title";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":author_id", $author_id);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $books[] = $row;
    }
    $stmt = null;
    $db = null;
} else {
    header("Location: /authors");
    $stmt = null;
    $db = null;
    exit();
}
?>

<div class="container">
    <?php if ($author_info): ?>
        <div class="author-header">
            <?php if (!empty($author_info['author_image'])): ?>
                <div class="author-portrait">
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($author_info['author_image']); ?>" alt="<?php echo h($author_info['name']); ?>">
                </div>
            <?php endif; ?>
            <h1 class="card-title"><?php echo h($author_info['name']); ?></h1>
            <p class="author-years">
                <?php echo ($author_info['birth_year'] ?: '?'); ?> -
                <?php echo ($author_info['death_year'] ?: '?'); ?>
            </p>
        </div>

        <div class="card">
            <h2 class="card-title">Biographie</h2>
            <div class="text-content">
                <?php echo nl2br(h($author_info['biography'])); ?>
            </div>
        </div>

        <h2>Œuvres</h2>

        <?php if (count($books) > 0): ?>
            <div class="book-list">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <h3><a href="<?=getBookUrl($author, $book['url_title'] ?? $book['book_id']);?>"><?php echo h($book['title']); ?></a></h3>
                        <p>Année: <?php echo ($book['publication_year'] ?: 'Inconnue'); ?></p>
                        <p><?php echo (strlen($book['description']) > 100 ? substr(h($book['description']), 0, 100) . '...' : h($book['description'])); ?></p>
                        <a href="<?=getBookUrl($author, $book['url_title'] ?? $book['book_id']);?>" class="btn">En savoir plus</a>
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
<?php
include 'includes/footer.php';
?>