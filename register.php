<?php
require_once 'includes/db.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($mysqli, $_POST['username']);
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = $_POST['password'];

    $checkUser = $mysqli->query("SELECT id FROM User WHERE email = '$email' OR username = '$username'");
    
    if ($checkUser->num_rows > 0) {
        $error = "L'email ou le nom d'utilisateur est déjà utilisé.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO `User` (username, email, password, balance, role) VALUES ('$username', '$email', '$hashedPassword', 0.00, 'user')";
        
        if ($mysqli->query($sql)) {
            $_SESSION['user_id'] = $mysqli->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Erreur lors de l'inscription.";
        }
    }
}
?>