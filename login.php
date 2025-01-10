<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Finn brukeren i databasen
    $stmt = $conn->prepare("SELECT user_id, password_hash, email, secret_key, twofa_method FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verifiser passordet
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $row['email'];
            $_SESSION['secret_key'] = $row['secret_key'];
            $_SESSION['twofa_method'] = $row['twofa_method'];

            // Sjekk hvilken 2FA-metode som er valgt og omdiriger
            if ($row['twofa_method'] === 'email') {
                header("Location: email_2fa.php");
                exit;
            } elseif ($row['twofa_method'] === 'app') {
                header("Location: app_2fa.php");
                exit;
            }
        } else {
            echo "Feil brukernavn eller passord.";
        }
    } else {
        echo "Feil brukernavn eller passord.";
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Logg inn</h2>
    <form action="login.php" method="POST">
        <label for="username">Brukernavn:</label>
        <input type="text" name="username" required>
        <br>
        <label for="password">Passord:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Logg inn</button>
    </form>
</body>
</html>
