<?php
global $mysqli;
include('conexao.php');

if (isset($_POST['email']) && isset($_POST['senha'])) {
    if (strlen($_POST['email']) == 0) {
        echo "Preencha seu e-mail";
    } else if (strlen($_POST['senha']) == 0) {
        echo "Preencha sua senha";
    } else {
        $email = $mysqli->real_escape_string($_POST['email']);
        $senha = $mysqli->real_escape_string($_POST['senha']);

        $sql_code = "SELECT * FROM usuarios 
                     WHERE email = '$email' AND senha = '$senha'";

        $sql_query = $mysqli->query($sql_code) or die("Falha na execucao do código SQL: " . $mysqli->error);

        $quantidade = $sql_query->num_rows;

        if ($quantidade == 1) {
            $usuario = $sql_query->fetch_assoc();

            if (!isset($_SESSION)) {
                session_start();
            }

            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];

            header("Location: ../Sistema/portal.php");
        } else {
            echo "Falha ao logar! E-mail ou senha incorretos";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Arsenal:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/reset.css">
    <link rel="stylesheet" href="login.css">
    <title>Portal Cautela</title>
</head>
<body>


    <div id="fundo">
    <div id="logo_cautela">
        <img src="../../imagens/Logo completo 1-Escuro.png" alt="Logo Cautela">
    </div>
    
    <div id="login">
     <form action="login.php" method="post" class="formulario">
                <label for="login">Login</label>
                    <input type="email" placeholder="E-mail" name="email" required id="id_email">
                    <input type="password" placeholder="Senha" name="senha" required id="id_senha" >
                    <input type="submit" placeholder="Entrar" id="id_submit">
            </form>
    </div> 
    
    </div>
</body>
</html>