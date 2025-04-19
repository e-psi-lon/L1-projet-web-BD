<?php
require_once 'includes/utils.php';

// Get database connection
$db = getDbConnection();

$attributes = [
    'author_id' => 'id',
    'name' => 'name',
    'url_name' => 'url_name',
    'birth_year' => 'birth_year',
    'death_year' => 'death_year',
    'biography' => 'biography',
];




// If a query parameter is provided, filter the books based on all the attributes
$query = "SELECT * FROM authors";
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
// Include a book_count and a book array with the url_title and title
foreach ($books as &$book) {
    $book['book_count'] = 0;
    $stmt = $db->prepare("SELECT COUNT(*) FROM books WHERE author_id = :author_id");
    $stmt->bindParam(':book_id', $book['book_id']);
    $stmt->execute();
    $book['book_count'] = $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT url_title, title FROM books WHERE author_id = :author_id");
    $stmt->bindParam(':author_id', $book['author_id']);
    $stmt->execute();
    $book['books'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Close the database connection
$db = null;
echo json_encode($books, JSON_PRETTY_PRINT);