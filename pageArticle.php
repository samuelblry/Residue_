<?php 
require_once 'includes/db.php'; 

// 1. On récupère l'ID de l'article dans l'URL (ex: pageArticle.php?id=1)
// Si aucun ID n'est passé, on affiche l'article 1 par défaut
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// 2. On récupère les infos de l'article (Titre, Prix, Description)
$queryArticle = $mysqli->query("SELECT * FROM Article WHERE id = $article_id");

// Si l'article n'existe pas, on renvoie vers l'accueil
if ($queryArticle->num_rows === 0) {
    header("Location: index.php");
    exit();
}
$article = $queryArticle->fetch_assoc();

// 3. On récupère TOUTES les images liées à cet article
// On met l'image principale (is_main = 1) en premier
$queryImages = $mysqli->query("SELECT url FROM Image WHERE article_id = $article_id ORDER BY is_main DESC, id ASC");

$images = [];
while ($img = $queryImages->fetch_assoc()) {
    $images[] = $img['url'];
}

// On encode les images en JSON pour pouvoir les donner au JavaScript du carrousel
$imagesJson = json_encode($images);

include 'includes/header.php'; 
?>

    <section class="articlePageContainer">
        <!-- Colonne de gauche : Grille d'images -->
        <div class="articleImagesGrid">
            <?php 
            if (!empty($images)) {
                // If there are exactly 5 images, the layout in the image looks like 2x2 + 1 bottom left.
                // It can be just a flex or grid.
                foreach ($images as $imgUrl) {
                    echo '<div class="gridImageWrapper"><img src="' . htmlspecialchars($imgUrl) . '" alt="' . htmlspecialchars($article['name']) . '"></div>';
                }
                // To match the mockup visually, we might need placeholders if not enough images
                $missing = 5 - count($images);
                if ($missing > 0 && count($images) > 0) {
                    for($i=0; $i<$missing; $i++) {
                        echo '<div class="gridImageWrapper placeholderImage"></div>';
                    }
                }
            } else {
                for($i=0; $i<5; $i++) {
                    echo '<div class="gridImageWrapper placeholderImage"></div>';
                }
            }
            ?>
        </div>

        <!-- Colonne de droite : Détails collants -->
        <div class="articleDetailsStickyContainer">
            <div class="articleDetailsSticky">
                <h1 class="productTitle"><?php echo nl2br(htmlspecialchars($article['name'])); ?></h1>
                <p class="productPrice"><?php echo number_format($article['price'], 2, ',', ' '); ?> EUR</p>
                
                <p class="productVendor">VENDU PAR RESIDUE_</p>

                <div class="productOptions">
                    <p class="optionLabel">COULEUR : NOIR</p>
                    <div class="colorSwatches">
                        <button class="swatch white selected" aria-label="Blanc"></button>
                        <button class="swatch navy" aria-label="Bleu marine"></button>
                        <button class="swatch pink" aria-label="Rose"></button>
                        <button class="swatch darkred" aria-label="Bordeaux"></button>
                        <button class="swatch black" aria-label="Noir"></button>
                    </div>

                    <p class="optionLabel" style="margin-top: 2rem;">TAILLE</p>
                    <div class="sizeOptions">
                        <button class="sizeBtn">XS</button>
                        <button class="sizeBtn">S</button>
                        <button class="sizeBtn">M</button>
                        <button class="sizeBtn">L</button>
                        <button class="sizeBtn">XL</button>
                    </div>
                </div>

                <div class="productActions">
                    <form action="cart.php" method="POST" style="width: 100%; margin-bottom: 0.5rem;">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btnAddToCart">AJOUTER AU PANIER</button>
                    </form>
                    <button class="btnAddToFav">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg> 
                        AJOUTER AUX FAVORIS
                    </button>
                </div>

                <div class="productAccordions">
                    <details class="accordion">
                        <summary>DÉTAILS DU PRODUIT <span class="icon">+</span></summary>
                        <div class="accordionContent">
                            <p><?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                            <dl>
                                <dt>Composition</dt><dd>100 % coton</dd>
                                <dt>Texture</dt><dd>Matières épaisses</dd>
                                <dt>Coupe</dt><dd>Oversize et carrée</dd>
                                <dt>Finition</dt><dd>Logo brodé</dd>
                                <dt>Couleur principale</dt><dd>Noir</dd>
                                <dt>Disponibilité</dt><dd>Prêt à expédier</dd>
                                <dt>Livraison</dt><dd>Internationale</dd>
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

    <!-- Section Suggestions -->
    <section class="suggestionsSection">
        <h3 class="suggestionsTitle">SUGGESTIONS</h3>
        <div class="suggestionsGrid">
            <?php 
            for($i=0; $i<5; $i++): 
            ?>
            <a href="#" class="suggestionCardFake">
                <div class="suggestionImagePlaceholder"></div>
                <div class="suggestionInfo">
                    <p class="suggestionName">TITRE DE L'ARTICLE</p>
                    <p class="suggestionPrice">XX EUR</p>
                    <div class="suggestionColors">
                        <span class="dot" style="background-color: darkred;"></span>
                        <span class="dot" style="background-color: navy;"></span>
                        <span class="dot" style="background-color: black;"></span>
                    </div>
                </div>
            </a>
            <?php endfor; ?>
        </div>
    </section>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Gestion des tailles
    const sizeBtns = document.querySelectorAll('.sizeBtn');
    sizeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sizeBtns.forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
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
</script>

<?php include 'includes/footer.php'; ?>