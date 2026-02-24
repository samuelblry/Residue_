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
    $author_id = $_SESSION['user_id'];

    // Vérification des champs de base
    if (empty($name) || empty($price)) {
        $error = "Veuillez remplir les champs obligatoires (Nom, Prix).";
    } elseif (!isset($_FILES['images']) || count($_FILES['images']['name']) === 0 || empty($_FILES['images']['name'][0])) {
        $error = "Veuillez joindre au moins une image.";
    } else {
        $mysqli->begin_transaction();

        try {
            // Insérer l'article
            $stmt = $mysqli->prepare("INSERT INTO article (name, description, price, author_id, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdis", $name, $description, $price, $author_id, $category);

            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de la création de l'article.");
            }

            $article_id = $stmt->insert_id;

            // Dossier de destination des images
            $uploadDir = 'img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Extensions autorisées
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            $firstImage = true;
            $uploadSuccessCount = 0;

            foreach ($_FILES['images']['name'] as $key => $filename) {
                // S'il n'y a pas d'erreur de transfert pour ce fichier
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['images']['tmp_name'][$key];
                    $fileType = $_FILES['images']['type'][$key];
                    $fileSize = $_FILES['images']['size'][$key]; // En octets

                    // Vérifier l'extension et le type MIME
                    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                    if (in_array($fileType, $allowedTypes) && in_array($fileExt, $allowedExtensions)) {
                        // Limite de taille (ex: 5 Mo)
                        if ($fileSize <= 5 * 1024 * 1024) {
                            // Générer un nom de fichier unique
                            $newFilename = uniqid('art_' . $article_id . '_') . '.' . $fileExt;
                            $destination = $uploadDir . $newFilename;

                            // Déplacer le fichier
                            if (move_uploaded_file($tmpName, $destination)) {
                                // Enregistrer dans la base de données
                                $isMain = $firstImage ? 1 : 0;
                                $stmtImg = $mysqli->prepare("INSERT INTO image (article_id, url, is_main) VALUES (?, ?, ?)");
                                $stmtImg->bind_param("isi", $article_id, $destination, $isMain);

                                if ($stmtImg->execute()) {
                                    $firstImage = false; // Les suivantes ne seront plus l'image principale
                                    $uploadSuccessCount++;
                                }
                            }
                        } else {
                            throw new Exception("L'image " . htmlspecialchars($filename) . " dépasse la taille maximale autorisée (5 Mo).");
                        }
                    } else {
                        throw new Exception("Le format du fichier " . htmlspecialchars($filename) . " n'est pas autorisé. (JPG, PNG, WEBP uniquement)");
                    }
                }
            }

            if ($uploadSuccessCount > 0) {
                $mysqli->commit();
                $success = "L'article a été mis en vente avec succès avec " . $uploadSuccessCount . " image(s) !";
            } else {
                throw new Exception("Aucune image n'a pu être téléchargée valide.");
            }

        } catch (Exception $e) {
            $mysqli->rollback();
            $error = $e->getMessage();
        }
    }
}
include 'includes/header.php';
?>

<div class="contactContainer">
    <h1 class="titleFormular">Mettre en vente</h1>
    <p class="subtitleFormular">Ajoutez un nouvel article à la boutique RESIDUE_</p>

    <?php if (!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div style="color: #16a34a; margin-bottom: 1rem; font-weight: bold;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Ajout de l'enctype multipart/form-data pour l'upload de fichiers -->
    <form action="sell.php" method="POST" enctype="multipart/form-data" class="formularContainer">
        <fieldset class="contactFieldset">
            <legend>Détails de l'article</legend>
            <div class="formGroup">
                <label for="name">Nom de l'article *</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="formGroup">
                <label for="category">Catégorie</label>
                <select id="category" name="category"
                    style="width: 100%; padding: 1rem; background-color: transparent; border: 1px solid #a8a29e; font-family: 'Inter', sans-serif; font-size: 1rem; border-radius: 0;">
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
                <!-- Remplacé le champ texte par un champ fichier (multiple) -->
                <label for="images">Images de l'article * (Supporte plusieurs fichiers : ctrl+clic ou maj+clic)</label>
                <input type="file" id="images" name="images[]" accept="image/png, image/jpeg, image/webp" multiple
                    required>
                <small style="color: #6b7280; margin-top: 0.5rem; display: block;">Une fois sélectionnées, les images
                    seront copiées dans le dossier img/. La première sélectionnée sera l'image principale.</small>
            </div>
        </fieldset>

        <button type="submit" class="sendBtnFormular">Mettre en vente</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>