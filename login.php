<?php
require_once 'includes/db.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = $_POST['password'];

    $result = $mysqli->query("SELECT id, username, password, role FROM User WHERE email = '$email'");
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Aucun compte trouvé avec cet email.";
    }
}
include 'includes/header.php';
?>

<div class="contactContainer">
    <h1 class="titleFormular">Connexion</h1>
    <p class="subtitleFormular">Accédez à votre compte RESIDUE_</p>

    <?php if(!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="formularContainer">
        <fieldset class="contactFieldset">
            <div class="formGroup">
                <label for="email">Adresse E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="formGroup">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
        </fieldset>
        
        <button type="submit" class="sendBtnFormular">Se connecter</button>
    </form>
    <p style="margin-top: 1rem;">Pas de compte ? <a href="register.php" style="font-weight: bold; text-decoration: underline;">S'inscrire</a></p>
</div>

<?php include 'includes/footer.php'; ?>
