<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = isset($_POST['nom']) ? mysqli_real_escape_string($mysqli, $_POST['nom']) : '';
    $prenom = isset($_POST['prenom']) ? mysqli_real_escape_string($mysqli, $_POST['prenom']) : '';
    $username = mysqli_real_escape_string($mysqli, $_POST['username']);
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = $_POST['password'];

    $checkUser = $mysqli->query("SELECT id FROM User WHERE email = '$email' OR username = '$username'");
    
    if ($checkUser->num_rows > 0) {
        $error = "L'email ou le nom d'utilisateur est déjà utilisé.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO `User` (username, email, password, balance, role, nom, prenom) VALUES ('$username', '$email', '$hashedPassword', 0.00, 'user', " . ($nom ? "'$nom'" : "NULL") . ", " . ($prenom ? "'$prenom'" : "NULL") . ")";
        
        if ($mysqli->query($sql)) {
            $_SESSION['user_id'] = $mysqli->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            
            header("Location: " . BASE_URL . "index.php");
            exit();
        } else {
            $error = "Erreur lors de l'inscription.";
        }
    }
}
include BASE_PATH . 'includes/header.php';
?>

<div class="loginPageContainer leftAligned">
    
    <div class="loginBackground">
        <img src="<?= BASE_URL ?>img/background/fondMontagnes.png" alt="Paysage Montagnes Residue">
    </div>

    
    <div class="loginPanel">
        <div class="loginContent">
            <h1 class="loginTitle">INSCRIPTION</h1>
            <p class="loginSubtitle">CRÉER VOTRE COMPTE</p>

            <?php if(!empty($error)): ?>
                <div class="loginErrorMsg">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>auth/register.php" method="POST" class="loginForm">
                
                <div class="loginFormGroup">
                    <input type="text" id="nom" name="nom" placeholder="NOM">
                </div>
                <div class="loginFormGroup">
                    <input type="text" id="prenom" name="prenom" placeholder="PRENOM">
                </div>
                
                
                <div class="loginFormGroup">
                    <input type="text" id="username" name="username" placeholder="PSEUDO" required>
                </div>
                <div class="loginFormGroup">
                    <input type="email" id="email" name="email" placeholder="EMAIL" required>
                </div>
                <div class="loginFormGroup">
                    <input type="password" id="password" name="password" placeholder="MOT DE PASSE" required>
                </div>
                <div class="loginFormGroup">
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="CONFIRMER LE MOT DE PASSE" required>
                </div>
                
                <button type="submit" class="loginSubmitBtn">CRÉER LE COMPTE</button>
            </form>

            <p class="loginRegisterLink">
                VOUS EN AVEZ DÉJÀ UN ? <a href="<?= BASE_URL ?>auth/login.php">CONNECTEZ VOUS !</a>
            </p>
        </div>
    </div>
</div>

<script>
    
    
    function alignGlassPanel() {
        const header = document.getElementById('navBar');
        const panel = document.querySelector('.loginPanel');
        
        if (header && panel) {
            const headerHeight = header.offsetHeight;
            panel.style.marginTop = headerHeight + 'px';
            panel.style.minHeight = `calc(100vh - ${headerHeight}px)`;
            panel.style.height = 'auto'; 
        }
    }

    
    window.addEventListener('DOMContentLoaded', alignGlassPanel);
    window.addEventListener('resize', alignGlassPanel);
    window.addEventListener('load', alignGlassPanel);
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>