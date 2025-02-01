<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura e sanitiza os dados do formulário
    $nome = htmlspecialchars(trim($_POST['nome']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $telefone = htmlspecialchars(trim($_POST['telefone']));
    $detalhes = htmlspecialchars(trim($_POST['detalhes']));

    // Verifica se os campos obrigatórios estão preenchidos e se o email é válido
    if (empty($nome) || empty($email) || empty($telefone)) {
        die("Erro: Todos os campos obrigatórios devem ser preenchidos!");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Erro: O e-mail fornecido é inválido.");
    }

    // Endereço de email para onde os dados serão enviados
    $destinatario = 'igorpieralini@gmail.com';
    $assunto = 'Novo cadastro de contato';

    // Constrói o corpo da mensagem
    $conteudo = "Nome: $nome\n";
    $conteudo .= "Email: $email\n";
    $conteudo .= "Telefone: $telefone\n";
    $conteudo .= "Mais Detalhes: $detalhes\n";

    // Configura os headers do e-mail
    $headers = "From: reply-to@cautela.com\r\n";
    $headers .= "Reply-To: \r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Envia o e-mail
    if (mail($destinatario, $assunto, $conteudo, $headers)) {
        echo '<script>alert("Obrigado! Seu formulário foi enviado com sucesso.");</script>';
        exit();
    } else {
        echo '<script>alert("Ocorreu um erro ao enviar o formulário. Tente novamente mais tarde.");</script>';
    }
} else {
    http_response_code(405); // Resposta HTTP 405 Método não permitido
    die("Erro: Método inválido.");
}
