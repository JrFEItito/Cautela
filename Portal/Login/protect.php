<?php
if (!isset($_SESSION)) {
    session_start();
}


if(!isset($_SESSION['id'])){
    die("Voce nao pode acessar essa página, porque nao esta logado. <p><a href=\"../Login/login.php\">Entrar</a></p>");
}

if (!isset($_SESSION['id'])) {
    header("Location: ../Login/login.php");
    exit();
}
?>
