<?php include 'includes/header.php'; ?>
    <div class="container">
        <div class="card">
            <h1 class="card-title">Une erreur s'est produite.</h1>
            <p>Nous sommes désolés, une erreur s'est produite lors de votre demande.</p>
            <a href="/" class="btn">Retourner à l'accueil</a>
            <?php
            if (!empty($e)) {
                echo "<pre class='alert alert-danger'>";
                echo $e->getMessage();
                echo "</pre>";
            }
            ?>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>