<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';

// 1. On récupère l'ID de l'article dans l'URL (ex: pageArticle.php?id=1)
// Si aucun ID n'est passé, on affiche l'article 1 par défaut
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// 2. On récupère les infos de l'article (Titre, Prix, Description) avec le vendeur
$queryArticle = $mysqli->query("
    SELECT a.*, u.username as author_name, u.role as author_role 
    FROM Article a 
    LEFT JOIN User u ON a.author_id = u.id 
    WHERE a.id = $article_id
");

// Si l'article n'existe pas, on renvoie vers l'accueil
if ($queryArticle->num_rows === 0) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}
$article = $queryArticle->fetch_assoc();

// Déterminer le nom du vendeur à afficher
$vendorName = "RESIDUE_";
$vendorLink = "";
if (!empty($article['author_name']) && $article['author_role'] !== 'admin') {
    $vendorName = strtoupper($article['author_name']);
    $vendorLink = BASE_URL . 'auth/account.php?id=' . $article['author_id'];
}

// 2b. On récupère les stocks pour cet article
$queryStock = $mysqli->query("SELECT * FROM stock WHERE article_id = $article_id");
$stock = $queryStock->fetch_assoc();
if (!$stock) {
    // S'il n'y a pas d'entrée de stock, on initialise à 0 pour éviter des erreurs
    $stock = ['quant_xs' => 0, 'quant_s' => 0, 'quant_m' => 0, 'quant_l' => 0, 'quant_xl' => 0];
}

// 3. On récupère TOUTES les images liées à cet article
// On met l'image principale (is_main = 1) en premier
$queryImages = $mysqli->query("SELECT url FROM Image WHERE article_id = $article_id ORDER BY is_main DESC, id ASC");

$images = [];
while ($img = $queryImages->fetch_assoc()) {
    $images[] = $img['url'];
}

// On encode les images en JSON pour pouvoir les donner au JavaScript du carrousel
$imagesJson = json_encode($images);

// Vérifier si l'article est en favori
$isFav = false;
if (isset($_SESSION['user_id'])) {
    $favCheck = $mysqli->query("SELECT id FROM favorite WHERE user_id = " . intval($_SESSION['user_id']) . " AND article_id = $article_id");
    if ($favCheck && $favCheck->num_rows > 0) {
        $isFav = true;
    }
}

include BASE_PATH . 'includes/header.php';
?>

<section class="articlePageContainer">
    <!-- Colonne de gauche : Grille d'images -->
    <div class="articleImagesGrid">
        <?php
        if (!empty($images)) {
            foreach ($images as $imgUrl) {
                echo '<div class="gridImageWrapper"><img src="' . BASE_URL . htmlspecialchars($imgUrl) . '" alt="' . htmlspecialchars($article['name']) . '"></div>';
            }
        } else {
            echo '<div class="gridImageWrapper placeholderImage"></div>';
        }
        ?>
    </div>

    <!-- Colonne de droite : Détails collants -->
    <div class="articleDetailsStickyContainer">
        <div class="articleDetailsSticky">
            <h1 class="productTitle"><?php echo nl2br(htmlspecialchars($article['name'])); ?></h1>
            <p class="productPrice"><?php echo number_format($article['price'], 2, ',', ' '); ?> EUR</p>

            <p class="productVendor">VENDU PAR 
                <?php if ($vendorLink): ?>
                    <a href="<?php echo htmlspecialchars($vendorLink); ?>" style="text-decoration: underline; color: inherit;"><?php echo htmlspecialchars($vendorName); ?></a>
                <?php else: ?>
                    <?php echo htmlspecialchars($vendorName); ?>
                <?php endif; ?>
            </p>

            <div class="productOptions">
                <p class="optionLabel">TAILLE</p>
                <div class="sizeOptions">
                    <?php
                    $sizes = [
                        'XS' => $stock['quant_xs'],
                        'S'  => $stock['quant_s'],
                        'M'  => $stock['quant_m'],
                        'L'  => $stock['quant_l'],
                        'XL' => $stock['quant_xl']
                    ];
                    foreach ($sizes as $sizeLabel => $qty) {
                        $disabledAttr = ($qty > 0) ? '' : 'disabled="disabled"';
                        $classInfo = ($qty > 0) ? 'sizeBtn' : 'sizeBtn out-of-stock';
                        echo '<button class="' . $classInfo . '" data-size="' . $sizeLabel . '" ' . $disabledAttr . '>' . $sizeLabel . '</button>';
                    }
                    ?>
                </div>
            </div>

            <div class="productActions">
                <form action="<?= BASE_URL ?>shop/cart.php" method="POST" id="addToCartForm" style="width: 100%; margin-bottom: 0.5rem;" onsubmit="return validateSizeSelection();">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="size" id="selectedSizeInput" value="">
                    
                    <button type="submit" class="btnAddToCart">AJOUTER AU PANIER</button>
                </form>
                <button type="button" class="btnAddToFav" onclick="toggleFavoriteState(event, <?php echo $article['id']; ?>)">
                    <?php if ($isFav): ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" stroke-width="2" class="is-favorite">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        RETIRED DES FAVORIS
                    <?php else: ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        AJOUTER AUX FAVORIS
                    <?php endif; ?>
                </button>
            </div>

            <div class="productAccordions">
                <details class="accordion">
                    <summary>DÉTAILS DU PRODUIT <span class="icon">+</span></summary>
                    <div class="accordionContent">
                        <p><?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                        <dl>
                            <dt>Composition</dt>
                            <dd>100 % coton</dd>
                            <dt>Texture</dt>
                            <dd>Matières épaisses</dd>
                            <dt>Coupe</dt>
                            <dd>Oversize et carrée</dd>
                            <dt>Finition</dt>
                            <dd>Logo brodé</dd>
                            <dt>Couleur principale</dt>
                            <dd>Noir</dd>
                            <dt>Disponibilité</dt>
                            <dd>Prêt à expédier</dd>
                            <dt>Livraison</dt>
                            <dd>Internationale</dd>
                        </dl>
                    </div>
                </details>

                <details class="accordion">
                    <summary>CHARTE DES TAILLES <span class="icon">+</span></summary>
                    <div class="accordionContent">
                        <p>Guide des tailles non disponible pour le moment.</p>
                    </div>
                </details>
            </div>
        </div>
    </div>
