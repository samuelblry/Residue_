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

// Gestion de l'ajout au panier ou suppression
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $article_id = intval($_POST['article_id']);
        $quantity = intval($_POST['quantity'] ?? 1);
        $size = isset($_POST['size']) ? strtoupper(trim(mysqli_real_escape_string($mysqli, $_POST['size']))) : 'M';

        // 1. Validation du stock selon la taille sélectionnée
        $size_column = 'quant_' . strtolower($size);
        $valid_columns = ['quant_xs', 'quant_s', 'quant_m', 'quant_l', 'quant_xl'];
        
        if (!in_array($size_column, $valid_columns)) {
            $error = "Taille invalide.";
        } else {
            $stockQuery = $mysqli->query("SELECT $size_column FROM stock WHERE article_id = $article_id");
            if ($stockQuery && $stockQuery->num_rows > 0) {
                $stockRow = $stockQuery->fetch_assoc();
                $available_stock = intval($stockRow[$size_column]);
                
                if ($available_stock < $quantity) {
                    $error = "Stock insuffisant pour la taille $size.";
                } else {
                    // 2. Vérifier si l'article (avec cette taille spécifique) est déjà dans le panier
                    $check = $mysqli->query("SELECT id, quantity FROM cart WHERE user_id = $user_id AND article_id = $article_id AND size = '$size'");
                    if ($check->num_rows > 0) {
                        $row = $check->fetch_assoc();
                        $newQuantity = $row['quantity'] + $quantity;
                        $mysqli->query("UPDATE cart SET quantity = $newQuantity WHERE id = " . $row['id']);
                    } else {
                        $mysqli->query("INSERT INTO cart (user_id, article_id, quantity, size) VALUES ($user_id, $article_id, $quantity, '$size')");
                    }
                    $success = "Article ajouté au panier.";
                }
            } else {
               $error = "Erreur lors de la vérification des stocks ou article introuvable.";
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'remove') {
        $cart_id = intval($_POST['cart_id']);
        $mysqli->query("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
        $success = "Article retiré du panier.";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
        $cart_id = intval($_POST['cart_id']);
        $change = intval($_POST['quantity_change']);
        
        $checkCart = $mysqli->query("SELECT article_id, quantity, size FROM cart WHERE id = $cart_id AND user_id = $user_id");
        if ($checkCart && $checkCart->num_rows > 0) {
            $row = $checkCart->fetch_assoc();
            $newQuantity = $row['quantity'] + $change;
            
            if ($newQuantity <= 0) {
                $mysqli->query("DELETE FROM cart WHERE id = $cart_id");
                $success = "Article retiré du panier.";
            } else {
                $article_id = $row['article_id'];
                $size = strtolower($row['size']);
                $size_column = 'quant_' . $size;
                
                $stockQuery = $mysqli->query("SELECT $size_column FROM stock WHERE article_id = $article_id");
                if ($stockQuery && $stockQuery->num_rows > 0) {
                    $stockRow = $stockQuery->fetch_assoc();
                    $available_stock = intval($stockRow[$size_column]);
                    
                    if ($newQuantity <= $available_stock) {
                        $mysqli->query("UPDATE cart SET quantity = $newQuantity WHERE id = $cart_id");
                    } else {
                        $error = "Stock insuffisant pour augmenter la quantité.";
                    }
                }
            }
        }
    }
}

// Récupérer les articles du panier
$sql = "SELECT cart.id as cart_id, cart.quantity, cart.size, article.id as article_id, article.name, article.price, 
               (SELECT url FROM image WHERE article_id = article.id AND is_main = 1 LIMIT 1) as image_url 
        FROM cart 
        JOIN article ON cart.article_id = article.id 
        WHERE cart.user_id = $user_id";

$result = $mysqli->query($sql);
$cartItems = [];
$total = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}

// Récupérer le solde de l'utilisateur
$userBalance = 0;
$resUser = $mysqli->query("SELECT balance FROM user WHERE id=$user_id");
if ($resUser && $resUser->num_rows > 0) {
    $userRow = $resUser->fetch_assoc();
    $userBalance = floatval($userRow['balance']);
}

