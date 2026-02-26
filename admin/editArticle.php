<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";
$article_id = 0;


if (isset($_POST['article_id'])) {
    $article_id = intval($_POST['article_id']);
} elseif (isset($_GET['id'])) {
    $article_id = intval($_GET['id']);
}

if ($article_id === 0) {
    header("Location: " . BASE_URL . "auth/account.php");
    exit();
}

$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 1 : 0;


$checkAuth = $mysqli->query("SELECT * FROM article WHERE id=$article_id AND (author_id=$user_id OR $isAdmin=1)");
if ($checkAuth->num_rows === 0) {
    header("Location: " . BASE_URL . "auth/account.php");
    exit();
}
$article = $checkAuth->fetch_assoc();


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    
    if ($_POST['action'] === 'delete_article') {
        $mysqli->query("DELETE FROM article WHERE id=$article_id");
        header("Location: " . BASE_URL . "auth/account.php");
        exit();
    }
    
    
    if ($_POST['action'] === 'update_article') {
        $name = mysqli_real_escape_string($mysqli, $_POST['name']);
        $description = mysqli_real_escape_string($mysqli, $_POST['description']);
        $price = floatval($_POST['price']);
        $category = isset($_POST['category']) ? mysqli_real_escape_string($mysqli, $_POST['category']) : $article['category'];
        
        
        $stmt = $mysqli->prepare("UPDATE article SET name=?, description=?, price=?, category=? WHERE id=?");
        $stmt->bind_param("ssdsi", $name, $description, $price, $category, $article_id);
        
        if ($stmt->execute()) {
            $success = "L'article a été mis à jour avec succès !";
            
            $article['name'] = $name;
            $article['description'] = $description;
            $article['price'] = $price;
            $article['category'] = $category;
        } else {
            $error = "Erreur lors de la mise à jour des infos principales.";
        }

        
        $stocks = isset($_POST['stock']) ? $_POST['stock'] : [];
        $quant_xs = isset($stocks['XS']) ? intval($stocks['XS']) : 0;
        $quant_s = isset($stocks['S']) ? intval($stocks['S']) : 0;
        $quant_m = isset($stocks['M']) ? intval($stocks['M']) : 0;
        $quant_l = isset($stocks['L']) ? intval($stocks['L']) : 0;
        $quant_xl = isset($stocks['XL']) ? intval($stocks['XL']) : 0;
        
        
        $checkStock = $mysqli->query("SELECT id FROM stock WHERE article_id=$article_id");
        if ($checkStock->num_rows > 0) {
            $stmt_stock = $mysqli->prepare("UPDATE stock SET quant_xs=?, quant_s=?, quant_m=?, quant_l=?, quant_xl=? WHERE article_id=?");
            $stmt_stock->bind_param("iiiiii", $quant_xs, $quant_s, $quant_m, $quant_l, $quant_xl, $article_id);
            $stmt_stock->execute();
        } else {
            $stmt_stock = $mysqli->prepare("INSERT INTO stock (article_id, quant_xs, quant_s, quant_m, quant_l, quant_xl) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_stock->bind_param("iiiiii", $article_id, $quant_xs, $quant_s, $quant_m, $quant_l, $quant_xl);
            $stmt_stock->execute();
        }

        
        if (!empty($_POST['deleted_images'])) {
            $deleted_ids = explode(',', $_POST['deleted_images']);
            foreach ($deleted_ids as $del_id) {
                $del_id = intval($del_id);
                if ($del_id > 0) {
                    $imgRes = $mysqli->query("SELECT url FROM image WHERE id=$del_id AND article_id=$article_id");
                    if ($imgRes->num_rows > 0) {
                        $imgRow = $imgRes->fetch_assoc();
                        if (file_exists($imgRow['url'])) {
                            unlink($imgRow['url']); 
                        }
                    }
                }
            }
        }

        
        $mysqli->query("DELETE FROM image WHERE article_id=$article_id");

        
        $finalOrder = [];
        if (!empty($_POST['final_image_order'])) {
            
            $finalOrder = explode(',', $_POST['final_image_order']);
        }

        $isMainAssigned = false;
        
        $uploadDir = '../img/articles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($finalOrder as $item) {
            $isMain = !$isMainAssigned ? 1 : 0;
            
            if (strpos($item, 'existing:') === 0) {
                $url = substr($item, 9);
                $stmtImg = $mysqli->prepare("INSERT INTO image (article_id, url, is_main) VALUES (?, ?, ?)");
                $stmtImg->bind_param("isi", $article_id, $url, $isMain);
                $stmtImg->execute();
                $isMainAssigned = true;
            } elseif (strpos($item, 'new:') === 0) {
                $fileIndex = intval(substr($item, 4));
                if (isset($_FILES['images']) && $_FILES['images']['error'][$fileIndex] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['images']['tmp_name'][$fileIndex];
                    $filename = $_FILES['images']['name'][$fileIndex];
                    $fileType = $_FILES['images']['type'][$fileIndex];
                    $fileSize = $_FILES['images']['size'][$fileIndex];
                    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                    if (in_array($fileType, $allowedTypes) && in_array($fileExt, $allowedExtensions)) {
                        if ($fileSize <= 5 * 1024 * 1024) {
                            $newFilename = uniqid('art_' . $article_id . '_') . '.' . $fileExt;
                            $destination = $uploadDir . $newFilename;
                            $dbUrl = 'img/articles/' . $newFilename;

                            if (move_uploaded_file($tmpName, $destination)) {
                                $stmtImg = $mysqli->prepare("INSERT INTO image (article_id, url, is_main) VALUES (?, ?, ?)");
                                $stmtImg->bind_param("isi", $article_id, $dbUrl, $isMain);
                                $stmtImg->execute();
                                $isMainAssigned = true;
                            }
                        } else {
                            $error = "L'image " . htmlspecialchars($filename) . " dépasse la taille (5 Mo).";
                        }
                    } else {
                        $error = "Le format de " . htmlspecialchars($filename) . " n'est pas autorisé.";
                    }
                }
            }
        }
    }
}



