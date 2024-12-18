<?php
$materiaId = $_GET['materia_id'];

$conexao = new mysqli("localhost", "root", "", "PASSEINESSA");

if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
}

$sql = "SELECT ID, NM_CONTEUDO FROM TB_CONTEUDOS WHERE ID_MATERIA = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $materiaId);
$stmt->execute();
$resultado = $stmt->get_result();

$conteudos = array();
while ($linha = $resultado->fetch_assoc()) {
    $conteudos[] = $linha;
}

echo json_encode($conteudos);

$stmt->close();
$conexao->close();
?>
