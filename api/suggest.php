<?php
session_start();
require_once 'includes/utils.php';

$suggestionTypes = ['author', 'book', 'chapter'];
$selectedType = $_POST['suggestion_type'] ?? 'author';
$errorMessages = [];
$successMessage = '';
$user = $_SESSION['user'];
error_log(json_encode($_POST));
error_log(json_encode($user));
if (!$user) {
    $errorMessages[] = "Vous devez être connecté pour soumettre une suggestion.";
    $_SESSION['error_messages'] = $errorMessages;
    header("Location: /suggestions/suggest?type=$selectedType");
}

if (!in_array($selectedType, $suggestionTypes)) {
    $errorMessages[] = "Type de suggestion invalide";
    $_SESSION['error_messages'] = $errorMessages;
} else {
    $connection = getDbConnection();
    try {
        $connection->beginTransaction();

        // Insérer d'abord dans la table suggestions
        $stmt = $connection->prepare('INSERT INTO suggestions (user_id, suggestion_type, status) VALUES (:user_id, :suggestion_type, :status)');
        $stmt->execute([
            'user_id' => $user['id'],
            'suggestion_type' => $selectedType,
            'status' => 'pending'
        ]);

        $suggestionId = $connection->lastInsertId();

        // Traiter selon le type de suggestion
        switch ($selectedType) {
            case 'author':
                // Validation
                $authorName = trim($_POST['author_name'] ?? '');
                $authorUrlName = toUrlName($authorName);
                $birthYear = !empty($_POST['birth_year']) ? intval($_POST['birth_year']) : null;
                $deathYear = !empty($_POST['death_year']) ? intval($_POST['death_year']) : null;
                $biography = trim($_POST['biography'] ?? '');

                if (empty($authorName)) {
                    throw new Exception("Le nom de l'auteur est obligatoire");
                }

                // Vérifier si l'URL name existe déjà
                $checkStmt = $connection->prepare('SELECT COUNT(*) FROM author_suggestions WHERE author_url_name = :url_name AND suggestion_id != :suggestion_id');
                $checkStmt->execute(['url_name' => $authorUrlName, 'suggestion_id' => $suggestionId]);
                if ($checkStmt->fetchColumn() > 0) {
                    throw new Exception("Un auteur avec ce nom existe déjà dans les suggestions");
                }

                // Insertion dans author_suggestions
                $stmt = $connection->prepare('INSERT INTO author_suggestions (suggestion_id, author_name, author_url_name, birth_year, death_year, biography) 
                                            VALUES (:suggestion_id, :author_name, :author_url_name, :birth_year, :death_year, :biography)');
                $stmt->execute([
                    'suggestion_id' => $suggestionId,
                    'author_name' => $authorName,
                    'author_url_name' => $authorUrlName,
                    'birth_year' => $birthYear,
                    'death_year' => $deathYear,
                    'biography' => $biography
                ]);
                break;

            case 'book':
                // Validation
                $authorId = !empty($_POST['author_id']) ? intval($_POST['author_id']) : 0;
                $title = trim($_POST['title'] ?? '');
                $urlTitle = toUrlName($title);
                $publicationYear = !empty($_POST['publication_year']) ? intval($_POST['publication_year']) : null;
                $description = trim($_POST['description'] ?? '');

                if (empty($title)) {
                    throw new Exception("Le titre du livre est obligatoire");
                }
                if ($authorId <= 0) {
                    throw new Exception("Veuillez sélectionner un auteur valide");
                }

                // Insertion dans book_suggestions
                $stmt = $connection->prepare('INSERT INTO book_suggestions (suggestion_id, author_id, title, url_title, publication_year, description) 
                                            VALUES (:suggestion_id, :author_id, :title, :url_title, :publication_year, :description)');
                $stmt->execute([
                    'suggestion_id' => $suggestionId,
                    'author_id' => $authorId,
                    'title' => $title,
                    'url_title' => $urlTitle,
                    'publication_year' => $publicationYear,
                    'description' => $description
                ]);
                break;

            case 'chapter':
                // Validation
                $bookId = !empty($_POST['book_id']) ? intval($_POST['book_id']) : 0;
                $title = trim($_POST['title'] ?? '');
                $chapterNumber = !empty($_POST['chapter_number']) ? intval($_POST['chapter_number']) : 0;
                $content = trim($_POST['content'] ?? '');

                if ($bookId <= 0) {
                    throw new Exception("Veuillez sélectionner un livre valide");
                }
                if ($chapterNumber <= 0) {
                    throw new Exception("Le numéro du chapitre doit être supérieur à zéro");
                }
                if (empty($content)) {
                    throw new Exception("Le contenu du chapitre est obligatoire");
                }

                // Insertion dans chapter_suggestions
                $stmt = $connection->prepare('INSERT INTO chapter_suggestions (suggestion_id, book_id, title, chapter_number, content) 
                                            VALUES (:suggestion_id, :book_id, :title, :chapter_number, :content)');
                $stmt->execute([
                    'suggestion_id' => $suggestionId,
                    'book_id' => $bookId,
                    'title' => $title,
                    'chapter_number' => $chapterNumber,
                    'content' => $content
                ]);
                break;
        }

        $connection->commit();
        $successMessage = "Votre suggestion a été soumise avec succès et sera examinée par nos administrateurs.";
        $_SESSION['error_messages'] = $errorMessages;
        $_SESSION['success_message'] = $successMessage;

    } catch (Exception $e) {
        $connection->rollBack();
        $errorMessages[] = "Erreur lors de la soumission : " . $e->getMessage();
        $_SESSION['error_messages'] = $errorMessages;
        $_SESSION['success_message'] = $successMessage;

    }
    $connection = null;
    header("Location: /suggestions/suggest?type=$selectedType");
}
