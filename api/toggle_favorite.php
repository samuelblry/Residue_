<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non connecté']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer le corps de la requête en JSON
$data = json_decode(file_get_contents('php://input'), true);

// Fallback sur POST si ce n'est pas du JSON
$article_id = isset($data['article_id']) ? intval($data['article_id']) : (isset($_POST['article_id']) ? intval($_POST['article_id']) : 0);

if ($article_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID article invalide']);
    exit();
}

// Vérifier si l'article existe
$checkArticle = $mysqli->query("SELECT id FROM article WHERE id = $article_id");
if ($checkArticle->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'L\'article n\'existe pas']);
    exit();
}

// Vérifier si le favori existe déjà
$stmt = $mysqli->prepare("SELECT id FROM favorite WHERE user_id = ? AND article_id = ?");
$stmt->bind_param("ii", $user_id, $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Il existe déjà -> on le supprime
    $delStmt = $mysqli->prepare("DELETE FROM favorite WHERE user_id = ? AND article_id = ?");
    $delStmt->bind_param("ii", $user_id, $article_id);
    if ($delStmt->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression']);
    }
} else {
    // N'existe pas -> on l'ajoute
    $insStmt = $mysqli->prepare("INSERT INTO favorite (user_id, article_id) VALUES (?, ?)");
    $insStmt->bind_param("ii", $user_id, $article_id);
    if ($insStmt->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'added']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'ajout']);
    }
}
?>
