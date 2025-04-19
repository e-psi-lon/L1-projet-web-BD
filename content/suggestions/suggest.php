<?php
include 'includes/header.php';

if (!isset($_SESSION['user'])) {
    header('Location: /auth/login');
    exit();
}

$user = $_SESSION['user'];
$successMessage = $_SESSION['success_message'] ?? '';
$errorMessages = $_SESSION['error_messages'] ?? [];

unset($_SESSION['success_message'], $_SESSION['error_messages']);

$suggestionTypes = ['auteur' => 'author', 'livre' => 'book', 'chapitre' => 'chapter'];
$selectedType = $_GET['type'] ?? 'author';

try {
    $connection = getDbConnection();
    $authors = $connection->query('SELECT author_id, name FROM authors ORDER BY name')->fetchAll();
    $books = $connection->query('SELECT book_id, title, author_id FROM books ORDER BY title')->fetchAll();
    $connection = null;
} catch (PDOException $e) {
    $errorMessages[] = "Erreur lors de la récupération des données : " . $e->getMessage();
    $authors = [];
    $books = [];
}
?>

    <div class="container">
        <div class="card">
            <h2 class="card-title">Suggérer du contenu</h2>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMessages)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errorMessages as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="suggestion-type-selector">
                <form class="form-group" action="" method="GET">
                    <label for="type">Choisir le type de suggestion :</label>
                    <div class="suggestion-type-buttons">
                        <?php foreach ($suggestionTypes as $label => $type): ?>
                            <button type="submit" name="type" value="<?php echo $type; ?>"
                                    class="btn <?php echo ($selectedType === $type) ? 'btn-primary' : 'btn-secondary'; ?>">
                                <?php echo ucfirst($label); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>

            <div class="suggestion-form">
                <?php if ($selectedType === 'author'): ?>
                    <form class="form-group" method="POST" action="/api/suggest">
                        <input type="hidden" name="suggestion_type" value="author">

                        <div class="form-group">
                            <label for="author_name">Nom de l'auteur</label>
                            <input type="text" id="author_name" name="author_name" required>
                        </div>

                        <div class="form-group">
                            <label for="birth_year">Année de naissance</label>
                            <input type="number" id="birth_year" name="birth_year" placeholder="Ex: -100 pour 100 av. J.-C.">
                        </div>

                        <div class="form-group">
                            <label for="death_year">Année de décès</label>
                            <input type="number" id="death_year" name="death_year" placeholder="Ex: 44 pour 44 apr. J.-C.">
                        </div>

                        <div class="form-group">
                            <label for="biography">Biographie</label>
                            <textarea id="biography" name="biography" rows="10" placeholder="Biographie de l'auteur..."></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Soumettre</button>
                        </div>
                    </form>
                <?php elseif ($selectedType === 'book'): ?>
                    <form class="form-group" method="POST" action="/api/suggest">
                        <input type="hidden" name="suggestion_type" value="book">

                        <div class="form-group">
                            <label for="author_id">Auteur</label>
                            <select id="author_id" name="author_id" required>
                                <option value="">Sélectionnez un auteur</option>
                                <?php foreach ($authors as $author): ?>
                                    <option value="<?php echo $author['author_id']; ?>">
                                        <?php echo htmlspecialchars($author['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="title">Titre du livre</label>
                            <input type="text" id="title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="publication_year">Année de publication</label>
                            <input type="number" id="publication_year" name="publication_year" placeholder="Ex: -44 pour 44 av. J.-C.">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="8" placeholder="Description du livre..."></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Soumettre</button>
                        </div>
                    </form>
                <?php elseif ($selectedType === 'chapter'): ?>
                    <form class="form-group" method="POST" action="/api/suggest">
                        <input type="hidden" name="suggestion_type" value="chapter">
                        <div class="form-group">
                            <label for="author_id">Auteur</label>
                            <select id="author_id" name="author_id" required>
                                <option value="">Sélectionnez un auteur</option>
                                <?php foreach ($authors as $author): ?>
                                    <option value="<?php echo $author['author_id']; ?>">
                                        <?php echo htmlspecialchars($author['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="book_id">Livre</label>
                            <select id="book_id" name="book_id" required>
                                <option value="">Sélectionnez un livre</option>

                            </select>
                        </div>

                        <div class="form-group">
                            <label for="chapter_number">Numéro du chapitre</label>
                            <input type="number" id="chapter_number" name="chapter_number" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="title">Titre du chapitre</label>
                            <input type="text" id="title" name="title">
                        </div>

                        <div class="form-group">
                            <label for="content">Contenu du chapitre</label>
                            <textarea id="content" name="content" rows="15" required placeholder="Texte du chapitre..."></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Soumettre</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
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
                            chapterNumberInput.value = chapterCount + 1;
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
        });
    </script>

<?php include 'includes/footer.php'; ?>
