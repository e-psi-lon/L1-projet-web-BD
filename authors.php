<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>Auteurs</h1>
    
    <div class="search-box">
        <label for="author-search"></label>
        <input type="text" id="author-search" placeholder="Rechercher des auteurs..." onkeyup="filterAuthors()">
    </div>
    
    <div class="author-list">
        <?php
        // Get database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Query all authors
        $query = "SELECT * FROM authors ORDER BY name";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="author-card">';
                echo '<h3><a href="/authors/' . urlencode($row['url_name'] ?? $row['name']) . '">' . htmlspecialchars($row['name']) . '</a></h3>';
                echo '<p>(' . ($row['birth_year'] ?: '?') . ' - ' . ($row['death_year'] ?: '?') . ')</p>';
                echo '<p>' . (strlen($row['biography']) > 150 ? substr(htmlspecialchars($row['biography']), 0, 150) . '...' : htmlspecialchars($row['biography'])) . '</p>';
                echo '<a href="/authors/' . urlencode($row['url_name'] ?? $row['name']) . '" class="btn btn-secondary">Voir les œuvres</a>';
                echo '</div>';
            }
        } else {
            echo '<p>Aucun auteur trouvé dans la base de données.</p>';
        }
        ?>
    </div>
</div>

<script>
function filterAuthors() {
    const searchTerm = document.getElementById('author-search').value.toLowerCase();
    const authors = document.querySelectorAll('.author-card');
    
    authors.forEach(author => {
        const authorName = author.querySelector('h3').textContent.toLowerCase();
        if (authorName.includes(searchTerm)) {
            author.style.display = '';
        } else {
            author.style.display = 'none';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>