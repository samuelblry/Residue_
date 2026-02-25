<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$is_own_account = true;

if (isset($_GET['id']) && intval($_GET['id']) !== $current_user_id) {
    $user_id = intval($_GET['id']);
    $is_own_account = false;
} else {
    $user_id = $current_user_id;
}

$success = "";
$error = "";

// Traitement des formulaires (seulement si c'est notre propre compte)
if ($is_own_account && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_balance':
                $amount = floatval($_POST['amount']);
                if ($amount > 0) {
                    $mysqli->query("UPDATE user SET balance = balance + $amount WHERE id=$user_id");
                    $success = "Solde rechargé de " . number_format($amount, 2) . " €.";
                }
                break;
            case 'edit_info':
                $new_pseudo = $mysqli->real_escape_string($_POST['pseudo']);
                $new_prenom = $mysqli->real_escape_string($_POST['prenom']);
                $new_nom = $mysqli->real_escape_string($_POST['nom']);
                $new_email = $mysqli->real_escape_string($_POST['email']);
                
                $update_query = "UPDATE user SET username='$new_pseudo', prenom='$new_prenom', nom='$new_nom', email='$new_email'";
                
                // Modification du mot de passe
                if (!empty($_POST['new_password']) || !empty($_POST['current_password']) || !empty($_POST['confirm_new_password'])) {
                    if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_new_password'])) {
                        $error = "Veuillez remplir tous les champs de mot de passe pour le modifier.";
                    } else {
                        $resUser = $mysqli->query("SELECT password FROM user WHERE id=$user_id");
                        $userData = $resUser->fetch_assoc();
                        
                        if (password_verify($_POST['current_password'], $userData['password'])) {
                            if ($_POST['new_password'] === $_POST['confirm_new_password']) {
                                $new_password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                                $update_query .= ", password='$new_password_hash'";
                            } else {
                                $error = "Le nouveau mot de passe et la confirmation ne correspondent pas.";
                            }
                        } else {
                            $error = "L'ancien mot de passe est incorrect.";
                        }
                    }
                }
                if (empty($error)) {
                    $update_query .= " WHERE id=$user_id";
                    if ($mysqli->query($update_query)) {
                        $success = "Informations mises à jour avec succès.";
                    } else {
                        $error = "Erreur lors de la mise à jour des informations.";
                    }
                }
                break;
            case 'upload_avatar':
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['profile_pic']['tmp_name'];
                    $filename = $_FILES['profile_pic']['name'];
                    $fileType = $_FILES['profile_pic']['type'];
                    $fileSize = $_FILES['profile_pic']['size'];
                    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

                    if (in_array($fileType, $allowedTypes) && in_array($fileExt, $allowedExtensions)) {
                        if ($fileSize <= 5 * 1024 * 1024) {
                            $uploadDir = 'img/profiles/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            $newFilename = uniqid('user_' . $user_id . '_') . '.' . $fileExt;
                            $destination = $uploadDir . $newFilename;

                            if (move_uploaded_file($tmpName, $destination)) {
                                // Mettre à jour la base de données
                                $stmt = $mysqli->prepare("UPDATE user SET profile_pic=? WHERE id=?");
                                $stmt->bind_param("si", $newFilename, $user_id);
                                if ($stmt->execute()) {
                                    $success = "Photo de profil mise à jour avec succès.";
                                } else {
                                    $error = "Erreur lors de la mise à jour en base de données.";
                                }
                            } else {
                                $error = "Erreur lors de l'enregistrement de l'image.";
                            }
                        } else {
                            $error = "La photo de profil dépasse la taille de 5 Mo.";
                        }
                    } else {
                        $error = "Le format de l'image (JPG, PNG, WEBP) n'est pas autorisé.";
                    }
                } else {
                    $error = "Veuillez sélectionner une image valide.";
                }
                break;
            case 'delete_account':
                if ($mysqli->query("DELETE FROM user WHERE id=$user_id")) {
                    session_destroy();
                    header("Location: " . BASE_URL . "index.php");
                    exit();
                } else {
                    $error = "Erreur lors de la suppression du compte.";
                }
                break;
        }
    }
}

// Récupérer les infos actuelles de l'utilisateur visé
$resUser = $mysqli->query("SELECT * FROM user WHERE id=$user_id");
if ($resUser->num_rows === 0) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}
$user = $resUser->fetch_assoc();

