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
    $stmt = $pdo->prepare("UPDATE suggestions SET status = 'rejected', reviewed_by = ? WHERE suggestion_id = ?");
    $stmt->execute([$_SESSION['user']['id'], $suggestionId]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback the transaction in case of error
    if (isset($pdo)) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors du traitement de la demande: ' . $e->getMessage()]);
}