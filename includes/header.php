<?php
if(session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESIDUE_</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <script src="js/script.js" defer></script>
</head>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$hasHero = in_array($currentPage, ['index.php', 'apropos.php', 'login.php', 'register.php']);
?>
<body class="<?php echo $hasHero ? '' : 'page-with-nav-space'; ?>">

    <nav id="navBar" class="navBar">
        <div class="navBarPrincipal" id="navBarPrincipal">
            <div class="navContent">

                <div class="desktopLinks">
                    <a href="shop.php" id="btnBoutiqueNav">Boutique</a>
                    <a href="index.php" id="btnBrandNav">Collection</a>
                    <a href="contact.php" id="btnContactNav">Contact</a>
                </div>

                <div class="mainLogo">
                    <a href="./index.php">
                        RESIDUE_
                    </a>
                </div>

                <div class="rightIcons" style="display: flex; align-items: center; gap: 1rem;">
                    <a href="apropos.php" id="btnAPropos">A propos</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin.php">Admin</a>
                        <?php endif; ?>
                        <a href="account.php" id="btnCompte">Compte</a>
                        <a href="cart.php">Panier</a>
                        <a href="logout.php">Déconnexion</a>
                    <?php else: ?>
                        <a href="login.php" id="btnCompte">Compte</a>
                        <a href="cart.php">Panier</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="navBarSecondary" id="navBarSecondary">
            <div class="navSubContent">
                <a href="shop.php">Voir tout</a>
                <a href="shop.php?category=Hoodies">Hoodies</a>
                <a href="shop.php?category=Knitwear">Knits</a>
                <a href="shop.php?category=Pantalons">Pantalons</a>
                <a href="shop.php?category=Vestes">Vestes</a>
                <a href="shop.php?category=T-shirts">T-shirts</a>
                <a href="shop.php?category=Accessoires">Accessoires</a>
            </div>
        </div>
    </nav>