
<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login/login.php");
    exit();
}

$usuarioId = $_SESSION['usuario_id']; 
$nomeUsuario = $_SESSION['usuario_nome'];

$temAtividades = false;

$host = "localhost";
$usuario = "root"; 
$senha = ""; 
$banco = "PASSEINESSA"; 

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Conexão falhou: " . $conexao->connect_error);
}

$sql = "SELECT m.NM_MATERIA AS materia_nome, 
               c.NM_CONTEUDO AS conteudo_nome,
               SUM(a.NR_HORA * 3600 + a.NR_MINUTO * 60 + a.NR_SEGUNDO) AS tempo_total
        FROM TB_MATERIAS m
        INNER JOIN TB_CONTEUDOS c ON m.ID = c.ID_MATERIA
        INNER JOIN TB_ATIVIDADE a ON c.ID = a.ID_CONTEUDO AND a.ID_USUARIO = ?
        WHERE m.ID_USUARIO = ?
        GROUP BY m.ID, c.ID
        HAVING tempo_total > 0
        ORDER BY tempo_total ASC
        LIMIT 1"; 

$stmt = $conexao->prepare($sql);
$stmt->bind_param("ii", $usuarioId, $usuarioId); 
$stmt->execute();
$result = $stmt->get_result();

$materiaNome = $conteudoNome = $tempo = "";
if ($result && $result->num_rows > 0) {
    $temAtividades = true;
    $row = $result->fetch_assoc();
    $tempo = gmdate("H:i:s", $row['tempo_total']);
    $materiaNome = $row['materia_nome'];
    $conteudoNome = $row['conteudo_nome'];
} 
$sqlTop3MaisEstudadas = "SELECT m.NM_MATERIA AS materia_nome, 
                                c.NM_CONTEUDO AS conteudo_nome,
                                SUM(a.NR_HORA * 3600 + a.NR_MINUTO * 60 + a.NR_SEGUNDO) AS tempo_total
                         FROM TB_MATERIAS m
                         INNER JOIN TB_CONTEUDOS c ON m.ID = c.ID_MATERIA
                         INNER JOIN TB_ATIVIDADE a ON c.ID = a.ID_CONTEUDO AND a.ID_USUARIO = ?
                         WHERE m.ID_USUARIO = ?
                         GROUP BY m.ID, c.ID
                         HAVING tempo_total > 0
                         ORDER BY tempo_total DESC
                         LIMIT 2";

$stmtTop3MaisEstudadas = $conexao->prepare($sqlTop3MaisEstudadas);
$stmtTop3MaisEstudadas->bind_param("ii", $usuarioId, $usuarioId); 
$stmtTop3MaisEstudadas->execute();
$resultTop3MaisEstudadas = $stmtTop3MaisEstudadas->get_result();

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="chapéu-de-formatura.png" type="image/x-icon">
    <link rel="stylesheet" href="stylemenu.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="Estilos/Home/style.css">
    <title>Início</title>

</head>

