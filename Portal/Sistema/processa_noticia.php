<?php
// Configuração do banco de dados
$host = "localhost";
$user = "root";
$password = "";
$dbname = "login";

// Conexão com o banco
$conn = new mysqli($host, $user, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar dados do formulário
    $data = $_POST['data'];
    $titulo = $_POST['titulo'];
    $conteudo = $_POST['conteudo'];
    $link = $_POST['link'];

    // Verificar se uma imagem foi enviada
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagemNome = basename($_FILES['imagem']['name']);
        $caminhoImagem = 'uploads/' . $imagemNome;

        // Criar diretório "uploads" se não existir
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Mover imagem para o diretório
        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem)) {
            die("Erro ao fazer upload da imagem.");
        }
    } else {
        $imagemNome = null; // Caso nenhuma imagem tenha sido enviada
    }

    // Inserir notícia no banco (incluindo o link)
    $sql = "INSERT INTO noticias (data, titulo, conteudo, imagem, link) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $data, $titulo, $conteudo, $imagemNome, $link);

    if ($stmt->execute()) {
        echo "Notícia adicionada com sucesso!";
    } else {
        echo "Erro ao adicionar notícia: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
