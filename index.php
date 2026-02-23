<?php 
require_once 'includes/db.php'; 
include 'includes/header.php'; 

// 1. Requête pour récupérer tous les articles avec leur image principale (is_main = 1)
// On les trie du plus récent au plus ancien
$sql = "SELECT Article.id, Article.name, Article.price, Image.url AS image_url 
        FROM Article 
        LEFT JOIN Image ON Article.id = Image.article_id AND Image.is_main = 1 
        ORDER BY Article.publish_date DESC";

$resultArticles = $mysqli->query($sql);

// On stocke les résultats dans un tableau pour pouvoir les réutiliser dans plusieurs sections
$articles = [];
if ($resultArticles && $resultArticles->num_rows > 0) {
    while($row = $resultArticles->fetch_assoc()) {
        $articles[] = $row;
    }
}
?>

    <header class="heroSection">
        <div class="heroBgContainer">
            <img src="./img/bg.webp" class="heroImg" alt="Image de fond de la section">
        </div>
        <div class="heroContent">
            <h1 class="heroTitle">RESIDUE_</h1>
            <p class="heroSubtitle">no waste, just taste.</p>
            <a href="./pageArticle.php" class="btnHeroShowNewArticle"
                aria-label="Ouvre la page des derniers ajouts">Voir les derniers ajouts</a>
        </div>
    </header>

    <section id="shop" class="shopSection reveal">

        <div class="articleNew">
            <div class="shopHeader">
                <div>
                    <span class="smallTitle">Nouveautés</span>
                    <h2 class="bigTitle">Derniers ajouts</h2>
                </div>
                <a href="./error.php" class="seeAllLink">
                    Tout voir
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </a>
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
        </div>

        <div class="separationCategoryArticle"></div>

        <div class="articleBestSteller">
            <div class="shopHeader">
                <div>
                    <span class="smallTitle">Les meilleurs ventes</span>
                    <h2 class="bigTitle">Best steller</h2>
                </div>
                <a href="./error.php" class="seeAllLink">
                    Tout voir
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </a>
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
        </div>
    </section>

    <div class="CategoryMenu reveal">
        <a href="./error.php" class="categoryItem">
            <div class="categoryImage">
                <img src="./img/knitPorte.webp" alt="Knits">
            </div>
            <span class="categoryTitle">KNITS</span>
        </a>

        <a href="./error.php" class="categoryItem">
            <div class="categoryImage">
                <img src="./img/hoodieZipPorte.webp" alt="Hoodies">
            </div>
            <span class="categoryTitle">HOODIES</span>
        </a>

        <a href="./error.php" class="categoryItem">
            <div class="categoryImage">
                <img src="./img/tshirtPorte.webp" alt="T-shirts">
            </div>
            <span class="categoryTitle">T-SHIRTS</span>
        </a>

        <a href="./error.php" class="categoryItem">
            <div class="categoryImage">
                <img src="./img/pantPorte.webp" alt="Pantalons">
            </div>
            <span class="categoryTitle">PANTALONS</span>
        </a>

        <a href="./error.php" class="categoryItem">
            <div class="categoryImage">
                <img src="./img/ceinturePorte.webp" alt="Accessoires">
            </div>
            <span class="categoryTitle">ACCESSOIRES</span>
        </a>
    </div>

    <section id="manifesto" class="textSection reveal">
        <div class="textContent">
            <div class="textLeft">
                <h2 class="mainHeading">allez voir notre nouveau<br><span class="redWord">knit</span><br>en édition
                    limitée !</h2>
                <a href="./pageArticle.php" class="btnDrop" aria-label="Ouvre la page du drop">Voir le drop ici !</a>
                <p class="paragraph">Heavy materials | Oversize | Boxy fit | Embroidery logo | 100% COTTON | Main color
                    : BLACK |
                    Ready to ship | Worldwide shipping</p>
            </div>
        </div>
        <img src="./img/brand/imgSocialMedia2.webp" class="backgroundSocialMedia" alt="Image de fond">
    </section>

<?php include 'includes/footer.php'; ?>