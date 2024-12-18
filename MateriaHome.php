<?php
session_start(); 

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /trabalhofaculdade/login.php");
    exit();
}

$conexao = new mysqli("localhost", "root", "", "PASSEINESSA");

if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
}

function verificarAtividadesVinculadas($id) {
    global $conexao;
    $sql = "SELECT COUNT(*) as total FROM TB_ATIVIDADE WHERE ID_MATERIA = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $linha = $resultado->fetch_assoc();
    $stmt->close();
    return $linha['total'] > 0;
}

function excluirConteudos($id) {
    global $conexao;
    $sql = "DELETE FROM TB_CONTEUDOS WHERE ID_MATERIA = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function excluirMateria($id) {
    global $conexao;
    if (verificarAtividadesVinculadas($id)) {
        return "<script>alert('Não é possível excluir a matéria. Existem atividade(s) vinculada(s) a ela.');</script>";
    } else {
        excluirConteudos($id);

        $sql = "DELETE FROM TB_MATERIAS WHERE ID = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->close();
            return "<script>alert('Matéria e conteúdos excluídos com sucesso!'); window.location.href='MateriaHome.php';</script>";
        } else {
            $stmt->close();
            return "Erro ao excluir a matéria: " . $stmt->error;
        }
    }
}

$mensagem = "";
if (isset($_POST['excluir'])) {
    $id = intval($_POST['id']);
    $mensagem = excluirMateria($id);
}

$sql = "SELECT ID, NM_MATERIA FROM TB_MATERIAS WHERE ID_USUARIO = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="Estilos/materiaHomeT/styles.css?v=1.0">
    <link rel="stylesheet" href="stylemenu.css?v=1.0">
    <link rel="shortcut icon" href="chapéu-de-formatura.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matérias</title>

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

    <div class="card-materia">
        <div class="margin-botom cadastrar_materia">
        <a href="CadastrarMateria.php?usuario_id=<?php echo $_SESSION['usuario_id']; ?>" class="link_cadastar_materia">
                <svg width="18px" height="18px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 12H20M12 4V20" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Cadastrar matéria
            </a>
        </div>
        <h2>Lista de Matérias</h2>
        <?php
        if ($resultado->num_rows > 0) {
            echo "<center><div class='table-container'>";
            echo "<table class='resultado'>
            <tr>
                <th>Matéria</th>
                <th>Ações</th>
            </tr>";

            while ($linha = $resultado->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($linha["NM_MATERIA"]) . "</td>
                        <td>
                            <div class='display-flex-container'>
                                <div>
                                    <form method='POST' action='EditarMateria.php'>
                                        <input type='hidden' name='id' value='" . $linha["ID"] . "'>
                                        <button type='submit' name='editar' class='editar-btn'>
                                            <svg width='22px' height='22px' viewBox='0 0 192 192' xmlns='http://www.w3.org/2000/svg' xml:space='preserve' fill='none'>
                                                <path d='m104.175 90.97-4.252 38.384 38.383-4.252L247.923 15.427V2.497L226.78-18.646h-12.93zm98.164-96.96 31.671 31.67' class='cls-1' style='fill:none;fill-opacity:1;fill-rule:nonzero;stroke:#000000;stroke-width:6.336;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:none;stroke-opacity:1' transform='translate(-77.923 40.646)'></path>
                                                <path d='m195.656 33.271-52.882 52.882' style='fill:none;fill-opacity:1;fill-rule:nonzero;stroke:#000000;stroke-width:6.336;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:5;stroke-dasharray:none;stroke-opacity:1' transform='translate(-77.923 40.646)'></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                <div>
                                    <form method='POST' action='MateriaHome.php'>
                                        <input type='hidden' name='id' value='" . $linha["ID"] . "'>
                                        <button type='submit' name='excluir' class='excluir-btn' onclick='return confirm(\"Tem certeza que deseja excluir esta matéria?\");'>
                                            <svg width='22px' height='22px' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'>
                                                <path d='M4 6H20M16 6L15.7294 5.18807C15.4671 4.40125 15.3359 4.00784 15.0927 3.71698C14.8779 3.46013 14.6021 3.26132 14.2905 3.13878C13.9376 3 13.523 3 12.6936 3H11.3064C10.477 3 10.0624 3 9.70951 3.13878C9.39792 3.26132 9.12208 3.46013 8.90729 3.71698C8.66411 4.00784 8.53293 4.40125 8.27062 5.18807L8 6M19 6V17C19 18.1046 18.1046 19 17 19H7C5.89543 19 5 18.1046 5 17V6M19 6H5' stroke='#000000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>";
            }
            echo "</table></div></center>";
        } else {
            echo "<center><p>Nenhuma matéria encontrada.</p></center>";
        }

        if ($mensagem) {
            echo "<center>$mensagem</center>";
        }

        $conexao->close();
        ?>
    </div>
</body>
</html>
