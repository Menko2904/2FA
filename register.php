<?php
session_start();
include 'db.php';
require 'libs/php-2fa/GoogleAuthenticator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Generer hashed passord
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Generer en hemmelig nøkkel for 2FA
    $ga = new PHPGangsta_GoogleAuthenticator();
    $secret = $ga->createSecret();

    // Forbered SQL-spørringen med hemmelig nøkkel inkludert
    $stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash, secret_key) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $passwordHash, $secret);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username;
        $_SESSION['secret_key'] = $secret; // Lagre hemmelig nøkkel i sesjonen

        // Generer QR-koden for 2FA og vis den til brukeren
        $qrCodeUrl = $ga->getQRCodeGoogleUrl("DittProsjektnavn", $secret);
        echo "Skann denne QR-koden med din 2FA-app:";
        echo '<img src="'.$qrCodeUrl.'" alt="QR-kode">';
        echo "<p>Din hemmelige nøkkel: $secret</p>";

        echo '<p><a href="welcome.php">Fortsett til velkomstsiden</a></p>';
    } else {
        echo "Feil under registrering.";
    }
}
?>


<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Registrering</title>
</head>
<body>
    <h2>Registrer deg</h2>
    <form action="register.php" method="POST">
        <label for="username">Brukernavn:</label>
        <input type="text" name="username" required>
        <br>
        <label for="email">E-post:</label>
        <input type="email" name="email" required>
        <br>
        <label for="password">Passord:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Registrer</button>
    </form>
    <p>Har du allerede en konto? <a href="login.php">Logg inn her</a></p>
</body>
</html>

