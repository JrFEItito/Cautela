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

        <script>

        </script>

    </head>

    <body>

        <div>
            <img src="" alt="">
        </div>

        <header>
            <div class="titles">
                <h1>BEM VINDO, <?php echo $_SESSION['nome'];?></h1>

                <h2>Ferramente de Notícias</h2>
            </div>

            <div>
                <button></button>
            </div>
        </header>

        <main>
            <h3>Notícias atuais</h3>

            <div class="noticias-atuais">
                <div id="texto-noticias">

                    <div class="linha2"></div>

                    <h2>Lorem Ipsum</h2>

                    <p>
                        lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus ut lacus euismod, efficitur massa et, congue massa.
                    </p>

                </div>

                <div class="noticias">
                    <?php
                    // Defina o diretório base onde as imagens estão armazenadas
                    $diretorioImagens = 'uploads/';

                    // Verifica se há notícias no banco de dados
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) { ?>
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

                                    <!-- Botão para carregar a notícia no formulário -->
                                    <a href="?id=<?php echo $row['id']; ?>" class="editar-btn">Editar</a>
                                </div>
                            </div>
                        <?php }
                    } else {
                        echo "<p>Nenhuma notícia encontrada.</p>";
                    }
                    ?>
                </div>
            </div>
            <?php
            $diretorioImagens = 'uploads/';
            $noticiaSelecionada = null;

            // Excluir notícia
            if (isset($_GET['excluir'])) {
                $id = $_GET['excluir'];

                // Buscar a imagem antes de excluir
                $sql = "SELECT imagem FROM noticias WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $noticia = $resultado->fetch_assoc();

                if ($noticia && !empty($noticia['imagem'])) {
                    $caminhoImagem = $diretorioImagens . $noticia['imagem'];
                    if (file_exists($caminhoImagem)) {
                        unlink($caminhoImagem);
                    }
                }

                // Excluir do banco de dados
                $sql = "DELETE FROM noticias WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    echo "<p style='color: green;'>Notícia excluída com sucesso!</p>";
                } else {
                    echo "<p style='color: red;'>Erro ao excluir notícia.</p>";
                }
            }

            // Verifica se um ID foi enviado para edição
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $sql = "SELECT * FROM noticias WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $noticiaSelecionada = $result->fetch_assoc();
            }

            // Atualizar a notícia no banco de dados
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
                $id = $_POST['id'];
                $data = $_POST['data'];
                $titulo = $_POST['titulo'];
                $conteudo = $_POST['conteudo'];
                $link = $_POST['link'];

                if (!empty($_FILES['imagem']['name'])) {
                    $imagemNome = basename($_FILES['imagem']['name']);
                    $caminhoImagem = $diretorioImagens . $imagemNome;
                    move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem);

                    $sql = "UPDATE noticias SET data=?, titulo=?, conteudo=?, link=?, imagem=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssi", $data, $titulo, $conteudo, $link, $imagemNome, $id);
                } else {
                    $sql = "UPDATE noticias SET data=?, titulo=?, conteudo=?, link=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssi", $data, $titulo, $conteudo, $link, $id);
                }

                if ($stmt->execute()) {
                    echo "<p style='color: green;'>Notícia atualizada com sucesso!</p>";
                    $noticiaSelecionada = null;
                } else {
                    echo "<p style='color: red;'>Erro ao atualizar a notícia.</p>";
                }
            }

            // Consulta todas as notícias
            $sql = "SELECT * FROM noticias ORDER BY data DESC";
            $result = $conn->query($sql);
            ?>

            <?php if ($noticiaSelecionada) { ?>
                <!-- Formulário de Edição -->
                <div class="add-noticia">
                    <h3>Editar Notícia</h3>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $noticiaSelecionada['id']; ?>">

                        <label>Data:
                            <input type="date" name="data" required value="<?php echo $noticiaSelecionada['data']; ?>">
                        </label><br>

                        <label>Título:
                            <input type="text" name="titulo" required value="<?php echo $noticiaSelecionada['titulo']; ?>">
                        </label><br>

                        <label>Conteúdo:
                            <textarea name="conteudo" rows="5" required><?php echo $noticiaSelecionada['conteudo']; ?></textarea>
                        </label><br>

                        <label>Link:
                            <input type="text" name="link" required value="<?php echo $noticiaSelecionada['link']; ?>">
                        </label><br>

                        <label>Imagem:
                            <input type="file" name="imagem" accept="image/*">
                        </label><br>

                        <input type="submit" value="Atualizar Notícia">
                    </form>

                    <!-- Botão para cancelar edição e voltar para a lista -->
                    <a href="?" class="cancelar-btn">Voltar</a>
                </div>

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
            <?php } else { ?>

            <?php } ?>



            <div class="add-noticia">
                <form action="processa_noticia.php" method="POST" enctype="multipart/form-data">
                    <label>
                        Data:
                        <input type="date" name="data" required>
                    </label>
                    <br>
                    <label>
                        Título:
                        <input type="text" name="titulo" required>
                    </label>
                    <br>
                    <label>
                        Conteúdo:
                        <textarea name="conteudo" rows="5" required></textarea>
                    </label>
                    <br>
                    <label>
                        Link:
                        <input type="text" name="link" required>
                        Imagem:
                        <input type="file" name="imagem" accept="image/*">
                    </label>
                    <br>
                    <input type="submit" value="Adicionar Notícia">
                </form>
            </div>
        </main>

    </body>

</html>