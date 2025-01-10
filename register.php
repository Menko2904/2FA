<?php
session_start();
require_once 'libs/php-2fa/GoogleAuthenticator.php'; // Eller bruk Composer-autoloader
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $twofa_method = $_POST['twofa_method']; // Hent valgt 2FA-metode

    // Sjekk om brukernavnet allerede finnes
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Brukernavn allerede tatt. Vennligst velg et annet.";
        exit;
    }

    // Sjekk om e-posten allerede finnes
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "E-post allerede registrert. Vennligst velg en annen.";
        exit;
    }

    // Generer hashed passord
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Generer en hemmelig nøkkel for app-basert 2FA hvis valgt
    $secret = null;
    if ($twofa_method === 'app') {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $secret = $ga->createSecret();
    }

    // Sett inn data i databasen
    $stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash, secret_key, twofa_method) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $passwordHash, $secret, $twofa_method);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username;

        if ($twofa_method === 'app') {
            // Generer QR-kode for app-basert 2FA
            $qrCodeUrl = $ga->getQRCodeGoogleUrl('2FA', $secret);
            echo "Skann denne QR-koden med din 2FA-app:";
            echo '<img src="' . $qrCodeUrl . '" alt="QR-kode">';
            echo "<p>Din hemmelige nøkkel: $secret</p>";
        } else {
            // Bekreft at e-post er valgt som 2FA-metode
            echo "Du har valgt e-post som 2FA-metode. Sjekk e-posten din for 2FA-koder ved pålogging.";
        }
    } else {
        echo "Feil under registrering.";
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
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
        <label for="twofa_method">Velg 2FA-metode:</label>
        <select name="twofa_method" required>
            <option value="app">2FA-app</option>
            <option value="email">E-post</option>
        </select>
        <br>
        <button type="submit">Registrer</button>
    </form>
    <p>Har du allerede en konto? <a href="login.php">Logg inn her</a></p>
</body>
</html>
