<?php
require_once 'includes/db.php';

// Gestion de la catégorie
$categoryFilter = "";
$categoryTitle = "VOIR TOUT";
$params = [];
$types = "";

if (isset($_GET['category']) && !empty(trim($_GET['category']))) {
    $categoryFilter = trim($_GET['category']);
    $categoryTitle = strtoupper($categoryFilter);
    // Cas spécial pour Knits/Knitwear si l'URL utilise un nom différent
    if ($categoryFilter === "Knits") $categoryFilter = "Knitwear";
}

// Construction de la requête
$sql = "SELECT Article.id, Article.name, Article.price, Image.url AS image_url 
        FROM Article 
        LEFT JOIN Image ON Article.id = Image.article_id AND Image.is_main = 1 ";

if ($categoryFilter !== "") {
    $sql .= "WHERE Article.category = ? ";
    $params[] = $categoryFilter;
    $types .= "s";
}

$sql .= "ORDER BY Article.publish_date DESC";

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

$elementCount = count($articles);

include 'includes/header.php';
?>

<div class="shopCatalogContainer">

    <!-- En-tête de la page catalogue -->
    <div class="catalogHeader">
        <div class="catalogHeaderLeft">
            <h1 class="catalogTitle"><?php echo htmlspecialchars($categoryTitle); ?></h1>
            <span class="catalogCount"><?php echo $elementCount; ?> ÉLÉMENTS</span>
        </div>
        <div class="catalogHeaderRight">
            <button class="sortBtn">
                TRIÉ PAR <span class="sortIcon">⇌</span>
            </button>
            <button class="filterBtn">FILTRES</button>
        </div>
    </div>

    <!-- Grille des produits -->
    <div class="catalogGrid">
        <?php if(!empty($articles)): ?>
            <?php foreach($articles as $article): ?>
                <a href="pageArticle.php?id=<?php echo $article['id']; ?>" class="catalogCard">
                    <div class="catalogImgWrapper">
                        <?php if(!empty($article['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['name']); ?>">
                        <?php else: ?>
                            <div class="catalogPlaceholder"></div>
                        <?php endif; ?>
                    </div>
                    <div class="catalogInfo">
                        <h3 class="catalogName"><?php echo htmlspecialchars($article['name']); ?></h3>
                        <span class="catalogPrice"><?php echo number_format($article['price'], 2, ',', ' '); ?> EUR</span>
                        <div class="catalogColors">
                            <!-- Couleurs statiques pour le visuel demandé par la maquette -->
                            <span class="catalogColorDot" style="background-color: #dc2626;"></span>
                            <span class="catalogColorDot" style="background-color: #3b82f6;"></span>
                            <span class="catalogColorDot" style="background-color: #e5e7eb;"></span>
                            <span class="catalogColorDot" style="background-color: #1f2937;"></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun article trouvé dans cette catégorie.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
