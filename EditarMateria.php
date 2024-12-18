<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="Estilos/editarMaterias/styles.css">
    <link rel="stylesheet" href="stylemenu.css?v=1.0">
    <link rel="shortcut icon" href="chapéu-de-formatura.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Matéria</title>
    
</head>
<body>
    <menu class="Meu_Item">
        <a href="home.php" class="Item_Menu ">
            <i class="fa fa-home"></i> Início
        </a>
        <a href="calendario/index.php" class="Item_Menu">
            <i class="fa fa-calendar"></i> Acompanhamento
        </a>
        <a href="atividadehome.php" class="Item_Menu">
            <i class="fa fa-tasks"></i> Atividade
        </a>
        <a href="materiahome.php" class="Item_Menu active">
            <i class="fa fa-book"></i> Matérias
        </a>
    </menu>
    <center>
        <div>
            <h2>Editar Matéria</h2>

            <?php
            if (isset($_POST["editar"]) && isset($_POST["id"])) {
                $idEditar = $_POST["id"];

                $host = "localhost";
                $usuario = "root";
                $senha = "";
                $banco = "PASSEINESSA";
                $conexao = new mysqli($host, $usuario, $senha, $banco);

                if ($conexao->connect_error) {
                    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
                }

                $sqlMateria = "SELECT ID, NM_MATERIA FROM TB_MATERIAS WHERE ID = ?";
                $stmt = $conexao->prepare($sqlMateria);
                $stmt->bind_param("i", $idEditar);
                $stmt->execute();
                $stmt->bind_result($id, $nmMateria);
                $stmt->fetch();
                $stmt->close();

                $sqlConteudos = "SELECT NM_CONTEUDO FROM TB_CONTEUDOS WHERE ID_MATERIA = ?";
                $stmtConteudos = $conexao->prepare($sqlConteudos);
                $stmtConteudos->bind_param("i", $idEditar);
                $stmtConteudos->execute();
                $resultConteudos = $stmtConteudos->get_result();
                $conteudos = [];

                while ($row = $resultConteudos->fetch_assoc()) {
                    $conteudos[] = $row['NM_CONTEUDO'];
                }

                $stmtConteudos->close();
                $conexao->close();
            }
            ?>
            <form method="POST" action="atualizar_materia.php">
                <div class="Card">
                    <div class="c1">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <label for="nmMateria">Matéria:</label>
                        <br>
                        <input type="text" name="nmMateria" class="input_Text" value="<?php echo $nmMateria; ?>" required>
                        <br>

                        <label for="conteudo">Adicionar novo conteúdo:</label>
                        <br>
                        <input type="text" id="conteudo" class="input_Text">
                        <button type="button" onclick="adicionarConteudo()">Adicionar</button>
                        <ul class="conteudos-list" id="conteudosList">
                            <?php foreach ($conteudos as $conteudo) : ?>
                                <li><?php echo htmlspecialchars($conteudo); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <input type="hidden" name="conteudos" id="conteudosInput">
                    <div class="btn">
                        <input type="submit" value="Salvar" class="Btn_Salvar">
                        <a href="MateriaHome.php"><input type="button" value="Voltar" class="Btn_Cancelar"></a>
                    </div>
                </div>
            </form>
        </div>
    </center>

    <script>
        let conteudos = <?php echo json_encode($conteudos); ?>;

        function adicionarConteudo() {
            const conteudoInput = document.getElementById('conteudo');
            const conteudo = conteudoInput.value;

            if (conteudo) {
                conteudos.push(conteudo);
                atualizarLista();
                conteudoInput.value = '';
            }
        }

        function atualizarLista() {
            const conteudosList = document.getElementById('conteudosList');
            conteudosList.innerHTML = '';

            conteudos.forEach((conteudo) => {
                const li = document.createElement('li');
                li.textContent = conteudo;
                conteudosList.appendChild(li);
            });

            document.getElementById('conteudosInput').value = JSON.stringify(conteudos);
        }

        atualizarLista();
    </script>
</body>
</html>
