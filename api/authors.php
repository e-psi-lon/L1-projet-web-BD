<?php
require_once 'includes/utils.php';
header('Content-Type: application/json');

$db = getDbConnection();

$query = "SELECT * FROM authors ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = null;
$db = null;

echo json_encode($result);