// Générer des frais de livraison aléatoires (sauvegardés en session pour ne pas changer à chaque rechargement de page)
if (!isset($_SESSION['delivery_fee'])) {
    $possible_fees = [5.99, 6.99, 7.99, 8.99, 9.99];
    $_SESSION['delivery_fee'] = $possible_fees[array_rand($possible_fees)];
}
$delivery_fee = $_SESSION['delivery_fee'];

$total_with_delivery = empty($cartItems) ? 0 : $total + $delivery_fee;
$is_balance_sufficient = ($userBalance >= $total_with_delivery);

// Récupérer 5 articles au hasard pour les suggestions
$suggestions = [];
$suggestQuery = "SELECT article.id, article.name, article.price, 
                        (SELECT url FROM image WHERE article_id = article.id AND is_main = 1 LIMIT 1) as image_url 
                 FROM article 
                 ORDER BY RAND() LIMIT 5";
$suggestResult = $mysqli->query($suggestQuery);
if ($suggestResult && $suggestResult->num_rows > 0) {
    while ($row = $suggestResult->fetch_assoc()) {
        $suggestions[] = $row;
    }
}

include BASE_PATH . 'includes/header.php';
?>

<!-- HTML structur -->
<div class="contactContainer" style="max-width: 1500px; padding: 2rem 5%;">
    <h1 class="titleFormular" style="text-align: left; margin-bottom: 2rem; font-size: 2rem;">PANIER</h1>

    <?php if(!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div style="color: #16a34a; margin-bottom: 1rem; font-weight: bold;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <p>Votre panier est vide. <a href="<?= BASE_URL ?>index.php" style="text-decoration: underline;">Retour à la boutique</a></p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 4rem; align-items: start;">
            <!-- Colonne de gauche : Articles -->
            <div style="display: flex; flex-direction: column; border-top: 1px solid #e7e5e4;">
                <?php foreach($cartItems as $item): ?>
                    <div style="display: flex; gap: 1.5rem; padding: 1.5rem 0; border-bottom: 1px solid #e7e5e4; align-items: center;">
                        <!-- Image article -->
                        <img src="<?= BASE_URL . htmlspecialchars($item['image_url'] ?? 'img/default.jpg') ?>" style="width: 100px; height: 130px; object-fit: cover; background-color: #f5f5f4;" alt="">
                        
                        <!-- Infos article -->
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-start;">
                            <h3 style="text-transform: uppercase; font-weight: bold; font-size: 0.9rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p style="font-size: 0.75rem; color: #57534e; text-transform: uppercase; margin-bottom: 0.2rem;">Taille : <?php echo htmlspecialchars($item['size'] ?? 'XX'); ?></p>
                            <p style="font-size: 0.75rem; color: #57534e; text-transform: uppercase; margin-bottom: 1rem;">Vendu par : <span style="font-weight: bold;">RESIDUE_</span></p>
                            <p style="font-weight: bold; font-size: 0.8rem; margin-top: auto;"><?php echo number_format($item['price'], 2, ',', ' '); ?> EUR</p>
                        </div>
                        
                        <!-- Actions de quantité -->
                        <div style="border: 1px solid #d6d3d1; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.2rem 0.5rem; width: 80px;">
                            <form method="POST" action="<?= BASE_URL ?>shop/cart.php" style="margin: 0;">
                                <input type="hidden" name="action" value="update_quantity">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <input type="hidden" name="quantity_change" value="-1">
                                <button type="submit" style="background: none; border: none; cursor: pointer; font-size: 1rem;">-</button>
                            </form>
                            
                            <span style="font-size: 0.9rem; margin: 0 0.2rem;"><?php echo $item['quantity']; ?></span>
                            
                            <form method="POST" action="<?= BASE_URL ?>shop/cart.php" style="margin: 0;">
                                <input type="hidden" name="action" value="update_quantity">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <input type="hidden" name="quantity_change" value="1">
                                <button type="submit" style="background: none; border: none; cursor: pointer; font-size: 1rem;">+</button>
                            </form>
                        </div>
                        
                        <!-- Prix total article -->
                        <div style="font-weight: bold; font-size: 0.9rem; width: 80px; text-align: right; margin-right: 1.5rem;">
                            <?php echo number_format($item['price'] * $item['quantity'], 2, ',', ' '); ?> EUR
                        </div>

                        <!-- Bouton supprimer au design minimaliste (X) -->
                        <form method="POST" action="<?= BASE_URL ?>shop/cart.php" style="margin: 0;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <button type="submit" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #1c1917; font-weight: bold; padding: 0;">&#10005;</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Colonne de droite : Récapitulatif -->
            <div style="position: sticky; top: 100px;">
                <h3 style="font-weight: bold; margin-bottom: 1.5rem; text-transform: uppercase; font-size: 0.9rem;">Récapitulatif</h3>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.8rem; text-transform: uppercase;">
                    <span>Total des articles</span>
                    <span style="font-weight: bold;"><?php echo number_format($total, 2, ',', ' '); ?> EUR</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.8rem; text-transform: uppercase; border-bottom: 1px solid #c8c5c4; padding-bottom: 1.5rem;">
                    <span>Frais de livraison</span>
                    <span style="font-weight: bold;"><?php echo number_format($delivery_fee, 2, ',', ' '); ?> EUR</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 2.5rem; font-size: 0.9rem; text-transform: uppercase;">
                    <span>Total</span>
                    <span style="font-weight: bold;"><?php echo number_format($total_with_delivery, 2, ',', ' '); ?> EUR</span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8rem; text-transform: uppercase; margin-top: 3rem;">
                    <span style="font-weight: bold;">Solde actuel du compte</span>
                    <span style="font-weight: bold;"><?php echo number_format($userBalance, 2, ',', ' '); ?> EUR</span>
                </div>
                
                <?php if (!$is_balance_sufficient): ?>
                    <p style="font-size: 0.65rem; color: #1c1917; font-weight: bold; text-transform: uppercase; margin-bottom: 1.5rem;">
                        Le solde est insuffisant pour votre achat. <a href="<?= BASE_URL ?>auth/account.php" style="text-decoration: underline; font-weight: 800;">Recharger ici !</a>
                    </p>
                    <button disabled style="width: 100%; padding: 1rem; background-color: #78716c; color: white; border: none; font-weight: bold; cursor: not-allowed; text-transform: uppercase; letter-spacing: 1px;">Commander</button>
                <?php else: ?>
                    <p style="font-size: 0.65rem; color: #16a34a; font-weight: bold; text-transform: uppercase; margin-bottom: 1.5rem;">
                        Solde suffisant pour votre achat.
                    </p>
                    <a href="<?= BASE_URL ?>shop/checkout.php" style="display: block; width: 100%; padding: 1rem; background-color: #1c1917; color: white; border: none; font-weight: bold; cursor: pointer; text-transform: uppercase; text-align: center; text-decoration: none; letter-spacing: 1px;">Commander</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Suggestions (affiché même si panier vide) -->
    <div style="margin-top: 6rem;">
        <h2 style="font-weight: bold; margin-bottom: 2rem; text-transform: uppercase; font-size: 1.2rem;">Suggestions</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
            <?php foreach($suggestions as $sugg): ?>
                <a href="<?= BASE_URL ?>shop/pageArticle.php?id=<?php echo $sugg['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                    <img src="<?= BASE_URL . htmlspecialchars($sugg['image_url'] ?? 'img/default.jpg') ?>" style="width: 100%; height: 280px; object-fit: cover; background-color: #f5f5f4; margin-bottom: 0.5rem;" alt="">
                    <h3 style="font-size: 0.8rem; font-weight: bold; text-transform: uppercase; margin-bottom: 0.2rem;"><?php echo htmlspecialchars($sugg['name']); ?></h3>
                    <p style="font-size: 0.8rem; font-weight: bold;"><?php echo number_format($sugg['price'], 2, ',', ' '); ?> EUR</p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>
