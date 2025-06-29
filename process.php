<?php
// passwort, hardcoded
$correctPassword = 'password123';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // validierung der benutzerdaten
    if (empty($username) || empty($password)) {
        echo "<p style='color: red;'>Benutzername und Passwort benoetigt</p>";
    } elseif ($password !== $correctPassword) {
        echo "<p style='color: red;'>Passwort ungueltig</p>";
    } else {
        // erfolgreicher Login
        echo "<p style='color: green;'>Willkommen, " . htmlspecialchars($username) . "!</p>";
    }
}
?>
