<?php
include 'includes/header.php';

if (!isset($_SESSION['user'])) {
    header('Location: auth/login');
    exit();
}

$user = $_SESSION['user'];
$updateSuccess = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation basique
    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur ne peut pas être vide";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide";
    }

    if (!empty($password) && $password !== $_POST['confirm-password']) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }

    if (empty($errors)) {
        try {
            $connection = getDbConnection();

            if (!empty($password)) {
                $stmt = $connection->prepare('UPDATE users SET username = :username, email = :email, password = :password WHERE user_id = :id');
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'id' => $user['user_id']
                ]);
            } else {
                $stmt = $connection->prepare('UPDATE users SET username = :username, email = :email WHERE user_id = :id');
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'id' => $user['user_id']
                ]);
            }

            // Mise à jour des données dans la session
            $user['username'] = $username;
            $user['email'] = $email;
            $_SESSION['user'] = $user;

            $updateSuccess = true;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : veuillez réessayer";
        }
    }
}
?>
    <div class="container">
        <script type="module" src="/assets/js/account.js"></script>
        <div class="card">
            <h2 class="card-title">Mon compte</h2>

            <?php if ($updateSuccess): ?>
                <div class="alert alert-success">Votre compte a été mis à jour avec succès.</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="form-group" id="accountForm" method="POST" action="account">
                <div>
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>
                <div>
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                </div>
                <div>
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" value="" readonly>
                </div>
                <div style="display: none;" id="password-confirm-field">
                    <label for="confirm-password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm-password" name="confirm-password" value="" readonly>
                </div>
                <div style="margin-top: 1rem;"></div>
                <button id="editButton" onclick="editAccount()" type="button">Modifier</button>
                <button id="saveButton" style="display: none;" type="submit">Enregistrer</button>
                <button id="cancelButton" style="display: none;" type="button">Annuler</button>
            </form>
        </div>
    </div>
<?php
include 'includes/footer.php';
?>