$queryStock = $mysqli->query("SELECT * FROM stock WHERE article_id = $article_id");
if ($queryStock->num_rows > 0) {
    $currentStock = $queryStock->fetch_assoc();
} else {
    $currentStock = ['quant_xs' => 0, 'quant_s' => 0, 'quant_m' => 0, 'quant_l' => 0, 'quant_xl' => 0];
}


$queryImages = $mysqli->query("SELECT * FROM image WHERE article_id = $article_id ORDER BY is_main DESC, id ASC");
$existingImages = [];
while ($img = $queryImages->fetch_assoc()) {
    $existingImages[] = [
        'id' => $img['id'],
        'url' => BASE_URL . $img['url'],
        'raw_url' => $img['url']
    ];
}

include BASE_PATH . 'includes/header.php';
?>


<form id="deleteForm" action="<?= BASE_URL ?>admin/editArticle.php" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_article">
    <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
</form>

<form id="mainEditForm" action="<?= BASE_URL ?>admin/editArticle.php" method="POST" enctype="multipart/form-data" class="addArticleContainer">
    <input type="hidden" name="action" value="update_article">
    <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
    
    <input type="hidden" id="deletedImagesInput" name="deleted_images" value="">
    
    <input type="hidden" id="finalImageOrderInput" name="final_image_order" value="">

    <h1 class="addArticleMainTitle">MODIFIER L'ARTICLE #<?php echo $article_id; ?></h1>

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
                <!-- Les images existantes sont prechargées via JS -->
                
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
                
                <div class="gridImageWrapper placeholderImage" id="initialPlaceholder"></div>
            </div>
        </div>

        
        <div class="addArticleRight">
            
            <div class="editGroup">
                
                <input type="text" name="name" class="editableInput titleInput" placeholder="TITRE DE L'ARTICLE" value="<?php echo htmlspecialchars($article['name']); ?>" required>
                <span class="editIcon">✎</span>
            </div>
            <div class="editGroup priceWrapper">
                
                <input type="number" name="price" step="0.01" class="editableInput priceInput" placeholder="XX" value="<?php echo htmlspecialchars($article['price']); ?>" required>
                <span class="currencySuffix">EUR</span>
            </div>

            <div class="vendorInfo">
                VENDU PAR <strong><?php echo strtoupper(htmlspecialchars($_SESSION['username'])); ?></strong>
            </div>

            <div class="formSection" style="flex-direction: column; align-items: flex-start; border-bottom: none;">
                <div class="sectionHeader" style="margin-bottom: 0.5rem; width: 100%;">CATÉGORIE</div>
                <select name="category" class="categorySelect" required>
                    <?php $savedCat = strtolower(trim($article['category'])); ?>
                    <option value="Knitwear" <?php if($savedCat === 'knitwear') echo 'selected'; ?>>KNITWEAR</option>
                    <option value="Vestes" <?php if($savedCat === 'vestes') echo 'selected'; ?>>VESTES</option>
                    <option value="Hoodies" <?php if($savedCat === 'hoodies') echo 'selected'; ?>>HOODIES</option>
                    <option value="T-shirts" <?php if($savedCat === 't-shirts') echo 'selected'; ?>>T-SHIRTS</option>
                    <option value="Pantalons" <?php if($savedCat === 'pantalons') echo 'selected'; ?>>PANTALONS</option>
                    <option value="Accessoires" <?php if($savedCat === 'accessoires') echo 'selected'; ?>>ACCESSOIRES</option>
                    <option value="Autre" <?php if($savedCat === 'autre') echo 'selected'; ?>>AUTRE</option>
                </select>
            </div>

            <div class="formSection" style="flex-direction: column; align-items: flex-start;">
                <div class="sectionHeader" style="margin-bottom: 0.5rem; width: 100%;">TAILLES DISPONIBLES</div>
                <div class="sizeOptionsSelect" id="sizeSelector">
                    <button type="button" class="sizeBtn selectable <?php echo ($currentStock['quant_xs'] > 0) ? 'selected' : ''; ?>" data-size="XS">XS</button>
                    <button type="button" class="sizeBtn selectable <?php echo ($currentStock['quant_s'] > 0) ? 'selected' : ''; ?>" data-size="S">S</button>
                    <button type="button" class="sizeBtn selectable <?php echo ($currentStock['quant_m'] > 0) ? 'selected' : ''; ?>" data-size="M">M</button>
                    <button type="button" class="sizeBtn selectable <?php echo ($currentStock['quant_l'] > 0) ? 'selected' : ''; ?>" data-size="L">L</button>
                    <button type="button" class="sizeBtn selectable <?php echo ($currentStock['quant_xl'] > 0) ? 'selected' : ''; ?>" data-size="XL">XL</button>
                </div>
            </div>

            <div class="formSection" style="flex-direction: column; align-items: flex-start; border-bottom: none;" id="stockSectionContainer">
                <div class="sectionHeader" style="margin-bottom: 1rem; width: 100%;">STOCK PAR TAILLE</div>
                
                <div id="stockLinesWrapper" style="width: 100%;">
                    <?php 
                    $sizesMap = [
                        'XS' => $currentStock['quant_xs'],
                        'S' => $currentStock['quant_s'],
                        'M' => $currentStock['quant_m'],
                        'L' => $currentStock['quant_l'],
                        'XL' => $currentStock['quant_xl']
                    ];
                    $anySizeSelected = false;
                    foreach($sizesMap as $s => $qty): 
                        if ($qty > 0) $anySizeSelected = true;
                    ?>
                    <div class="stockLine" id="stockLine-<?php echo $s; ?>" style="display: <?php echo ($qty > 0) ? 'flex' : 'none'; ?>; margin-bottom: 0.5rem; align-items: center; justify-content: space-between;">
                        <span class="stockSize" style="font-weight: 700; width: 30px;"><?php echo $s; ?></span>
                        <div style="display: flex; align-items: center; background: transparent; border-bottom: 1px solid #737373; width: 80%;">
                            <input type="number" name="stock[<?php echo $s; ?>]" class="editableInput stockInput" value="<?php echo $qty; ?>" min="0" style="width: 100%; border: none;">
                            <span style="font-size: 0.7rem; color: #737373; margin-left: 5px;">EXEMPLAIRES</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="noSizeSelectedMsg" style="display: <?php echo $anySizeSelected ? 'none' : 'block'; ?>; font-size: 0.8rem; color: #d1d5db; font-style: italic;">
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
                        <textarea name="description" class="editableInput descInput" placeholder="ENTREZ LA DESCRIPTION DE VÔTRE PRODUIT ICI"><?php echo htmlspecialchars($article['description']); ?></textarea>
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
        <button type="button" class="actionBtn cancelBtn" onclick="confirmDelete()" style="color: #dc2626; border-color: #dc2626;"><span class="crossIcon">✕</span> SUPPRIMER</button>
        <button type="submit" class="actionBtn publishBtn">CONFIRMER</button>
    </div>
