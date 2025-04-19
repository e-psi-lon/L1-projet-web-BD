<?php
include 'includes/header.php';

if (!isset($_SESSION['user'])) {
    header('Location: /auth/login');
    exit();
}

if (empty($suggestionId)) {
    header('Location: /suggestions/my/suggestions');
    exit();
}

$user = $_SESSION['user'];
$errorMessages = $_SESSION['error_messages'] ?? [];
$from = $_SESSION['from'] ?? '';
unset($_SESSION['from']);

unset($_SESSION['error_messages']);

try {
    $connection = getDbConnection();

    // Fetch the main suggestion details
    $stmt = $connection->prepare('
        SELECT s.suggestion_id, s.user_id, s.suggestion_type, s.status
        FROM suggestions s
        WHERE s.suggestion_id = :id
    ');
    $stmt->execute(['id' => $suggestionId]);
    $suggestion = $stmt->fetch();

    if (!$suggestion) {
        throw new Exception("Suggestion non trouvée ou vous n'êtes pas autorisé à la modifier");
    }

    if ($suggestion['user_id'] !== $user['id'] && !$user['is_admin']) {
        // It's not the user's suggestion, they don't have access to it
        header('Location: /suggestions/my/suggestions');
    }

    $suggestionType = $suggestion['suggestion_type'];

    // Fetch the suggestion details based on the type
    $stmt = match ($suggestionType) {
        'author' => $connection->prepare('
                SELECT author_name, birth_year, death_year, biography
                FROM author_suggestions
                WHERE suggestion_id = :id
            '),
        'book' => $connection->prepare('
                SELECT author_id, title, publication_year, description
                FROM book_suggestions
                WHERE suggestion_id = :id
            '),
        'chapter' => $connection->prepare('
                SELECT book_id, title, chapter_number, content
                FROM chapter_suggestions
                WHERE suggestion_id = :id
            '),
        default => throw new Exception("Type de suggestion non reconnu"),
    };

    $stmt->execute(['id' => $suggestionId]);
    $suggestionData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$suggestionData) {
        throw new Exception("Données de suggestion introuvables");
    }

    // Fetch the authors to show them in the form
    $authors = $connection->query('SELECT author_id, name FROM authors ORDER BY name')->fetchAll();
    $books = [];

    if ($suggestionType === 'chapter' && isset($suggestionData['book_id'])) {
        // For the chapter, fetch the book details
        $stmt = $connection->prepare('
            SELECT b.book_id, b.title, b.author_id
            FROM books b
            WHERE b.book_id = :book_id
        ');
        $stmt->execute(['book_id' => $suggestionData['book_id']]);
        $book = $stmt->fetch();

        if ($book) {
            // Fetch all books by the same author
            $stmt = $connection->prepare('
                SELECT book_id, title, author_id
                FROM books
                WHERE author_id = :author_id
                ORDER BY title
            ');
            $stmt->execute(['author_id' => $book['author_id']]);
            $books = $stmt->fetchAll();

            // Add author_id to suggestion data to pre-fill the dropdown
            $suggestionData['author_id'] = $book['author_id'];
        }
    } else if ($suggestionType === 'book' && isset($suggestionData['author_id'])) {
        // For the books, fetch the books by the selected author
        $stmt = $connection->prepare('
            SELECT book_id, title, author_id
            FROM books
            WHERE author_id = :author_id
            ORDER BY title
        ');
        $stmt->execute(['author_id' => $suggestionData['author_id']]);
        $books = $stmt->fetchAll();
    } else {
        $books = $connection->query('SELECT book_id, title, author_id FROM books ORDER BY title')->fetchAll();
    }

    $connection = null;
} catch (Exception $e) {
    $errorMessages[] = "Erreur : " . $e->getMessage();
    $suggestion = null;
    $suggestionType = '';
    $suggestionData = [];
    $authors = [];
    $books = [];
}

$typeLabels = [
    'author' => 'Auteur',
    'book' => 'Livre',
    'chapter' => 'Chapitre'
];
?>

<div class="container">
    <div class="card">
        <h2 class="card-title">Information sur la suggestion n° <?php echo htmlspecialchars($suggestionId); ?></h2>

        <?php if (!empty($errorMessages)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errorMessages as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($suggestion): ?>
            <div class="suggestion-type-info">
                <p>Type de suggestion : <strong><?php echo $typeLabels[$suggestionType] ?? $suggestionType; ?></strong></p>
            </div>
            <?php if ($user['is_admin'] && $suggestion['user_id'] !== $user['id']): ?>
                <div class="suggestion-author-info">
                    <p>Suggestion faite par : <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                </div>
            <?php endif; ?>
            <div>
                <div class="card">
                    <div class="suggestion-status">
                        <span class="badge <?php
                        echo match($suggestion['status']) {
                            'approved' => 'badge-success',
                            'rejected' => 'badge-danger',
                            default => 'badge-warning'
                        };
                        ?>">
                            <?php
                            echo match($suggestion['status']) {
                                'approved' => 'Approuvée',
                                'rejected' => 'Rejetée',
                                default => 'En attente'
                            };
                            ?>
                        </span>
                    </div>
                <?php if ($suggestionType === 'author'): ?>
                    <table class="table mt-4">
                        <tr>
                            <th>Nom de l'auteur</th>
                            <td><?php echo htmlspecialchars($suggestionData['author_name'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Année de naissance</th>
                            <td><?php echo htmlspecialchars($suggestionData['birth_year'] ?? 'Non spécifiée'); ?></td>
                        </tr>
                        <tr>
                            <th>Année de décès</th>
                            <td><?php echo htmlspecialchars($suggestionData['death_year'] ?? 'Non spécifiée'); ?></td>
                        </tr>
                        <tr>
                            <th>Biographie</th>
                            <td><?php echo nl2br(htmlspecialchars($suggestionData['biography'] ?? '')); ?></td>
                        </tr>
                    </table>
                <?php elseif ($suggestionType === 'book'): ?>
                    <table class="table mt-4">
                        <tr>
                            <th>Auteur</th>
                            <td>
                                <?php
                                $authorName = '';
                                foreach ($authors as $author) {
                                    if ($author['author_id'] == ($suggestionData['author_id'] ?? '')) {
                                        $authorName = htmlspecialchars($author['name']);
                                        break;
                                    }
                                }
                                echo $authorName;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Titre du livre</th>
                            <td><?php echo htmlspecialchars($suggestionData['title'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Année de publication</th>
                            <td><?php echo htmlspecialchars($suggestionData['publication_year'] ?? 'Non spécifiée'); ?></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><?php echo nl2br(htmlspecialchars($suggestionData['description'] ?? '')); ?></td>
                        </tr>
                    </table>

                    <div class="form-group">
                        <?php if ($suggestion['status'] === 'pending' && !$user['is_admin']): ?>
                            <a href="/suggestions/edit/<?php echo $suggestionId; ?>" class="btn">Modifier</a>
                        <?php endif; ?>
                        <?php if ($from === 'admin'): ?>
                            <a href="/admin/suggestions" class="btn btn-secondary">Retour à la gestion</a>
                        <?php else: ?>
                            <a href="/suggestions/my/suggestions" class="btn btn-secondary">Retour</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php elseif ($suggestionType === 'chapter'): ?>
                    <table class="table mt-4">
                        <tr>
                            <th>Auteur</th>
                            <td>
                                <?php
                                $authorName = '';
                                foreach ($authors as $author) {
                                    if ($author['author_id'] == ($suggestionData['author_id'] ?? '')) {
                                        $authorName = htmlspecialchars($author['name']);
                                        break;
                                    }
                                }
                                echo $authorName;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Titre du livre</th>
                            <td>
                                <?php
                                $bookTitle = '';
                                foreach ($books as $book) {
                                    if ($book['book_id'] == ($suggestionData['book_id'] ?? '')) {
                                        $bookTitle = htmlspecialchars($book['title']);
                                        break;
                                    }
                                }
                                echo $bookTitle;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Chapitre</th>
                            <td>
                                <?php
                                $chapterNumber = htmlspecialchars($suggestionData['chapter_number'] ?? '');
                                echo $chapterNumber ? "Chapitre n°$chapterNumber" : 'Non spécifié';
                                ?>
                        </tr>
                        <tr>
                            <th>Examen</th>
                            <td>
                                <?php
                                echo htmlspecialchars($suggestion['admin_notes'] ?? 'Cette suggestion n\'a pas encore été examinée, il n\'y a donc pas de notes.');
                                ?>
                            </td>
                        </tr>
                    </table>
                    <!-- Content of the chapter -->
                    <div class="chapter-content">
                        <h3>Contenu du chapitre</h3>
                        <p><?php echo nl2br(htmlspecialchars($suggestionData['content'] ?? '')); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <p>La suggestion demandée n'a pas été trouvée, ou vous n'êtes pas autorisé à la modifier.</p>
                <p><a href="/suggestions" class="btn">Retour à mes suggestions</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bookSelector = document.getElementById('book_id');

        if (bookSelector) {
            bookSelector.addEventListener('change', function() {
                if (!this.value) return;

                fetch(`/api/books?id=${this.value}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur réseau ou serveur');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const chapterCount = data[0].chapter_count || 0;
                        const chapterNumberInput = document.getElementById('chapter_number');
                        chapterNumberInput.setAttribute('placeholder', `1-${chapterCount + 1}`);
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Impossible de récupérer les informations du livre');
                    });
            });
        }

        const authorSelector = document.getElementById('author_id');
        if (authorSelector && bookSelector) {
            authorSelector.addEventListener('change', function() {
                const authorId = this.value;
                fetch(`/api/books?author_id=${authorId}`)
                    .then(response => response.json())
                    .then(data => {
                        bookSelector.innerHTML = '<option value="">Sélectionnez un livre</option>';
                        data.forEach(book => {
                            const option = document.createElement('option');
                            option.value = book.book_id;
                            option.textContent = book.title;
                            bookSelector.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Impossible de récupérer les livres de cet auteur');
                    });
            });
        }

        // Trigger change event on page load to set initial book options
        if (bookSelector && bookSelector.value) {
            const event = new Event('change');
            bookSelector.dispatchEvent(event);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
