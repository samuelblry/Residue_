<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// 1. Calcul du total du panier et récupération des articles
$sqlCart = "SELECT cart.id as cart_id, cart.quantity, cart.size, article.id as article_id, article.name, article.price, 
                   (SELECT url FROM image WHERE article_id = article.id AND is_main = 1 LIMIT 1) as image_url 
            FROM cart 
            JOIN article ON cart.article_id = article.id 
            WHERE cart.user_id = $user_id";
$resCart = $mysqli->query($sqlCart);
$cartItems = [];
$totalCart = 0;
if ($resCart && $resCart->num_rows > 0) {
    while ($row = $resCart->fetch_assoc()) {
        $cartItems[] = $row;
        $totalCart += $row['price'] * $row['quantity'];
    }
}

if ($totalCart == 0 && empty($success)) {
    header("Location: " . BASE_URL . "shop/cart.php");
    exit();
}

// Frais de livraison (depuis la session, ou défaut)
$delivery_fee = isset($_SESSION['delivery_fee']) ? $_SESSION['delivery_fee'] : 5.99;
$total = $totalCart + $delivery_fee;

// Récupérer le solde de l'utilisateur
$sqlUser = "SELECT balance FROM user WHERE id = $user_id";
$resUser = $mysqli->query($sqlUser);
$userRow = $resUser->fetch_assoc();
$balance = floatval($userRow['balance']);
$newBalance = $balance - $total;

// 2. Traitement du paiement
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Delivery info (not saved in DB for this simple version, but could be)
    $del_prenom = mysqli_real_escape_string($mysqli, $_POST['del_prenom'] ?? '');
    $del_nom = mysqli_real_escape_string($mysqli, $_POST['del_nom'] ?? '');
    $del_address = mysqli_real_escape_string($mysqli, $_POST['del_address'] ?? '');
    $del_zipcode = mysqli_real_escape_string($mysqli, $_POST['del_zipcode'] ?? '');
    $del_city = mysqli_real_escape_string($mysqli, $_POST['del_city'] ?? '');
    
    // Billing info (used for invoice)
    $address = mysqli_real_escape_string($mysqli, $_POST['bill_address'] ?? $_POST['address'] ?? '');
    $city = mysqli_real_escape_string($mysqli, $_POST['bill_city'] ?? $_POST['city'] ?? '');
    $zipcode = mysqli_real_escape_string($mysqli, $_POST['bill_zipcode'] ?? $_POST['zipcode'] ?? '');

    if ($balance >= $total) {
        $mysqli->begin_transaction();
        try {
            // Récupération complète des champs pour la BDD
            $bill_prenom = mysqli_real_escape_string($mysqli, $_POST['bill_prenom'] ?? '');
            $bill_nom = mysqli_real_escape_string($mysqli, $_POST['bill_nom'] ?? '');
            $bill_country = mysqli_real_escape_string($mysqli, $_POST['bill_country'] ?? '');
            
            $del_country = mysqli_real_escape_string($mysqli, $_POST['del_country'] ?? '');
            $del_instructions = mysqli_real_escape_string($mysqli, $_POST['del_instructions'] ?? '');

            // Créer la facture avec toutes les nouvelles colonnes
            $stmt = $mysqli->prepare("INSERT INTO invoice 
                (user_id, amount, billing_firstname, billing_lastname, billing_address, billing_city, billing_zipcode, billing_country, 
                 shipping_firstname, shipping_lastname, shipping_address, shipping_zipcode, shipping_city, shipping_country, additional_instructions) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("idsssssssssssss", 
                $user_id, $total, 
                $bill_prenom, $bill_nom, $address, $city, $zipcode, $bill_country,
                $del_prenom, $del_nom, $del_address, $del_zipcode, $del_city, $del_country, $del_instructions
            );
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
            header("Location: " . BASE_URL . "shop/success.php?id=" . $invoice_id);
            exit();
        } catch (Exception $e) {
            $mysqli->rollback();
            $error = "Erreur lors du traitement du paiement.";
        }
    } else {
        $error = "Solde insuffisant. Veuillez recharger votre compte.";
    }
}

include BASE_PATH . 'includes/header.php';
?>

