<?php
session_start();
require_once 'includes/utils.php';
ensureLoggedIn();

// Check if suggestion ID is provided and is valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de suggestion non valide.']);
    exit;
}

$suggestionId = (int) $_GET['id'];
$userId = $_SESSION['user']['id'];

error_log('User ID: ' . $userId . ' - Suggestion ID: ' . $suggestionId);

// Check if the user has access to the suggestion OR is an admin
$pdo = getDbConnection();
$stmt = $pdo->prepare("
    SELECT s.*
    FROM suggestions s
    RIGHT JOIN users u ON s.user_id = u.user_id
    WHERE s.suggestion_id = ? AND (s.user_id = ? OR u.is_admin = TRUE);");
$stmt->execute([$suggestionId, $userId]);
$suggestion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$suggestion) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Suggestion non trouvée ou accès non autorisé.']);
    exit;
}
error_log('Suggestion found: ' . json_encode($suggestion));

// Fetch the details based on the suggestion type
$stmt = null;
if ($suggestion['suggestion_type'] === 'author') {
    $stmt = $pdo->prepare("SELECT * FROM author_suggestions WHERE suggestion_id = ?");
} elseif ($suggestion['suggestion_type'] === 'book') {
    $stmt = $pdo->prepare("
        SELECT bs.*, a.name as author_name 
        FROM book_suggestions bs
        LEFT JOIN authors a ON bs.author_id = a.author_id
        WHERE bs.suggestion_id = ?
    ");
} elseif ($suggestion['suggestion_type'] === 'chapter') {
    $stmt = $pdo->prepare("
        SELECT cs.*, b.title as book_title 
        FROM chapter_suggestions cs
        LEFT JOIN books b ON cs.book_id = b.book_id
        WHERE cs.suggestion_id = ?
    ");
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Type de suggestion non valide.']);
    exit;
}
$stmt->execute([$suggestionId]);
$details = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$details) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Détails de la suggestion non trouvés.']);
    exit;
}
if ($details['author_image']) {
    unset($details['author_image']);
    $details['author_image_url'] = '/api/author-image?suggestion_id=' . $suggestionId;
}
error_log('Details found: ' . json_encode($details));
// Prepare the response
$response = [
    'type' => $suggestion['suggestion_type'],
    'status' => $suggestion['status'],
    'details' => $details,
    'admin_notes' => $suggestion['admin_notes'],
];

// Output details in JSON
header('Content-Type: application/json');
echo json_encode($response);
