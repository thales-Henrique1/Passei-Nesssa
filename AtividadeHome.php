<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="chapéu-de-formatura.png" type="image/x-icon">
    <link rel="stylesheet" href="Estilos/atividadeHome/style.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="stylemenu.css?v=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividade início</title>
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
    <div class="paddingXL">
        <div class="card-materia">
            <div class="margin-bottom cadastar_atividade">
                <a href="CadastrarAtividade.php" class="link_cadastar_atividade">
                    <svg width="18px" height="18px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 12H20M12 4V20" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Cadastrar atividade
                </a>                  
            </div>
            <h2>Lista de Atividades realizadas</h2>

            <form method="GET" action="AtividadeHome.php">
                <label for="ordenar">Ordenar por tempo:</label>
                <button type="submit" name="ordenar" value="desc">
                    <i class="fa fa-arrow-up" title="Mais tempo"></i>
                </button>
                <button type="submit" name="ordenar" value="asc">
                    <i class="fa fa-arrow-down" title="Menos tempo"></i>
                </button>
            </form>

            <?php
            session_start(); 

            function excluirAtividade($id) {
                $host = "localhost";
                $usuario = "root";
                $senha = "";
                $banco = "PASSEINESSA";
                $conexao = new mysqli($host, $usuario, $senha, $banco);

                if ($conexao->connect_error) {
                    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
                }

                $sql = "DELETE FROM TB_ATIVIDADE WHERE ID = ?";
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    header("Location: AtividadeHome.php");
                    exit();
                } else {
                    echo "Erro ao excluir a atividade: " . $stmt->error;
                }

                $stmt->close();
                $conexao->close();
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
                $idAtividade = $_POST['id'];
                excluirAtividade($idAtividade);
            }

            $host = "localhost";
            $usuario = "root";
            $senha = "";
            $banco = "PASSEINESSA";
            $conexao = new mysqli($host, $usuario, $senha, $banco);

            if ($conexao->connect_error) {
                die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
            }

            $idUsuario = $_SESSION['usuario_id']; 

            $ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'desc';
            $sqlAtividades = "SELECT A.ID, M.NM_MATERIA, C.NM_CONTEUDO, A.DT_INICIO, A.NR_HORA, A.NR_MINUTO, A.NR_SEGUNDO
                            FROM TB_ATIVIDADE A
                            JOIN TB_MATERIAS M ON A.ID_MATERIA = M.ID
                            JOIN TB_CONTEUDOS C ON A.ID_CONTEUDO = C.ID
                            WHERE M.ID_USUARIO = ? 
                            ORDER BY (A.NR_HORA * 3600 + A.NR_MINUTO * 60 + A.NR_SEGUNDO) $ordenar";

            $stmtAtividades = $conexao->prepare($sqlAtividades);
            $stmtAtividades->bind_param("i", $idUsuario);
            $stmtAtividades->execute();
            $resultadoAtividades = $stmtAtividades->get_result();

            if ($resultadoAtividades->num_rows > 0) {
                echo "<center><div class='table-container'>";
                echo "<table class='resultado'>";
                echo "<tr><th>Matéria</th><th>Data</th><th>Conteúdo</th><th>Duração</th><th>Ações</th></tr>";
                while ($linha = $resultadoAtividades->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $linha["NM_MATERIA"] . "</td>";
                    echo "<td>" . date("d/m/Y", strtotime($linha["DT_INICIO"])) . "</td>";
                    echo "<td>" . ($linha["NM_CONTEUDO"] ? $linha["NM_CONTEUDO"] : "Nenhum conteúdo vinculado") . "</td>";
                    echo "<td>" . $linha["NR_HORA"] . "h " . $linha["NR_MINUTO"] . "min " . $linha["NR_SEGUNDO"] . "s</td>";
                    echo "<td>
                            <div class='display-flex-container'>
                                <div>
                                    <form method='GET' action='EditarAtividade.php'>
                                        <input type='hidden' name='editar' value='true'>
                                        <input type='hidden' name='id' value='" . $linha["ID"] . "'>
                                        <button type='submit' class='editar-btn'>
                                            <svg width='22px' height='22px' viewBox='0 0 192 192' xmlns='http://www.w3.org/2000/svg' xml:space='preserve' fill='none'>
                                                <path d='m104.175 90.97-4.252 38.384 38.383-4.252L247.923 15.427V2.497L226.78-18.646h-12.93zm98.164-96.96 31.671 31.67' class='cls-1' style='fill:none;fill-opacity:1;fill-rule:nonzero;stroke:#000000;stroke-width:6.336;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:none;stroke-opacity:1' transform='translate(-77.923 40.646)'></path>
                                                <path d='m195.656 33.271-52.882 52.882' style='fill:none;fill-opacity:1;fill-rule:nonzero;stroke:#000000;stroke-width:6.336;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:5;stroke-dasharray:none;stroke-opacity:1' transform='translate(-77.923 40.646)'></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                <div>
                                    <form method='POST' action='AtividadeHome.php'>
                                        <input type='hidden' name='id' value='" . $linha["ID"] . "'>
                                        <button type='submit' name='excluir' class='excluir-btn'  onclick='return confirm(\"Tem certeza que deseja excluir esta atividade?\");'>
                                            <svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 24 24' fill='none'>
                                                <path d='M3 6H5H21' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'></path>
                                                <path d='M8 6V4C8 3.44772 8.44772 3 9 3H15C15.5523 3 16 3.44772 16 4V6' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'></path>
                                                <path d='M19 6V20C19 20.5523 18.5523 21 18 21H6C5.44772 21 5 20.5523 5 20V6' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                          </td>";
                    echo "</tr>";
                }
                echo "</table></div></center>";
            } else {
                echo "<center><h3>Você não possui atividades registradas.</h3></center>";
            }

            $stmtAtividades->close();
            $conexao->close();
            ?>
        </div>
    </div>
</body>
</html>
