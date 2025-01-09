<?php
session_start();
session_destroy(); // Ødelegger alle sesjonsdata
header("Location: login.php"); // Omdirigerer til innloggingssiden
exit;
?>
