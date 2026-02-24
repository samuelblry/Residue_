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

// Gestion de l'ajout au panier ou suppression
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $article_id = intval($_POST['article_id']);
        $quantity = intval($_POST['quantity'] ?? 1);

        // Vérifier si l'article est déjà dans le panier
        $check = $mysqli->query("SELECT id, quantity FROM cart WHERE user_id = $user_id AND article_id = $article_id");
        if ($check->num_rows > 0) {
            $row = $check->fetch_assoc();
            $newQuantity = $row['quantity'] + $quantity;
            $mysqli->query("UPDATE cart SET quantity = $newQuantity WHERE id = " . $row['id']);
        } else {
            $mysqli->query("INSERT INTO cart (user_id, article_id, quantity) VALUES ($user_id, $article_id, $quantity)");
        }
        $success = "Article ajouté au panier.";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'remove') {
        $cart_id = intval($_POST['cart_id']);
        $mysqli->query("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
        $success = "Article retiré du panier.";
    }
}

// Récupérer les articles du panier
$sql = "SELECT cart.id as cart_id, cart.quantity, article.id as article_id, article.name, article.price, 
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

include 'includes/header.php';
?>

<div class="contactContainer" style="max-width: 1000px;">
    <h1 class="titleFormular">Votre Panier</h1>

    <?php if(!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div style="color: #16a34a; margin-bottom: 1rem; font-weight: bold;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <p>Votre panier est vide. <a href="index.php" style="text-decoration: underline;">Retour à la boutique</a></p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem;">
            <?php foreach($cartItems as $item): ?>
                <div style="display: flex; gap: 1rem; border: 1px solid #e7e5e4; padding: 1rem; align-items: center;">
                    <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'img/default.jpg'); ?>" style="width: 100px; height: 100px; object-fit: cover;" alt="">
                    <div style="flex: 1;">
                        <h3 style="text-transform: uppercase; font-weight: bold;"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Prix: <?php echo number_format($item['price'], 2, ',', ' '); ?> €</p>
                        <p>Quantité: <?php echo $item['quantity']; ?></p>
                    </div>
                    <div>
                        <p style="font-weight: bold; font-size: 1.2rem; margin-bottom: 1rem;">
                            <?php echo number_format($item['price'] * $item['quantity'], 2, ',', ' '); ?> €
                        </p>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <button type="submit" style="color: #dc2626; text-decoration: underline; font-size: 0.8rem;">Retirer</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="border-top: 2px solid #1c1917; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.5rem; text-transform: uppercase;">Total : <?php echo number_format($total, 2, ',', ' '); ?> €</h2>
            <a href="checkout.php" class="sendBtnFormular" style="width: auto; padding: 1rem 2rem; display: inline-block; text-align: center;">Procéder au paiement</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
