<?php
include('../Login/protect.php');

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

// Buscar notícias
$sql = "SELECT * FROM noticias ORDER BY data DESC";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../style/reset.css">
    <link rel="stylesheet" href="portal.css">
    <title>Portal Cautela</title>
    <link href="https://fonts.googleapis.com/css2?family=Arsenal:wght@400&display=swap" rel="stylesheet">
</head>

<body>
<form id="form-menu" method="POST">
    <!-- Botão Logout -->
    <a href="?acao=logout">
        <img src="../../imagens/logout.png" style="width:80%; margin-top: 100%;" alt="Logout">
    </a>

    <a href="javascript:void(0);" onclick="toggleElements(1)">
        <img src="../../imagens/ferramentaGrafico.png" style="width:80%; margin-top: 100%;" alt="Logout">
    </a>

    <a href="javascript:void(0);" onclick="toggleElements(2)">
        <img src="../../imagens/ferramenteNoticias.png" style="width:80%; margin-top: 100%;" alt="Logout">
    </a>
</form>



<main>
    <section class="cabecalho">
        <div class="titles">
            <h1>BEM VINDO, <?php echo $_SESSION['nome'];?></h1>
            <h2>Ferramenta de Notícias</h2>
        </div>

        <div class="hex-button" id="btnToggle">
            <img src="../../imagens/add-noticia.png" alt="Botão">
        </div>
    </section>

    <?php
    // Configuração do diretório onde as imagens são armazenadas
    $diretorioImagens = 'uploads/';
    $noticiaSelecionada = null;

    // Verifica se foi enviado um ID para edição ou visualização de notícia
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Prepara a consulta para obter a notícia pelo ID
        $sql = "SELECT * FROM noticias WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            echo "<p style='color: red;'>Erro ao preparar a consulta SQL para edição.</p>";
        } else {
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                echo "<p style='color: red;'>Erro ao executar a consulta SQL para edição: " . $stmt->error . "</p>";
            } else {
                $result = $stmt->get_result();
                $noticiaSelecionada = $result->fetch_assoc();
                if (!$noticiaSelecionada) {
                    echo "<p style='color: red;'>Notícia não encontrada.</p>";
                }
            }
        }
    }

    // Verifica se foi solicitado excluir a notícia
    if (isset($_GET['excluir'])) {
        $idExcluir = $_GET['excluir'];

        // Buscar a imagem antes de excluir
        $sql = "SELECT imagem FROM noticias WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            echo "<p style='color: red;'>Erro ao preparar a consulta SQL para buscar imagem.</p>";
        } else {
            $stmt->bind_param("i", $idExcluir);
            if (!$stmt->execute()) {
                echo "<p style='color: red;'>Erro ao executar a consulta SQL para buscar imagem: " . $stmt->error . "</p>";
            } else {
                $resultado = $stmt->get_result();
                $noticia = $resultado->fetch_assoc();

                if ($noticia && !empty($noticia['imagem'])) {
                    $caminhoImagem = $diretorioImagens . $noticia['imagem'];
                    if (file_exists($caminhoImagem)) {
                        if (!unlink($caminhoImagem)) {
                            echo "<p style='color: red;'>Erro ao excluir a imagem.</p>";
                        }
                    } else {
                        echo "<p style='color: orange;'>Imagem não encontrada, mas a notícia será excluída.</p>";
                    }
                }
            }

            // Exclui a notícia do banco de dados
            $sql = "DELETE FROM noticias WHERE id = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                echo "<p style='color: red;'>Erro ao preparar a consulta SQL para exclusão.</p>";
            } else {
                $stmt->bind_param("i", $idExcluir);
                if (!$stmt->execute()) {
                    echo "<p style='color: red;'>Erro ao excluir a notícia: " . $stmt->error . "</p>";
                } else {
                    echo "<p style='color: green;'>Notícia excluída com sucesso!</p>";
                    // Redireciona para a página inicial após a exclusão
                    header("Location: index.php");
                    exit();
                }
            }
        }
    }

    // Atualiza a notícia após o formulário de edição ser submetido
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $data = $_POST['data'];
        $titulo = $_POST['titulo'];
        $conteudo = $_POST['conteudo'];
        $link = $_POST['link'];

        // Se houver uma nova imagem, faz o upload e atualiza
        if (!empty($_FILES['imagem']['name'])) {
            $imagemNome = basename($_FILES['imagem']['name']);
            $caminhoImagem = $diretorioImagens . $imagemNome;

            // Verifica se o arquivo é uma imagem válida
            if (getimagesize($_FILES['imagem']['tmp_name'])) {
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem)) {
                    // Atualiza os dados no banco de dados
                    $sql = "UPDATE noticias SET data=?, titulo=?, conteudo=?, link=?, imagem=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssi", $data, $titulo, $conteudo, $link, $imagemNome, $id);
                } else {
                    echo "<p style='color: red;'>Erro ao mover o arquivo de imagem.</p>";
                }
            } else {
                echo "<p style='color: red;'>O arquivo enviado não é uma imagem válida.</p>";
            }
        } else {
            // Caso não haja imagem, apenas atualiza os dados da notícia
            $sql = "UPDATE noticias SET data=?, titulo=?, conteudo=?, link=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $data, $titulo, $conteudo, $link, $id);
        }

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Notícia atualizada com sucesso!</p>";
            // Redireciona após atualização
            header("Location: ?id=" . $id);
            exit();
        } else {
            echo "<p style='color: red;'>Erro ao atualizar a notícia: " . $stmt->error . "</p>";
        }
    }

    // Consulta todas as notícias para exibição
    $sql = "SELECT * FROM noticias ORDER BY data DESC";
    $result = $conn->query($sql);
    ?>


    <section id="noticias-atuais">
        <h3>Notícias atuais</h3>

        <!-- Exibindo as notícias -->
        <div class="noticias-atuais">
            <div id="carrossel-content" class="noticias">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) { ?>
                        <div>
                            <a href="?id=<?php echo $row['id']; ?>&t=<?php echo time(); ?>" class="editar-btn">
                                <img src="../../imagens/lapis.png" alt="Botão">
                            </a>

                            <div class="noticia">
                                <div class="img-noticia">
                                    <?php if (!empty($row['imagem'])) { ?>
                                        <img src="<?php echo $diretorioImagens . $row['imagem']; ?>" alt="Imagem da notícia">
                                    <?php } ?>
                                </div>
                                <div class="conteudo-noticia">
                                    <p class="data-noticia">
                                        <?php echo date("d M Y", strtotime($row['data'])); ?>
                                    </p>
                                    <h5><?php echo htmlspecialchars($row['titulo']); ?></h5>
                                    <p><?php echo nl2br(htmlspecialchars($row['conteudo'])); ?></p>

                                    <div class="btn-sbs">
                                        <div class="hexagono-noticia">
                                            <div class="hexagono-symbol">></div>
                                        </div>
                                        <div>Saiba Mais</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php }
                } else {
                    echo "<p>Nenhuma notícia encontrada.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <section id="ferramente-noticias">
        <?php


        // Exibindo formulário de edição quando uma notícia é selecionada
        if ($noticiaSelecionada) { ?>
            <div id="editar-noticia" class="add-noticia">
                <div class="noticia">
                    <div class="img-noticia">
                        <?php if (!empty($noticiaSelecionada['imagem'])) { ?>
                            <img src="<?php echo $diretorioImagens . $noticiaSelecionada['imagem']; ?>" alt="Imagem da notícia">
                        <?php } ?>
                    </div>
                    <div class="conteudo-noticia">
                        <p class="data-noticia">
                            <?php echo date("d M Y", strtotime($noticiaSelecionada['data'])); ?>
                        </p>
                        <h5><?php echo htmlspecialchars($noticiaSelecionada['titulo']); ?></h5>
                        <p><?php echo nl2br(htmlspecialchars($noticiaSelecionada['conteudo'])); ?></p>
                    </div>
                </div>

                <div id="edit-noticia">
                    <h4>Editar Notícia</h4>
                    <form id="form-edit" method="POST" enctype="multipart/form-data">
                        <input class="form-input" type="hidden" name="id" value="<?php echo $noticiaSelecionada['id']; ?>">

                        <label>
                            <input class="form-input" type="date" name="data" required value="<?php echo $noticiaSelecionada['data']; ?>">
                        </label><br>

                        <label>
                            <input class="form-input" type="text" name="titulo" required value="<?php echo $noticiaSelecionada['titulo']; ?>">
                        </label><br>

                        <label>
                            <textarea class="form-input" name="conteudo" rows="5" required><?php echo $noticiaSelecionada['conteudo']; ?></textarea>
                        </label><br>

                        <label>
                            <input class="form-input" type="text" name="link" required value="<?php echo $noticiaSelecionada['link']; ?>">
                        </label><br>

                        <label>
                            <input class="form-input" type="file" name="imagem" accept="image/*">
                        </label><br>

                        <label>
                            <input id="btn-confirm-editar" class="form-input" type="submit" value="Atualizar Notícia">
                            <button type="button" id="btn-excluir" onclick="excluirNoticia(<?php echo $noticiaSelecionada['id']; ?>)">Excluir Notícia</button>
                        </label>
                    </form>
                </div>
            </div>
        <?php }
        ?>

        <div id="add-noticia"  class="add-noticia">
            <h4>Adicionar</h4>
            <form id="form-add-noticia" action="processa_noticia.php" method="POST" enctype="multipart/form-data">
                <label id="primeira-label">
                    <input class="form-input" type="date" name="data" placeholder="DATA" required>
                    <input class="form-input" type="text" name="titulo" placeholder="TÍTULO" required>
                </label>
                <label id="segunda-label">
                    <input class="form-input" type="text" name="link" placeholder="LINK" required>
                    <input class="form-input" type="file" name="imagem" accept="image/*">
                    <textarea class="form-input" name="conteudo" placeholder="RESUMO" rows="5" required></textarea>
                    <input class="form-submit" type="submit" value="SALVAR">
                </label>
            </form>
        </div>

    </section>



    <section id="troca-grafico" style="display: none;">

        <div class="titles">
            <h1>BEM VINDO, <?php echo $_SESSION['nome'];?></h1>
            <h2>Ferramenta do Gráfico</h2>
        </div>

        <img id="img-sinistro" src="imagen/grafico.png" alt="Gráfico atualizado">

        <form id="form-grafico" action="trocaImagem.php" method="POST" enctype="multipart/form-data">
            <input type="file" id="input-file" name="nova_imagem" accept="image/*" required>
            <label for="input-file">Escolher Imagem</label>
            <button type="submit">Trocar Imagem</button>
        </form>
    </section>

</main>

<script>
    document.querySelector("form").addEventListener("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(this);

        fetch("trocaImagem.php", {
            method: "POST",
            body: formData
        }).then(response => {
            return response.text();
        }).then(data => {
            document.getElementById("img-sinistro").src = "carregarImagem.php?" + new Date().getTime(); // Atualiza a imagem sem cache
        }).catch(error => console.error("Erro:", error));
    });
