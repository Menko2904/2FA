<?php
session_start();
require 'libs/php-2fa/GoogleAuthenticator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['2fa_code'])) {
    $inputCode = $_POST['2fa_code'];
    $ga = new PHPGangsta_GoogleAuthenticator();
    $secret = $_SESSION['secret_key'];

    if ($ga->verifyCode($secret, $inputCode, 2)) {
        unset($_SESSION['secret_key']);
        header("Location: welcome.php");
        exit;
    } else {
        echo "Feil TOTP-kode.";
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>App 2FA</title>
</head>
<body>
    <h2>Skriv inn din 2FA-kode fra appen</h2>
    <form action="app_2fa.php" method="POST">
        <label for="2fa_code">2FA-kode:</label>
        <input type="text" name="2fa_code" required>
        <br>
        <button type="submit">Verifiser</button>
    </form>
</body>
</html>
