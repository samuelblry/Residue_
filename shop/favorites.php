<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


$categoryFilter = "";
$categoryTitle = "MES FAVORIS";
$selectedSizes = [];

if (isset($_GET['category']) && !empty(trim($_GET['category']))) {
    $categoryFilter = trim($_GET['category']);
    if ($categoryFilter !== "Toutes") {
        $categoryTitle = strtoupper($categoryFilter);
        if ($categoryFilter === "Knits") $categoryFilter = "Knitwear";
    } else {
        $categoryFilter = "";
    }
}

if (isset($_GET['sizes']) && is_array($_GET['sizes'])) {
    $selectedSizes = $_GET['sizes'];
}


$sql = "SELECT Article.id, Article.name, Article.price, Image.url AS image_url 
        FROM Article 
        INNER JOIN favorite ON Article.id = favorite.article_id AND favorite.user_id = ?
        LEFT JOIN Image ON Article.id = Image.article_id AND Image.is_main = 1 
        LEFT JOIN stock ON Article.id = stock.article_id 
        WHERE (stock.quant_xs + stock.quant_s + stock.quant_m + stock.quant_l + stock.quant_xl) > 0 ";

$params = [$user_id];
$types = "i";

if ($categoryFilter !== "") {
    $sql .= "AND Article.category = ? ";
    $params[] = $categoryFilter;
    $types .= "s";
}

if (!empty($selectedSizes)) {
    $sizeConditions = [];
    foreach ($selectedSizes as $size) {
        $cleanSize = strtolower(trim($size));
        $validSizes = ['xs', 's', 'm', 'l', 'xl'];
        if (in_array($cleanSize, $validSizes)) {
            $sizeConditions[] = "stock.quant_" . $cleanSize . " > 0";
        }
    }
    if (!empty($sizeConditions)) {
        $sql .= "AND (" . implode(" OR ", $sizeConditions) . ") ";
    }
}


$sortOrder = "date_desc";
if (isset($_GET['sort'])) {
    $sortParam = trim($_GET['sort']);
    $validSorts = ['date_desc', 'date_asc', 'price_asc', 'price_desc'];
    if (in_array($sortParam, $validSorts)) {
        $sortOrder = $sortParam;
    }
}

switch ($sortOrder) {
    case 'date_asc':
        $sql .= "ORDER BY Article.publish_date ASC";
        break;
    case 'price_asc':
        $sql .= "ORDER BY Article.price ASC";
        break;
    case 'price_desc':
        $sql .= "ORDER BY Article.price DESC";
        break;
    case 'date_desc':
    default:
        $sql .= "ORDER BY Article.publish_date DESC";
        break;
}

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


$userFavorites = [];
if (isset($_SESSION['user_id'])) {
    $favQuery = $mysqli->query("SELECT article_id FROM favorite WHERE user_id = " . intval($_SESSION['user_id']));
    while ($favRow = $favQuery->fetch_assoc()) {
        $userFavorites[] = $favRow['article_id'];
    }
}

$elementCount = count($articles);

include BASE_PATH . 'includes/header.php';
?>

