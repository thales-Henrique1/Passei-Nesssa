<?php
session_start();
$conexao = new mysqli("localhost", "root", "", "PASSEINESSA");

if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
}

$id_usuario = $_SESSION['usuario_id'];
$sql = "SELECT * FROM TB_MATERIAS WHERE ID_USUARIO = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$options = '';
while ($materia = $resultado->fetch_assoc()) {
    $options .= "<option value='{$materia['ID']}'>{$materia['NM_MATERIA']}</option>";
}

echo $options;

$conexao->close();
?>
