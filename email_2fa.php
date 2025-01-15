<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Funksjon for å sende en 2FA-kode på e-post
function sendTwoFACode($email) {
    $twoFACode = mt_rand(100000, 999999);
    $_SESSION['2fa_code'] = $twoFACode;
    $_SESSION['2fa_expiry'] = time() + 300; // Koden utløper om 300 sekunder (5 minutter)

    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.sendgrid.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'apikey';
        $mail->Password = ''; // Bytt ut med din API-nøkkel
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('t113studios@gmail.com', '2FA');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Din 2FA-kode';
        $mail->Body = "<p>Din 2FA-kode er:</p><h2>$twoFACode</h2><p>Koden er gyldig i 5 minutter.</p>";

        $mail->send();
        echo "En ny 2FA-kode er sendt til e-posten din.";
    } catch (Exception $e) {
        echo "Kunne ikke sende 2FA-koden. Feil: {$mail->ErrorInfo}";
    }
}

// Håndter verifisering av 2FA-koden
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['2fa_code'])) {
        $inputCode = $_POST['2fa_code'];

        if (isset($_SESSION['2fa_code'], $_SESSION['2fa_expiry'])) {
            if (time() > $_SESSION['2fa_expiry']) {
                echo "2FA-koden er utløpt. Vennligst be om en ny kode.";
                unset($_SESSION['2fa_code']);
                unset($_SESSION['2fa_expiry']);
            } elseif ($inputCode == $_SESSION['2fa_code']) {
                unset($_SESSION['2fa_code']);
                unset($_SESSION['2fa_expiry']);
                header("Location: welcome.php");
                exit;
            } else {
                echo "Feil 2FA-kode.";
            }
        } else {
            echo "Ingen gyldig 2FA-kode er opprettet. Vennligst be om en ny kode.";
        }
    } elseif (isset($_POST['resend_2fa_code'])) {
        // Håndter forespørsel om ny kode
        if (isset($_SESSION['email'])) {
            sendTwoFACode($_SESSION['email']);
        } else {
            echo "Ingen e-postadresse er knyttet til økten.";
        }
    }
} else {
    // Send initial 2FA-kode
    if (isset($_SESSION['email'])) {
        sendTwoFACode($_SESSION['email']);
    } else {
        echo "Ingen e-postadresse er knyttet til økten.";
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Email 2FA</title>
</head>
<body>
    <h2>Skriv inn din 2FA-kode</h2>
    <form action="email_2fa.php" method="POST">
        <label for="2fa_code">2FA-kode:</label>
        <input type="text" name="2fa_code" required>
        <br>
        <button type="submit">Verifiser</button>
    </form>
    <form action="email_2fa.php" method="POST">
        <button type="submit" name="resend_2fa_code">Send ny kode</button>
    </form>
</body>
</html>
