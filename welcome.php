<?php
session_start();

// Sjekk om brukeren er logget inn
if (!isset($_SESSION['username'])) {
    // Hvis ikke logget inn, omdiriger til login.php
    header("Location: login.php");
    exit;
}

// Hent brukernavnet fra sesjonen
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velkommen</title>
</head>
<body>
    <h1>Velkommen, <?php echo htmlspecialchars($username); ?>!</h1>
    <p>Du er nå logget inn.</p>
    <a href="logout.php">Logg ut</a>
</body>
</html>
