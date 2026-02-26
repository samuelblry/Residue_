<?php

$host = "localhost";
$user = "root";
$pass = ""; 
$dbname = "residue_";

$mysqli = new mysqli($host, $user, $pass, $dbname);


if ($mysqli->connect_error) {
    die("Connexion échouée : " . $mysqli->connect_error);
}


if (!defined('BASE_URL')) {
    define('BASE_URL', '/Residue_/');
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/../');
}
?>