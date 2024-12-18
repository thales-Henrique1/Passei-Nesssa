<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não autenticado.");
}

$usuario_id = $_SESSION['usuario_id'];

$conexao = new mysqli("localhost", "root", "", "PASSEINESSA");

if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
}

$sql = "
    SELECT 
        TB_ATIVIDADE.DT_INICIO AS data,
        TB_MATERIAS.NM_MATERIA AS materia,
        TB_CONTEUDOS.NM_CONTEUDO AS conteudo,
        CONCAT(TB_ATIVIDADE.NR_HORA, 'h ', TB_ATIVIDADE.NR_MINUTO, 'min ', TB_ATIVIDADE.NR_SEGUNDO, 's') AS tempo
    FROM 
        TB_ATIVIDADE
    INNER JOIN 
        TB_MATERIAS ON TB_ATIVIDADE.ID_MATERIA = TB_MATERIAS.ID
    INNER JOIN 
        TB_CONTEUDOS ON TB_ATIVIDADE.ID_CONTEUDO = TB_CONTEUDOS.ID
    WHERE 
        TB_ATIVIDADE.ID_USUARIO = ?
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$atividades = [];

while ($row = $resultado->fetch_assoc()) {
    $atividades[] = [
        'title' => $row['materia'] . ' - ' . $row['conteudo'],
        'start' => $row['data'],
        'extendedProps' => [
            'materia' => $row['materia'],
            'conteudo' => $row['conteudo'],
            'tempo' => $row['tempo']
        ]
    ];
}

$stmt->close();
$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário de Estudos</title>
    <link rel="shortcut icon" href="../chapéu-de-formatura.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../stylemenu.css?v=1.0">
    <link rel="stylesheet" href="../Estilos/calendario/style.css?v=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.4/index.global.min.js"></script>

</head>
<body>
    <menu class="Meu_Item">
        <a href="../home.php" class="Item_Menu ">
            <i class="fa fa-home"></i> Início
        </a>
        <a href="index.php" class="Item_Menu active">
            <i class="fa fa-calendar"></i> Acompanhamento
        </a>
        <a href="../atividadehome.php" class="Item_Menu">
            <i class="fa fa-tasks"></i> Atividade
        </a>
        <a href="../materiahome.php" class="Item_Menu">
            <i class="fa fa-book"></i> Matérias
        </a>
    </menu>
    <div class="submenu">
        <a href="index.php" class="submenu-item active">
            <i class="fa fa-calendar"></i> Calendário
        </a>
        <a href="../AcompanhamentoMaterias/index.php" class="submenu-item ">
            <i class="fa fa-bar-chart"></i> Acompanhamento de Matérias
        </a>
    </div>
    <div class="display-flex-center">
        <div id="calendar"></div>
    </div>

    <div id="activityModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h2>Atividades do Dia</h2>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridDay'
                },
                buttonText: {
                    today: 'Hoje',
                    month: 'Mês',
                    day: 'Dia'
                },
                events: <?php echo json_encode($atividades); ?>,
                dateClick: function(info) {
                    const selectedDate = info.dateStr;
                    const activitiesOfDay = <?php echo json_encode($atividades); ?>.filter(activity => activity.start === selectedDate);

                    let modalContent = document.getElementById('modalContent');
                    modalContent.innerHTML = '';

                    if (activitiesOfDay.length > 0) {
                        activitiesOfDay.forEach(activity => {
                            const activityDetails = `
                                <p><strong>Matéria:</strong> ${activity.extendedProps.materia} <strong>Conteúdo:</strong> ${activity.extendedProps.conteudo}</p>
                                <p><strong>⏱️</strong> ${activity.extendedProps.tempo}</p>
                                <hr>
                            `;
                            modalContent.innerHTML += activityDetails;
                        });
                    } else {
                        modalContent.innerHTML = '<p>Não há atividades para esta data.</p>';
                    }

                    document.getElementById('activityModal').style.display = 'flex';
                }
            });
            calendar.render();
        });

        function closeModal() {
            document.getElementById('activityModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('activityModal');
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
