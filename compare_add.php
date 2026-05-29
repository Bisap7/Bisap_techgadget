<?php
session_start();

$id = $_POST['id'];

if (!isset($_SESSION['compare'])) {
    $_SESSION['compare'] = [];
}

if (!in_array($id, $_SESSION['compare'])) {

    if (count($_SESSION['compare']) < 3) {
        $_SESSION['compare'][] = $id;
    }

}

header("Location: products.php");
exit();
?>