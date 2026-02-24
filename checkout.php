<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// 1. Calcul du total du panier
$sqlCart = "SELECT SUM(article.price * cart.quantity) as total 
            FROM cart JOIN article ON cart.article_id = article.id 
            WHERE cart.user_id = $user_id";
$resCart = $mysqli->query($sqlCart);
$total = 0;
if ($resCart && $resCart->num_rows > 0) {
    $row = $resCart->fetch_assoc();
    $total = floatval($row['total']);
}

if ($total == 0) {
    header("Location: cart.php");
    exit();
}

// 2. Traitement du paiement
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = mysqli_real_escape_string($mysqli, $_POST['address']);
    $city = mysqli_real_escape_string($mysqli, $_POST['city']);
    $zipcode = mysqli_real_escape_string($mysqli, $_POST['zipcode']);

    // Vérifier le solde de l'utilisateur
    $sqlUser = "SELECT balance FROM user WHERE id = $user_id";
    $resUser = $mysqli->query($sqlUser);
    $userRow = $resUser->fetch_assoc();
    $balance = floatval($userRow['balance']);

    if ($balance >= $total) {
        $mysqli->begin_transaction();
        try {
            // Déduire le solde
            $newBalance = $balance - $total;
            $mysqli->query("UPDATE user SET balance = $newBalance WHERE id = $user_id");

            // Créer la facture
            $stmt = $mysqli->prepare("INSERT INTO invoice (user_id, amount, billing_address, billing_city, billing_zipcode) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("idsss", $user_id, $total, $address, $city, $zipcode);
            $stmt->execute();

            $invoice_id = $mysqli->insert_id; // On récupère l'ID de la facture nouvellement créée

            // Ajouter les articles de la facture dans la table invoice_item
            $sqlCartItems = "SELECT cart.article_id, cart.quantity, article.price FROM cart JOIN article ON cart.article_id = article.id WHERE cart.user_id = $user_id";
            $resCartItems = $mysqli->query($sqlCartItems);
            if ($resCartItems && $resCartItems->num_rows > 0) {
                $stmtItem = $mysqli->prepare("INSERT INTO invoice_item (invoice_id, article_id, quantity, price) VALUES (?, ?, ?, ?)");
                while ($cItem = $resCartItems->fetch_assoc()) {
                    $stmtItem->bind_param("iiid", $invoice_id, $cItem['article_id'], $cItem['quantity'], $cItem['price']);
                    $stmtItem->execute();
                }
            }

            // Vider le panier
            $mysqli->query("DELETE FROM cart WHERE user_id = $user_id");

            $mysqli->commit();
            $success = "Paiement réussi ! Votre commande a été validée.";
            $total = 0; // Le panier est maintenant vide
        } catch (Exception $e) {
            $mysqli->rollback();
            $error = "Erreur lors du traitement du paiement.";
        }
    } else {
        $error = "Solde insuffisant. Veuillez recharger votre compte.";
    }
}

include 'includes/header.php';
?>

<div class="contactContainer" style="max-width: 800px;">
    <h1 class="titleFormular">Checkout</h1>

    <?php if (!empty($error)): ?>
        <div
            style="color: #dc2626; margin-bottom: 1rem; font-weight: bold; background: #fee2e2; padding: 1rem; border: 1px solid #f87171;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div
            style="color: #16a34a; margin-bottom: 1rem; font-weight: bold; background: #dcfce7; padding: 1rem; border: 1px solid #4ade80;">
            <?php echo htmlspecialchars($success); ?>
            <br><br>
            <a href="index.php" style="text-decoration: underline; color: #1c1917;">Retour à l'accueil</a>
        </div>
    <?php else: ?>
        <div style="border: 1px solid #e7e5e4; padding: 2rem; margin-bottom: 2rem;">
            <h2 style="text-transform: uppercase; font-size: 1.2rem; margin-bottom: 1rem;">Récapitulatif de la commande</h2>
            <p style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">Total à payer :
                <?php echo number_format($total, 2, ',', ' '); ?> €</p>
        </div>

        <form action="checkout.php" method="POST" class="formularContainer">
            <fieldset class="contactFieldset">
                <legend>Adresse de facturation</legend>
                <div class="formGroup">
                    <label for="address">Adresse</label>
                    <input type="text" id="address" name="address" required>
                </div>
                <div class="formGroup">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" required>
                </div>
                <div class="formGroup">
                    <label for="zipcode">Code Postal</label>
                    <input type="text" id="zipcode" name="zipcode" required>
                </div>
            </fieldset>

            <button type="submit" class="sendBtnFormular">Confirmer et Payer</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>