<?php
require_once '../config/auth.php';
require_once '../classes/Database.php';
require_once '../classes/Configuracao.php';

$config = new Configuracao();
$usuario_id = $_SESSION['id_usuario'];
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secao = $_POST['secao'] ?? '';

    if ($secao === 'empresa') {
        $config->definir('nome_empresa', $_POST['nome_empresa']);
        $config->definir('email_empresa', $_POST['email_empresa']);
        $config->definir('telefone_empresa', $_POST['telefone_empresa']);
        $config->definir('endereco_empresa', $_POST['endereco_empresa']);
        $config->definir('cnpj_empresa', $_POST['cnpj_empresa']);
        $sucesso = 'Configurações da empresa salvas com sucesso!';
    } elseif ($secao === 'pagamento') {
        $config->definir('multa_atraso', $_POST['multa_atraso']);
        $config->definir('juros_mensais', $_POST['juros_mensais']);
        $config->definir('dias_carencia', $_POST['dias_carencia']);
        $sucesso = 'Configurações de pagamento salvas com sucesso!';
    } elseif ($secao === 'email') {
        $config->definir('email_host', $_POST['email_host']);
        $config->definir('email_port', $_POST['email_port']);
        $config->definir('email_user', $_POST['email_user']);
        $config->definir('email_pass', $_POST['email_pass']);
        $config->definir('email_de', $_POST['email_de']);
        $config->definir('notificar_pagamento', $_POST['notificar_pagamento'] ?? '0');
        $sucesso = 'Configurações de email salvas com sucesso!';
    }
}

