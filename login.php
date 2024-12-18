<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="chapéu-de-formatura.png" type="image/x-icon">
    <link rel="stylesheet" href="Estilos/login/styles.css">
    <title>Login</title>
</head>

<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required>

            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required>

            <input type="submit" value="Entrar">
        </form>

        <div>
            <p>Ainda não tem uma conta? <a href="#registrar" onclick="mostrarRegistro()">Crie uma conta</a></p>
        </div>
    </div>

    <div id="registrar" style="display:none;" class="container">
        <h2>Criar Conta</h2>
        <form method="POST">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required>

            <label for="email_registro">E-mail:</label>
            <input type="email" name="email_registro" id="email_registro" required>

            <label for="senha_registro">Senha:</label>
            <input type="password" name="senha_registro" id="senha_registro" required>

            <input type="submit" name="registrar" value="Registrar">
        </form>

        <div>
            <p>Já tem uma conta? <a href="#" onclick="esconderRegistro()">Faça login</a></p>
        </div>
    </div>

    <script>
        function mostrarRegistro() {
                document.getElementById('registrar').style.display = 'block';
                document.querySelector('.container').style.display = 'none'; 
            }

        function esconderRegistro() {
            document.getElementById('registrar').style.display = 'none';
            document.querySelector('.container').style.display = 'block'; 
        }
    </script>

    <?php
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conexao = new mysqli("localhost", "root", "", "PASSEINESSA");

        if ($conexao->connect_error) {
            die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
        }

        if (isset($_POST['email']) && isset($_POST['senha'])) {
            $email = $_POST['email'];
            $senha = $_POST['senha'];

            $sql = "SELECT * FROM TB_USUARIOS WHERE NM_EMAIL = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $usuario = $resultado->fetch_assoc();
                if (password_verify($senha, $usuario['VL_SENHA'])) {
                    $_SESSION['usuario_id'] = $usuario['ID'];
                    $_SESSION['usuario_nome'] = $usuario['NM_NOME'];
                    header("Location: /trabalhofaculdade/home.php");
                    exit();
                } else {
                    echo "<script>alert('Senha incorreta.');</script>";
                }
            } else {
                echo "<script>alert('Email não encontrado.');</script>";
            }
        }

        if (isset($_POST['registrar'])) {
            $nome = $_POST['nome'];
            $email_registro = $_POST['email_registro'];
            $senha_registro = password_hash($_POST['senha_registro'], PASSWORD_DEFAULT);

            $sql = "SELECT * FROM TB_USUARIOS WHERE NM_EMAIL = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("s", $email_registro);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                echo "<script>alert('Email já cadastrado.');</script>";
            } else {
                $sql = "INSERT INTO TB_USUARIOS (NM_EMAIL, VL_SENHA, NM_NOME) VALUES (?, ?, ?)";
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param("sss", $email_registro, $senha_registro, $nome);
                if ($stmt->execute()) {
                    echo "<script>alert('Conta criada com sucesso!');</script>";
                } else {
                    echo "<script>alert('Erro ao criar conta.');</script>";
                }
            }
        }

        $conexao->close();
    }
    ?>
</body>
</html>
