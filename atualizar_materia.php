<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idMateria = $_POST['id'];
    $nomeMateria = $_POST['nmMateria'];
    $conteudos = json_decode($_POST['conteudos'], true);

    $host = "localhost";
    $usuario = "root";
    $senha = "";
    $banco = "PASSEINESSA";

    $conexao = new mysqli($host, $usuario, $senha, $banco);

    if ($conexao->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
    }

    $sqlAtualizarMateria = "UPDATE TB_MATERIAS SET NM_MATERIA = ? WHERE ID = ?";
    $stmtMateria = $conexao->prepare($sqlAtualizarMateria);
    $stmtMateria->bind_param("si", $nomeMateria, $idMateria);
    $stmtMateria->execute();
    $stmtMateria->close();

    $sqlBuscarConteudos = "SELECT NM_CONTEUDO FROM TB_CONTEUDOS WHERE ID_MATERIA = ?";
    $stmtBuscarConteudos = $conexao->prepare($sqlBuscarConteudos);
    $stmtBuscarConteudos->bind_param("i", $idMateria);
    $stmtBuscarConteudos->execute();
    $resultConteudos = $stmtBuscarConteudos->get_result();
    $conteudosExistentes = [];

    while ($row = $resultConteudos->fetch_assoc()) {
        $conteudosExistentes[] = $row['NM_CONTEUDO'];
    }
    $stmtBuscarConteudos->close();

    $sqlInserirConteudo = "INSERT INTO TB_CONTEUDOS (NM_CONTEUDO, ID_MATERIA) VALUES (?, ?)";
    $stmtConteudo = $conexao->prepare($sqlInserirConteudo);

    foreach ($conteudos as $conteudo) {
        if (!in_array($conteudo, $conteudosExistentes)) {
            $stmtConteudo->bind_param("si", $conteudo, $idMateria);
            $stmtConteudo->execute();
        }
    }

    $stmtConteudo->close();
    $conexao->close();
    echo "<script>alert('Matéria e conteúdo atualizadas com sucesso!'); window.location.href = 'MateriaHome.php';</script>";
    /*header("Location: MateriaHome.php");*/
    exit();
}
?>