<body>

    <menu class="Meu_Item">
        <a href="home.php" class="Item_Menu active">
            <i class="fa fa-home"></i> Início
        </a>
        <a href="calendario/index.php" class="Item_Menu">
            <i class="fa fa-calendar"></i> Acompanhamento
        </a>
        <a href="atividadehome.php" class="Item_Menu">
            <i class="fa fa-tasks"></i> Atividade
        </a>
        <a href="materiahome.php" class="Item_Menu">
            <i class="fa fa-book"></i> Matérias
        </a>
    </menu>

    <div class="Display-flex">
        <div class="card-historia">
            <h1>Bem-vindo de volta <?php echo htmlspecialchars($nomeUsuario); ?>!</h1>
            <p>Plataforma responsável pelo controle e gerenciamento dos conteúdos e matérias relacionados a concursos públicos. Tem como objetivo principal auxiliar o concursando a ter uma análise mais precisa do tempo estudado e assim otimizar sua preparação para o concurso desejado.</p>
        </div>
        <div class="gráfico"></div>
        <div class="redirecionar-atividade">
            <p>𝐋𝐢𝐬𝐭𝐚 𝐝𝐞 𝐀𝐭𝐢𝐯𝐚𝐝𝐢𝐝𝐞𝐬</p>
            <div class="red-atv">
                <a href="atividadehome.php">Ir para as atividades</a>
            </div>
        </div>
        
    </div>
    <?php if ($temAtividades): ?>
        <div style="display: flex; gap: 10px;">
            <div class="post-it-Menos">
                <center>
                    <div class="menosEstudada">
                            <h4> Matéria menos estudada  </h4>
                    </div> 
                    <div class="margin-top-M">               
                        <p><strong>Matéria:</strong> <?php echo $materiaNome; ?> <strong>Conteúdo:</strong> <?php echo $conteudoNome; ?></p>
                        <?php
                            $horasMenosEstudada = floor($row['tempo_total'] / 3600);
                            $minutosMenosEstudada = floor(($row['tempo_total'] % 3600) / 60);
                            $segundosMenosEstudada = $row['tempo_total'] % 60;
                        ?>
                            <p><strong>⏱️</strong> 
                                <?php echo "{$horasMenosEstudada}h {$minutosMenosEstudada}m {$segundosMenosEstudada}s"; ?> 
                            </p>
                    </div>
                </center>
            </div>
            
            <div class="post-it-Mais">
                <center>
                    <div class="maisEstudada">
                        <h4> Matérias mais estudadas  </h4>
                    </div>
                    <div class="margin-top-M">
                        <?php while ($rowTop2 = $resultTop3MaisEstudadas->fetch_assoc()): ?>
                            <p><strong>Matéria:</strong> <?php echo $rowTop2['materia_nome']; ?>  <strong>Conteúdo:</strong> <?php echo $rowTop2['conteudo_nome']; ?></p>
                            <?php
                                $horas = floor($rowTop2['tempo_total'] / 3600);
                                $minutos = floor(($rowTop2['tempo_total'] % 3600) / 60);
                                $segundos = $rowTop2['tempo_total'] % 60;
                            ?>
                            <p><strong>⏱️</strong> 
                                <?php echo "{$horas}h {$minutos}m {$segundos}s"; ?> 
                            </p>
                            <hr>
                        <?php endwhile; ?>
                    </div>
                </center>
            </div>
        </div>
    <?php endif; ?>
    <center>
        <div class="container">
            <form id="atividadeForm">
                <select id="materias" name="materia">
                    <option value="0">Matérias</option>
                </select>
                <select id="conteudo" name="conteudo" disabled>
                    <option value="0">Conteúdo</option>
                </select>
                <input type="button" value="Iniciar" class="IN" id="iniciarCronometro" disabled>
                <div class="button-group">
                    <input type="button" value="Salvar" class="SAL" style="display: none;">
                    <input type="button" value="Cancelar" class="CAN" style="display: none;">
                </div>

                <div id="cronometro" style="display: none;">Tempo: 00:00</div>
            </form>
        </div>
    </center>
    <script>
        $(document).ready(function() {
            $.ajax({
                url: 'carregar_materias.php', 
                type: 'POST',
                success: function(response) {
                    $('#materias').append(response);
                },
                error: function() {
                    console.error('Erro ao carregar matérias.');
                }
            });

            $('#materias').on('change', function() {
                var materiaId = $(this).val();
                $('#conteudo').html('<option value="0">Conteúdo</option>').prop('disabled', true); 
                $('#iniciarCronometro').prop('disabled', true);
                
                if (materiaId !== '0') {
                    $.ajax({
                        url: 'carregar_conteudos.php', 
                        type: 'POST',
                        data: { materia_id: materiaId },
                        success: function(response) {
                            $('#conteudo').html(response).prop('disabled', false);
                            $('#iniciarCronometro').prop('disabled', false);
                        },
                        error: function() {
                            console.error('Erro ao carregar conteúdos.');
                        }
                    });
                }
            });

            var cronometro;
            var segundos = 0;

            function atualizarCronometro() {
                var horas = Math.floor(segundos / 3600);
                var minutos = Math.floor((segundos % 3600) / 60);
                var segundosRestantes = segundos % 60;
                var tempoFormatado = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundosRestantes).padStart(2, '0')}`;
                $('#cronometro').text('Tempo: ' + tempoFormatado);
            }

            $('#iniciarCronometro').on('click', function() {
                cronometro = setInterval(function() {
                    segundos++;
                    atualizarCronometro();
                }, 1000); 

                $('.IN').hide();
                $('.SAL').show();
                $('.CAN').show();
                $('#cronometro').show();
            });
/**/
            $('.SAL').on('click', function() {
    if ($('#materias').val() === '0' || $('#conteudo').val() === '0') {
        alert('Selecione uma matéria e um conteúdo.');
        return;
    }

    var idMateria = $('#materias').val();
    var idConteudo = $('#conteudo').val();

    var horas = Math.floor(segundos / 3600);
    var minutos = Math.floor((segundos % 3600) / 60);
    var segundosRestantes = segundos % 60;

    $.ajax({
        url: 'salvar_atividade.php', 
        type: 'POST',
        data: {
            idMateria: idMateria,
            idConteudo: idConteudo,
            nrHora: horas,
            nrMinuto: minutos,
            nrSegundo: segundosRestantes
        },
        success: function(response) {
            if (response === 'Atividade salva com sucesso!') {
                alert('Atividade salva com sucesso.');
                location.reload();
            } else {
                alert('Erro ao salvar a atividade.');
            }
        },
        error: function() {
            alert('Erro ao salvar a atividade.');
        }
    });
});

/* */
            $('.CAN').on('click', function() {
                clearInterval(cronometro);
                segundos = 0;
                atualizarCronometro();
                $('.SAL').hide();
                $('.CAN').hide();
                $('#cronometro').hide();
                $('#materias').val('0').change(); 
            });

            $('.close-btn').on('click', function() {
                $('#popupModal').fadeOut(); 
            });

            <?php if ($temAtividades): ?>
                $('#popupModal').fadeIn();
            <?php endif; ?>
        });
    </script>

</body>
</html>
