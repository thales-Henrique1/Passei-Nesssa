<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "PASSEINESSA";

if (isset($_POST['materia_id'])) {
    $materiaId = $_POST['materia_id'];

    $conexao = new mysqli($host, $usuario, $senha, $banco);

    if ($conexao->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
    }

    $sql = "SELECT ID, NM_CONTEUDO FROM TB_CONTEUDOS WHERE ID_MATERIA = ?";
    $stmt = $conexao->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $materiaId);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $options = "<option value='0'>Selecione um conteúdo</option>";
            while ($linha = $resultado->fetch_assoc()) {
                $options .= "<option value='" . $linha["ID"] . "'>" . htmlspecialchars($linha["NM_CONTEUDO"]) . "</option>";
            }
            echo $options;
        } else {
            echo "<option value='0'>Nenhum conteúdo encontrado</option>";
        }
        $stmt->close();
    } else {
        echo "<option value='0'>Erro na preparação da consulta</option>";
    }
    $conexao->close();
} else {
    echo "<option value='0'>ID da matéria não fornecido</option>";
}
?>
