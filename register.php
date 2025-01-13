<?php
session_start();
require_once 'libs/php-2fa/GoogleAuthenticator.php';
include 'db.php';

// Generer CSRF-token for beskyttelse
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$username = $email = $password = $twofa_method = ""; // Behold feltene ved feil
$error = ""; // Variabel for å lagre feilmeldinger

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Ugyldig forespørsel.";
    } else {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];
        $twofa_method = $_POST['twofa_method'];

        if (!$email) {
            $error = "Ugyldig e-postadresse.";
        } elseif (strlen($username) < 3 || strlen($username) > 20) {
            $error = "Brukernavnet må være mellom 3 og 20 tegn.";
        } elseif (strlen($password) < 8) {
            $error = "Passordet må være minst 8 tegn langt.";
        } else {
            // Sjekk om brukernavnet allerede finnes
            $stmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Brukernavn allerede tatt. Vennligst velg et annet.";
            } else {
                // Sjekk om e-posten allerede finnes
                $stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "E-post allerede registrert. Vennligst velg en annen.";
                } else {
                    // Generer hashed passord
                    $options = ['cost' => 12];
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT, $options);

                    // Generer en hemmelig nøkkel for 2FA hvis valgt metode er app
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
                            // Generer QR-kode
                            $qrCodeUrl = $ga->getQRCodeGoogleUrl('2FA', $secret);
                            echo "Skann denne QR-koden med din 2FA-app:";
                            echo '<img src="' . htmlspecialchars($qrCodeUrl, ENT_QUOTES, 'UTF-8') . '" alt="QR-kode">';
                            echo "<p>Din hemmelige nøkkel: " . htmlspecialchars($secret, ENT_QUOTES, 'UTF-8') . "</p>";
                        } else {
                            echo "Registrering vellykket. Du kan nå logge inn.";
                        }
                        exit;
                    } else {
                        $error = "Feil under registrering.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrering</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Registrer deg</h2>

        <!-- Vis feilmelding hvis noe går galt -->
        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <label for="username">Brukernavn:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            <br>
            <label for="email">E-post:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            <br>
            <label for="password">Passord:</label>
            <input type="password" name="password" required>
            <br>
            <label for="twofa_method">Velg 2FA-metode:</label>
            <select name="twofa_method" required>
                <option value="app" <?php if ($twofa_method === 'app') echo 'selected'; ?>>2FA-app</option>
                <option value="email" <?php if ($twofa_method === 'email') echo 'selected'; ?>>E-post</option>
            </select>
            <br>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit">Registrer</button>
        </form>
        <p>Har du allerede en konto? <a href="login.php">Logg inn her</a></p>
    </div>
</body>
</html>
