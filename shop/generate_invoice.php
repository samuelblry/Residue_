<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../fpdf/fpdf.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($invoice_id === 0) {
    die("ID de facture invalide.");
}

// Vérifier que l'utilisateur a le droit de voir cette facture (soit c'est la sienne, soit il est admin)
$role = $_SESSION['role'] ?? 'user';
$sql = "SELECT * FROM invoice WHERE id = ?";
if ($role !== 'admin') {
    $sql .= " AND user_id = $user_id";
}

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Facture introuvable ou accès refusé.");
}

$invoice = $result->fetch_assoc();

// Récupérer les articles
$stmtItems = $mysqli->prepare("SELECT ii.*, a.name FROM invoice_item ii LEFT JOIN article a ON ii.article_id = a.id WHERE ii.invoice_id = ?");
$stmtItems->bind_param("i", $invoice_id);
$stmtItems->execute();
$itemsResult = $stmtItems->get_result();
$items = [];
$totalItems = 0;
while ($row = $itemsResult->fetch_assoc()) {
    $items[] = $row;
    $totalItems += ($row['price'] * $row['quantity']);
}

$delivery_fee = $invoice['amount'] - $totalItems;

// Génération du PDF
class PDF extends FPDF {
    function Header() {
        // Logo RESIDUE_ Textuel
        $this->SetFont('Arial','B',24);
        $this->Cell(80,10,'RESIDUE_',0,0,'L');
        
        // Titre Facture
        $this->SetFont('Arial','B',15);
        $this->Cell(110,10,'FACTURE',0,1,'R');
        $this->Ln(10);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Infos Facture
$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,6,utf8_decode('Facture N°: ') . str_pad($invoice['id'], 5, '0', STR_PAD_LEFT), 0, 0);
$pdf->Cell(90,6,'Date: ' . date('d/m/Y', strtotime($invoice['transaction_date'])), 0, 1, 'R');
$pdf->Ln(10);

// Adresses
$pdf->SetFont('Arial','B',10);
$pdf->Cell(95,6,'Informations de Facturation',0,0,'L');
$pdf->Cell(95,6,'Informations de Livraison',0,1,'L');

$pdf->SetFont('Arial','',10);
$bill_name = utf8_decode($invoice['billing_firstname'] . ' ' . $invoice['billing_lastname']);
$ship_name = utf8_decode($invoice['shipping_firstname'] . ' ' . $invoice['shipping_lastname']);

$pdf->Cell(95,5,$bill_name,0,0,'L');
$pdf->Cell(95,5,$ship_name,0,1,'L');

$pdf->Cell(95,5,utf8_decode($invoice['billing_address']),0,0,'L');
$pdf->Cell(95,5,utf8_decode($invoice['shipping_address']),0,1,'L');

$pdf->Cell(95,5,utf8_decode($invoice['billing_zipcode'] . ' ' . $invoice['billing_city']),0,0,'L');
$pdf->Cell(95,5,utf8_decode($invoice['shipping_zipcode'] . ' ' . $invoice['shipping_city']),0,1,'L');

$pdf->Cell(95,5,utf8_decode($invoice['billing_country']),0,0,'L');
$pdf->Cell(95,5,utf8_decode($invoice['shipping_country']),0,1,'L');
$pdf->Ln(10);

if (!empty($invoice['additional_instructions'])) {
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(0,5,'Instructions de livraison :',0,1,'L');
    $pdf->SetFont('Arial','I',9);
    $pdf->MultiCell(0,5,utf8_decode($invoice['additional_instructions']));
    $pdf->Ln(5);
}

// Tableau des articles
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(240,240,240);
$pdf->Cell(20,8,'QTE',1,0,'C',true);
$pdf->Cell(110,8,'DESCRIPTION',1,0,'C',true);
$pdf->Cell(30,8,'PRIX U.',1,0,'C',true);
$pdf->Cell(30,8,'TOTAL',1,1,'C',true);

$pdf->SetFont('Arial','',10);
foreach($items as $item) {
    $name = utf8_decode($item['name'] ? $item['name'] : 'Article supprimé');
    $pdf->Cell(20,8,$item['quantity'],1,0,'C');
    $pdf->Cell(110,8,$name,1,0,'L');
    $pdf->Cell(30,8,number_format($item['price'], 2, ',', ' ') . ' ' . chr(128),1,0,'R');
    $pdf->Cell(30,8,number_format($item['price'] * $item['quantity'], 2, ',', ' ') . ' ' . chr(128),1,1,'R');
}
$pdf->Ln();

// Récapitulatif
$pdf->SetFont('Arial','B',10);
$pdf->Cell(130,8,'',0,0);
$pdf->Cell(30,8,'Sous-total',1,0,'R',true);
$pdf->Cell(30,8,number_format($totalItems, 2, ',', ' ') . ' ' . chr(128),1,1,'R');

$pdf->Cell(130,8,'',0,0);
$pdf->Cell(30,8,'Livraison',1,0,'R',true);
$pdf->Cell(30,8,number_format($delivery_fee, 2, ',', ' ') . ' ' . chr(128),1,1,'R');

$pdf->Cell(130,8,'',0,0);
$pdf->Cell(30,8,'TOTAL TTC',1,0,'R',true);
$pdf->Cell(30,8,number_format($invoice['amount'], 2, ',', ' ') . ' ' . chr(128),1,1,'R');

$pdf->Output('I', 'Facture_RESIDUE_' . $invoice['id'] . '.pdf');
?>
