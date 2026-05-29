<?php
session_start();

unset($_SESSION['compare']);

header("Location: compare.php");
exit();
?>