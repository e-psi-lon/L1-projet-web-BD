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

$notes = $_POST['notes'] ?? null;
if (!$notes) {
    http_response_code(400);
    echo json_encode(['error' => 'Notes à ajouter manquantes']);
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
    $stmt = $pdo->prepare("UPDATE suggestions SET status = 'reviewed', admin_notes = ?, reviewed_by = ? WHERE suggestion_id = ?");
    $stmt->execute([$notes, $_SESSION['user']['id'], $suggestionId]);
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

