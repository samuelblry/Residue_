<?php
require_once 'includes/db.php';

// Gestion de la recherche et des catégories
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

// Requête pour NOUVEAUTÉS (publié il y a moins de 7 jours)
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

// Requête pour BEST SELLERS (top 5 des articles les plus vendus calculé grâce à invoice_item)
// Si aucune recherche ni catégorie n'est appliquée, on prend juste les meilleures ventes
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

include 'includes/header.php';
?>

<header class="heroSection">
    <div class="heroLayer heroBg">
        <img src="./img/background/fondNuage.png" class="heroImg" alt="Image de nuages">
    </div>
    <div class="heroContentTitle">
        <h1 class="heroTitle">RESIDUE_</h1>
    </div>
    <div class="heroLayer heroFg">
        <img src="./img/background/fondDesert.png" class="heroImg" alt="Image de désert">
    </div>
    <div class="heroContentSubtitle">
        <p class="heroSubtitle">no waste, just taste.</p>
    </div>
    <div class="heroOverlay">
        <a href="./pageArticle.php" class="btnHeroShowNewArticle" aria-label="Ouvre la page des derniers ajouts">VOIR
            LES DERNIERS AJOUTS</a>
    </div>
</header>

<section id="shop" class="shopSection reveal">

    <div class="articleNew">
        <div class="shopHeader">
            <div class="shopHeaderTop">
                <h2 class="bigTitle">
                    <?php echo isset($_GET['q']) ? 'Pour : ' . htmlspecialchars($_GET['q']) : (isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'NOUVEAUTÉS'); ?>
                </h2>
                <a href="shop.php" class="filterLink">VOIR TOUT</a>
            </div>
            <?php if (!isset($_GET['q'])): ?>
                <div class="shopHeaderCategories">
                    <a href="shop.php" class="<?php echo !isset($_GET['category']) ? 'active' : ''; ?>">VOIR TOUT</a>
                    <a href="shop.php?category=Hoodies"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Hoodies') ? 'active' : ''; ?>">HOODIES</a>
                    <a href="shop.php?category=Knitwear"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Knitwear') ? 'active' : ''; ?>">KNITS</a>
                    <a href="shop.php?category=Pantalons"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Pantalons') ? 'active' : ''; ?>">PANTALONS</a>
                    <a href="shop.php?category=Vestes"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Vestes') ? 'active' : ''; ?>">VESTES</a>
                    <a href="shop.php?category=T-shirts"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'T-shirts') ? 'active' : ''; ?>">T-SHIRTS</a>
                    <a href="shop.php?category=Accessoires"
                        class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Accessoires') ? 'active' : ''; ?>">ACCESSOIRES</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="productsGrid">
            <?php if (!empty($articlesNew)): ?>
                <?php foreach ($articlesNew as $article): ?>
                    <div class="productCard">
                        <div class="cardImage">
                            <a href="./pageArticle.php?id=<?php echo $article['id']; ?>">
                                <span class="tagRed">New</span>
                                <img src="<?php echo htmlspecialchars($article['image_url'] ?? 'img/default.jpg'); ?>"
                                    class="imgFront" alt="<?php echo htmlspecialchars($article['name']); ?>">
                                <?php if (!empty($article['hover_image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($article['hover_image_url']); ?>" class="imgBack"
                                        alt="<?php echo htmlspecialchars($article['name']); ?> porté">
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="cardInfo">
                            <div>
                                <h3 class="articleName"><?php echo htmlspecialchars($article['name']); ?></h3>
                            </div>
                            <span class="articlePrice"><?php echo number_format($article['price'], 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune nouveauté cette semaine.</p>
            <?php endif; ?>
        </div>

        <div class="viewAllContainer">
            <a href="shop.php" class="btnViewAll">VOIR TOUT</a>
        </div>
    </div>
</section>

<div class="CategoryMenu reveal">
    <a href="shop.php?category=Hoodies" class="categoryItem">
        <div class="categoryImage">
            <img src="./img/hoodieZipPorte.webp" alt="Hoodies">
        </div>
        <span class="categoryTitle">HOODIES</span>
    </a>

    <a href="shop.php?category=Knitwear" class="categoryItem">
        <div class="categoryImage">
            <img src="./img/knitPorte.webp" alt="Knit">
        </div>
        <span class="categoryTitle">KNIT</span>
    </a>

    <a href="shop.php?category=T-shirts" class="categoryItem">
        <div class="categoryImage">
            <img src="./img/tshirtPorte.webp" alt="T-Shirt">
        </div>
        <span class="categoryTitle">T-SHIRT</span>
    </a>

    <a href="shop.php?category=Pantalons" class="categoryItem">
        <div class="categoryImage">
            <img src="./img/pantPorte.webp" alt="Pantalon">
        </div>
        <span class="categoryTitle">PANTALON</span>
    </a>

    <a href="shop.php?category=Accessoires" class="categoryItem">
        <div class="categoryImage">
            <img src="./img/ceinturePorte.webp" alt="Accessoires">
        </div>
        <span class="categoryTitle">ACCESSOIRES</span>
    </a>
</div>

<section class="shopSection reveal">
    <div class="articleBestSteller">
        <div class="shopHeader">
            <div class="shopHeaderTop">
                <h2 class="bigTitle">BEST SELLER</h2>
                <a href="shop.php" class="filterLink">VOIR TOUT</a>
            </div>
            <div class="shopHeaderCategories">
                <a href="shop.php" class="active">VOIR TOUT</a>
                <a href="shop.php?category=Hoodies">HOODIES</a>
                <a href="shop.php?category=Knitwear">KNITS</a>
                <a href="shop.php?category=Pantalons">PANTALONS</a>
                <a href="shop.php?category=Vestes">VESTES</a>
                <a href="shop.php?category=T-shirts">T-SHIRTS</a>
                <a href="shop.php?category=Accessoires">ACCESSOIRES</a>
            </div>
        </div>

        <div class="productsGrid">
            <?php if (!empty($articlesBestsellers)): ?>
                <?php foreach ($articlesBestsellers as $article): ?>
                    <div class="productCard">
                        <div class="cardImage">
                            <span class="tagRed">Bestseller</span>
                            <a href="./pageArticle.php?id=<?php echo $article['id']; ?>">
                                <img src="<?php echo htmlspecialchars($article['image_url'] ?? 'img/default.jpg'); ?>"
                                    class="imgFront" alt="<?php echo htmlspecialchars($article['name']); ?>">
                                <?php if (!empty($article['hover_image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($article['hover_image_url']); ?>" class="imgBack"
                                        alt="<?php echo htmlspecialchars($article['name']); ?> porté">
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="cardInfo">
                            <div>
                                <h3 class="articleName"><?php echo htmlspecialchars($article['name']); ?></h3>
                            </div>
                            <span class="articlePrice"><?php echo number_format($article['price'], 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Encore aucune vente à afficher.</p>
            <?php endif; ?>
        </div>

        <div class="viewAllContainer">
            <a href="shop.php" class="btnViewAll">VOIR TOUT</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>