<?php
require_once 'includes/db.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($mysqli, $_POST['username']);
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = $_POST['password'];

    $checkUser = $mysqli->query("SELECT id FROM User WHERE email = '$email' OR username = '$username'");
    
    if ($checkUser->num_rows > 0) {
        $error = "L'email ou le nom d'utilisateur est déjà utilisé.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO `User` (username, email, password, balance, role) VALUES ('$username', '$email', '$hashedPassword', 0.00, 'user')";
        
        if ($mysqli->query($sql)) {
            $_SESSION['user_id'] = $mysqli->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Erreur lors de l'inscription.";
        }
    }
}
include 'includes/header.php';
?>

<div class="contactContainer">
    <h1 class="titleFormular">Créer un compte</h1>
    <p class="subtitleFormular">Rejoignez-nous pour accéder à nos dernières collections et gérer vos commandes.</p>

    <?php if(!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST" class="formularContainer">
        <fieldset class="contactFieldset">
            <legend>Vos informations</legend>
            <div class="formGroup">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="formGroup">
                <label for="email">Adresse E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="formGroup">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
        </fieldset>
        
        <button type="submit" class="sendBtnFormular">S'inscrire</button>
    </form>
    <p style="margin-top: 1rem;">Déjà un compte ? <a href="login.php" style="font-weight: bold; text-decoration: underline;">Se connecter</a></p>
</div>

<?php include 'includes/footer.php'; ?>