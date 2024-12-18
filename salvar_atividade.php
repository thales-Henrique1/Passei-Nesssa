<?php
session_start(); 

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "PASSEINESSA";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
if (isset($_SESSION['usuario_id'], $_POST['idMateria'], $_POST['idConteudo'], $_POST['nrHora'], $_POST['nrMinuto'], $_POST['nrSegundo'])) {
    $idUsuario = $_SESSION['usuario_id']; 
    $idMateria = intval($_POST['idMateria']);
    $idConteudo = intval($_POST['idConteudo']);  
    $nrHora = intval($_POST['nrHora']);
    $nrMinuto = intval($_POST['nrMinuto']);
    $nrSegundo = intval($_POST['nrSegundo']);

    $stmtConteudo = $conn->prepare("SELECT NM_CONTEUDO FROM TB_CONTEUDOS WHERE ID = ?");
    $stmtConteudo->bind_param("i", $idConteudo);

    if ($stmtConteudo->execute()) {
        $stmtConteudo->bind_result($nomeConteudo);
        if ($stmtConteudo->fetch()) {
            $stmtConteudo->close();

            $stmt = $conn->prepare("INSERT INTO TB_ATIVIDADE (ID_MATERIA, DT_INICIO, ID_CONTEUDO, NR_HORA, NR_MINUTO, NR_SEGUNDO, ID_USUARIO) VALUES (?, NOW(), ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiiii", $idMateria, $idConteudo, $nrHora, $nrMinuto, $nrSegundo, $idUsuario);

            if ($stmt->execute()) {
                echo "Atividade salva com sucesso!";
            } else {
                echo "Erro ao salvar atividade: " . $stmt->error;
            }
        }
    } else {
        echo "Erro ao buscar conteúdo: " . $stmtConteudo->error;
    }
} else {
    echo "Erro: Parâmetros insuficientes.";
}


$conn->close();
?>
