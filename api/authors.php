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
$query = "SELECT authors.*, COUNT(*) as chapter_count 
              FROM books 
              JOIN authors ON books.author_id = authors.author_id
              GROUP BY author_id, authors.name";
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
$authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Include a book array with the url_title and title
foreach ($authors as &$author) {
    $stmt = $db->prepare("SELECT url_title, title FROM books WHERE author_id = :author_id");
    $stmt->bindParam(':author_id', $author['author_id']);
    $stmt->execute();
    $authors['books'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Close the database connection
$db = null;
echo json_encode($authors, JSON_PRETTY_PRINT);