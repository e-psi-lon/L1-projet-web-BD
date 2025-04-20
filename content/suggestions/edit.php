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
$successMessage = $_SESSION['success_message'] ?? '';
$errorMessages = $_SESSION['error_messages'] ?? [];

unset($_SESSION['success_message'], $_SESSION['error_messages']);

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
    
    if ($suggestion['user_id'] !== $user['id']) {
        // It's not the user's suggestion, they don't have access to it
        header('Location: /suggestions/my/suggestions');
    }
    
    $suggestionType = $suggestion['suggestion_type'];
    
    // Récupérer les données spécifiques selon le type de suggestion
    // Récupérer les données spécifiques selon le type de suggestion
    $stmt = null;
    if ($suggestionType === 'author') {
        $stmt = $connection->prepare('
        SELECT author_name, birth_year, death_year, biography
        FROM author_suggestions
        WHERE suggestion_id = :id
    ');
    } elseif ($suggestionType === 'book') {
        $stmt = $connection->prepare('
        SELECT author_id, title, publication_year, description
        FROM book_suggestions
        WHERE suggestion_id = :id
    ');
    } elseif ($suggestionType === 'chapter') {
        $stmt = $connection->prepare('
        SELECT book_id, title, chapter_number, content
        FROM chapter_suggestions
        WHERE suggestion_id = :id
    ');
    } else {
        throw new Exception("Type de suggestion non reconnu");
    }
    
    $stmt->execute(['id' => $suggestionId]);
    $suggestionData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$suggestionData) {
        throw new Exception("Données de suggestion introuvables");
    }
    
    // Fetch the authors to show them in the form
    $authors = $connection->query('SELECT author_id, name FROM authors ORDER BY name')->fetchAll();
    $books = [];
    
    if ($suggestionType === 'chapter' && isset($suggestionData['book_id'])) {
        // Pour les chapitres, récupérer également le author_id à partir du book_id
        $stmt = $connection->prepare('
            SELECT b.book_id, b.title, b.author_id
            FROM books b
            WHERE b.book_id = :book_id
        ');
        $stmt->execute(['book_id' => $suggestionData['book_id']]);
        $book = $stmt->fetch();
        
        if ($book) {
            // Récupérer tous les livres de cet auteur
            $stmt = $connection->prepare('
                SELECT book_id, title, author_id
                FROM books
                WHERE author_id = :author_id
                ORDER BY title
            ');
            $stmt->execute(['author_id' => $book['author_id']]);
            $books = $stmt->fetchAll();
            
            // Ajouter author_id aux données de suggestion pour préremplir la liste déroulante
            $suggestionData['author_id'] = $book['author_id'];
        }
    } else if ($suggestionType === 'book' && isset($suggestionData['author_id'])) {
        // Pour les livres, récupérer les livres de l'auteur sélectionné
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
        <h2 class="card-title">Modifier une suggestion</h2>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?php echo h($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessages)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errorMessages as $error): ?>
                        <li><?php echo h($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($suggestion): ?>
            <div class="suggestion-type-info">
                <p>Type de suggestion : <strong><?php echo $typeLabels[$suggestionType] ?? $suggestionType; ?></strong></p>
            </div>

            <div class="suggestion-form">
                <?php if ($suggestionType === 'author'): ?>
                    <form class="form-group" method="POST" action="/api/suggestions/edit">
                        <input type="hidden" name="suggestion_id" value="<?php echo $suggestionId; ?>">
                        <input type="hidden" name="suggestion_type" value="author">

                        <div class="form-group">
                            <label for="author_name">Nom de l'auteur</label>
                            <input type="text" id="author_name" name="author_name" value="<?php echo h($suggestionData['author_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="birth_year">Année de naissance</label>
                            <input type="number" id="birth_year" name="birth_year" value="<?php echo h($suggestionData['birth_year'] ?? ''); ?>" placeholder="Ex: -100 pour 100 av. J.-C.">
                        </div>

                        <div class="form-group">
                            <label for="death_year">Année de décès</label>
                            <input type="number" id="death_year" name="death_year" value="<?php echo h($suggestionData['death_year'] ?? ''); ?>" placeholder="Ex: 44 pour 44 apr. J.-C.">
                        </div>

                        <div class="form-group">
                            <label for="biography">Biographie</label>
                            <textarea id="biography" name="biography" rows="10" placeholder="Biographie de l'auteur..."><?php echo h($suggestionData['biography'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Mettre à jour</button>
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='/suggestions/my/suggestions'">Annuler</button>
                        </div>
                    </form>
                <?php elseif ($suggestionType === 'book'): ?>
                    <form class="form-group" method="POST" action="/api/suggestions/edit">
                        <input type="hidden" name="suggestion_id" value="<?php echo $suggestionId; ?>">
                        <input type="hidden" name="suggestion_type" value="book">

                        <div class="form-group">
                            <label for="author_id">Auteur</label>
                            <select id="author_id" name="author_id" required>
                                <option value="">Sélectionnez un auteur</option>
                                <?php foreach ($authors as $author): ?>
                                    <option value="<?php echo $author['author_id']; ?>" <?php echo ($suggestionData['author_id'] ?? '') == $author['author_id'] ? 'selected' : ''; ?>>
                                        <?php echo h($author['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="title">Titre du livre</label>
                            <input type="text" id="title" name="title" value="<?php echo h($suggestionData['title'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="publication_year">Année de publication</label>
                            <input type="number" id="publication_year" name="publication_year" value="<?php echo h($suggestionData['publication_year'] ?? ''); ?>" placeholder="Ex: -44 pour 44 av. J.-C.">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="8" placeholder="Description du livre..."><?php echo h($suggestionData['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Mettre à jour</button>
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='/suggestions/my/suggestions'">Annuler</button>
                        </div>
                    </form>
                <?php elseif ($suggestionType === 'chapter'): ?>
                    <form class="form-group" method="POST" action="/api/suggestions/edit">
                        <input type="hidden" name="suggestion_id" value="<?php echo $suggestionId; ?>">
                        <input type="hidden" name="suggestion_type" value="chapter">
                        
                        <div class="form-group">
                            <label for="author_id">Auteur</label>
                            <select id="author_id" name="author_id" required>
                                <option value="">Sélectionnez un auteur</option>
                                <?php foreach ($authors as $author): ?>
                                    <option value="<?php echo $author['author_id']; ?>" <?php echo ($suggestionData['author_id'] ?? '') == $author['author_id'] ? 'selected' : ''; ?>>
                                        <?php echo h($author['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="book_id">Livre</label>
                            <select id="book_id" name="book_id" required>
                                <option value="">Sélectionnez un livre</option>
                                <?php foreach ($books as $book): ?>
                                    <option value="<?php echo $book['book_id']; ?>" <?php echo ($suggestionData['book_id'] ?? '') == $book['book_id'] ? 'selected' : ''; ?>>
                                        <?php echo h($book['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="chapter_number">Numéro du chapitre</label>
                            <input type="number" id="chapter_number" name="chapter_number" min="1" value="<?php echo h($suggestionData['chapter_number'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="title">Titre du chapitre</label>
                            <input type="text" id="title" name="title" value="<?php echo h($suggestionData['title'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="content">Contenu du chapitre</label>
                            <textarea id="content" name="content" rows="15" required placeholder="Texte du chapitre..."><?php echo h($suggestionData['content'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Mettre à jour</button>
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='/suggestions/my/suggestions'">Annuler</button>
                        </div>
                    </form>
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
