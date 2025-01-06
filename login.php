<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password_hash FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password_hash'])) {
            echo "Innlogging vellykket!";
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
    <h2>Login</h2>
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