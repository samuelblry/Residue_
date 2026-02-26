<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';


$whereClauses = [];
$params = [];
$types = "";

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $q = "%" . trim($_GET['q']) . "%";
    $whereClauses[] = "(Article.name LIKE ? OR Article.description LIKE ?)";
    $params[] = $q;
    $params[] = $q;
    $types .= "ss";
}

if (isset($_GET['category']) && !empty(trim($_GET['category']))) {
    $cat = trim($_GET['category']);
    $whereClauses[] = "Article.category = ?";
    $params[] = $cat;
    $types .= "s";
}

$whereSQL = "";
if (!empty($whereClauses)) {
    $whereSQL = " WHERE " . implode(" AND ", $whereClauses);
}


$sqlNew = "SELECT Article.id, Article.name, Article.price, Image.url AS image_url, 
           (SELECT url FROM Image WHERE article_id = Article.id AND is_main = 0 LIMIT 1) AS hover_image_url
           FROM Article 
           LEFT JOIN Image ON Article.id = Image.article_id AND Image.is_main = 1 
           $whereSQL
           AND Article.publish_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
           ORDER BY Article.publish_date DESC";

$stmtNew = $mysqli->prepare($sqlNew);
if (!empty($params)) {
    $stmtNew->bind_param($types, ...$params);
}
$stmtNew->execute();
$resultNew = $stmtNew->get_result();

$articlesNew = [];
if ($resultNew && $resultNew->num_rows > 0) {
    while ($row = $resultNew->fetch_assoc()) {
        $articlesNew[] = $row;
    }
}



$sqlBestsellers = "SELECT Article.id, Article.name, Article.price, Image.url AS image_url, 
                   (SELECT url FROM Image WHERE article_id = Article.id AND is_main = 0 LIMIT 1) AS hover_image_url,
                   COALESCE(SUM(invoice_item.quantity), 0) as total_sold
                   FROM Article 
                   LEFT JOIN Image ON Article.id = Image.article_id AND Image.is_main = 1 
                   LEFT JOIN invoice_item ON Article.id = invoice_item.article_id
                   $whereSQL
                   GROUP BY Article.id
                   ORDER BY total_sold DESC, Article.publish_date DESC
                   LIMIT 5";

$stmtBest = $mysqli->prepare($sqlBestsellers);
if (!empty($params)) {
    $stmtBest->bind_param($types, ...$params);
}
$stmtBest->execute();
$resultBest = $stmtBest->get_result();

$articlesBestsellers = [];
if ($resultBest && $resultBest->num_rows > 0) {
    while ($row = $resultBest->fetch_assoc()) {
        $articlesBestsellers[] = $row;
}
}


$userFavorites = [];
if (isset($_SESSION['user_id'])) {
    $favQuery = $mysqli->query("SELECT article_id FROM favorite WHERE user_id = " . intval($_SESSION['user_id']));
    while ($favRow = $favQuery->fetch_assoc()) {
        $userFavorites[] = $favRow['article_id'];
    }
}

include 'includes/header.php';
?>

<header class="heroSection">
    <div class="heroLayer heroBg">
        <img src="<?= BASE_URL ?>img/background/fondNuage.png" class="heroImg" alt="Image de nuages">
    </div>
    <div class="heroContentTitle">
        <h1 class="heroTitle">RESIDUE_</h1>
    </div>
    <div class="heroLayer heroFg">
        <img src="<?= BASE_URL ?>img/background/fondDesert.png" class="heroImg" alt="Image de désert">
    </div>
    <div class="heroContentSubtitle">
        <p class="heroSubtitle">no waste, just taste.</p>
    </div>
</header>

<section id="shop" class="shopSection reveal">

    <div class="articleNew">
        <div class="shopHeader">
            <div class="shopHeaderTop">
                <h2 class="bigTitle">
                    <?php echo isset($_GET['q']) ? 'Pour : ' . htmlspecialchars($_GET['q']) : (isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'NOUVEAUTÉS'); ?>
                </h2>
                <a href="<?= BASE_URL ?>shop/shop.php" class="filterLink">VOIR TOUT</a>
            </div>
            <?php if (!isset($_GET['q'])): ?>
                <div class="shopHeaderCategories">
                    <a href="<?= BASE_URL ?>shop/shop.php" class="<?php echo !isset($_GET['category']) ? 'active' : ''; ?>">VOIR TOUT</a>
                    <a href="<?= BASE_URL ?>shop/shop.php?category=Hoodies"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Hoodies') ? 'active' : ''; ?>">HOODIES</a>
                    <a href="<?= BASE_URL ?>shop/shop.php?category=Knitwear"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Knitwear') ? 'active' : ''; ?>">KNITS</a>
                    <a href="<?= BASE_URL ?>shop/shop.php?category=Pantalons"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Pantalons') ? 'active' : ''; ?>">PANTALONS</a>
                    <a href="<?= BASE_URL ?>shop/shop.php?category=Vestes"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Vestes') ? 'active' : ''; ?>">VESTES</a>
                    <a href="<?= BASE_URL ?>shop/shop.php?category=T-shirts"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'T-shirts') ? 'active' : ''; ?>">T-SHIRTS</a>
                    <a href="<?= BASE_URL ?>shop/shop.php?category=Accessoires"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Accessoires') ? 'active' : ''; ?>">ACCESSOIRES</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="catalogGrid">
            <?php if (!empty($articlesNew)): ?>
                <?php foreach ($articlesNew as $article): ?>
                    <a href="<?= BASE_URL ?>shop/pageArticle.php?id=<?php echo $article['id']; ?>" class="catalogCard">
                        <div class="catalogImgWrapper" style="position: relative;">
                            <span class="tagRed" style="position: absolute; top: 10px; left: 10px; z-index: 10;">New</span>
                            <?php if(!empty($article['image_url'])): ?>
                                <img src="<?= BASE_URL . htmlspecialchars($article['image_url']) ?>" alt="<?php echo htmlspecialchars($article['name']); ?>">
                            <?php else: ?>
                                <div class="catalogPlaceholder"></div>
                            <?php endif; ?>
                        </div>
                        <div class="catalogInfo">
                            <h3 class="catalogName"><?php echo htmlspecialchars($article['name']); ?></h3>
                            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                <span class="catalogPrice"><?php echo number_format($article['price'], 2, ',', ' '); ?> EUR</span>
                                <?php $isFav = in_array($article['id'], $userFavorites); ?>
                                <button type="button" class="favoriteBtn" data-id="<?php echo $article['id']; ?>" onclick="toggleFavorite(event, <?php echo $article['id']; ?>)">
                                    <?php if ($isFav): ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="heart-icon is-favorite"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    <?php else: ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="heart-icon"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune nouveauté cette semaine.</p>
            <?php endif; ?>
        </div>

        <div class="viewAllContainer">
            <a href="<?= BASE_URL ?>shop/shop.php" class="btnViewAll">VOIR TOUT</a>
        </div>
    </div>