$empresa = $config->obterConfiguracoesEmpresa();
$pagamento = $config->obterConfiguracoesPagamento();
$email = $config->obterConfiguracoesEmail();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Sistema de Semi-Joias</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .config-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .config-menu {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .config-menu h3 {
            font-size: 14px;
            color: #999;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .config-menu-item {
            padding: 12px 15px;
            color: #666;
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: all 0.3s;
            display: block;
            text-decoration: none;
        }

        .config-menu-item:hover {
            background: #f5f5f5;
            color: #667eea;
        }

        .config-menu-item.active {
            background: #f0f0f0;
            color: #667eea;
            border-left-color: #667eea;
        }

        .config-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .config-section {
            display: none;
        }

        .config-section.active {
            display: block;
        }

        .config-section h2 {
            margin-bottom: 30px;
            font-size: 24px;
            color: #333;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .alerta {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alerta.sucesso {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alerta.erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .secao-info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }

        .secao-info p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .config-container {
                grid-template-columns: 1fr;
            }

            .config-menu {
                position: static;
                display: flex;
                gap: 10px;
                overflow-x: auto;
                padding: 15px;
            }

            .config-menu-item {
                padding: 10px 15px;
                border-left: none;
                border-bottom: 2px solid transparent;
                white-space: nowrap;
            }

            .config-menu-item.active {
                border-bottom-color: #667eea;
                border-left: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <h2>Semi-Joias</h2>
        </div>
        <div class="navbar-menu">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="vendas.php" class="nav-link">Vendas</a>
            <a href="clientes.php" class="nav-link">Clientes</a>
            <a href="configuracoes.php" class="nav-link active">?? Configurações</a>
            <div class="nav-user">
                <a href="logout.php" class="nav-logout">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 style="margin-bottom: 30px;">Configurações do Sistema</h1>

        <div class="config-container">
            <!-- Menu Lateral -->
            <div class="config-menu">
                <h3>Geral</h3>
                <a href="#" class="config-menu-item active" onclick="mudarSecao('empresa', event)">
                    ?? Dados da Empresa
                </a>
                <a href="#" class="config-menu-item" onclick="mudarSecao('pagamento', event)">
                    ?? Configurações de Pagamento
                </a>
                <h3 style="margin-top: 20px;">Comunicação</h3>
                <a href="#" class="config-menu-item" onclick="mudarSecao('email', event)">
                    ?? Email
                </a>
            </div>

            <!-- Conteúdo -->
            <div class="config-content">
                <?php if ($sucesso): ?>
                <div class="alerta sucesso">
                    ? <?php echo $sucesso; ?>
                </div>
                <?php endif; ?>

                <?php if ($erro): ?>
                <div class="alerta erro">
                    ? <?php echo $erro; ?>
                </div>
                <?php endif; ?>

                <!-- Seção: Dados da Empresa -->
                <div id="empresa" class="config-section active">
                    <h2>Dados da Empresa</h2>

                    <div class="secao-info">
                        <p>?? Cadastre as informações principais da sua empresa. Estes dados serão utilizados em relatórios e comunicações com clientes.</p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="secao" value="empresa">

                        <div class="form-grid">
                            <div>
                                <div class="form-group">
                                    <label>Nome da Empresa</label>
                                    <input type="text" name="nome_empresa" value="<?php echo htmlspecialchars($empresa['nome_empresa']); ?>" required>
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Email Comercial</label>
                                    <input type="email" name="email_empresa" value="<?php echo htmlspecialchars($empresa['email_empresa']); ?>">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Telefone Comercial</label>
                                    <input type="tel" name="telefone_empresa" value="<?php echo htmlspecialchars($empresa['telefone_empresa']); ?>" class="input-telefone">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>CNPJ/CPF</label>
                                    <input type="text" name="cnpj_empresa" value="<?php echo htmlspecialchars($empresa['cnpj_empresa']); ?>" class="input-cpf-cnpj">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Endereço</label>
                            <input type="text" name="endereco_empresa" value="<?php echo htmlspecialchars($empresa['endereco_empresa']); ?>" style="width: 100%;">
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Salvar Configurações</button>
                    </form>
                </div>

                <!-- Seção: Pagamento -->
                <div id="pagamento" class="config-section">
                    <h2>Configurações de Pagamento</h2>

                    <div class="secao-info">
                        <p>? Configure as políticas de cobrança de multa e juros sobre atrasos no pagamento.</p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="secao" value="pagamento">

                        <div class="form-grid">
                            <div>
                                <div class="form-group">
                                    <label>Multa por Atraso (%)</label>
                                    <input type="number" name="multa_atraso" value="<?php echo $pagamento['multa_atraso']; ?>" min="0" max="100" step="0.01">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Juros Mensais (%)</label>
                                    <input type="number" name="juros_mensais" value="<?php echo $pagamento['juros_mensais']; ?>" min="0" max="100" step="0.01">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Dias de Carência</label>
                                    <input type="number" name="dias_carencia" value="<?php echo $pagamento['dias_carencia']; ?>" min="0" step="1">
                                </div>
                            </div>
                        </div>

                        <div class="secao-info">
                            <p><strong>Exemplo:</strong> Com 5 dias de carência, multa de 2% e juros de 1% ao mês, um boleto de R$ 100,00 com 10 dias de atraso custará R$ 103,00 (R$ 100 + R$ 2 de multa + R$ 1 de juros).</p>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Salvar Configurações</button>
                    </form>
                </div>

                <!-- Seção: Email -->
                <div id="email" class="config-section">
                    <h2>Configurações de Email</h2>

                    <div class="secao-info">
                        <p>?? Configure um servidor SMTP para enviar notificaçães de pagamento e outros emails automatizados.</p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="secao" value="email">

                        <div class="form-grid">
                            <div>
                                <div class="form-group">
                                    <label>Host SMTP</label>
                                    <input type="text" name="email_host" value="<?php echo htmlspecialchars($email['email_host']); ?>" placeholder="smtp.gmail.com">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Porta</label>
                                    <input type="number" name="email_port" value="<?php echo $email['email_port']; ?>" placeholder="587">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Usuário (Email)</label>
                                    <input type="email" name="email_user" value="<?php echo htmlspecialchars($email['email_user']); ?>">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Senha</label>
                                    <input type="password" name="email_pass" value="<?php echo htmlspecialchars($email['email_pass']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email de Resposta</label>
                            <input type="email" name="email_de" value="<?php echo htmlspecialchars($email['email_de']); ?>" style="width: 100%;">
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="notificar_pagamento" value="1" <?php echo $email['notificar_pagamento'] ? 'checked' : ''; ?>>
                                Enviar notificação quando receber pagamento
                            </label>
                        </div>

                        <div class="secao-info">
                            <p><strong>Gmail:</strong> Use sua senha de app (não a senha da conta). Ative "Acesso a apps menos seguros" nas Configurações da conta.</p>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Salvar Configurações</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        function mudarSecao(secao, event) {
            event.preventDefault();

            // Ocultar todas as seções
            document.querySelectorAll('.config-section').forEach(el => {
                el.classList.remove('active');
            });

            // Remover classe active de todos os itens do menu
            document.querySelectorAll('.config-menu-item').forEach(el => {
                el.classList.remove('active');
            });

            // Mostrar seção selecionada
            document.getElementById(secao).classList.add('active');

            // Marcar item do menu como ativo
            event.target.classList.add('active');
        }
    </script>
</body>
</html>