</form>

<script>
// Préchargement des images existantes depuis PHP
const existingImagesData = <?php echo json_encode($existingImages); ?>;

document.addEventListener("DOMContentLoaded", function() {
    const previewGrid = document.getElementById('previewGrid');
    const fileInput = document.getElementById('articleImage');
    const deletedImagesInput = document.getElementById('deletedImagesInput');
    const finalImageOrderInput = document.getElementById('finalImageOrderInput');
    
    // Contenu HTML du bouton "Ajouter une image"
    const addMoreHTML = '<div class="dropzoneContent"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline><line x1="12" y1="11" x2="12" y2="17"></line><line x1="9" y1="14" x2="15" y2="14"></line></svg></div>';

    let gridItems = []; 
    // Format des items: { type: 'existing', data: {id: 12, url: 'img/...'} } ou { type: 'new', data: FileObject }

    // Remplir initialement avec les images existantes
    existingImagesData.forEach(img => {
        gridItems.push({ type: 'existing', data: img });
    });

    let deletedImageIds = []; // IDs des images BDD à supprimer

    function updateInputsAndRender() {
        const dt = new DataTransfer();
        let newFileCount = 0;
        let finalOrderArray = [];

        gridItems.forEach(item => {
            if (item.type === 'new') {
                dt.items.add(item.data);
                finalOrderArray.push('new:' + newFileCount);
                newFileCount++;
            } else if (item.type === 'existing') {
                finalOrderArray.push('existing:' + item.data.raw_url);
            }
        });

        fileInput.files = dt.files;
        finalImageOrderInput.value = finalOrderArray.join(',');

        renderGrid();
    }

    function renderGrid() {
        previewGrid.innerHTML = '';

        gridItems.forEach((item, index) => {
            const div = document.createElement('div');
            div.className = 'gridImageWrapper';
            div.draggable = true;
            div.dataset.index = index;
            div.style.cursor = 'grab';
            div.style.position = 'relative'; 
            
            const img = document.createElement('img');
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            
            if (item.type === 'existing') {
                img.src = item.data.url;
            } else {
                img.src = URL.createObjectURL(item.data);
            }
            div.appendChild(img);

            const removeBtn = document.createElement('button');
            removeBtn.innerHTML = '✕';
            removeBtn.className = 'removeImageBtn';
            removeBtn.type = 'button';
            removeBtn.addEventListener('click', function(e) {
                e.stopPropagation(); 
                if (item.type === 'existing') {
                    deletedImageIds.push(item.data.id);
                    deletedImagesInput.value = deletedImageIds.join(',');
                }
                gridItems.splice(index, 1);
                updateInputsAndRender();
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

        if (gridItems.length === 0) {
            const placeholder = document.createElement('div');
            placeholder.className = 'gridImageWrapper placeholderImage';
            previewGrid.appendChild(placeholder);
        }
    }

    
    fileInput.addEventListener('change', function() {
        if (!this.files || this.files.length === 0) return;
        Array.from(this.files).forEach(file => {
            gridItems.push({ type: 'new', data: file });
        });
        updateInputsAndRender();
    });

    
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
            const movedItem = gridItems.splice(dragStartIndex, 1)[0];
            gridItems.splice(dragEndIndex, 0, movedItem);
            updateInputsAndRender();
        }
        return false;
    }

    document.addEventListener('dragend', (e) => {
        if (e.target.classList && e.target.classList.contains('gridImageWrapper')) {
            e.target.style.opacity = '1';
        }
    });

    
    updateInputsAndRender();
    const sizeBtns = document.querySelectorAll('.sizeBtn.selectable');
    const noSizeMsg = document.getElementById('noSizeSelectedMsg');
    
    
    sizeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('selected'); 
            
            const size = this.dataset.size;
            const stockLine = document.getElementById('stockLine-' + size);
            const stockInput = stockLine.querySelector('input');
            
            if (this.classList.contains('selected')) {
                stockLine.style.display = 'flex';
                if(stockInput.value === "0" || stockInput.value === "") stockInput.value = 1; 
            } else {
                stockLine.style.display = 'none';
                stockInput.value = 0;
            }
            
            const anySelected = document.querySelectorAll('.sizeBtn.selectable.selected').length > 0;
            noSizeMsg.style.display = anySelected ? 'none' : 'block';
        });
    });

    
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


function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer définitivement cet article ? Cette action est irréversible.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>
