<?php
session_start();

$userId = $_GET['usuario_id'] ?? null; 
if (!$userId) {
    header("Location: login/login.php");
    exit();
}

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "PASSEINESSA";

$conexao = new mysqli($host, $usuario, $senha, $banco);
if ($conexao->connect_error) {
    die("Erro ao conectar com o banco de dados.");
}

$sqlVerificaUsuario = "SELECT COUNT(*) as count FROM TB_USUARIOS WHERE ID = ?";
$stmtVerificaUsuario = $conexao->prepare($sqlVerificaUsuario);
$stmtVerificaUsuario->bind_param("i", $userId);
$stmtVerificaUsuario->execute();
$result = $stmtVerificaUsuario->get_result();
$userExists = $result->fetch_assoc()['count'] > 0;

if (!$userExists) {
    header("Location: login/login.php");
    exit();
}

$stmtVerificaUsuario->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="chapéu-de-formatura.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="stylemenu.css?v=1.0">
    <link rel="stylesheet" href="Estilos/cadastrarMateria/style.css">
    <title>Cadastrar Matéria</title>
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
    <div>
        <a href="MateriaHome.php" class="Voltar">Voltar à lista de matérias</a>
    </div>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?usuario_id=' . $userId); ?>">
        <div class="Center">
            <div class="Card">
                <h2>Cadastro de Matéria</h2>
                <div class="c1">
                    <label for="nome">Nome:</label><br>
                    <input type="text" placeholder="Nome da matéria" class="input_Text" name="nome" id="nome" required><br><br>

                    <label for="conteudo">Conteúdo:</label><br>
                    <input type="text" placeholder="Adicionar conteúdo" class="input_Text" id="conteudo">
                    <button type="button" onclick="adicionarConteudo()">Adicionar</button>
                    <ul class="conteudos-list" id="conteudosList"></ul>
                </div>

                <input type="hidden" name="conteudos" id="conteudosInput">

                <div class="btn">
                    <input type="submit" value="Salvar" class="Btn_Salvar">
                    <a href="MateriaHome.php"><button type="button" class="Btn_Cancelar">Voltar</button></a>
                </div>
            </div>
        </div>
    </form>

    <script>
        let conteudos = [];

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
    </script>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nomeMateria = htmlspecialchars($_POST['nome']); 
        $conteudos = json_decode($_POST['conteudos'], true);

        $conexao->begin_transaction();

        try {
            $sqlMateria = "INSERT INTO TB_MATERIAS (NM_MATERIA, ID_USUARIO) VALUES (?, ?)";
            $stmtMateria = $conexao->prepare($sqlMateria);
            $stmtMateria->bind_param("si", $nomeMateria, $userId); 
            if (!$stmtMateria->execute()) {
                throw new Exception("Erro ao cadastrar a matéria: " . $stmtMateria->error);
            }

            $idMateria = $stmtMateria->insert_id; 

            $sqlConteudo = "INSERT INTO TB_CONTEUDOS (NM_CONTEUDO, ID_MATERIA) VALUES (?, ?)";
            $stmtConteudo = $conexao->prepare($sqlConteudo);

            foreach ($conteudos as $conteudo) {
                $conteudo = htmlspecialchars($conteudo); 
                $stmtConteudo->bind_param("si", $conteudo, $idMateria);
                if (!$stmtConteudo->execute()) {
                    throw new Exception("Erro ao cadastrar conteúdo: " . $stmtConteudo->error);
                }
            }

            $conexao->commit();
            echo "<script>alert('Matéria e conteúdos cadastrados com sucesso!'); window.location.href = 'MateriaHome.php';</script>";

        } catch (Exception $e) {
            $conexao->rollback();
            echo "Erro ao cadastrar a matéria e os conteúdos: " . $e->getMessage();
        }

        $stmtMateria->close();
        $stmtConteudo->close();
        $conexao->close();
    }
    ?>
</body>
</html>