</section>

<div class="CategoryMenu reveal">
    <a href="<?= BASE_URL ?>shop/shop.php?category=Hoodies" class="categoryItem">
        <div class="categoryImage">
            <img src="<?= BASE_URL ?>img/articles/hoodieZipPorte.webp" alt="Hoodies">
        </div>
        <span class="categoryTitle">HOODIES</span>
    </a>

    <a href="<?= BASE_URL ?>shop/shop.php?category=Knitwear" class="categoryItem">
        <div class="categoryImage">
            <img src="<?= BASE_URL ?>img/articles/knitPorte.webp" alt="Knit">
        </div>
        <span class="categoryTitle">KNIT</span>
    </a>

    <a href="<?= BASE_URL ?>shop/shop.php?category=T-shirts" class="categoryItem">
        <div class="categoryImage">
            <img src="<?= BASE_URL ?>img/articles/tshirtPorte.webp" alt="T-Shirt">
        </div>
        <span class="categoryTitle">T-SHIRT</span>
    </a>

    <a href="<?= BASE_URL ?>shop/shop.php?category=Pantalons" class="categoryItem">
        <div class="categoryImage">
            <img src="<?= BASE_URL ?>img/articles/pantPorte.webp" alt="Pantalon">
        </div>
        <span class="categoryTitle">PANTALON</span>
    </a>

    <a href="<?= BASE_URL ?>shop/shop.php?category=Accessoires" class="categoryItem">
        <div class="categoryImage">
            <img src="<?= BASE_URL ?>img/articles/ceinturePorte.webp" alt="Accessoires">
        </div>
        <span class="categoryTitle">ACCESSOIRES</span>
    </a>
</div>

<section class="shopSection reveal">
    <div class="articleBestSteller">
        <div class="shopHeader">
            <div class="shopHeaderTop">
                <h2 class="bigTitle">BEST SELLER</h2>
                <a href="<?= BASE_URL ?>shop/shop.php" class="filterLink">VOIR TOUT</a>
            </div>
            <div class="shopHeaderCategories">
                <a href="<?= BASE_URL ?>shop/shop.php" class="active">VOIR TOUT</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=Hoodies">HOODIES</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=Knitwear">KNITS</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=Pantalons">PANTALONS</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=Vestes">VESTES</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=T-shirts">T-SHIRTS</a>
                <a href="<?= BASE_URL ?>shop/shop.php?category=Accessoires">ACCESSOIRES</a>
            </div>
        </div>

        <div class="catalogGrid">
            <?php if (!empty($articlesBestsellers)): ?>
                <?php foreach ($articlesBestsellers as $article): ?>
                    <a href="<?= BASE_URL ?>shop/pageArticle.php?id=<?php echo $article['id']; ?>" class="catalogCard">
                        <div class="catalogImgWrapper" style="position: relative;">
                            <span class="tagRed" style="position: absolute; top: 10px; left: 10px; z-index: 10;">Bestseller</span>
                            <?php if(!empty($article['image_url'])): ?>
                                <img src="<?= BASE_URL . htmlspecialchars($article['image_url']) ?>" alt="<?php echo htmlspecialchars($article['name']); ?>">
                            <?php else: ?>
                                <div class="catalogPlaceholder"></div>
                            <?php endif; ?>
                        </div>
                        <div class="catalogInfo">
                            <h3 class="catalogName"><?php echo htmlspecialchars($article['name']); ?></h3>
                            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                <span class="catalogPrice"><?php echo number_format($article['price'], 2, ',', ' '); ?> EUR</span>
                                <?php $isFav = in_array($article['id'], $userFavorites); ?>
                                <button type="button" class="favoriteBtn" data-id="<?php echo $article['id']; ?>" onclick="toggleFavorite(event, <?php echo $article['id']; ?>)">
                                    <?php if ($isFav): ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="heart-icon is-favorite"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    <?php else: ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="heart-icon"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Encore aucune vente à afficher.</p>
            <?php endif; ?>
        </div>

        <div class="viewAllContainer">
            <a href="<?= BASE_URL ?>shop/shop.php" class="btnViewAll">VOIR TOUT</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>