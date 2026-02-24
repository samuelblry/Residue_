<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Traitement des formulaires
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_info':
                $username = mysqli_real_escape_string($mysqli, $_POST['username']);
                $email = mysqli_real_escape_string($mysqli, $_POST['email']);
                // Vérifier si l'email ou username n'est pas déjà pris par un AUTRE utilisateur
                $check = $mysqli->query("SELECT id FROM user WHERE (email='$email' OR username='$username') AND id != $user_id");
                if ($check->num_rows > 0) {
                    $error = "Cet email ou nom d'utilisateur est déjà utilisé.";
                } else {
                    $mysqli->query("UPDATE user SET username='$username', email='$email' WHERE id=$user_id");
                    $_SESSION['username'] = $username;
                    $success = "Informations mises à jour.";
                }
                break;
            
            case 'add_balance':
                $amount = floatval($_POST['amount']);
                if ($amount > 0) {
                    $mysqli->query("UPDATE user SET balance = balance + $amount WHERE id=$user_id");
                    $success = "Solde rechargé de " . number_format($amount, 2) . " €.";
                }
                break;

            case 'delete_account':
                $mysqli->query("DELETE FROM user WHERE id=$user_id");
                session_destroy();
                header("Location: index.php");
                exit();
                break;
        }
    }
}

// Récupérer les infos actuelles de l'utilisateur
$resUser = $mysqli->query("SELECT * FROM user WHERE id=$user_id");
$user = $resUser->fetch_assoc();

// Récupérer les articles mis en vente par l'utilisateur (s'il en a)
$articlesVendus = [];
$resArticles = $mysqli->query("SELECT id, name, price FROM article WHERE author_id=$user_id");
if ($resArticles && $resArticles->num_rows > 0) {
    while($row = $resArticles->fetch_assoc()) {
        $articlesVendus[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="contactContainer" style="max-width: 800px;">
    <h1 class="titleFormular">Mon Compte</h1>

    <?php if(!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div style="color: #16a34a; margin-bottom: 1rem; font-weight: bold;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding: 1.5rem; border: 1px solid #e7e5e4;">
        <div>
            <h2 style="font-size: 1.2rem; text-transform: uppercase;">Solde Actuel</h2>
            <p style="font-size: 2rem; font-weight: bold; color: #1c1917;"><?php echo number_format($user['balance'], 2, ',', ' '); ?> €</p>
        </div>
        <form action="account.php" method="POST" style="display: flex; gap: 1rem;">
            <input type="hidden" name="action" value="add_balance">
            <input type="number" name="amount" value="50" min="1" step="1" style="width: 80px; padding: 0.5rem; text-align: center;">
            <button type="submit" class="sendBtnFormular" style="margin-top: 0; padding: 0.5rem 1rem;">Recharger</button>
        </form>
    </div>

    <form action="account.php" method="POST" class="formularContainer" style="margin-bottom: 3rem;">
        <input type="hidden" name="action" value="update_info">
        <fieldset class="contactFieldset">
            <legend>Mes Informations (Mettre à jour)</legend>
            <div class="formGroup">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="formGroup">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
        </fieldset>
        <button type="submit" class="sendBtnFormular">Mettre à jour</button>
    </form>

    <div style="margin-bottom: 3rem;">
        <h2 style="font-size: 1.2rem; text-transform: uppercase; margin-bottom: 1rem; border-bottom: 2px solid #e7e5e4; padding-bottom: 0.5rem;">Mes Articles en Vente</h2>
        <a href="sell.php" style="display: inline-block; margin-bottom: 1rem; text-decoration: underline; font-weight: bold;">+ Mettre un article en vente</a>
        
        <?php if(!empty($articlesVendus)): ?>
            <ul style="list-style: none; padding: 0;">
                <?php foreach($articlesVendus as $art): ?>
                    <li style="display: flex; justify-content: space-between; padding: 1rem; border: 1px solid #e7e5e4; margin-bottom: 0.5rem;">
                        <span><strong><?php echo htmlspecialchars($art['name']); ?></strong> (<?php echo number_format($art['price'], 2); ?> €)</span>
                        <form action="editArticle.php" method="POST">
                            <input type="hidden" name="article_id" value="<?php echo $art['id']; ?>">
                            <button type="submit" style="text-decoration: underline; font-weight: bold;">Modifier</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Vous n'avez aucun article en vente.</p>
        <?php endif; ?>
    </div>

    <div style="border-top: 2px solid #e7e5e4; padding-top: 2rem;">
        <form action="account.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte définitivement ?');">
            <input type="hidden" name="action" value="delete_account">
            <button type="submit" style="color: #dc2626; text-decoration: underline; font-weight: bold;">Supprimer mon compte</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
