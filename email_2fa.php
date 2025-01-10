<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['2fa_code'])) {
    $inputCode = $_POST['2fa_code'];

    if (isset($_SESSION['2fa_code']) && $inputCode == $_SESSION['2fa_code']) {
        unset($_SESSION['2fa_code']);
        header("Location: welcome.php");
        exit;
    } else {
        echo "Feil 2FA-kode.";
    }
} else {
    // Generer og send 2FA-kode på e-post
    $twoFACode = mt_rand(100000, 999999);
    $_SESSION['2fa_code'] = $twoFACode;

    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.sendgrid.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'apikey';
        $mail->Password = 'sett api key'; // Bytt ut med din API-nøkkel
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('t113studios@gmail.com', '2FA');
        $mail->addAddress($_SESSION['email']);

        $mail->isHTML(true);
        $mail->Subject = 'Din 2FA-kode';
        $mail->Body = "<p>Din 2FA-kode er:</p><h2>$twoFACode</h2>";

        $mail->send();
        echo "2FA-kode sendt til e-posten din.";
    } catch (Exception $e) {
        echo "Kunne ikke sende 2FA-koden. Feil: {$mail->ErrorInfo}";
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
</body>
</html>
