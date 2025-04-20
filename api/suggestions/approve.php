<?php
session_start();

require_once 'includes/utils.php';
// Check user authentication and admin privileges
ensureLoggedInError();
ensureAdminError();

// Get suggestion ID from POST data
$suggestionId = $_POST['suggestion_id'] ?? null;
if (!$suggestionId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de la suggestion manquant']);
    exit;
}

try {
    $pdo = getDbConnection();
    $pdo->beginTransaction();

    // Check if the suggestion exists and get its type
    $stmt = $pdo->prepare("SELECT suggestion_type FROM suggestions WHERE suggestion_id = ?");
    $stmt->execute([$suggestionId]);
    $suggestion = $stmt->fetch();

    if (!$suggestion) {
        throw new Exception('Suggestion non trouvée');
    }

    // Update suggestion status
    $stmt = $pdo->prepare("UPDATE suggestions SET status = 'approved', reviewed_by = ? WHERE suggestion_id = ?");
    $stmt->execute([$_SESSION['user']['id'], $suggestionId]);

    // Move data to the appropriate table based on suggestion_type
    switch ($suggestion['suggestion_type']) {
        case 'author':
            $stmt = $pdo->prepare("
                INSERT INTO authors (name, url_name, birth_year, death_year, biography, author_image)
                SELECT author_name, author_url_name, birth_year, death_year, biography, author_image
                FROM author_suggestions 
                WHERE suggestion_id = ?
            ");
            break;
        case 'book':
            $stmt = $pdo->prepare("
                INSERT INTO books (title, url_title, author_id, publication_year, description)
                SELECT title, url_title, author_id, publication_year, description 
                FROM book_suggestions 
                WHERE suggestion_id = ?
            ");
            break;
        case 'chapter':
            $stmt = $pdo->prepare("
                INSERT INTO chapters (book_id, title, content, chapter_number)
                SELECT book_id, title, content, chapter_number
                FROM chapter_suggestions 
                WHERE suggestion_id = ?
            ");
            break;
        default:
            throw new Exception('Type de suggestion invalide');
    }

    $stmt->execute([$suggestionId]);
    $pdo->commit();

    http_response_code(200);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

