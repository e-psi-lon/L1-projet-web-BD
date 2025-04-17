<?php
require_once 'includes/utils.php';
header('Content-Type: application/json');

// Get database connection
$db = getDbConnection();

// Query all data
$query = "SELECT * FROM books ORDER BY title";
$stmt = $db->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = null;
$db = null;

// Output a JSON formatted response
echo json_encode($result);


// It goes the same for all API endpoints