<?php

// Charger les variables d'environnement depuis le fichier .env
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
        putenv(trim($name) . "=" . trim($value));
    }
}

loadEnv(__DIR__ . '/../.env');

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');

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