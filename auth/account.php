<?php 
include 'includes/header.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user->update($_POST);
    $connection = getDbConnection();
    $stmt = $connection->prepare('UPDATE users SET username = :username, email = :email, password = :password WHERE id = :id');
    $stmt->execute([
        'username' => $user->username,
        'email' => $user->email,
        'password' => $user->password,
        'id' => $user->id
    ]);
    header('Location: account.php');
    exit();
}
?>
    <div class="container">
        <div class="card">
            <h2 class="card-title">Mon compte</h2>
            <div class="form-group" id="accountForm">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user->username); ?>" disabled>
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" disabled>
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($user->password); ?>" disabled>
                
                <button id="editButton">Modifier</button>
                <button id="saveButton" style="display: none;" type="submit">Enregistrer</button>
                <button id="cancelButton" style="display: none;">Annuler</button>
            </div>
        </div>
    </div>
<?php
include 'includes/footer.php';
?>