</script>

<script>
    function excluirNoticia(id) {
        if (confirm("Tem certeza que deseja excluir esta notícia?")) {
            window.location.href = "?excluir=" + id;
        }
    }
</script>

<script>

    // Garantir que #editar-noticia comece com display: none
    document.getElementById("add-noticia").style.display = "none";

    // Exibir #add-noticia ao clicar no botão de imagem
    document.getElementById("btnToggle").addEventListener("click", function() {
        document.getElementById("add-noticia").style.display = "flex"; // Exibe o #add-noticia
        document.body.style.height = "380vh"; // Ajusta a altura da página de forma automática
    });

    // Exibir #editar-noticia ao clicar no botão de editar
    document.querySelectorAll(".editar-btn").forEach(button => {
        button.addEventListener("click", function(event) {
            document.getElementById("editar-noticia").style.display = "flex"; // Exibe o #editar-noticia
            document.body.style.height = "150vh"; // Ajusta a altura da página de forma automática
        });
    });

</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const carrosselContent = document.getElementById('carrossel-content');
        const noticias = document.querySelectorAll('.noticia');

        // Ajuste do scroll para iniciar no começo
        carrosselContent.scrollLeft = 0;
    });
</script>

<script>
    function toggleElements(option) {
        // Selecionando os elementos
        var cabecalho = document.querySelector('.cabecalho');
        var noticiasAtuais = document.querySelector('#noticias-atuais');
        var ferramentaNoticias = document.querySelector('#ferramente-noticias');
        var trocaGrafico = document.querySelector('#troca-grafico');

        if (option === 1) {
            // Para o primeiro link (exibir #troca-grafico e esconder outros)
            cabecalho.style.display = 'none';
            noticiasAtuais.style.display = 'none';
            ferramentaNoticias.style.display = 'none';
            trocaGrafico.style.display = 'flex';
            document.body.style.height = "140vh"; // Ajusta a altura da página de forma automática
        } else if (option === 2) {
            // Para o segundo link (mostrar os elementos anteriores e esconder #troca-grafico)
            cabecalho.style.display = 'flex';
            noticiasAtuais.style.display = 'flex';
            ferramentaNoticias.style.display = 'flex';
            trocaGrafico.style.display = 'none';
            document.body.style.height = "300vh"; // Ajusta a altura da página de forma automática
        }
    }
</script>

</body>

</html>