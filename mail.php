<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $detalhes = $_POST['detalhes'];

    // Endereço de email para onde os dados serão enviados
    $destinatario = 'igorpieralini@gmail.com';

    // Assunto do email
    $assunto = 'Novo cadastro de contato';

    // Constrói o corpo da mensagem
    $conteudo = "Nome: $nome\n";
    $conteudo .= "Email: $email\n";
    $conteudo .= "Telefone: $telefone\n";
    $conteudo .= "Mais Detalhes: $detalhes\n";

    // Envia o email
    if (mail($destinatario, $assunto, $conteudo)) {
        echo '<script>alert("Obrigado por submeter o formulário. Seus dados foram enviados com sucesso.");</script>';
        exit();
    } else {
        echo '<script>alert("Ocorreu um erro ao enviar o formulário. Por favor, tente novamente.");</script>';
    }
} else {
    http_response_code(405); // Resposta HTTP 405 Método não permitido
}
?>