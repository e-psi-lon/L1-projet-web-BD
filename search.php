<?php include 'includes/header.php';?>

    <div class="container">
        <h1>Recherche parmi les textes</h1>

        <div class="search-box">
            <label for="book-search"></label>
            <input type="text" id="book-search" placeholder="Rechercher des textes..." onkeyup="filterBook()">
        </div>

        <div class="book-list">
            <?php

            ?>
        </div>
    </div>

    <script>
        function filterBook() {
        }
    </script>

<?php include 'includes/footer.php'; ?>