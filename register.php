<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $passwordHash);
    if ($stmt->execute()) {
        $_SESSION['username'] = $username;  // Lagre brukernavnet i sesjonen
        header("Location: welcome.php");    // Omdiriger til welcome.php
        exit;
    } else {
        echo "Feil under registrering.";
    }
}

require 'libs/php-2fa/GoogleAuthenticator.php';

$ga = new PHPGangsta_GoogleAuthenticator();
$secret = $ga->createSecret();  // Generer en hemmelig nøkkel

echo "Skann denne QR-koden med din 2FA-app:";
echo '<img src="'.$ga->getQRCodeGoogleUrl("DittProsjektnavn", $secret).'" alt="QR-kode">';
echo "<p>Din hemmelige nøkkel: $secret</p>";

// Lagre den hemmelige nøkkelen i databasen sammen med brukeren
$stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash, secret_key) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $passwordHash, $secret);

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

