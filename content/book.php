<?php
include 'includes/header.php';

// Check if the author parameter is provided
if (empty($author)) {
    header("Location: /authors");
    exit();
}

if (empty($book)) {
    header("Location: ".getAuthorUrl($author));
    exit();
}

$author_info = null;
$book_info = null;
$chapters = [];

// Get database connection
$db = getDbConnection();

// Get book information by name (using URL-safe name)
$query = "SELECT 
    books.book_id, books.url_title, books.title, books.publication_year, books.description,
    authors.author_id, authors.name, authors.url_name, authors.birth_year, authors.death_year, authors.biography, (authors.image IS NOT NULL) AS image
FROM books
    JOIN authors ON books.author_id = authors.author_id
WHERE books.url_title = :title
    AND authors.url_name = :author_name";
$stmt = $db->prepare($query);
$stmt->bindParam(":author_name", $author);
$stmt->bindParam(":title", $book);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $author_info = [
        'name' => $result['name'],
        'url_name' => $result['url_name'],
        'birth_year' => $result['birth_year'],
        'death_year' => $result['death_year'],
        'biography' => $result['biography'],
        'image' => $result['image'] ? '<img src="/api/author-image?author_id=' . $result['author_id'] . '" alt="' . h($result['name']) . '" class="author-image-small">' : ''
    ];
    $book_info = [
        'title' => $result['title'],
        'url_title' => $result['url_title'],
        'publication_year' => $result['publication_year'],
        'description' => $result['description']
    ];

    // Get chapters for this book
    $query = "SELECT * FROM chapters WHERE book_id = :book_id ORDER BY chapter_number";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":book_id", $result['book_id']);
    $stmt->execute();
    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: " .getAuthorUrl($author));
    exit();
}

$stmt = null;
$db = null;
?>
    <div class="container">
        <?php if ($book_info): ?>
            <div class="book-header">
                <h1 class="card-title"><?php echo h($book_info['title']); ?></h1>
                <p class="book-year">
                    <?php echo ($book_info['publication_year'] ? 'Publié en ' . $book_info['publication_year'] : 'Date de publication inconnue'); ?>
                </p>
            </div>

            <div class="card">
                <h2 class="card-title">Description</h2>
                <div class="text-content">
                    <?php echo nl2br(h($book_info['description'] ?: 'Aucune description disponible.')); ?>
                </div>
            </div>

            <div class="author-section card">
                <h2 class="card-title">À propos de l'auteur</h2>

                <h3>
                    <a class="card-header" href="<?= getAuthorUrl($author_info['url_name']); ?>">
                        <?php echo $author_info['image']; ?>
                        <?php echo h($author_info['name']); ?>
                    </a>
                </h3>
                <p class="author-years">
                    <?php echo ($author_info['birth_year'] ?: '?'); ?> -
                    <?php echo ($author_info['death_year'] ?: '?'); ?>
                </p>
                <div class="author-bio-preview">
                    <?php echo (strlen($author_info['biography']) > 200 ?
                        substr(h($author_info['biography']), 0, 200) . '...' :
                        h($author_info['biography'])); ?>
                </div>
                <a href="<?= getAuthorUrl($author_info['url_name']); ?>" class="btn">Voir la page de l'auteur</a>
            </div>

            <?php if (count($chapters) > 0): ?>
                <h2>Chapitres & Parties</h2>
                <div class="chapter-list">
                    <?php foreach ($chapters as $chapter): ?>
                        <div class="chapter-item card">
                            <h3><?php echo h($chapter['title'] ?: 'Chapitre ' . $chapter['chapter_number']); ?></h3>
                            <p><?php echo (strlen($chapter['content']) > 100 ?
                                    substr(h($chapter['content']), 0, 100) . '...' :
                                    h($chapter['content'])); ?></p>
                            <a href="<?=getChapterUrl($author_info['url_name'], $book_info['url_title'], $chapter['chapter_number']);?>" class="btn">Lire le chapitre</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Livre non trouvé.</p>
        <?php endif; ?>
    </div>

<?php include 'includes/footer.php'; ?>