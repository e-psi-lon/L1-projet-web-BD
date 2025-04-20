<?php
session_start();
require_once 'includes/utils.php';

$suggestionTypes = ['author', 'book', 'chapter'];
$selectedType = $_POST['suggestion_type'] ?? 'author';
$errorMessages = [];
$successMessage = '';
ensureLoggedInError("/suggestions/suggest?type=$selectedType");
$user = $_SESSION['user'];


if (!in_array($selectedType, $suggestionTypes)) {
    $errorMessages[] = "Type de suggestion invalide"; // Invalid suggestion type
    $_SESSION['error_messages'] = $errorMessages;
    http_response_code(400);
} else {
    $connection = getDbConnection();
    try {
        $connection->beginTransaction();

        // Insert first in the table suggestions
        $stmt = $connection->prepare('INSERT INTO suggestions (user_id, suggestion_type, status) VALUES (:user_id, :suggestion_type, :status)');
        $stmt->execute([
            'user_id' => $user['id'],
            'suggestion_type' => $selectedType,
            'status' => 'pending'
        ]);

        $suggestionId = $connection->lastInsertId();

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

                // Insert into author_suggestions
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

                // Insert into book_suggestions
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

                // Insert into chapter_suggestions
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
        $successMessage = "Votre suggestion a été soumise avec succès et sera examinée par nos administrateurs."; // Your suggestion has been successfully submitted and will be reviewed by our administrators
        $_SESSION['error_messages'] = $errorMessages;
        $_SESSION['success_message'] = $successMessage;

    } catch (Exception $e) {
        $connection->rollBack();
        $errorMessages[] = "Erreur lors de la soumission : " . $e->getMessage(); // Error during submission
        $_SESSION['error_messages'] = $errorMessages;
        $_SESSION['success_message'] = $successMessage;

    }
    $connection = null;
    http_response_code(200);
    header("Location: /suggestions/suggest?type=$selectedType");
}
