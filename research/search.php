<?php include 'includes/header.php';?>

    <div class="container">
        <h1>Recherche parmi les textes</h1>

        <div class="search-container">
            <form method="GET" action="search" class="search-form">
                <div class="search-box">
                    <label for="search-input"></label>
                    <input type="text" id="search-input" name="q" placeholder="Rechercher des textes..." value="<?php echo isset($_GET['q']) ? h($_GET['q']) : ''; ?>">
                    <button type="submit" class="btn">
                        <i data-lucide="search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="search-results" id="search-results">
            <?php
            // If a request was made
            if (!empty($_GET['q'])) {
                $search = $_GET['q'];
                $db = getDbConnection();

                // Request to find matches
                $query = "
                SELECT 
                    books.title AS book_title,
                    books.url_title AS url_title,
                    chapters.title AS chapter_title,
                    chapters.chapter_number,
                    authors.name AS author_name,
                    authors.url_name as url_name,
                    chapters.content,
                    CASE 
                        WHEN books.title LIKE :search THEN 3
                        WHEN chapters.title LIKE :search THEN 2
                        ELSE 1
                    END AS relevance
                FROM chapters
                JOIN books ON chapters.book_id = books.book_id
                JOIN authors ON books.author_id = authors.author_id
                WHERE 
                    books.title LIKE :search
                    OR chapters.title LIKE :search
                    OR chapters.content LIKE :search
                ORDER BY relevance DESC, book_title ASC, chapter_number ASC
                LIMIT 50";

                $stmt = $db->prepare($query);
                $searchParam = '%' . $search . '%';
                $stmt->bindParam(':search', $searchParam);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($results) > 0) {
                    echo '<h2>Résultats de recherche pour "' . h($search) . '"</h2>';

                    $currentBook = '';
                    $currentAuthor = '';

                    foreach ($results as $result) {
                        if ($currentBook != $result['book_title'] || $currentAuthor != $result['author_name']) {
                            // Start of a new book
                            if ($currentBook != '') {
                                echo '</div>'; // End the chapter container
                                echo '</div>'; // End the book card
                            }

                            $currentBook = $result['book_title'];
                            $currentAuthor = $result['author_name'];

                            echo '<div class="search-result-book card">';
                            echo '<div class="book-header">';
                            echo '<h3><a href="'. getBookUrl($result['url_name'], $result['url_title']) . '">' . h($currentBook) . '</a></h3>';
                            echo '<p>par <a href="'. getAuthorUrl($result['url_name']) . '">' . h($currentAuthor) . '</a></p>';
                            echo '</div>';
                            echo '<div class="book-chapters">';
                        }

                        // Show all found chapters
                        $chapterTitle = $result['chapter_title'] ? h($result['chapter_title']) : 'Chapitre ' . $result['chapter_number'];
                        $previewText = truncateText(strip_tags($result['content']), 150, $search);

                        echo '<div class="search-result-chapter">';
                        echo '<h4><a href="'.getChapterUrl($result['url_name'], $result['url_title'], $result['chapter_number']).'">'.$chapterTitle.'</a></h4>';
                        echo '<div class="chapter-preview">' . $previewText . '</div>';
                        echo '</div>';
                    }

                    if ($currentBook != '') {
                        echo '</div>'; // End the chapter container
                        echo '</div>'; // End the book card
                    }
                } else {
                    echo '<div class="alert alert-danger">';
                    echo '<h2>Aucun résultat pour "' . h($search) . '"</h2>';
                    echo '<p>Essayez d\'utiliser des termes de recherche différents ou plus généraux.</p>';
                    echo '</div>';
                }

            }
            $stmt = null;
            $db = null;
            ?>
        </div>
    </div>


<?php include 'includes/footer.php'; ?>