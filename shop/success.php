<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

include BASE_PATH . 'includes/header.php';
?>

<div class="loginPageContainer leftAligned">
    <!-- Image de fond -->
    <div class="loginBackground">
        <img src="<?= BASE_URL ?>img/background/fondPontDesert.png" alt="Paysage Desert Residue">
    </div>

    <!-- Panneau vitré à gauche (réutilisation des classes de la page login) -->
    <div class="loginPanel">
        <div class="loginContent" style="display: flex; flex-direction: column; align-items: flex-start; text-align: left; padding: 4rem 2rem;">
            
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 4rem; width: 100%;">
                <!-- Cercle blanc avec icône SVG -->
                <div style="background-color: white; border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#57534e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #1c1917;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <path d="M9 15l2 2 4-4"></path>
                    </svg>
                </div>
                
                <div>
                    <h1 class="loginTitle" style="font-size: 1.8rem; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.5rem;">COMMANDE VALIDÉE !</h1>
                    <p class="loginSubtitle" style="font-size: 0.8rem; margin-bottom: 0; color: #fff;">MERCI POUR TON ACHAT</p>
                </div>
            </div>

            <div style="width: 100%; display: flex; flex-direction: column; gap: 1rem; align-items: center; margin-top: auto;">
                <a href="<?= BASE_URL ?>shop/generate_invoice.php?id=<?php echo $invoice_id; ?>" target="_blank" style="display: block; width: 80%; padding: 1rem; border: 1px solid #fff; background: transparent; color: #fff; text-decoration: none; font-weight: bold; text-align: center; font-size: 0.85rem; letter-spacing: 0.05em; transition: all 0.3s; margin-bottom: 0.5rem;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
                    TÉLÉCHARGE TA FACTURE ICI
                </a>
                
                <a href="<?= BASE_URL ?>auth/account.php" style="display: block; width: 80%; padding: 1rem; border: 1px solid #fff; background: transparent; color: #fff; text-decoration: none; font-weight: bold; text-align: center; font-size: 0.85rem; letter-spacing: 0.05em; transition: all 0.3s;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
                    VOIR LA COMMANDE
                </a>
            </div>
            
        </div>
    </div>
</div>

<script>
    // Récupération de la fonction d'alignement de login.php pour la cohérence
    function alignGlassPanel() {
        const header = document.getElementById('navBar');
        const panel = document.querySelector('.loginPanel');
        
        if (header && panel) {
            const headerHeight = header.offsetHeight;
            panel.style.marginTop = headerHeight + 'px';
            panel.style.minHeight = `calc(100vh - ${headerHeight}px)`;
            panel.style.height = 'auto';
        }
    }
    window.addEventListener('DOMContentLoaded', alignGlassPanel);
    window.addEventListener('resize', alignGlassPanel);
    window.addEventListener('load', alignGlassPanel);
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>
