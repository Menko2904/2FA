<?php
session_start();
include 'db.php';
require 'libs/php-2fa/GoogleAuthenticator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $twoFACode = $_POST['2fa_code'];

    // Finn brukeren i databasen
    $stmt = $conn->prepare("SELECT user_id, password_hash, secret_key FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verifiser passordet
        if (password_verify($password, $row['password_hash'])) {
            $ga = new PHPGangsta_GoogleAuthenticator();
            $secret = $row['secret_key'];

            // Verifiser 2FA-koden
            if ($ga->verifyCode($secret, $twoFACode, 2)) {
                $_SESSION['username'] = $username;
                header("Location: welcome.php");
                exit;
            } else {
                echo "Feil TOTP-kode.";
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
        <label for="2fa_code">TOTP-kode:</label>
        <input type="text" name="2fa_code" required>
        <br>
        <button type="submit">Logg inn</button>
    </form>

    <p>Har du ikke en konto? <a href="register.php">Registrer deg her</a></p>
</body>
</html>

