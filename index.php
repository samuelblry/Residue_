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

// Requête pour récupérer tous les articles avec leur image principale
$sql = "SELECT Article.id, Article.name, Article.price, Image.url AS image_url 
        FROM Article 
        LEFT JOIN Image ON Article.id = Image.article_id AND Image.is_main = 1 
        $whereSQL
        ORDER BY Article.publish_date DESC";

$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultArticles = $stmt->get_result();

$articles = [];
if ($resultArticles && $resultArticles->num_rows > 0) {
    while($row = $resultArticles->fetch_assoc()) {
        $articles[] = $row;
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
            <a href="./pageArticle.php" class="btnHeroShowNewArticle"
                aria-label="Ouvre la page des derniers ajouts">VOIR LES DERNIERS AJOUTS</a>
        </div>
    </header>

    <section id="shop" class="shopSection reveal">

        <div class="articleNew">
            <div class="shopHeader">
                <div class="shopHeaderTop">
                    <h2 class="bigTitle"><?php echo isset($_GET['q']) ? 'Pour : ' . htmlspecialchars($_GET['q']) : (isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'NOUVEAUTÉS'); ?></h2>
                    <a href="index.php" class="filterLink">VOIR TOUT</a>
                </div>
                <?php if (!isset($_GET['q'])): ?>
                <div class="shopHeaderCategories">
                    <a href="index.php" class="<?php echo !isset($_GET['category']) ? 'active' : ''; ?>">VOIR TOUT</a>
                    <a href="index.php?category=Hoodies" class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Hoodies') ? 'active' : ''; ?>">HOODIES</a>
                    <a href="index.php?category=Knitwear" class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Knitwear') ? 'active' : ''; ?>">KNITS</a>
                    <a href="index.php?category=Pantalons" class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Pantalons') ? 'active' : ''; ?>">PANTALONS</a>
                    <a href="index.php?category=Vestes" class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Vestes') ? 'active' : ''; ?>">VESTES</a>
                    <a href="index.php?category=T-shirts" class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'T-shirts') ? 'active' : ''; ?>">T-SHIRTS</a>
                    <a href="index.php?category=Accessoires" class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Accessoires') ? 'active' : ''; ?>">ACCESSOIRES</a>
                </div>
                <?php endif; ?>
            </div>

            <div class="productsGrid">
                <?php if(!empty($articles)): ?>
                    <?php foreach($articles as $article): ?>
                        <div class="productCard">
                            <div class="cardImage">
                                <a href="./pageArticle.php?id=<?php echo $article['id']; ?>">
                                    <span class="tagRed">New</span>
                                    <img src="<?php echo htmlspecialchars($article['image_url'] ?? 'img/default.jpg'); ?>" class="imgFront" alt="<?php echo htmlspecialchars($article['name']); ?>">
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
                    <p>Aucun article disponible pour le moment.</p>
                <?php endif; ?>
            </div>
            
            <div class="viewAllContainer">
                <a href="./index.php" class="btnViewAll">VOIR TOUT</a>
            </div>
        </div>
    </section>

    <div class="CategoryMenu reveal">
        <a href="./index.php?category=Hoodies" class="categoryItem">
            <div class="categoryImage">
                <img src="./img/hoodieZipPorte.webp" alt="Hoodies">
            </div>
            <span class="categoryTitle">HOODIES</span>
        </a>

        <a href="./index.php?category=Knitwear" class="categoryItem">
            <div class="categoryImage">
                <img src="./img/knitPorte.webp" alt="Knit">
            </div>
            <span class="categoryTitle">KNIT</span>
        </a>

        <a href="./index.php?category=T-shirts" class="categoryItem">
            <div class="categoryImage">
                <img src="./img/tshirtPorte.webp" alt="T-Shirt">
            </div>
            <span class="categoryTitle">T-SHIRT</span>
        </a>

        <a href="./index.php?category=Pantalons" class="categoryItem">
            <div class="categoryImage">
                <img src="./img/pantPorte.webp" alt="Pantalon">
            </div>
            <span class="categoryTitle">PANTALON</span>
        </a>

        <a href="./index.php?category=Accessoires" class="categoryItem">
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
                    <a href="index.php" class="filterLink">VOIR TOUT</a>
                </div>
                <div class="shopHeaderCategories">
                    <a href="index.php" class="active">VOIR TOUT</a>
                    <a href="index.php?category=Hoodies">HOODIES</a>
                    <a href="index.php?category=Knitwear">KNITS</a>
                    <a href="index.php?category=Pantalons">PANTALONS</a>
                    <a href="index.php?category=Vestes">VESTES</a>
                    <a href="index.php?category=T-shirts">T-SHIRTS</a>
                    <a href="index.php?category=Accessoires">ACCESSOIRES</a>
                </div>
            </div>

            <div class="productsGrid">
                <?php if(!empty($articles)): ?>
                    <?php foreach($articles as $article): ?>
                        <div class="productCard">
                            <div class="cardImage">
                                <span class="tagRed">Bestseller</span>
                                <a href="./pageArticle.php?id=<?php echo $article['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($article['image_url'] ?? 'img/default.jpg'); ?>" class="imgFront" alt="<?php echo htmlspecialchars($article['name']); ?>">
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
                <?php endif; ?>
            </div>

            <div class="viewAllContainer">
                <a href="./index.php" class="btnViewAll">VOIR TOUT</a>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>