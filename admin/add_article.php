<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
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
    
    // Traitement des stocks par taille
    $stocks = isset($_POST['stock']) ? $_POST['stock'] : [];
    
    // On peut avoir une validation basique : un article doit avoir au moins 1 stock dans n'importe quelle taille
    $total_stock = 0;
    foreach($stocks as $qty) {
        $total_stock += intval($qty);
    }

    if (empty($name) || empty($price)) {
        $error = "Veuillez remplir les champs obligatoires (Titre, Prix).";
    } elseif ($total_stock <= 0) {
        $error = "Veuillez définir un stock pour au moins une taille.";
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

            $uploadDir = '../img/articles/';
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
                            $dbUrl = 'img/articles/' . $newFilename;

                            if (move_uploaded_file($tmpName, $destination)) {
                                $isMain = $firstImage ? 1 : 0;
                                $stmtImg = $mysqli->prepare("INSERT INTO image (article_id, url, is_main) VALUES (?, ?, ?)");
                                $stmtImg->bind_param("isi", $article_id, $dbUrl, $isMain);

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
                $quant_xs = isset($stocks['XS']) ? intval($stocks['XS']) : 0;
                $quant_s = isset($stocks['S']) ? intval($stocks['S']) : 0;
                $quant_m = isset($stocks['M']) ? intval($stocks['M']) : 0;
                $quant_l = isset($stocks['L']) ? intval($stocks['L']) : 0;
                $quant_xl = isset($stocks['XL']) ? intval($stocks['XL']) : 0;

                $stmt_stock = $mysqli->prepare("INSERT INTO stock (article_id, quant_xs, quant_s, quant_m, quant_l, quant_xl) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_stock->bind_param("iiiiii", $article_id, $quant_xs, $quant_s, $quant_m, $quant_l, $quant_xl);
                $stmt_stock->execute();
                
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

include BASE_PATH . 'includes/header.php';
?>

<form action="<?= BASE_URL ?>admin/add_article.php" method="POST" enctype="multipart/form-data" class="addArticleContainer">
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
                    <option value="Vestes">VESTES</option>
                    <option value="Hoodies">HOODIES</option>
                    <option value="T-shirts">T-SHIRTS</option>
                    <option value="Pantalons">PANTALONS</option>
                    <option value="Accessoires">ACCESSOIRES</option>
                    <option value="Autre">AUTRE</option>
                </select>
            </div>

            <div class="formSection" style="flex-direction: column; align-items: flex-start;">
                <div class="sectionHeader" style="margin-bottom: 0.5rem; width: 100%;">TAILLES DISPONIBLES</div>
                <div class="sizeOptionsSelect" id="sizeSelector">
                    <button type="button" class="sizeBtn selectable" data-size="XS">XS</button>
                    <button type="button" class="sizeBtn selectable" data-size="S">S</button>
                    <button type="button" class="sizeBtn selectable" data-size="M">M</button>
                    <button type="button" class="sizeBtn selectable" data-size="L">L</button>
                    <button type="button" class="sizeBtn selectable" data-size="XL">XL</button>
                </div>
            </div>

            <div class="formSection" style="flex-direction: column; align-items: flex-start; border-bottom: none;" id="stockSectionContainer">
                <div class="sectionHeader" style="margin-bottom: 1rem; width: 100%;">STOCK PAR TAILLE</div>
                
                <div id="stockLinesWrapper" style="width: 100%;">
                    <!-- Les lignes s'affichent ou se cachent selon les tailles sélectionnées -->
                    <?php 
                    $sizes = ['XS', 'S', 'M', 'L', 'XL'];
                    foreach($sizes as $s): 
                    ?>
                    <div class="stockLine" id="stockLine-<?php echo $s; ?>" style="display: none; margin-bottom: 0.5rem; align-items: center; justify-content: space-between;">
                        <span class="stockSize" style="font-weight: 700; width: 30px;"><?php echo $s; ?></span>
                        <div style="display: flex; align-items: center; background: transparent; border-bottom: 1px solid #737373; width: 80%;">
                            <input type="number" name="stock[<?php echo $s; ?>]" class="editableInput stockInput" value="0" min="0" style="width: 100%; border: none;">
                            <span style="font-size: 0.7rem; color: #737373; margin-left: 5px;">EXEMPLAIRES</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="noSizeSelectedMsg" style="font-size: 0.8rem; color: #d1d5db; font-style: italic;">
                    Sélectionnez d'abord des tailles pour leur attribuer un stock.
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
        <button type="button" class="actionBtn cancelBtn" onclick="window.location.href='<?= BASE_URL ?>auth/account.php'"><span class="crossIcon">✕</span> ANNULER</button>
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

    // 2. Gestion dynamique des Tailles et Stocks
    const sizeBtns = document.querySelectorAll('.sizeBtn.selectable');
    const noSizeMsg = document.getElementById('noSizeSelectedMsg');
    
    // Fonctionnalité de sélection de taille
    sizeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('selected'); // Ajoute/retire la classe .selected (noire)
            
            const size = this.dataset.size;
            const stockLine = document.getElementById('stockLine-' + size);
            const stockInput = stockLine.querySelector('input');
            
            if (this.classList.contains('selected')) {
                // Afficher la ligne de stock associée
                stockLine.style.display = 'flex';
                // Mettre à 1 par défaut quand on sélectionne une nouvelle taille si elle était à 0
                if(stockInput.value === "0" || stockInput.value === "") stockInput.value = 1; 
            } else {
                // Cacher et remettre à 0
                stockLine.style.display = 'none';
                stockInput.value = 0;
            }
            
            // Afficher/Cacher le message global "Sélectionnez une taille"
            const anySelected = document.querySelectorAll('.sizeBtn.selectable.selected').length > 0;
            noSizeMsg.style.display = anySelected ? 'none' : 'block';
        });
    });

    // 3. Gestion des accordéons
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

<?php include BASE_PATH . 'includes/footer.php'; ?>
