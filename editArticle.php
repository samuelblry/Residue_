<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";
$article_id = 0;

// La page doit être accessible en méthode POST pour récupérer l'ID
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Si on vient juste d'arriver sur la page pour modifier
    if (isset($_POST['article_id']) && !isset($_POST['action'])) {
        $article_id = intval($_POST['article_id']);
    }
    // Si on soumet le formulaire de modification
    elseif (isset($_POST['action']) && $_POST['action'] === 'update_article') {
        $article_id = intval($_POST['article_id']);
        $name = mysqli_real_escape_string($mysqli, $_POST['name']);
        $description = mysqli_real_escape_string($mysqli, $_POST['description']);
        $price = floatval($_POST['price']);
        $category = mysqli_real_escape_string($mysqli, $_POST['category']);

        // Mettre à jour (seulement si l'utilisateur est l'auteur ou admin)
        $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 1 : 0;
        
        $checkAuth = $mysqli->query("SELECT id FROM article WHERE id=$article_id AND (author_id=$user_id OR $isAdmin=1)");
        if ($checkAuth->num_rows > 0) {
            $stmt = $mysqli->prepare("UPDATE article SET name=?, description=?, price=?, category=? WHERE id=?");
            $stmt->bind_param("ssdsi", $name, $description, $price, $category, $article_id);
            if ($stmt->execute()) {
                $success = "Article mis à jour avec succès.";
            } else {
                $error = "Erreur lors de la mise à jour de l'article.";
            }
        } else {
            $error = "Vous n'avez pas l'autorisation de modifier cet article.";
            $article_id = 0;
        }
    }
    // Si on supprime l'article
    elseif (isset($_POST['action']) && $_POST['action'] === 'delete_article') {
        $article_id = intval($_POST['article_id']);
        $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 1 : 0;
        
        $checkAuth = $mysqli->query("SELECT id FROM article WHERE id=$article_id AND (author_id=$user_id OR $isAdmin=1)");
        if ($checkAuth->num_rows > 0) {
            $mysqli->query("DELETE FROM article WHERE id=$article_id");
            header("Location: account.php");
            exit();
        } else {
            $error = "Vous n'avez pas l'autorisation de supprimer cet article.";
        }
    }
}

// Si on n'a pas d'article ID (ex. accès via GET ou erreur)
if ($article_id === 0) {
    header("Location: index.php");
    exit();
}

// Récupérer les données de l'article pour pré-remplir le formulaire
$resArticle = $mysqli->query("SELECT * FROM article WHERE id=$article_id");
if ($resArticle->num_rows === 0) {
    header("Location: index.php");
    exit();
}
$article = $resArticle->fetch_assoc();

include 'includes/header.php';
?>

<div class="contactContainer" style="max-width: 800px;">
    <h1 class="titleFormular">Modifier l'article</h1>
    
    <?php if(!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold; background: #fee2e2; padding: 1rem; border: 1px solid #f87171;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($success)): ?>
        <div style="color: #16a34a; margin-bottom: 1rem; font-weight: bold; background: #dcfce7; padding: 1rem; border: 1px solid #4ade80;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form action="editArticle.php" method="POST" class="formularContainer">
        <input type="hidden" name="action" value="update_article">
        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
        
        <fieldset class="contactFieldset">
            <legend>Informations de l'article</legend>
            
            <div class="formGroup">
                <label for="name">Nom de l'article *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($article['name']); ?>" required>
            </div>
            
            <div class="formGroup">
                <label for="category">Catégorie</label>
                <select id="category" name="category" style="width: 100%; padding: 1rem; background-color: transparent; border: 1px solid #a8a29e; font-family: 'Inter', sans-serif; font-size: 1rem; border-radius: 0;">
                    <option value="Knitwear" <?php if($article['category']=='Knitwear') echo 'selected'; ?>>Knitwear</option>
                    <option value="Hoodies" <?php if($article['category']=='Hoodies') echo 'selected'; ?>>Hoodies</option>
                    <option value="T-shirts" <?php if($article['category']=='T-shirts') echo 'selected'; ?>>T-shirts</option>
                    <option value="Pantalons" <?php if($article['category']=='Pantalons') echo 'selected'; ?>>Pantalons</option>
                    <option value="Ceinture" <?php if($article['category']=='Ceinture') echo 'selected'; ?>>Accessoires</option>
                    <option value="Autre" <?php if($article['category']=='Autre') echo 'selected'; ?>>Autre</option>
                </select>
            </div>
            
            <div class="formGroup">
                <label for="price">Prix (€) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($article['price']); ?>" required>
            </div>
            
            <div class="formGroup">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($article['description']); ?></textarea>
            </div>
        </fieldset>
        
        <button type="submit" class="sendBtnFormular">Mettre à jour l'article</button>
    </form>

    <div style="border-top: 2px solid #e7e5e4; padding-top: 2rem; margin-top: 2rem;">
        <form action="editArticle.php" method="POST" onsubmit="return confirm('Supprimer définitivement cet article ?');">
            <input type="hidden" name="action" value="delete_article">
            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
            <button type="submit" style="color: #dc2626; text-decoration: underline; font-weight: bold;">Supprimer l'article</button>
        </form>
        <br>
        <a href="account.php" style="text-decoration: underline;">Retour au compte</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
