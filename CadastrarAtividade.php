<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$modoEdicao = false;
$idAtividade = "";

if (isset($_GET['editar']) && isset($_GET['id'])) {
    $modoEdicao = true;
    $idAtividade = $_GET['id'];
}

$conexao = new mysqli("localhost", "root", "", "PASSEINESSA");
if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materia = $_POST['materia'];
    $conteudo = $_POST['conteudo'];
    $data = $_POST['data'];
    $horas = $_POST['Horas'];
    $minutos = $_POST['Minutos'];
    $segundos = $_POST['Segundos'];
    $usuarioId = $_SESSION['usuario_id'];

    if (empty($materia) || empty($conteudo) || empty($data)) {
        die("Erro: Todos os campos obrigatórios devem ser preenchidos.");
    }

    $sql = "INSERT INTO TB_ATIVIDADE (ID_MATERIA, ID_CONTEUDO, DT_INICIO, NR_HORA, NR_MINUTO, NR_SEGUNDO, ID_USUARIO) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("iisiiii", $materia, $conteudo, $data, $horas, $minutos, $segundos, $usuarioId);

    if ($stmt->execute()) {
        echo "<script>alert('Atividade cadastrada com sucesso!'); window.location.href = 'AtividadeHome.php';</script>";
        /*header("Location: AtividadeHome.php");*/
        exit();
    } else {
        echo "Erro ao cadastrar a atividade: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="chapéu-de-formatura.png" type="image/x-icon">
    <link rel="stylesheet" href="Estilos/cadastrarAtividade/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="stylemenu.css?v=1.0">
    <title><?php echo $modoEdicao ? 'Editar Atividade' : 'Cadastrar Atividade'; ?></title>
</head>
<body>
    <menu class="Meu_Item">
        <a href="home.php" class="Item_Menu ">
            <i class="fa fa-home"></i> Início
        </a>
        <a href="calendario/index.php" class="Item_Menu">
            <i class="fa fa-calendar"></i> Acompanhamento
        </a>
        <a href="atividadehome.php" class="Item_Menu active">
            <i class="fa fa-tasks"></i> Atividade
        </a>
        <a href="materiahome.php" class="Item_Menu">
            <i class="fa fa-book"></i> Matérias
        </a>
    </menu>
    <div>
        <a href="AtividadeHome.php">Voltar à lista de atividades</a>
    </div>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <center>
            <div class="Cards">
                <h2><?php echo $modoEdicao ? 'Edição de Atividade' : 'Cadastro de Atividade'; ?></h2>
                <div class="c1">
                    <div class="NomeMateriaContainer">
                        <b>Matéria:</b><br><br>
                        <select name="materia" id="materiaSelect" required>
                            <option value="">Selecione uma matéria</option>
                            <?php
                            $usuarioId = $_SESSION['usuario_id'];
                            $sql = "SELECT ID, NM_MATERIA FROM TB_MATERIAS WHERE ID_USUARIO = ?";
                            $stmt = $conexao->prepare($sql);
                            $stmt->bind_param("i", $usuarioId);
                            $stmt->execute();
                            $resultado = $stmt->get_result();

                            if ($resultado->num_rows > 0) {
                                while ($linha = $resultado->fetch_assoc()) {
                                    echo "<option value='" . $linha["ID"] . "'>" . $linha["NM_MATERIA"] . "</option>";
                                }
                            }
                            $stmt->close();
                            ?>
                        </select>
                    </div>
                    <br>
                    <div class="ConteudoContainer">
                        <b>Conteúdo:</b><br>
                        <select name="conteudo" id="conteudoSelect" required>
                            <option value="">Selecione um conteúdo</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="DataContainer">
                        <p>Data</p>
                        <input type="date" name="data" id="data" class="Data" required>
                    </div>
                    <h3 style="text-align: left; margin-bottom: -10px;">Duração</h3>
                    <div class="Display-flex">
                        <p style="margin-left: 30px;"></p>
                        <p style="margin-left: 50px;"></p>
                        <p style="margin-left: 35px;"></p>
                    </div>
                    <div class="Display-flex">
                        <div class="duration-container">
                            <label for="Horas">Horas</label>
                            <input type="number" name="Horas" id="Horas" step="1" min="0" required>
                        </div>
                        <div class="duration-container">
                            <label for="Minutos">Minutos</label>
                            <input type="number" name="Minutos" id="Minutos" step="1" min="0" required>
                        </div>
                        <div class="duration-container">
                            <label for="Segundos">Segundos</label>
                            <input type="number" name="Segundos" id="Segundos" step="1" min="0" required>
                        </div>
                    </div>
                    <div class="btn">
                        <input type="submit" value="Salvar" class="Btn_Salvar">
                        <a href="AtividadeHome.php"><input type="button" value="Voltar" class="Btn_Cancelar"></a>
                    </div>
                </div>
            </div>
        </center>
    </form>

    <script>
        document.getElementById("materiaSelect").addEventListener("change", function () {
            var materiaId = this.value;
            var conteudoSelect = document.getElementById("conteudoSelect");
            conteudoSelect.innerHTML = "<option value=''>Selecione um conteúdo</option>";

            if (materiaId) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "buscar_conteudo.php?materia_id=" + materiaId, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var conteudos = JSON.parse(xhr.responseText);
                        conteudos.forEach(function (conteudo) {
                            var option = document.createElement("option");
                            option.value = conteudo.ID;
                            option.text = conteudo.NM_CONTEUDO;
                            conteudoSelect.appendChild(option);
                        });
                    }
                };
                xhr.send();
            }
        });
    </script>
</body>
</html>
