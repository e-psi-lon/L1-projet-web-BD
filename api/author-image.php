<?php

require_once 'includes/utils.php';

// It doesn't require any specific permissions to access images

$authorId = $_GET['author_id'] ?? null;
$suggestionId = $_GET['suggestion_id'] ?? null;

if ($authorId && $suggestionId) {
    http_response_code(400);
    echo json_encode(['error' => 'Veuillez spécifier uniquement un ID d\'auteur ou de suggestion, pas les deux.']);
    exit;
}
if (!$authorId && !$suggestionId) {
    http_response_code(400);
    echo json_encode(['error' => 'Veuillez spécifier un ID d\'auteur ou de suggestion.']);
    exit;
}
$pdo = getDbConnection();
$stmt = null;
if ($authorId) {
    $stmt = $pdo->prepare("SELECT image FROM authors WHERE author_id = ?");
    $stmt->execute([$authorId]);
} else {
    $stmt = $pdo->prepare("SELECT author_image FROM author_suggestions WHERE suggestion_id = ?");
    $stmt->execute([$suggestionId]);
}
$image = $stmt->fetchColumn();
if (!$image) {
    http_response_code(404);
    echo json_encode(['error' => 'Image non trouvée.']);
    exit;
}
$info = getimagesizefromstring($image);
if ($info === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des informations de l\'image.']);
    exit;
}
header('Content-Type: ' . $info['mime']);
switch ($info['mime']) {
    case 'image/jpeg':
        header('Content-Disposition: inline; filename="image.jpg"');
        break;
    case 'image/png':
        header('Content-Disposition: inline; filename="image.png"');
        break;
    case 'image/gif':
        header('Content-Disposition: inline; filename="image.gif"');
        break;
    default:
        http_response_code(415);
        echo json_encode(['error' => 'Type de contenu non pris en charge.']);
        exit;
}
header('Content-Length: ' . strlen($image));
echo $image;
exit;
