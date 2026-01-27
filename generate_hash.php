<?php
// Generuj hasło bcrypt dla testowego użytkownika
$password = "password123";
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Hasło: " . $password . "\n";
echo "Hash: " . $hash . "\n";
?>
