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

    <section class="articlePresentation">
        <div class="articlePresentationImg" aria-live="polite" aria-atomic="true">
            <button id="prevBtn" class="previousImgArticlePresentation" aria-label="Image précédente">&lt;</button>
            
            <img id="ImgCarousel" src="<?php echo htmlspecialchars($images[0] ?? 'img/default.jpg'); ?>" alt="<?php echo htmlspecialchars($article['name']); ?>">
            
            <button id="nextBtn" class="nextImgArticlePresentation" aria-label="Image suivante">&gt;</button>
        </div>

        <div class="articlePresentationDescription">
            <h2 class="titleArticlePresentation"><?php echo nl2br(htmlspecialchars($article['name'])); ?></h2>
            
            <p class="priceArticlePresentation"><strong><?php echo number_format($article['price'], 2, ',', ' '); ?>€</strong></p>

            <div class="sizeArticlePresentation">
                <button class="sizeArticle" id="sizeArticleS" onclick="selectSize(this)" aria-pressed="false">S</button>
                <button class="sizeArticle" id="sizeArticleM" onclick="selectSize(this)" aria-pressed="false">M</button>
                <button class="sizeArticle" id="sizeArticleL" onclick="selectSize(this)" aria-pressed="false">L</button>
                <button class="sizeArticle" id="sizeArticleXL" onclick="selectSize(this)" aria-pressed="false">XL</button>
            </div>

            <a href="./error.php" class="buyArticlePresentation">Acheter</a>

            <p class="descriptionArticlePresentation"><?php echo nl2br(htmlspecialchars($article['description'])); ?></p>

            <div class="detailArticlePresentation" id="definitions">
                <h3>Détails du produit :</h3>
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
        </div>
    </section>

    <section class="factoryPresentation">

        <video class="videoPresentationFactory" controls>
        <source src="./video/videoPresentationUsine.mp4" type="video/mp4">
        <track 
            src="./video/sous-titre.vtt" 
            kind="subtitles" 
            srclang="fr" 
            label="Français" 
            default>
        Votre navigateur ne supporte pas la balise vidéo.
        </video>

        <div class="DescriptionFactoryPresentation">
            <div class="titleDescriptionFactory">
                <h2>Découvrez notre usine</h2>
                <h3>Réalisation 100% française</h3>
            </div>
            <p>Chez <strong>RESIDUE_</strong>, nous croyons que la qualité naît de la proximité. C'est pourquoi nous avons choisi de 
                confier notre production à un atelier à taille humaine situé en France. Loin des cadences industrielles, 
                chaque pièce est façonnée avec soin par des artisans passionnés, garantissant des finitions irréprochables et 
                un respect total du produit.</p>
        </div>
    </section>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // On récupère le tableau d'images généré par PHP
    const articleImages = <?php echo $imagesJson; ?>;
    let currentImageIndex = 0;

    const imgElement = document.getElementById("ImgCarousel");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");

    if(prevBtn && nextBtn && imgElement && articleImages.length > 0) {
        
        // Clic sur Précédent
        prevBtn.addEventListener("click", () => {
            currentImageIndex = (currentImageIndex > 0) ? currentImageIndex - 1 : articleImages.length - 1;
            imgElement.src = articleImages[currentImageIndex];
        });

        // Clic sur Suivant
        nextBtn.addEventListener("click", () => {
            currentImageIndex = (currentImageIndex < articleImages.length - 1) ? currentImageIndex + 1 : 0;
            imgElement.src = articleImages[currentImageIndex];
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>