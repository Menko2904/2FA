<?php
$host = 'localhost';
$db = '2FA';
$user = 'root';
$pass = 'Bruker99!';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Tilkobling vellykket!";
}
?>
