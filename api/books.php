<?php
require_once 'includes/utils.php';

// Get database connection
$db = getDbConnection();

$attributes = [
    'books.book_id' => 'id',
    'title' => 'title',
    'publication_year' => 'publication_year',
    'description' => 'description',
    'author_id' => 'author_id'
];




// If a query parameter is provided, filter the books based on all the attributes
$query = "SELECT books.*, COUNT(*) as chapter_count 
              FROM chapters 
              RIGHT JOIN books ON books.book_id = chapters.book_id";
$conditions = [];
$params = [];
foreach ($attributes as $dbField => $paramName) {
    if (!empty($_GET[$paramName])) {
        $conditions[] = "$dbField = :".dbFieldToParamName($dbField);
        $params[":".dbFieldToParamName($dbField)] = is_numeric($_GET[$paramName]) ? (int)$_GET[$paramName] : $_GET[$paramName];
    }
}
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
// Add GROUP BY clause to group by book_id
$query .= " GROUP BY books.book_id, books.title;";
$stmt = $db->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Include a chapter array with the chapter_number and title
foreach ($books as &$book) {
    $stmt = $db->prepare("SELECT chapter_number, title FROM chapters WHERE book_id = :book_id");
    $stmt->bindParam(':book_id', $book['book_id']);
    $stmt->execute();
    $book['chapters'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Close the database connection
$db = null;
echo json_encode($books, JSON_PRETTY_PRINT);