<?php
require_once 'includes/utils.php';

// Get database connection
$db = getDbConnection();

$attributes = [
    'authors.author_id' => 'id',
    'name' => 'name',
    'url_name' => 'url_name',
    'birth_year' => 'birth_year',
    'death_year' => 'death_year',
    'biography' => 'biography',
];



// If a query parameter is provided, filter the books based on all the attributes
$query = "SELECT authors.*, COUNT(*) as chapter_count 
              FROM books 
              RIGHT JOIN authors ON books.author_id = authors.author_id";
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
$query .= " GROUP BY authors.author_id, authors.name;";
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