<?php
include 'includes/header.php';

// Check if the author parameter is provided
if (empty($author)) {
    header("Location: /authors");
    exit();
}

if (empty($book)) {
    header("Location: /authors/" . $author);
    exit();
}
?>

<?php
if (empty($chapter)) {
    header("Location: /authors/" . $author . "/books/" . $book);
    exit();
}

$book_info = null;
$chapter_info = null;

// Get database connection
$db = getDbConnection();

// Get chapter information by book and chapter number
$query = "SELECT 
    books.book_id, books.title AS book_title, books.publication_year, books.description,
    chapters.title AS chapter_title, chapters.chapter_number, chapters.content
FROM chapters
    JOIN books ON chapters.book_id = books.book_id
WHERE books.title = :title
    AND chapters.chapter_number = :chapter_number";
$stmt = $db->prepare($query);
$author_db = fromUrlName($author);
$book_db = fromUrlName($book);
$stmt->bindParam(":title", $book_db);
$stmt->bindParam(":chapter_number", $chapter);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    header("Location: /authors/" . $author . "/books/" . $book);
    exit();
}

$book_info = [
    'title' => $result['book_title'],
    'publication_year' => $result['publication_year'],
    'description' => $result['description']
];

$chapter_info = [
    'title' => $result['chapter_title'],
    'chapter_number' => $result['chapter_number'],
    'content' => $result['content']
];

$stmt = null;
$db = null;
?>
<div class="container">
    <?php if ($book_info && $chapter_info): ?>
        <div class="book-header">
            <h1><?php echo htmlspecialchars($book_info['title']); ?></h1>
            <p class="book-year">
                <?php echo ($book_info['publication_year'] ? 'Publié en ' . $book_info['publication_year'] : 'Date de publication inconnue'); ?>
            </p>
            <div class="text-content">
                <?php echo nl2br(htmlspecialchars($book_info['description'] ?: 'Aucune description disponible.')); ?>
            </div>
        </div>

        <div class="chapter-content">
            <h2><?php echo htmlspecialchars($chapter_info['title'] ?: 'Chapitre ' . $chapter_info['chapter_number']); ?></h2>
            <div class="text-content">
                <?php echo nl2br(htmlspecialchars($chapter_info['content'])); ?>
            </div>
        </div>
    <?php else: ?>
        <p>Chapitre non trouvé.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
