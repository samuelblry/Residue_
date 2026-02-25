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
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css?v=<?php echo time(); ?>">
    <script src="<?= BASE_URL ?>js/script.js" defer></script>
</head>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$hasHero = in_array($currentPage, ['index.php', 'apropos.php', 'login.php', 'register.php', 'success.php']);
?>
<body class="<?php echo $hasHero ? '' : 'page-with-nav-space'; ?>">

    <nav id="navBar" class="navBar">
        <div class="navBarPrincipal" id="navBarPrincipal">
            <div class="navContent">

                <div class="desktopLinks">
                    <a href="<?= BASE_URL ?>shop/shop.php" id="btnBoutiqueNav">Boutique</a>
                    <a href="<?= BASE_URL ?>contact.php" id="btnContactNav">Contact</a>
                </div>

                <div class="mainLogo">
                    <a href="<?= BASE_URL ?>index.php">
                        RESIDUE_
                    </a>
                </div>

                <div class="rightIcons" style="display: flex; align-items: center; gap: 1rem;">
                    <a href="<?= BASE_URL ?>apropos.php" id="btnAPropos">A propos</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>admin/admin.php">Admin</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>auth/account.php" id="btnCompte">Compte</a>
                        <a href="<?= BASE_URL ?>shop/cart.php">Panier</a>
                        <a href="<?= BASE_URL ?>auth/logout.php">Déconnexion</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>auth/login.php" id="btnCompte">Compte</a>
                        <a href="<?= BASE_URL ?>shop/cart.php">Panier</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="navBarSecondary" id="navBarSecondary">
            <div class="navSubContent">
                <a href="<?= BASE_URL ?>shop/shop.php">Voir tout</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=Hoodies">Hoodies</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=Knitwear">Knits</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=Pantalons">Pantalons</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=Vestes">Vestes</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=T-shirts">T-shirts</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=Accessoires">Accessoires</a>
            </div>
        </div>
    </nav>