<div class="contactContainer" style="max-width: 800px; padding: 2rem 5%; text-align: left;">
    <h1 class="titleFormular" style="text-align: left; margin-bottom: 2rem; font-size: 1.8rem; text-transform: uppercase;">Confirme ta commande</h1>

    <?php if (!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold; background: #fee2e2; padding: 1rem; border: 1px solid #f87171;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div style="color: #16a34a; margin-bottom: 1rem; font-weight: bold; background: #dcfce7; padding: 1rem; border: 1px solid #4ade80;">
            <?php echo htmlspecialchars($success); ?>
            <br><br>
            <a href="<?= BASE_URL ?>index.php" style="text-decoration: underline; color: #1c1917; font-weight: bold;">RETOUR À L'ACCUEIL</a>
        </div>
    <?php else: ?>
        
        <!-- Liste des articles -->
        <div style="margin-bottom: 3rem;">
            <h2 style="font-size: 1rem; font-weight: bold; margin-bottom: 1rem; text-transform: uppercase;">Articles</h2>
            <div style="border-top: 1px solid #d6d3d1;">
                <?php foreach($cartItems as $item): ?>
                    <div style="display: flex; gap: 1.5rem; padding: 1.5rem 0; border-bottom: 1px solid #d6d3d1;">
                        <img src="<?= BASE_URL . htmlspecialchars($item['image_url'] ?? 'img/default.jpg') ?>" style="width: 100px; height: 130px; object-fit: cover; background-color: #f5f5f4;" alt="">
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-start;">
                            <h3 style="text-transform: uppercase; font-weight: bold; font-size: 0.85rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p style="font-size: 0.75rem; color: #57534e; text-transform: uppercase; margin-bottom: 0.2rem; font-weight: bold;">
                                TAILLE : <?php echo htmlspecialchars($item['size'] ?? 'XX'); ?><br>
                                QUANTITÉ : <?php echo $item['quantity']; ?>
                            </p>
                            <p style="font-size: 0.75rem; color: #57534e; text-transform: uppercase; margin-top: 1.5rem; margin-bottom: 1rem;">
                                VENDU PAR : <span style="font-weight: bold; color: #1c1917;">RESIDUE_</span>
                            </p>
                            <p style="font-weight: bold; font-size: 0.85rem; margin-top: auto;">
                                <?php echo number_format($item['price'], 2, ',', ' '); ?> EUR
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <form action="<?= BASE_URL ?>shop/checkout.php" method="POST">
            <!-- Informations de Livraison -->
            <div style="margin-bottom: 3rem;">
                <h2 style="font-size: 1rem; font-weight: bold; margin-bottom: 1.5rem; text-transform: uppercase;">Informations de livraison</h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Prénom</label>
                        <input type="text" name="del_prenom" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Nom</label>
                        <input type="text" name="del_nom" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Adresse</label>
                    <input type="text" name="del_address" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Code Postale</label>
                        <input type="text" name="del_zipcode" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Ville</label>
                        <input type="text" name="del_city" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Pays</label>
                    <input type="text" name="del_country" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Instructions Supplémentaires</label>
                    <input type="text" name="del_instructions" style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                </div>
            </div>

            <!-- Informations de Facturation -->
            <div style="margin-bottom: 3rem;">
                <h2 style="font-size: 1rem; font-weight: bold; margin-bottom: 1.5rem; text-transform: uppercase;">Informations de facturation</h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Prénom</label>
                        <input type="text" name="bill_prenom" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Nom</label>
                        <input type="text" name="bill_nom" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Adresse</label>
                    <input type="text" name="bill_address" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Code Postale</label>
                        <input type="text" name="bill_zipcode" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Ville</label>
                        <input type="text" name="bill_city" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.3rem;">Pays</label>
                    <input type="text" name="bill_country" required style="width: 100%; padding: 0.8rem; border: 1px solid #d6d3d1; font-size: 0.9rem; background: transparent;">
                </div>
            </div>

            <!-- Récapitulatif -->
            <div style="margin-bottom: 2rem;">
                <h2 style="font-size: 1rem; font-weight: bold; margin-bottom: 1.5rem; text-transform: uppercase;">Récapitulatif</h2>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.8rem; text-transform: uppercase;">
                    <span>Total des articles</span>
                    <span style="font-weight: bold;"><?php echo number_format($totalCart, 2, ',', ' '); ?> EUR</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.8rem; text-transform: uppercase; border-bottom: 1px solid #d6d3d1; padding-bottom: 1rem;">
                    <span>Frais de livraison</span>
                    <span style="font-weight: bold;"><?php echo number_format($delivery_fee, 2, ',', ' '); ?> EUR</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3rem; font-size: 0.8rem; text-transform: uppercase;">
                    <span>Total</span>
                    <span style="font-weight: bold;"><?php echo number_format($total, 2, ',', ' '); ?> EUR</span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8rem; text-transform: uppercase;">
                    <span>Solde actuel du compte</span>
                    <span style="font-weight: bold;"><?php echo number_format($balance, 2, ',', ' '); ?> EUR</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; text-transform: uppercase;">
                    <span>Solde du compte après opération</span>
                    <span style="font-weight: bold; <?php echo ($newBalance < 0) ? 'color: #dc2626;' : ''; ?>"><?php echo number_format($newBalance, 2, ',', ' '); ?> EUR</span>
                </div>
            </div>

            <?php if ($newBalance >= 0): ?>
                <button type="submit" style="width: 100%; padding: 1.2rem; background-color: #1c1917; color: white; border: none; font-weight: bold; font-size: 0.9rem; cursor: pointer; text-transform: uppercase; letter-spacing: 1px;">Commander</button>
            <?php else: ?>
                <div style="text-align: center; color: #dc2626; font-weight: bold; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 1rem;">
                    Votre solde est insuffisant.
                </div>
                <button disabled style="width: 100%; padding: 1.2rem; background-color: #78716c; color: white; border: none; font-weight: bold; font-size: 0.9rem; cursor: not-allowed; text-transform: uppercase; letter-spacing: 1px;">Commander</button>
            <?php endif; ?>
            
        </form>
    <?php endif; ?>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>