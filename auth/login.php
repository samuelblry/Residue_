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
    
    <div class="loginBackground">
        <img src="<?= BASE_URL ?>img/background/fondMontagnes.png" alt="Paysage Montagnes Residue">
    </div>

    
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
                    <a href="#" id="forgotPasswordTrigger">MOT DE PASSE OUBLIÉ ?</a>
                </div>
                
                <button type="submit" class="loginSubmitBtn">SE CONNECTER</button>
            </form>

            <p class="loginRegisterLink">
                PAS DE COMPTE ? <a href="<?= BASE_URL ?>auth/register.php">CRÉER EN UN MAINTENANT !</a>
            </p>
        </div>
    </div>
</div>


<div id="forgotPasswordModal" class="forgotPasswordModal">
    <div class="forgotPasswordContent">
        <span id="forgotPasswordClose" class="forgotPasswordClose">&times;</span>
        <h2 class="forgotPasswordTitle">MAINTENANCE</h2>
        <p class="forgotPasswordText">LA FONCTIONNALITÉ DE RÉINITIALISATION DE MOT DE PASSE EST ACTUELLEMENT EN MAINTENANCE.<br><br>VEUILLEZ NOUS EXCUSER POUR LA GÊNE OCCASIONNÉE.</p>
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

    
    const forgotModal = document.getElementById('forgotPasswordModal');
    const forgotTrigger = document.getElementById('forgotPasswordTrigger');
    const forgotClose = document.getElementById('forgotPasswordClose');

    if (forgotTrigger && forgotModal) {
        forgotTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            forgotModal.style.display = 'flex';
        });
    }

    if (forgotClose && forgotModal) {
        forgotClose.addEventListener('click', function() {
            forgotModal.style.display = 'none';
        });
    }

    
    window.addEventListener('click', function(e) {
        if (e.target == forgotModal) {
            forgotModal.style.display = 'none';
        }
    });
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>