// Extraire prenom et nom (avec fallback s'ils n'existent pas)
$userPrenom = $user['prenom'] ?? $user['first_name'] ?? 'PRENOMUSER';
$userNom = $user['nom'] ?? $user['last_name'] ?? 'NOMUSER';
$userPseudo = $user['username'] ?? 'USER123';
$userEmail = $user['email'] ?? 'USER123@GMAIL.COM';
$userBalance = number_format($user['balance'], 2, ',', ' ');

// Récupérer les articles mis en vente par l'utilisateur visé
$articlesVendus = [];
$resArticles = $mysqli->query("SELECT Article.id, Article.name, Article.price, Image.url AS image_url 
                               FROM article 
                               LEFT JOIN Image ON Article.id = Image.article_id AND Image.is_main = 1
                               WHERE author_id=$user_id");
if ($resArticles && $resArticles->num_rows > 0) {
    while ($row = $resArticles->fetch_assoc()) {
        $articlesVendus[] = $row;
    }
}

// Récupérer l'historique des transactions SI c'est notre propre compte
$invoices = [];
if ($is_own_account) {
    $resInvoices = $mysqli->query("SELECT id, transaction_date, amount FROM invoice WHERE user_id=$user_id ORDER BY transaction_date DESC");
    if ($resInvoices && $resInvoices->num_rows > 0) {
        while ($row = $resInvoices->fetch_assoc()) {
            $row['items'] = [];
            $invoices[$row['id']] = $row;
        }
    }

    // Récupérer les articles des factures
    if (!empty($invoices)) {
        $invoice_ids = implode(',', array_keys($invoices));
        $sqlItems = "SELECT invoice_item.invoice_id, invoice_item.quantity, invoice_item.price, article.id as article_id, article.name, image.url as image_url 
                     FROM invoice_item 
                     LEFT JOIN article ON invoice_item.article_id = article.id 
                     LEFT JOIN image ON article.id = image.article_id AND image.is_main = 1 
                     WHERE invoice_item.invoice_id IN ($invoice_ids)";
        $resItems = $mysqli->query($sqlItems);
        if ($resItems && $resItems->num_rows > 0) {
            while ($item = $resItems->fetch_assoc()) {
                $invoices[$item['invoice_id']]['items'][] = $item;
            }
        }
    }
}

include BASE_PATH . 'includes/header.php';
?>

<div class="accountContainer">
    <h1 class="accountMainTitle">Mon Compte</h1>

    <?php if (!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold; font-size: 0.8rem; text-transform: uppercase;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div style="color: #16a34a; margin-bottom: 1rem; font-weight: bold; font-size: 0.8rem; text-transform: uppercase;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Identité, Données, Porte-Monnaie -->
    <div class="accountTopGrid">
        <form id="avatarUploadForm" action="<?= BASE_URL ?>auth/account.php" method="POST" enctype="multipart/form-data" style="display: none;">
            <input type="hidden" name="action" value="upload_avatar">
            <input type="file" id="avatarUploadInput" name="profile_pic" accept="image/jpeg, image/png, image/webp" onchange="document.getElementById('avatarUploadForm').submit();">
        </form>

        <label for="avatarUploadInput" class="accountAvatar" style="cursor: pointer; position: relative; overflow: hidden; display: block;" title="Modifier la photo de profil" onmouseover="this.querySelector('.avatarOverlay').style.opacity='1'" onmouseout="this.querySelector('.avatarOverlay').style.opacity='0'">
            <?php if (!empty($user['profile_pic']) && $user['profile_pic'] !== 'default.jpg'): ?>
                <img src="<?= BASE_URL ?>img/profiles/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: 100%;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" fill="currentColor"/>
                </svg>
            <?php endif; ?>
            
            <div class="avatarOverlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; color: white;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
            </div>
        </label>

        <div class="accountInfoBlock">
            <h3>Identité</h3>
            <div class="accountInfoLine"><strong>Pseudo</strong> <?php echo htmlspecialchars($userPseudo); ?></div>
            <div class="accountInfoLine"><strong>Prénom</strong> <?php echo htmlspecialchars($userPrenom); ?></div>
            <div class="accountInfoLine"><strong>Nom</strong> <?php echo htmlspecialchars($userNom); ?></div>
            <?php if ($is_own_account): ?>
            <button type="button" class="btnRecharge" onclick="openEditInfoModal()" style="margin-top: 1rem;">Modifier mes informations</button>
            <?php endif; ?>
        </div>

        <?php if ($is_own_account): ?>
        <div class="accountInfoBlock">
            <h3>Données</h3>
            <div class="accountInfoLine accountInfoWordWrap"><strong>Mail</strong> <?php echo htmlspecialchars($userEmail); ?></div>
            <div class="accountInfoLine"><strong>Mot de passe</strong> *************</div>
        </div>

        <div class="accountInfoBlock">
            <h3>Porte Monnaie</h3>
            <div class="accountInfoLine"><strong>Solde Actuel</strong> <?php echo $userBalance; ?> EUR</div>
            <button type="button" class="btnRecharge" onclick="openRechargeModal()">Recharger le solde</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Articles Postés -->
    <div class="accountSectionHeader">
        <h2>Articles Postés</h2>
        <?php if ($is_own_account): ?>
        <a href="<?= BASE_URL ?>admin/add_article.php" class="btnAccountAction">Ajouter un article</a>
        <?php endif; ?>
    </div>

    <div class="accountArticlesGrid">
        <?php foreach ($articlesVendus as $art): ?>
            <div class="accountArticleCard">
                <a href="<?= BASE_URL ?><?php echo $is_own_account ? 'admin/editArticle.php' : 'shop/pageArticle.php'; ?>?id=<?php echo $art['id']; ?>" class="accountArticleImage">
                    <?php if (!empty($art['image_url'])): ?>
                        <img src="<?= BASE_URL ?><?php echo htmlspecialchars($art['image_url']); ?>" alt="<?php echo htmlspecialchars($art['name']); ?>">
                    <?php endif; ?>
                </a>
                <div class="accountArticleTitle"><?php echo htmlspecialchars($art['name']); ?></div>
                <div class="accountArticlePrice"><?php echo number_format($art['price'], 2, ',', ' '); ?> EUR</div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Commandes -->
    <?php if ($is_own_account): ?>
    <div class="accountSectionHeader">
        <h2>Commandes</h2>
    </div>
    
    <?php foreach ($invoices as $inv): ?>
        <div class="orderBlock">
            <div class="orderHeader">
                <div>Numéro de commande <span>#<?php echo str_pad($inv['id'], 11, '85425874590', STR_PAD_LEFT); ?></span></div>
                <div>Total Payé <span><?php echo number_format($inv['amount'], 2, ',', ' '); ?> EUR</span></div>
            </div>
            
            <div class="accountArticlesGrid" style="margin-bottom: 0;">
                <?php foreach ($inv['items'] as $item): ?>
                    <div class="accountArticleCard">
                        <a href="<?= BASE_URL ?>shop/pageArticle.php?id=<?php echo $item['article_id']; ?>" class="accountArticleImage">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= BASE_URL ?><?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php endif; ?>
                        </a>
                        <div class="accountArticleTitle"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="accountArticlePrice"><?php echo number_format($item['price'], 2, ',', ' '); ?> EUR</div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button class="btnDownloadInvoice" onclick="window.open('<?= BASE_URL ?>shop/generate_invoice.php?id=<?php echo $inv['id']; ?>', '_blank')">Télécharger la facture</button>
        </div>
    <?php endforeach; ?>

    <div class="addArticleActions" style="margin-top: 3rem; justify-content: center; display: flex;">
        <button type="button" class="actionBtn cancelBtn" onclick="openDeleteAccountModal()" style="color: #dc2626; border-color: #dc2626; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 1rem 2rem; font-weight: bold; text-transform: uppercase; background: transparent; border: 1px solid #dc2626; cursor: pointer; transition: all 0.3s;"><span class="crossIcon" style="font-size: 1.2rem;">✕</span> SUPPRIMER MON COMPTE</button>
    </div>
    <?php endif; ?>

</div>

<!-- Modal Recharge -->
<div id="rechargeModal" class="rechargeModal">
    <div class="rechargeModalContent">
        <span class="closeRechargeModal" onclick="closeRechargeModal()">&times;</span>
        <h2>Recharger votre solde</h2>
        <form action="<?= BASE_URL ?>auth/account.php" method="POST">
            <input type="hidden" name="action" value="add_balance">
            <input type="number" name="amount" class="rechargeInput" placeholder="Montant en EUR" min="1" step="1" required>
            <button type="submit" class="btnConfirmRecharge">Confirmer</button>
        </form>
    </div>
</div>

<!-- Modal Edit Info -->
<div id="editInfoModal" class="rechargeModal">
    <div class="rechargeModalContent" style="max-width: 600px; padding: 1.5rem;">
        <span class="closeRechargeModal" onclick="closeEditInfoModal()">&times;</span>
        <h2 style="margin-bottom: 1rem; font-size: 1.5rem;">Modifier mes informations</h2>
        <form action="<?= BASE_URL ?>auth/account.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem; text-align: left;">
            <input type="hidden" name="action" value="edit_info">
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 150px;">
                    <label for="pseudo" style="font-weight: bold; display: block; margin-bottom: 0.2rem; font-size: 0.9rem;">Pseudo</label>
                    <input type="text" id="pseudo" name="pseudo" class="rechargeInput" style="padding: 0.5rem; font-size: 0.9rem;" value="<?php echo htmlspecialchars($userPseudo); ?>" required>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label for="email" style="font-weight: bold; display: block; margin-bottom: 0.2rem; font-size: 0.9rem;">Email</label>
                    <input type="email" id="email" name="email" class="rechargeInput" style="padding: 0.5rem; font-size: 0.9rem;" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 150px;">
                    <label for="prenom" style="font-weight: bold; display: block; margin-bottom: 0.2rem; font-size: 0.9rem;">Prénom</label>
                    <input type="text" id="prenom" name="prenom" class="rechargeInput" style="padding: 0.5rem; font-size: 0.9rem;" value="<?php echo htmlspecialchars($userPrenom); ?>" required>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label for="nom" style="font-weight: bold; display: block; margin-bottom: 0.2rem; font-size: 0.9rem;">Nom</label>
                    <input type="text" id="nom" name="nom" class="rechargeInput" style="padding: 0.5rem; font-size: 0.9rem;" value="<?php echo htmlspecialchars($userNom); ?>" required>
                </div>
            </div>
            
            <div style="border-top: 1px solid #e7e5e4; padding-top: 1rem; margin-top: 0.5rem;">
                <h3 style="margin-bottom: 0.8rem; font-size: 1rem;">Modifier le mot de passe (optionnel)</h3>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.8rem;">
                    <div style="flex: 1; min-width: 150px;">
                        <label for="current_password" style="font-weight: bold; display: block; margin-bottom: 0.2rem; font-size: 0.9rem;">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" class="rechargeInput" style="padding: 0.5rem; font-size: 0.9rem;" placeholder="Saisissez-le pour changer">
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 150px;">
                        <label for="new_password" style="font-weight: bold; display: block; margin-bottom: 0.2rem; font-size: 0.9rem;">Nouveau</label>
                        <input type="password" id="new_password" name="new_password" class="rechargeInput" style="padding: 0.5rem; font-size: 0.9rem;" placeholder="Nouveau">
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <label for="confirm_new_password" style="font-weight: bold; display: block; margin-bottom: 0.2rem; font-size: 0.9rem;">Confirmer</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" class="rechargeInput" style="padding: 0.5rem; font-size: 0.9rem;" placeholder="Confirmer">
                    </div>
                </div>
            </div>

            <button type="submit" class="btnConfirmRecharge" style="margin-top: 0.5rem; padding: 0.75rem;">Mettre à jour</button>
        </form>
    </div>
</div>

<!-- Modal Delete Account -->
<div id="deleteAccountModal" class="rechargeModal">
    <div class="rechargeModalContent">
        <span class="closeRechargeModal" onclick="closeDeleteAccountModal()">&times;</span>
        <h2 style="color: #dc2626;">Supprimer mon compte</h2>
        <p style="margin-bottom: 2rem; color: #78716c;">Êtes-vous sûr de vouloir supprimer définitivement votre compte ? Cette action est irréversible.</p>
        <form action="<?= BASE_URL ?>auth/account.php" method="POST">
            <input type="hidden" name="action" value="delete_account">
            <button type="submit" class="btnConfirmRecharge" style="background-color: #dc2626; color: white;">Oui, supprimer mon compte</button>
            <button type="button" class="btnConfirmRecharge" onclick="closeDeleteAccountModal()" style="margin-top: 1rem; background-color: #e7e5e4; color: #1c1917;">Annuler</button>
        </form>
    </div>
</div>

<script>
    function openRechargeModal() {
        document.getElementById('rechargeModal').classList.add('active');
        document.body.style.overflow = 'hidden'; // Empêche le scroll derrière
    }

    function closeRechargeModal() {
        document.getElementById('rechargeModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    function openEditInfoModal() {
        document.getElementById('editInfoModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEditInfoModal() {
        document.getElementById('editInfoModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    function openDeleteAccountModal() {
        document.getElementById('deleteAccountModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteAccountModal() {
        document.getElementById('deleteAccountModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Fermer si clic en dehors
    document.querySelectorAll('.rechargeModal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                if (this.id === 'rechargeModal') closeRechargeModal();
                if (this.id === 'editInfoModal') closeEditInfoModal();
                if (this.id === 'deleteAccountModal') closeDeleteAccountModal();
            }
        });
    });
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>