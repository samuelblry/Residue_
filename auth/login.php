<?php
require_once __DIR__ . '/../includes/db.php';
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
            
            header("Location: " . BASE_URL . "index.php");
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Aucun compte trouvé avec cet email.";
    }
}
include BASE_PATH . 'includes/header.php';
?>
<div class="loginPageContainer">
    <!-- Image de fond -->
    <div class="loginBackground">
        <img src="<?= BASE_URL ?>img/background/fondMontagnes.png" alt="Paysage Montagnes Residue">
    </div>

    <!-- Panneau vitré à droite -->
    <div class="loginPanel">
        <div class="loginContent">
            <h1 class="loginTitle">CONNECTION</h1>
            <p class="loginSubtitle">ENTREZ VOS INFORMATIONS</p>

            <?php if(!empty($error)): ?>
                <div class="loginErrorMsg">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>auth/login.php" method="POST" class="loginForm">
                <div class="loginFormGroup">
                    <input type="email" id="email" name="email" placeholder="EMAIL" required>
                </div>
                <div class="loginFormGroup">
                    <input type="password" id="password" name="password" placeholder="MOT DE PASSE" required>
                </div>

                <div class="loginForgotPass">
                    <a href="#">MOT DE PASSE OUBLIÉ ?</a>
                </div>
                
                <button type="submit" class="loginSubmitBtn">SE CONNECTER</button>
            </form>

            <p class="loginRegisterLink">
                PAS DE COMPTE ? <a href="<?= BASE_URL ?>auth/register.php">CRÉER EN UN MAINTENANT !</a>
            </p>
        </div>
    </div>
</div>

<script>
    // Permet de calculer dynamiquement la hauteur exacte du header pour que
    // la carte en verre s'arrête obligatoirement ET parfaitement en dessous
    function alignGlassPanel() {
        const header = document.getElementById('navBar');
        const panel = document.querySelector('.loginPanel');
        
        if (header && panel) {
            const headerHeight = header.offsetHeight;
            panel.style.marginTop = headerHeight + 'px';
            panel.style.minHeight = `calc(100vh - ${headerHeight}px)`;
            panel.style.height = 'auto'; // Permet de grandir
        }
    }

    // Exécuter au chargement et si la taille de la fenêtre change
    window.addEventListener('DOMContentLoaded', alignGlassPanel);
    window.addEventListener('resize', alignGlassPanel);
    // Petit fallback au cas où l'image ou la typo charge avec du retard
    window.addEventListener('load', alignGlassPanel);
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>
