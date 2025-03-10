<?php
include 'includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $errors[] = "Veuillez saisir à la fois votre nom d'utilisateur/email et votre mot de passe";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if input is email or username
        $query = "SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                // Store user data in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = (bool)$user['is_admin'];
                
                // Redirect to homepage
                header("Location: /index");
                exit();
            } else {
                $errors[] = "Mot de passe invalide";
            }
        } else {
            $errors[] = "Nom d'utilisateur ou email non trouvé";
        }
    }
}
?>

<div class="container">
    <div class="card">
        <h2 class="card-title">Connexion</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Nom d'utilisateur ou Email</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit">Se connecter</button>
            </div>
        </form>
        
        <p>Vous n'avez pas de compte ? <a href="/auth/register">Inscrivez-vous</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>