<?php
require_once 'includes/utils.php';

// Get database connection
$db = getDbConnection();

$attributes = [
    'book_id' => 'id',
    'title' => 'title',
    'publication_year' => 'publication_year',
    'description' => 'description',
    'author_id' => 'author_id'
];




// If a query parameter is provided, filter the books based on all the attributes
$query = "SELECT * FROM books";
$conditions = [];
$params = [];
foreach ($attributes as $dbField => $paramName) {
    if (!empty($_GET[$paramName])) {
        $conditions[] = "$dbField = :$dbField";
        $params[":$dbField"] = $_GET[$paramName];
    }
}
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$stmt = $db->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Include a chapter_count for each book and a chapter array with the chapter_number and title
foreach ($books as &$book) {
    $book['chapter_count'] = 0;
    $stmt = $db->prepare("SELECT COUNT(*) FROM chapters WHERE book_id = :book_id");
    $stmt->bindParam(':book_id', $book['book_id']);
    $stmt->execute();
    $book['chapter_count'] = $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT chapter_number, title FROM chapters WHERE book_id = :book_id");
    $stmt->bindParam(':book_id', $book['book_id']);
    $stmt->execute();
    $book['chapters'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Close the database connection
$db = null;
echo json_encode($books, JSON_PRETTY_PRINT);