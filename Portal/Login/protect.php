<?php
if (!isset($_SESSION)) {
    session_start();
}

<<<<<<< HEAD
    if(!isset($_SESSION['id'])){
        die("Voce nao pode acessar essa página, porque nao esta logado. <p><a href=\"../Login/login.php\">Entrar</a></p>");
    }
    
?>
=======
if (!isset($_SESSION['id'])) {
    header("Location: ../Login/login.php");
    exit();
}
?>
>>>>>>> 2cb1587899ade9607bd3c599242416d8029139d7
