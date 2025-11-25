<?php
require_once '../config/config.php';
require_once '../config/auth.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Configuracao.php';

$config = new Configuracao();
$usuario_id = $_SESSION['id_usuario'];
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secao = $_POST['secao'] ?? '';

    if ($secao === 'empresa') {
        $config->definir('nome_empresa', htmlspecialchars(trim($_POST['nome_empresa']), ENT_QUOTES, 'UTF-8'));
        $config->definir('email_empresa', htmlspecialchars(trim($_POST['email_empresa']), ENT_QUOTES, 'UTF-8'));
        $config->definir('telefone_empresa', htmlspecialchars(trim($_POST['telefone_empresa']), ENT_QUOTES, 'UTF-8'));
        $config->definir('endereco_empresa', htmlspecialchars(trim($_POST['endereco_empresa']), ENT_QUOTES, 'UTF-8'));
        $config->definir('cnpj_empresa', htmlspecialchars(trim($_POST['cnpj_empresa']), ENT_QUOTES, 'UTF-8'));
        $sucesso = 'Configuracoes da empresa salvas com sucesso!';
    } elseif ($secao === 'pagamento') {
        $config->definir('multa_atraso', floatval($_POST['multa_atraso'] ?? 0));
        $config->definir('juros_mensais', floatval($_POST['juros_mensais'] ?? 0));
        $config->definir('dias_carencia', intval($_POST['dias_carencia'] ?? 0));
        $sucesso = 'Configuracoes de pagamento salvas com sucesso!';
    } elseif ($secao === 'email') {
        $config->definir('email_host', htmlspecialchars(trim($_POST['email_host']), ENT_QUOTES, 'UTF-8'));
        $config->definir('email_port', intval($_POST['email_port'] ?? 587));
        $config->definir('email_user', htmlspecialchars(trim($_POST['email_user']), ENT_QUOTES, 'UTF-8'));
        $config->definir('email_pass', htmlspecialchars(trim($_POST['email_pass']), ENT_QUOTES, 'UTF-8'));
        $config->definir('email_de', htmlspecialchars(trim($_POST['email_de']), ENT_QUOTES, 'UTF-8'));
        $config->definir('notificar_pagamento', isset($_POST['notificar_pagamento']) ? '1' : '0');
        $sucesso = 'Configuracoes de email salvas com sucesso!';
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
    <title>Configuracoes - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/style.css'); ?>">
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
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1 style="margin-bottom: 30px;">Configuracoes do Sistema</h1>

        <div class="config-container">
            <!-- Menu Lateral -->
            <div class="config-menu">
                <h3>Geral</h3>
                <a href="#" class="config-menu-item active" onclick="mudarSecao('empresa', event)">
                    <i class="fas fa-building"></i> Dados da Empresa
                </a>
                <a href="#" class="config-menu-item" onclick="mudarSecao('pagamento', event)">
                    <i class="fas fa-money-bill-wave"></i> Configuracoes de Pagamento
                </a>
                <h3 style="margin-top: 20px;">Comunicacao</h3>
                <a href="#" class="config-menu-item" onclick="mudarSecao('email', event)">
                    <i class="fas fa-envelope"></i> Email
                </a>
            </div>

            <!-- Conteudo -->
            <div class="config-content">
                <?php if ($sucesso): ?>
                    <div class="alerta sucesso">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?>
                    </div>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <div class="alerta erro">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>

                <!-- Secao: Dados da Empresa -->
                <div id="empresa" class="config-section active">
                    <h2>Dados da Empresa</h2>

                    <div class="secao-info">
                        <p><i class="fas fa-info-circle"></i> Cadastre as informacoes principais da sua empresa. Estes dados serao utilizados em relatorios e comunicacoes com clientes.</p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="secao" value="empresa">

                        <div class="form-grid">
                            <div>
                                <div class="form-group">
                                    <label>Nome da Empresa</label>
                                    <input type="text" name="nome_empresa" value="<?php echo htmlspecialchars($empresa['nome_empresa'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Email Comercial</label>
                                    <input type="email" name="email_empresa" value="<?php echo htmlspecialchars($empresa['email_empresa'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Telefone Comercial</label>
                                    <input type="tel" name="telefone_empresa" value="<?php echo htmlspecialchars($empresa['telefone_empresa'], ENT_QUOTES, 'UTF-8'); ?>" class="input-telefone">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>CNPJ/CPF</label>
                                    <input type="text" name="cnpj_empresa" value="<?php echo htmlspecialchars($empresa['cnpj_empresa'], ENT_QUOTES, 'UTF-8'); ?>" class="input-cpf-cnpj">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Endereco</label>
                            <input type="text" name="endereco_empresa" value="<?php echo htmlspecialchars($empresa['endereco_empresa'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%;">
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-save"></i> Salvar Configuracoes
                        </button>
                    </form>
                </div>

                <!-- Secao: Pagamento -->
                <div id="pagamento" class="config-section">
                    <h2>Configuracoes de Pagamento</h2>

                    <div class="secao-info">
                        <p><i class="fas fa-money-bill-wave"></i> Configure as politicas de cobranca de multa e juros sobre atrasos no pagamento.</p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="secao" value="pagamento">

                        <div class="form-grid">
                            <div>
                                <div class="form-group">
                                    <label>Multa por Atraso (%)</label>
                                    <input type="number" name="multa_atraso" value="<?php echo floatval($pagamento['multa_atraso']); ?>" min="0" max="100" step="0.01">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Juros Mensais (%)</label>
                                    <input type="number" name="juros_mensais" value="<?php echo floatval($pagamento['juros_mensais']); ?>" min="0" max="100" step="0.01">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Dias de Carencia</label>
                                    <input type="number" name="dias_carencia" value="<?php echo intval($pagamento['dias_carencia']); ?>" min="0" step="1">
                                </div>
                            </div>
                        </div>

                        <div class="secao-info">
                            <p><strong>Exemplo:</strong> Com 5 dias de carencia, multa de 2% e juros de 1% ao mes, um boleto de R$ 100,00 com 10 dias de atraso custara R$ 103,00 (R$ 100 + R$ 2 de multa + R$ 1 de juros).</p>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-save"></i> Salvar Configuracoes
                        </button>
                    </form>
                </div>

                <!-- Secao: Email -->
                <div id="email" class="config-section">
                    <h2>Configuracoes de Email</h2>

                    <div class="secao-info">
                        <p><i class="fas fa-envelope"></i> Configure um servidor SMTP para enviar notificacoes de pagamento e outros emails automatizados.</p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="secao" value="email">

                        <div class="form-grid">
                            <div>
                                <div class="form-group">
                                    <label>Host SMTP</label>
                                    <input type="text" name="email_host" value="<?php echo htmlspecialchars($email['email_host'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="smtp.gmail.com">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Porta</label>
                                    <input type="number" name="email_port" value="<?php echo intval($email['email_port']); ?>" placeholder="587">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Usuario (Email)</label>
                                    <input type="email" name="email_user" value="<?php echo htmlspecialchars($email['email_user'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Senha</label>
                                    <input type="password" name="email_pass" value="<?php echo htmlspecialchars($email['email_pass'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email de Resposta</label>
                            <input type="email" name="email_de" value="<?php echo htmlspecialchars($email['email_de'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%;">
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="notificar_pagamento" value="1" <?php echo $email['notificar_pagamento'] ? 'checked' : ''; ?>>
                                Enviar notificacao quando receber pagamento
                            </label>
                        </div>

                        <div class="secao-info">
                            <p><strong>Gmail:</strong> Use sua senha de app (nao a senha da conta). Ative "Acesso a apps menos seguros" nas Configuracoes da conta.</p>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-save"></i> Salvar Configuracoes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo getAssetUrl('js/app.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('js/mascaras.js'); ?>"></script>
    <script>
        function mudarSecao(secao, event) {
            event.preventDefault();

            document.querySelectorAll('.config-section').forEach(el => {
                el.classList.remove('active');
            });

            document.querySelectorAll('.config-menu-item').forEach(el => {
                el.classList.remove('active');
            });

            document.getElementById(secao).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>

</html>