<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["nova_imagem"])) {
    $diretorio_destino = __DIR__ . "/imagen/"; // Caminho absoluto da pasta onde a imagem será salva

    // Verifica se a pasta 'imagens' existe, se não, cria ela
    if (!is_dir($diretorio_destino)) {
        mkdir($diretorio_destino, 0777, true); // Cria a pasta
    }

    // Define o nome fixo 'grafico.png' para a imagem
    $arquivo_destino = $diretorio_destino . "grafico.png";

    // Move o arquivo enviado para o caminho com nome fixo
    if (move_uploaded_file($_FILES["nova_imagem"]["tmp_name"], $arquivo_destino)) {
        // Atualiza o arquivo 'imagem_atual.txt' com o caminho fixo
        file_put_contents("imagem_atual.txt", "imagens/grafico.png");
        header("Location: index.php"); // Redireciona para a página principal
        exit;
    } else {
        echo "Erro ao enviar a imagem.";
    }
}
?>
