<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$success = "";
$error = "";

// Actions de suppression
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $id = intval($_POST['user_id']);
        if ($id !== $_SESSION['user_id']) { // Empêcher l'admin de se supprimer
            $mysqli->query("DELETE FROM user WHERE id = $id");
            $success = "Utilisateur supprimé.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_article') {
        $id = intval($_POST['article_id']);
        $mysqli->query("DELETE FROM article WHERE id = $id");
        $success = "Article supprimé.";
    }
}

// Récupérer tous les utilisateurs
$resUsers = $mysqli->query("SELECT id, username, email, role, balance FROM user ORDER BY id DESC");
$users = [];
if ($resUsers) {
    while($row = $resUsers->fetch_assoc()) $users[] = $row;
}

// Récupérer tous les articles
$resArticles = $mysqli->query("SELECT id, name, price, author_id FROM article ORDER BY publish_date DESC");
$articles = [];
if ($resArticles) {
    while($row = $resArticles->fetch_assoc()) $articles[] = $row;
}

// Récupérer toutes les factures
$resInvoices = $mysqli->query("SELECT invoice.id, invoice.transaction_date, invoice.amount, invoice.billing_address, invoice.billing_city, invoice.billing_zipcode, user.username FROM invoice JOIN user ON invoice.user_id = user.id ORDER BY invoice.transaction_date DESC");
$invoices = [];
if ($resInvoices) {
    while($row = $resInvoices->fetch_assoc()) $invoices[] = $row;
}

include BASE_PATH . 'includes/header.php';
?>

<div class="contactContainer" style="max-width: 1000px;">
    <h1 class="titleFormular">Panneau d'Administration</h1>
    
    <?php if(!empty($error)): ?>
        <div style="color: #dc2626; margin-bottom: 1rem; font-weight: bold;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div style="color: #16a34a; margin-bottom: 1rem; font-weight: bold;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <h2 style="text-transform: uppercase; margin-top: 3rem; margin-bottom: 1rem; border-bottom: 2px solid #1c1917; padding-bottom: 0.5rem;">Gestion des Utilisateurs</h2>
    <table style="width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 2rem;">
        <thead>
            <tr style="border-bottom: 1px solid #e7e5e4;">
                <th style="padding: 0.5rem;">ID</th>
                <th style="padding: 0.5rem;">Username</th>
                <th style="padding: 0.5rem;">Email</th>
                <th style="padding: 0.5rem;">Rôle</th>
                <th style="padding: 0.5rem;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $u): ?>
            <tr style="border-bottom: 1px solid #e7e5e4;">
                <td style="padding: 0.5rem;"><?php echo $u['id']; ?></td>
                <td style="padding: 0.5rem;"><?php echo htmlspecialchars($u['username']); ?></td>
                <td style="padding: 0.5rem;"><?php echo htmlspecialchars($u['email']); ?></td>
                <td style="padding: 0.5rem;"><?php echo htmlspecialchars($u['role']); ?></td>
                <td style="padding: 0.5rem;">
                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <form action="admin.php" method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <button type="submit" style="color: #dc2626; text-decoration: underline;">Supprimer</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2 style="text-transform: uppercase; margin-top: 3rem; margin-bottom: 1rem; border-bottom: 2px solid #1c1917; padding-bottom: 0.5rem;">Gestion Globale des Articles</h2>
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid #e7e5e4;">
                <th style="padding: 0.5rem;">ID</th>
                <th style="padding: 0.5rem;">Nom</th>
                <th style="padding: 0.5rem;">Prix</th>
                <th style="padding: 0.5rem;">Auteur ID</th>
                <th style="padding: 0.5rem;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($articles as $a): ?>
            <tr style="border-bottom: 1px solid #e7e5e4;">
                <td style="padding: 0.5rem;"><?php echo $a['id']; ?></td>
                <td style="padding: 0.5rem;"><?php echo htmlspecialchars($a['name']); ?></td>
                <td style="padding: 0.5rem;"><?php echo number_format($a['price'], 2); ?> €</td>
                <td style="padding: 0.5rem;"><?php echo $a['author_id'] ? $a['author_id'] : 'Inconnu'; ?></td>
                <td style="padding: 0.5rem;">
                    <form action="<?= BASE_URL ?>admin/editArticle.php" method="POST" style="display:inline-block; margin-right: 0.5rem;">
                        <input type="hidden" name="article_id" value="<?php echo $a['id']; ?>">
                        <button type="submit" style="text-decoration: underline;">Modifier</button>
                    </form>
                    <form action="admin.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Supprimer cet article ?');">
                        <input type="hidden" name="action" value="delete_article">
                        <input type="hidden" name="article_id" value="<?php echo $a['id']; ?>">
                        <button type="submit" style="color: #dc2626; text-decoration: underline;">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2 style="text-transform: uppercase; margin-top: 3rem; margin-bottom: 1rem; border-bottom: 2px solid #1c1917; padding-bottom: 0.5rem;">Historique des Transactions (Factures)</h2>
    <table style="width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 2rem;">
        <thead>
            <tr style="border-bottom: 1px solid #e7e5e4;">
                <th style="padding: 0.5rem;">ID</th>
                <th style="padding: 0.5rem;">Utilisateur</th>
                <th style="padding: 0.5rem;">Date</th>
                <th style="padding: 0.5rem;">Montant</th>
                <th style="padding: 0.5rem;">Adresse de facturation</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($invoices as $inv): ?>
            <tr style="border-bottom: 1px solid #e7e5e4;">
                <td style="padding: 0.5rem;"><?php echo $inv['id']; ?></td>
                <td style="padding: 0.5rem;"><?php echo htmlspecialchars($inv['username']); ?></td>
                <td style="padding: 0.5rem;"><?php echo date('d/m/Y H:i', strtotime($inv['transaction_date'])); ?></td>
                <td style="padding: 0.5rem;"><?php echo number_format($inv['amount'], 2); ?> €</td>
                <td style="padding: 0.5rem;">
                    <?php echo htmlspecialchars($inv['billing_address'] . ', ' . $inv['billing_zipcode'] . ' ' . $inv['billing_city']); ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($invoices)): ?>
            <tr>
                <td colspan="5" style="padding: 1rem; text-align: center; color: #78716c;">Aucune transaction enregistrée.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>
