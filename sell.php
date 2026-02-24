<?php
require_once 'includes/db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($mysqli, $_POST['name']);
    $description = mysqli_real_escape_string($mysqli, $_POST['description']);
    $price = floatval($_POST['price']);
    $category = mysqli_real_escape_string($mysqli, $_POST['category']);
    $imageUrl = mysqli_real_escape_string($mysqli, $_POST['image_url']);
    $author_id = $_SESSION['user_id'];

    if (empty($name) || empty($price) || empty($imageUrl)) {
        $error = "Veuillez remplir les champs obligatoires (Nom, Prix, URL de l'image).";
    } else {
        // Insérer l'article
        $stmt = $mysqli->prepare("INSERT INTO article (name, description, price, author_id, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $name, $description, $price, $author_id, $category);
        
        if ($stmt->execute()) {
            $article_id = $stmt->insert_id;
            
            // Insérer l'image principale
            $stmtImg = $mysqli->prepare("INSERT INTO image (article_id, url, is_main) VALUES (?, ?, 1)");
            $stmtImg->bind_param("is", $article_id, $imageUrl);
            $stmtImg->execute();

            $success = "L'article a été mis en vente avec succès !";
        } else {
            $error = "Erreur lors de la mise en vente de l'article.";
        }
    }
}
include 'includes/header.php';
?>

<div class="contactContainer">
    <h1 class="titleFormular">Mettre en vente</h1>
    <p class="subtitleFormular">Ajoutez un nouvel article à la boutique RESIDUE_</p>

    <?php if(!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($success)): ?>
        <div style="color: #16a34a; margin-bottom: 1rem; font-weight: bold;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form action="sell.php" method="POST" class="formularContainer">
        <fieldset class="contactFieldset">
            <legend>Détails de l'article</legend>
            <div class="formGroup">
                <label for="name">Nom de l'article *</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="formGroup">
                <label for="category">Catégorie</label>
                <select id="category" name="category" style="width: 100%; padding: 1rem; background-color: transparent; border: 1px solid #a8a29e; font-family: 'Inter', sans-serif; font-size: 1rem; border-radius: 0;">
                    <option value="Knitwear">Knitwear</option>
                    <option value="Hoodies">Hoodies</option>
                    <option value="T-shirts">T-shirts</option>
                    <option value="Pantalons">Pantalons</option>
                    <option value="Ceinture">Accessoires</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>
            <div class="formGroup">
                <label for="price">Prix (€) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            <div class="formGroup">
                <label for="description">Description (Optionnel)</label>
                <textarea id="description" name="description" rows="5"></textarea>
            </div>
            <div class="formGroup">
                <label for="image_url">URL de l'image (Lien vers l'image) *</label>
                <input type="text" id="image_url" name="image_url" placeholder="Ex: img/nouveau.webp" required>
            </div>
        </fieldset>
        
        <button type="submit" class="sendBtnFormular">Mettre en vente</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
