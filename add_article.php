<?php
require_once 'includes/db.php';
session_start();

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
    $category = isset($_POST['category']) ? mysqli_real_escape_string($mysqli, $_POST['category']) : 'Autre';
    $author_id = $_SESSION['user_id'];

    if (empty($name) || empty($price)) {
        $error = "Veuillez remplir les champs obligatoires (Titre, Prix).";
    } elseif (!isset($_FILES['images']) || count($_FILES['images']['name']) === 0 || empty($_FILES['images']['name'][0])) {
        $error = "Veuillez joindre au moins une image.";
    } else {
        $mysqli->begin_transaction();

        try {
            $stmt = $mysqli->prepare("INSERT INTO article (name, description, price, author_id, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdis", $name, $description, $price, $author_id, $category);

            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de la création de l'article.");
            }

            $article_id = $stmt->insert_id;

            $uploadDir = 'img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            $firstImage = true;
            $uploadSuccessCount = 0;

            foreach ($_FILES['images']['name'] as $key => $filename) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['images']['tmp_name'][$key];
                    $fileType = $_FILES['images']['type'][$key];
                    $fileSize = $_FILES['images']['size'][$key];
                    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                    if (in_array($fileType, $allowedTypes) && in_array($fileExt, $allowedExtensions)) {
                        if ($fileSize <= 5 * 1024 * 1024) {
                            $newFilename = uniqid('art_' . $article_id . '_') . '.' . $fileExt;
                            $destination = $uploadDir . $newFilename;

                            if (move_uploaded_file($tmpName, $destination)) {
                                $isMain = $firstImage ? 1 : 0;
                                $stmtImg = $mysqli->prepare("INSERT INTO image (article_id, url, is_main) VALUES (?, ?, ?)");
                                $stmtImg->bind_param("isi", $article_id, $destination, $isMain);

                                if ($stmtImg->execute()) {
                                    $firstImage = false;
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
                $success = "L'article a été mis en vente avec succès !";
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

<form action="add_article.php" method="POST" enctype="multipart/form-data" class="addArticleContainer">
    <h1 class="addArticleMainTitle">AJOUTER UN ARTICLE</h1>

    <?php if (!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 2rem; font-weight: bold; text-transform: uppercase;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div style="color: #16a34a; margin-bottom: 2rem; font-weight: bold; text-transform: uppercase;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="addArticleGrid">
        <!-- Colonne Gauche : Upload Image (Grille) -->
        <div class="addArticleLeft">
            <input type="file" id="articleImage" name="images[]" multiple class="hiddenInput" accept="image/png, image/jpeg, image/webp">
            
            <div class="articleImagesGrid" id="previewGrid">
                <!-- Zone de dépôt / Bouton d'ajout initial -->
                <div class="gridImageWrapper imageDropzone" id="dropzonePrimary">
                    <div class="dropzoneContent">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                            <line x1="12" y1="11" x2="12" y2="17"></line>
                            <line x1="9" y1="14" x2="15" y2="14"></line>
                        </svg>
                    </div>
                </div>
                <!-- Placeholder initial vide -->
                <div class="gridImageWrapper placeholderImage" id="initialPlaceholder"></div>
            </div>
        </div>

        <!-- Colonne Droite : Formulaire -->
        <div class="addArticleRight">
            
            <div class="editGroup">
                <!-- Inputs mockés en "plain text" -->
                <input type="text" name="name" class="editableInput titleInput" placeholder="TITRE DE L'ARTICLE" required>
                <span class="editIcon">✎</span>
            </div>
            <div class="editGroup priceWrapper">
                <!-- Prix avec EUR fixe à côté -->
                <input type="number" name="price" step="0.01" class="editableInput priceInput" placeholder="XX" required>
                <span class="currencySuffix">EUR</span>
            </div>

            <div class="vendorInfo">
                VENDU PAR <strong><?php echo strtoupper(htmlspecialchars($_SESSION['username'])); ?></strong>
            </div>

            <div class="formSection" style="flex-direction: column; align-items: flex-start; border-bottom: none;">
                <div class="sectionHeader" style="margin-bottom: 0.5rem; width: 100%;">CATÉGORIE</div>
                <select name="category" class="categorySelect" required>
                    <option value="" disabled selected>SÉLECTIONNER</option>
                    <option value="Knitwear">KNITWEAR</option>
                    <option value="Hoodies">HOODIES</option>
                    <option value="T-shirts">T-SHIRTS</option>
                    <option value="Pantalons">PANTALONS</option>
                    <option value="Ceinture">ACCESSOIRES</option>
                    <option value="Autre">AUTRE</option>
                </select>
            </div>

            <div class="formSection">
                <div class="sectionHeader">TAILLE</div>
                <button type="button" class="addBtnSquare">+</button>
            </div>

            <div class="formSection">
                <div class="sectionHeader">STOCK</div>
                <!-- Une ligne par taille comme demandé -->
                <div class="stockLine">
                    <span class="stockSize">S</span>
                    <input type="text" class="editableInput stockInput" value="10 EXEMPLAIRES">
                    <span class="editIcon">✎</span>
                </div>
                <div class="stockLine">
                    <span class="stockSize">M</span>
                    <input type="text" class="editableInput stockInput" value="10 EXEMPLAIRES">
                    <span class="editIcon">✎</span>
                </div>
                <div class="stockLine">
                    <span class="stockSize">L</span>
                    <input type="text" class="editableInput stockInput" value="10 EXEMPLAIRES">
                    <span class="editIcon">✎</span>
                </div>
            </div>

            <!-- Placeholders gris -->
            <div class="actionButtonsPlaceholder">
                <div class="greyPlaceholder"></div>
                <div class="greyPlaceholder"></div>
            </div>

            <!-- Accordéons -->
            <div class="accordion">
                <div class="accordionHeader">
                    <span>DÉTAILS DU PRODUIT</span>
                    <span class="accordionIcon">—</span>
                </div>
                <div class="accordionContent open">
                    <div class="editGroup">
                        <textarea name="description" class="editableInput descInput" placeholder="ENTREZ LA DESCRIPTION DE VÔTRE PRODUIT ICI"></textarea>
                        <span class="editIcon topAlign">✎</span>
                    </div>
                </div>
            </div>

            <div class="accordion">
                <div class="accordionHeader">
                    <span>CHARTE DES TAILLES</span>
                    <span class="accordionIcon">—</span>
                </div>
                <div class="accordionContent open">
                    <div class="editGroup">
                        <textarea class="editableInput descInput" placeholder="ENTREZ LES MESURATIONS DE VÔTRE ARTICLE ICI"></textarea>
                        <span class="editIcon topAlign">✎</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer Actions : Annuler + Publier en vert -->
    <div class="addArticleActions">
        <button type="button" class="actionBtn cancelBtn" onclick="window.location.href='account.php'"><span class="crossIcon">✕</span> ANNULER</button>
        <button type="submit" class="actionBtn publishBtn">PUBLIER</button>
    </div>
</form>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Upload d'image : Gestion multi-images dynamique
    const previewGrid = document.getElementById('previewGrid');
    const fileInput = document.getElementById('articleImage');
    
    // Contenu HTML du bouton "Ajouter une image"
    const addMoreHTML = '<div class="dropzoneContent"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline><line x1="12" y1="11" x2="12" y2="17"></line><line x1="9" y1="14" x2="15" y2="14"></line></svg></div>';

    let allFiles = []; // Pour stocker tous les fichiers accumulés

    // Clic initial sur la zone principale
    document.getElementById('dropzonePrimary').addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function() {
        if (!this.files || this.files.length === 0) return;

        // Ajouter les nouveaux fichiers au tableau existant
        Array.from(this.files).forEach(file => {
            allFiles.push(file);
        });

        updateFileInput();
        renderGrid();
    });

    function updateFileInput() {
        const dt = new DataTransfer();
        allFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    }

    function renderGrid() {
        previewGrid.innerHTML = '';

        allFiles.forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'gridImageWrapper';
            div.draggable = true;
            div.dataset.index = index;
            div.style.cursor = 'grab';
            div.style.position = 'relative'; // Pour positionner le bouton de suppression
            
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            div.appendChild(img);

            // Bouton de suppression (X)
            const removeBtn = document.createElement('button');
            removeBtn.innerHTML = '✕';
            removeBtn.className = 'removeImageBtn';
            removeBtn.type = 'button';
            removeBtn.addEventListener('click', function(e) {
                e.stopPropagation(); // Évite de déclencher le drag ou le clic éventuel derrière
                allFiles.splice(index, 1);
                updateFileInput();
                renderGrid();
            });
            div.appendChild(removeBtn);

            // Drag Events
            div.addEventListener('dragstart', handleDragStart);
            div.addEventListener('dragover', handleDragOver);
            div.addEventListener('drop', handleDrop);
            div.addEventListener('dragenter', handleDragEnter);
            div.addEventListener('dragleave', handleDragLeave);
            
            previewGrid.appendChild(div);
        });

        // Bouton d'ajout à la fin
        const addMoreDiv = document.createElement('div');
        addMoreDiv.className = 'gridImageWrapper imageDropzone';
        addMoreDiv.style.cursor = 'pointer';
        addMoreDiv.innerHTML = addMoreHTML;
        addMoreDiv.addEventListener('click', function() {
            fileInput.click();
        });
        previewGrid.appendChild(addMoreDiv);

        // Si la page est vidée, remettre le placeholder
        if (allFiles.length === 0) {
            const placeholder = document.createElement('div');
            placeholder.className = 'gridImageWrapper placeholderImage';
            previewGrid.appendChild(placeholder);
        }
    }

    let dragStartIndex;

    function handleDragStart(e) {
        dragStartIndex = +this.dataset.index;
        e.dataTransfer.effectAllowed = 'move';
        setTimeout(() => this.style.opacity = '0.5', 0);
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDragEnter(e) {
        this.style.border = '2px dashed #000';
    }

    function handleDragLeave(e) {
        this.style.border = 'none';
    }

    function handleDrop(e) {
        e.stopPropagation();
        this.style.border = 'none';
        const dragEndIndex = +this.dataset.index;
        
        if (dragStartIndex !== dragEndIndex && !isNaN(dragEndIndex)) {
            const movedFile = allFiles.splice(dragStartIndex, 1)[0];
            allFiles.splice(dragEndIndex, 0, movedFile);
            updateFileInput();
            renderGrid();
        }
        return false;
    }

    document.addEventListener('dragend', (e) => {
        if (e.target.classList && e.target.classList.contains('gridImageWrapper')) {
            e.target.style.opacity = '1';
        }
    });

    // 2. Gestion des accordéons
    const accordions = document.querySelectorAll('.accordionHeader');
    accordions.forEach(acc => {
        acc.addEventListener('click', function() {
            const content = this.nextElementSibling;
            if (content.classList.contains('open')) {
                content.classList.remove('open');
                content.style.display = 'none';
            } else {
                content.classList.add('open');
                content.style.display = 'block';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
