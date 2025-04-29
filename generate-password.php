<?php
$password = 'admin12345'; // The password you want to set for the admin
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo $hashedPassword;
?>
