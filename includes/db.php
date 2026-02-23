<?php
// Paramètres XAMPP par défaut
$host = "localhost";
$user = "root";
$pass = ""; 
$dbname = "residue_";

$mysqli = new mysqli($host, $user, $pass, $dbname);

// Vérification (important pour le point "Gestion d'erreurs" à l'oral)
if ($mysqli->connect_error) {
    die("Connexion échouée : " . $mysqli->connect_error);
}
?>