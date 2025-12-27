<?php
$password = 'password123';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Password: $password<br>";
echo "Generated bcrypt hash: $hash<br>";
?>