<div class="shopCatalogContainer">

    
    <div class="catalogHeader">
        <div class="catalogHeaderLeft">
            <h1 class="catalogTitle"><?php echo htmlspecialchars($categoryTitle); ?></h1>
            <span class="catalogCount"><?php echo $elementCount; ?> ÉLÉMENTS</span>
        </div>
        <div class="catalogHeaderRight">
            <form id="sortForm" method="GET" style="margin: 0;">
                
                <?php if ($categoryFilter !== ""): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryFilter); ?>">
                <?php endif; ?>
                <?php foreach ($selectedSizes as $size): ?>
                    <input type="hidden" name="sizes[]" value="<?php echo htmlspecialchars($size); ?>">
                <?php endforeach; ?>
                
                <select name="sort" class="sortSelect" onchange="document.getElementById('sortForm').submit();">
                    <option value="date_desc" <?php echo ($sortOrder === 'date_desc') ? 'selected' : ''; ?>>PLUS RÉCENT</option>
                    <option value="date_asc" <?php echo ($sortOrder === 'date_asc') ? 'selected' : ''; ?>>PLUS VIEUX</option>
                    <option value="price_asc" <?php echo ($sortOrder === 'price_asc') ? 'selected' : ''; ?>>PRIX CROISSANT</option>
                    <option value="price_desc" <?php echo ($sortOrder === 'price_desc') ? 'selected' : ''; ?>>PRIX DÉCROISSANT</option>
                </select>
            </form>
            <button class="filterBtn" id="openFilterBtn">FILTRES</button>
        </div>
    </div>

    
    <div class="catalogGrid">
        <?php if(!empty($articles)): ?>
            <?php foreach($articles as $article): ?>
                <a href="<?= BASE_URL ?>shop/pageArticle.php?id=<?php echo $article['id']; ?>" class="catalogCard">
                    <div class="catalogImgWrapper">
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
                            <?php 
                                
                                $isFav = true;
                            ?>
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
            <p style="grid-column: 1 / -1; min-height: 50vh;">Aucun article trouvé dans cette catégorie ou ces tailles.</p>
        <?php endif; ?>
    </div>
</div>


<div id="filterOverlay" class="filterOverlay"></div>


<aside id="filterSidebar" class="filterSidebar">
    <div class="filterSidebarHeader">
        <h2 style="font-weight: 700; text-transform: uppercase;">FILTRER</h2>
        <button id="closeFilterBtn" class="closeFilterBtn">&times;</button>
    </div>
    
    <form action="<?= BASE_URL ?>shop/favorites.php" method="GET" class="filterForm">
        <div class="filterSection">
            <h3 style="font-size: 0.9rem; margin-bottom: 1rem; font-weight: 700;">CATÉGORIE</h3>
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortOrder); ?>">
            <?php 
                $categories = ['Toutes' => 'Toutes', 'Knitwear' => 'Knitwear', 'Vestes' => 'Vestes', 'Hoodies' => 'Hoodies', 'T-shirts' => 'T-shirts', 'Pantalons' => 'Pantalons', 'Accessoires' => 'Accessoires', 'Autre' => 'Autre'];
                foreach($categories as $catValue => $catLabel):
                    $isChecked = ($categoryFilter === $catValue || ($categoryFilter === "" && $catValue === "Toutes")) ? 'checked' : '';
            ?>
                <label class="filterRadioLabel" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.8rem; cursor: pointer; font-size: 0.85rem; text-transform: uppercase;">
                    <input type="radio" name="category" value="<?= htmlspecialchars($catValue) ?>" <?= $isChecked ?> style="accent-color: #1c1917;">
                    <?= htmlspecialchars($catLabel) ?>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="filterSection">
            <h3 style="font-size: 0.9rem; margin-bottom: 1rem; font-weight: 700;">TAILLES</h3>
            <?php 
                $sizes = ['XS', 'S', 'M', 'L', 'XL'];
                foreach($sizes as $size):
                    $isChecked = in_array($size, $selectedSizes) ? 'checked' : '';
            ?>
                <label class="filterCheckboxLabel" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.8rem; cursor: pointer; font-size: 0.85rem; text-transform: uppercase;">
                    <input type="checkbox" name="sizes[]" value="<?= htmlspecialchars($size) ?>" <?= $isChecked ?> style="accent-color: #1c1917;">
                    <?= htmlspecialchars($size) ?>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btnApplyFilters" style="width: 100%; padding: 1rem; background-color: #1c1917; color: white; border: none; font-weight: 700; cursor: pointer; text-transform: uppercase; margin-top: 2rem;">APPLIQUER LES FILTRES</button>
    </form>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const filterBtn = document.getElementById('openFilterBtn');
        const closeBtn = document.getElementById('closeFilterBtn');
        const sidebar = document.getElementById('filterSidebar');
        const overlay = document.getElementById('filterOverlay');

        function openFilters() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeFilters() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if(filterBtn) filterBtn.addEventListener('click', openFilters);
        if(closeBtn) closeBtn.addEventListener('click', closeFilters);
        if(overlay) overlay.addEventListener('click', closeFilters);
    });
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>
