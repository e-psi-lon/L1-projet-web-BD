<?php

// Get database connection
$db = getDbConnection();

// Query all authors
$query = "SELECT * FROM authors ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = null;
$db = null;

// Output a JSON formatted response
echo json_encode($result);
