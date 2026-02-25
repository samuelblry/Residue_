<?php
// Paramètres XAMPP par défaut
$host = "localhost";
$user = "root";
$pass = ""; 
$dbname = "residue_";

$mysqli = new mysqli($host, $user, $pass, $dbname);

// Vérification
if ($mysqli->connect_error) {
    die("Connexion échouée : " . $mysqli->connect_error);
}

// Définir la constante BASE_URL si elle n'existe pas
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Residue_/');
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/../');
}
?>