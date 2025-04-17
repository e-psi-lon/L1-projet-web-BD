<?php
require_once 'includes/utils.php';
header('Content-Type: application/json');

if (!isset($_GET['q']) || strlen($_GET['q']) < 3) {
    echo json_encode([]);
    exit;
}

$search = $_GET['q'];
$db = getDbConnection();

// Requête simplifiée pour l'autocomplétion
$query = "
SELECT 
    books.title AS book_title,
    books.url_title AS url_title,
    chapters.title AS chapter_title,
    chapters.chapter_number,
    authors.name AS author_name,
    authors.url_name as url_name,
    chapters.content,
    CASE 
        WHEN books.title LIKE :search THEN 3
        WHEN chapters.title LIKE :search THEN 2
        ELSE 1
    END AS relevance
FROM chapters
JOIN books ON chapters.book_id = books.book_id
JOIN authors ON books.author_id = authors.author_id
WHERE 
    books.title LIKE :search
    OR chapters.title LIKE :search
    OR chapters.content LIKE :search
ORDER BY relevance DESC, book_title ASC, chapter_number ASC
LIMIT 50";

$stmt = $db->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


echo json_encode($results);