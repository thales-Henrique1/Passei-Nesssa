<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="chapéu-de-formatura.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="stylemenu.css?v=1.0">
    <link rel="stylesheet" href="Estilos/editarAtividade/styles.css">
    <title>Editar Atividade</title>
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

    <?php
    $modoEdicao = false;
    $idAtividade = "";
    $materiaId = "";
    $conteudoId = "";
    $conteudo = "";
    $data = "";
    $horas = 0;
    $minutos = 0;
    $segundos = 0;

    if (isset($_GET['editar']) && isset($_GET['id'])) {
        $modoEdicao = true;
        $idAtividade = $_GET['id'];

        $conexao = new mysqli("localhost", "root", "", "PASSEINESSA");

        if ($conexao->connect_error) {
            die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
        }

        $sql = "SELECT A.ID, A.ID_MATERIA, A.ID_CONTEUDO, M.NM_MATERIA, C.NM_CONTEUDO, A.DT_INICIO, A.NR_HORA, A.NR_MINUTO, A.NR_SEGUNDO 
                FROM TB_ATIVIDADE A
                JOIN TB_MATERIAS M ON A.ID_MATERIA = M.ID
                JOIN TB_CONTEUDOS C ON A.ID_CONTEUDO = C.ID
                WHERE A.ID = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $idAtividade);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $linha = $resultado->fetch_assoc();
            $materiaId = $linha['ID_MATERIA'];
            $conteudoId = $linha['ID_CONTEUDO'];
            $conteudo = $linha['NM_CONTEUDO'];
            $data = $linha['DT_INICIO'];
            $horas = $linha['NR_HORA'];
            $minutos = $linha['NR_MINUTO'];
            $segundos = $linha['NR_SEGUNDO'];
        } else {
            echo "Atividade não encontrada.";
            exit();
        }

        $stmt->close();
        $conexao->close();
    }
    ?>

    <form method="POST" action="editarAtividade.php">
        <input type="hidden" name="modoEdicao" value="<?php echo $modoEdicao ? 'true' : 'false'; ?>">
        <input type="hidden" name="id" value="<?php echo $idAtividade; ?>">

        <center>
            <div class="Cards">
                <h2>Edição de Atividade</h2>
                <div class="c1">
                    <div class="NomeMateriaContainer">
                        <b>Nome da matéria:</b><br><br>
                        <select name="materia" id="materiaSelect">
                            <option value="">Selecione uma matéria</option>
                            <?php
                            $conexao = new mysqli("localhost", "root", "", "PASSEINESSA");

                            if ($conexao->connect_error) {
                                die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
                            }

                            $sql = "SELECT ID, NM_MATERIA FROM TB_MATERIAS";
                            $resultado = $conexao->query($sql);
                            if ($resultado->num_rows > 0) {
                                while ($linha = $resultado->fetch_assoc()) {
                                    $selected = ($linha["ID"] == $materiaId) ? 'selected' : '';
                                    echo "<option value='" . $linha["ID"] . "' $selected>" . $linha["NM_MATERIA"] . "</option>";
                                }
                            }
                            $conexao->close();
                            ?>
                        </select>
                    </div>
                    <br>
                    <div class="ConteudoContainer">
                        <b>Conteúdo:</b><br>
                        <select name="conteudo" id="conteudoSelect">
                            <option value="">Selecione um conteúdo</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="DataContainer">
                        <p>Data</p>
                        <input type="date" name="data" id="data" class="Data" value="<?php echo $data; ?>">
                    </div>
                    <h3 style="text-align: left; margin-bottom: -10px;">Duração</h3>
                    <div class="Display-flex">
                        <p style="margin-left: 10px;">Horas</p><br>
                        <p style="margin-left: 20px;">Minutos</p><br>
                        <p style="margin-left: 10px;">Segundos</p><br>
                    </div>
                    <div class="Display-flex">
                        <input type="number" name="Horas" id="Horas" step="0.01" value="<?php echo $horas; ?>">
                        <input type="number" name="Minutos" id="Minutos" value="<?php echo $minutos; ?>">
                        <input type="number" name="Segundos" id="Segundos" value="<?php echo $segundos; ?>">
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
        window.onload = function() {
            var conteudoSelect = document.getElementById("conteudoSelect");
            var selectedMateriaId = "<?php echo $materiaId; ?>";

            if (selectedMateriaId) {
                var xhr = new XMLHttpRequest();
                var url = "buscar_conteudo.php?materia_id=" + selectedMateriaId;
                xhr.open("GET", url, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var conteudos = JSON.parse(xhr.responseText);
                        conteudos.forEach(function (conteudo) {
                            var option = document.createElement("option");
                            option.value = conteudo.ID;
                            option.text = conteudo.NM_CONTEUDO;
                            if (conteudo.ID == "<?php echo $conteudoId; ?>") {
                                option.selected = true;
                            }
                            conteudoSelect.appendChild(option);
                        });
                    }
                };
                xhr.send();
            }
        };

        document.getElementById("materiaSelect").addEventListener("change", function () {
            var materiaId = this.value;
            var conteudoSelect = document.getElementById("conteudoSelect");
            conteudoSelect.innerHTML = "<option value=''>Selecione um conteúdo</option>";

            if (materiaId) {
                var xhr = new XMLHttpRequest();
                var url = "buscar_conteudo.php?materia_id=" + materiaId;
                xhr.open("GET", url, true);
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

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $modoEdicao = $_POST["modoEdicao"] === "true";
    $idEditar = $_POST["id"];
    $materia = $_POST["materia"];
    $conteudo = $_POST["conteudo"];
    $data = $_POST["data"];
    $horas = (int)$_POST["Horas"];
    $minutos = (int)$_POST["Minutos"];
    $segundos = (int)$_POST["Segundos"];

    $data = date('Y-m-d', strtotime($data)); 

    $conexao = new mysqli("localhost", "root", "", "PASSEINESSA");

    if ($conexao->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
    }

    if ($modoEdicao) {
        $sql = "UPDATE TB_ATIVIDADE SET ID_MATERIA = ?, ID_CONTEUDO = ?, DT_INICIO = ?, NR_HORA = ?, NR_MINUTO = ?, NR_SEGUNDO = ? WHERE ID = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("iissiii", $materia, $conteudo, $data, $horas, $minutos, $segundos, $idEditar);
        $stmt->execute();
        //echo "Atividade atualizada com sucesso!";
        $stmt->close();
        echo "<script>alert('Atividades atualizada com sucesso!');
        window.location.href = 'AtividadeHome.php';
        </script>";
    }

    $conexao->close();
}
?>
