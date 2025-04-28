<?php
$password = 'password';
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

echo "INSERT INTO users (username, password) VALUES ('admin@gmail.com', '$hashedPassword');";
?>