</section>

<?php
// Récupérer 5 articles au hasard pour les suggestions, en excluant l'article courant
$suggestions = [];
$suggestQuery = "SELECT article.id, article.name, article.price, 
                        (SELECT url FROM image WHERE article_id = article.id AND is_main = 1 LIMIT 1) as image_url 
                 FROM article 
                 WHERE id != $article_id
                 ORDER BY RAND() LIMIT 5";
$suggestResult = $mysqli->query($suggestQuery);
if ($suggestResult && $suggestResult->num_rows > 0) {
    while ($row = $suggestResult->fetch_assoc()) {
        $suggestions[] = $row;
    }
}
?>
<?php
// Fetch user favorites for suggestions toggle state
$userFavorites = [];
if (isset($_SESSION['user_id'])) {
    $favQuery = $mysqli->query("SELECT article_id FROM favorite WHERE user_id = " . intval($_SESSION['user_id']));
    while ($favRow = $favQuery->fetch_assoc()) {
        $userFavorites[] = $favRow['article_id'];
    }
}
?>
<!-- Section Suggestions -->
<section class="suggestionsSection">
    <h3 class="suggestionsTitle">SUGGESTIONS</h3>
    <div class="suggestionsGrid">
        <?php foreach ($suggestions as $sugg): ?>
            <div class="suggestionCardFake">
                <a href="<?= BASE_URL ?>shop/pageArticle.php?id=<?php echo $sugg['id']; ?>" class="suggestionImageLink">
                    <?php if (!empty($sugg['image_url'])): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($sugg['image_url']) ?>" class="suggestionImage" alt="">
                    <?php else: ?>
                        <div class="suggestionImagePlaceholder"></div>
                    <?php endif; ?>
                </a>
                <div class="suggestionInfo">
                    <div>
                        <a href="<?= BASE_URL ?>shop/pageArticle.php?id=<?php echo $sugg['id']; ?>" style="text-decoration: none; color: inherit;">
                            <p class="suggestionName"><?php echo htmlspecialchars($sugg['name']); ?></p>
                        </a>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <span class="suggestionPrice"><?php echo number_format($sugg['price'], 2, ',', ' '); ?> EUR</span>
                        <?php $isFavSugg = in_array($sugg['id'], $userFavorites); ?>
                        <button type="button" class="favoriteBtn" data-id="<?php echo $sugg['id']; ?>" onclick="toggleFavorite(event, <?php echo $sugg['id']; ?>)">
                            <?php if ($isFavSugg): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="heart-icon is-favorite"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            <?php else: ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="heart-icon"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            <?php endif; ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
    function validateSizeSelection() {
        const selectedSizeInput = document.getElementById('selectedSizeInput');
        if (!selectedSizeInput.value) {
            alert('Veuillez sélectionner une taille avant d\'ajouter au panier.');
            return false;
        }
        return true;
    }

    document.addEventListener("DOMContentLoaded", () => {
        // Gestion des tailles
        const sizeBtns = document.querySelectorAll('.sizeBtn:not(.out-of-stock)');
        const selectedSizeInput = document.getElementById('selectedSizeInput');
        
        sizeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                sizeBtns.forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                selectedSizeInput.value = btn.dataset.size;
            });
        });

        // Gestion de l'icône de l'accordéon (+ / -)
        const accordions = document.querySelectorAll('.accordion');
        accordions.forEach(acc => {
            acc.addEventListener('click', (e) => {
                // on details click, toggle icon. Details toggles after click event.
                // Using a short timeout to check open state after toggle
                setTimeout(() => {
                    const icon = acc.querySelector('.icon');
                    if (icon) {
                        icon.textContent = acc.open ? '-' : '+';
                    }
                }, 10);
            });
        });
    });
    function toggleFavoriteState(event, articleId) {
        event.preventDefault();
        
        const btn = event.currentTarget || event.target.closest('button');
        if (!btn) return;
        const icon = btn.querySelector('svg');

        const baseUrl = '<?= BASE_URL ?>';
        
        fetch(baseUrl + 'api/toggle_favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ article_id: articleId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.action === 'added') {
                    icon.classList.add('is-favorite');
                    btn.innerHTML = btn.innerHTML.replace('AJOUTER AUX FAVORIS', 'RETIRER DES FAVORIS');
                } else if (data.action === 'removed') {
                    icon.classList.remove('is-favorite');
                    btn.innerHTML = btn.innerHTML.replace('RETIRER DES FAVORIS', 'AJOUTER AUX FAVORIS');
                }
            } else {
                if (data.message === 'Non connecté') {
                    window.location.href = baseUrl + 'auth/login.php';
                } else {
                    console.error("Error toggling favorite:", data.message);
                }
            }
        })
        .catch(error => {
            console.error("Fetch error:", error);
        });
    }
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>