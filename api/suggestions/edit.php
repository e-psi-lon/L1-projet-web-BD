<?php
session_start();
require_once 'includes/utils.php';

$suggestionTypes = ['author', 'book', 'chapter'];
$selectedType = $_POST['suggestion_type'] ?? 'author';
$errorMessages = [];
$successMessage = '';
ensureLoggedInError("/suggestions/suggest?type=$selectedType");
$user = $_SESSION['user'];
$suggestionId = $_POST['suggestion_id'] ?? 0;

if (!in_array($selectedType, $suggestionTypes)) {
    $errorMessages[] = "Type de suggestion invalide";
    http_response_code(400);
    $_SESSION['error_messages'] = $errorMessages;
} else {
    $connection = getDbConnection();
    try {
        $connection->beginTransaction();

        // Update first in the table suggestions
        $stmt = $connection->prepare('UPDATE suggestions SET user_id = :user_id, suggestion_type = :suggestion_type, status = :status WHERE suggestion_id = :suggestion_id');
        $stmt->execute([
            'user_id' => $user['id'],
            'suggestion_type' => $selectedType,
            'status' => 'pending',
            'suggestion_id' => $suggestionId
        ]);

        // Handle according to the suggestion type
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

                $imageParams = [];
                if (isset($_FILES['author_image']) && $_FILES['author_image']['error'] == 0) {
                    // Vérification du type de fichier
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($fileInfo, $_FILES['author_image']['tmp_name']);
                    finfo_close($fileInfo);

                    if (!in_array($mimeType, $allowedTypes)) {
                        throw new Exception("Type de fichier non autorisé. Formats acceptés: JPEG, PNG, GIF");
                    }

                    // Vérification de la taille (2MB max)
                    if ($_FILES['author_image']['size'] > 2 * 1024 * 1024) {
                        throw new Exception("L'image est trop volumineuse (max 2MB)");
                    }

                    // Lecture de l'image en binaire
                    $authorImage = file_get_contents($_FILES['author_image']['tmp_name']);
                    $imageParams = ['author_image' => $authorImage];
                }

                // Check if a suggestion with the same URL name already exists
                $checkStmt = $connection->prepare('SELECT COUNT(*) FROM author_suggestions WHERE author_url_name = :url_name AND suggestion_id != :suggestion_id');
                $checkStmt->execute(['url_name' => $authorUrlName, 'suggestion_id' => $suggestionId]);
                if ($checkStmt->fetchColumn() > 0) {
                    throw new Exception("Un auteur avec ce nom existe déjà dans les suggestions");
                }
                // Check if the author already exists in the 'authors' table
                $checkStmt = $connection->prepare('SELECT COUNT(*) FROM authors WHERE url_name = :url_name');
                $checkStmt->execute(['url_name' => $authorUrlName]);
                if ($checkStmt->fetchColumn() > 0) {
                    throw new Exception("Un auteur avec ce nom existe déjà dans la base de données");
                }

                // Update author_suggestions
                $updateFields = 'author_name = :author_name, author_url_name = :author_url_name, birth_year = :birth_year, death_year = :death_year, biography = :biography';
                if (!empty($imageParams)) {
                    $updateFields .= ', author_image = :author_image';
                }

                // Update author_suggestions
                $stmt = $connection->prepare("UPDATE author_suggestions SET {$updateFields} WHERE suggestion_id = :suggestion_id");
                $params = [
                    'suggestion_id' => $suggestionId,
                    'author_name' => $authorName,
                    'author_url_name' => $authorUrlName,
                    'birth_year' => $birthYear,
                    'death_year' => $deathYear,
                    'biography' => $biography,
                ];

                if (!empty($imageParams)) {
                    $params = array_merge($params, $imageParams);
                }

                $stmt->execute($params);
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

                // Check if a suggestion with the same URL title already exists for this author
                $checkStmt = $connection->prepare('SELECT COUNT(*) FROM book_suggestions WHERE author_id = :author_id AND url_title = :url_title AND suggestion_id != :suggestion_id');
                $checkStmt->execute([
                    'author_id' => $authorId,
                    'url_title' => $urlTitle,
                    'suggestion_id' => $suggestionId
                ]);
                if ($checkStmt->fetchColumn() > 0) {
                    throw new Exception("Un livre avec ce titre existe déjà dans les suggestions pour cet auteur");
                }

                // Check if the book already exists in the 'books' table
                $checkStmt = $connection->prepare('SELECT COUNT(*) FROM books WHERE author_id = :author_id AND url_title = :url_title');
                $checkStmt->execute([
                    'author_id' => $authorId,
                    'url_title' => $urlTitle
                ]);
                if ($checkStmt->fetchColumn() > 0) {
                    throw new Exception("Un livre avec ce titre existe déjà dans la base de données pour cet auteur");
                }

                // Check if the author exists
                $checkStmt = $connection->prepare('SELECT COUNT(*) FROM authors WHERE author_id = :author_id');
                $checkStmt->execute(['author_id' => $authorId]);
                if ($checkStmt->fetchColumn() == 0) {
                    throw new Exception("L'auteur sélectionné n'existe pas");
                }

                // Update book_suggestions
                $stmt = $connection->prepare('UPDATE book_suggestions SET author_id = :author_id, title = :title, url_title = :url_title, publication_year = :publication_year, description = :description WHERE suggestion_id = :suggestion_id');
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

                // Check if the book exists
                $checkStmt = $connection->prepare('SELECT COUNT(*) FROM books WHERE book_id = :book_id');
                $checkStmt->execute(['book_id' => $bookId]);
                if ($checkStmt->fetchColumn() == 0) {
                    throw new Exception("Le livre sélectionné n'existe pas"); 
                }

                // Check if a chapter with this number already exists in suggestions
                $checkStmt = $connection->prepare('SELECT COUNT(*) FROM chapter_suggestions WHERE book_id = :book_id AND chapter_number = :chapter_number AND suggestion_id != :suggestion_id');
                $checkStmt->execute([
                    'book_id' => $bookId,
                    'chapter_number' => $chapterNumber,
                    'suggestion_id' => $suggestionId
                ]);
                if ($checkStmt->fetchColumn() > 0) {
                    throw new Exception("Un chapitre avec ce numéro existe déjà dans les suggestions pour ce livre"); 
                }

                // Check if a chapter with this number already exists in the 'chapters' table
                $checkStmt = $connection->prepare('SELECT COUNT(*) FROM chapters WHERE book_id = :book_id AND chapter_number = :chapter_number');
                $checkStmt->execute([
                    'book_id' => $bookId,
                    'chapter_number' => $chapterNumber
                ]);
                if ($checkStmt->fetchColumn() > 0) {
                    throw new Exception("Un chapitre avec ce numéro existe déjà dans la base de données pour ce livre"); 
                }

                // Update chapter_suggestions
                $stmt = $connection->prepare('UPDATE chapter_suggestions SET book_id = :book_id, title = :title, chapter_number = :chapter_number, content = :content WHERE suggestion_id = :suggestion_id');
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
        $successMessage = "Votre suggestion a été mise à jour avec succès et sera examinée par nos administrateurs.";
        $_SESSION['error_messages'] = $errorMessages;
        $_SESSION['success_message'] = $successMessage;
        http_response_code(200);
    } catch (Exception $e) {
        $connection->rollBack();
        $errorMessages[] = "Erreur lors de la modification : " . $e->getMessage();
        http_response_code(500);
        $_SESSION['error_messages'] = $errorMessages;
        $_SESSION['success_message'] = $successMessage;

    }
    $connection = null;
    header("Location: /suggestions/$suggestionId/edit");
}
