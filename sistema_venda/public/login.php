<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/config.php';
    require_once '../classes/Database.php';
    require_once '../classes/Usuario.php';

    $usuario = new Usuario();
    $resultado = $usuario->autenticar($_POST['email'], $_POST['senha']);

    if ($resultado) {
        $_SESSION['id_usuario'] = $resultado['id_usuario'];
        $_SESSION['nome_usuario'] = $resultado['nome'];
        $_SESSION['email_usuario'] = $resultado['email'];
        header('Location: dashboard.php');
        exit;
    } else {
        $erro = 'Email ou senha invalidos';
    }
}

if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestao</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 450px;
            padding: 50px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h1 {
            font-size: 28px;
            color: #1c813c;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #999;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #f1f1f1;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0094e1;
            background-color: #fff;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #1c813c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            background-color: #156430;
        }

        .erro {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f1f1f1;
            border-left: 4px solid #dc3545;
            font-size: 14px;
        }

        .login-footer {
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .login-footer a {
            color: #0094e1;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-chart-line"></i> Gestao</h1>
            <p>Sistema de Controle de Vendas</p>
        </div>

        <?php if (isset($erro)): ?>
            <div class="erro">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>

        <div class="login-footer">
            Nao tem conta? <a href="registro.php">Registre-se aqui</a>
        </div>
    </div>
</body>
</html>