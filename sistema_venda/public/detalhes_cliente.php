<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../config/config.php';
require_once '../config/auth.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Cliente.php';
require_once CLASSES_PATH . '/Venda.php';

$cliente_obj = new Cliente();
$venda_obj = new Venda();

$idCliente = $_GET['id'] ?? 0;

if (!$idCliente) {
    header('Location: ' . PUBLIC_URL_RELATIVE . 'clientes.php');
    exit;
}

$cliente = $cliente_obj->obter($idCliente);

if (!$cliente) {
    header('Location: ' . PUBLIC_URL_RELATIVE . 'clientes.php');
    exit;
}

$vendas = $venda_obj->obterPorCliente($idCliente);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Cliente - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/style.css'); ?>">
    <style>
        /* ===== CLIENTE HEADER ===== */
        .cliente-header {
            background: linear-gradient(135deg, var(--gray-900) 0%, var(--gray-800) 100%);
            color: var(--white);
            padding: 40px;
            border-radius: var(--border-radius-lg);
            margin-bottom: 30px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .cliente-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            transform: translate(100px, -100px);
        }

        .cliente-header-conteudo {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            gap: 30px;
            flex-wrap: wrap;
        }

        .cliente-header-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--gray-700) 0%, var(--gray-600) 100%);
            border-radius: var(--border-radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 700;
            color: var(--white);
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-lg);
            flex-shrink: 0;
        }

        .cliente-header-info {
            flex: 1;
            min-width: 250px;
        }

        .cliente-header-info h1 {
            margin: 0 0 15px 0;
            font-size: 28px;
            color: var(--white);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cliente-header-info p {
            margin: 8px 0;
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .cliente-header-stats {
            display: flex;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .cliente-header-stat {
            display: flex;
            flex-direction: column;
        }

        .cliente-header-stat-valor {
            font-size: 20px;
            font-weight: 700;
            color: var(--white);
        }

        .cliente-header-stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
            font-weight: 600;
        }

        .cliente-header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .cliente-header .btn {
            background-color: var(--white);
            color: var(--gray-900);
            border: none;
            font-weight: 600;
        }

        .cliente-header .btn:hover {
            background-color: var(--gray-100);
            transform: translateY(-2px);
        }

        .cliente-header .btn-secondary {
            background-color: transparent;
            color: var(--white);
            border: 2px solid var(--white);
            font-weight: 600;
        }

        .cliente-header .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: var(--white);
        }

        /* ===== CLIENTE HEADER - BADGES ===== */
        .cliente-header-badges {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .cliente-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background-color: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: var(--border-radius-full);
            font-size: 12px;
            color: var(--white);
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .cliente-badge i {
            font-size: 14px;
        }

        /* ===== RESPONSIVE CLIENTE HEADER ===== */
        @media (max-width: 768px) {
            .cliente-header {
                padding: 30px;
            }

            .cliente-header-conteudo {
                gap: 20px;
            }

            .cliente-header-avatar {
                width: 100px;
                height: 100px;
                font-size: 40px;
            }

            .cliente-header-info h1 {
                font-size: 24px;
            }

            .cliente-header-stats {
                gap: 20px;
            }

            .cliente-header-actions {
                width: 100%;
                margin-top: 20px;
            }

            .cliente-header-actions .btn {
                flex: 1;
                min-width: 120px;
            }
        }

        @media (max-width: 480px) {
            .cliente-header {
                padding: 20px;
            }

            .cliente-header-conteudo {
                flex-direction: column;
                gap: 15px;
            }

            .cliente-header-avatar {
                width: 80px;
                height: 80px;
                font-size: 32px;
                margin: 0 auto;
            }

            .cliente-header-info {
                text-align: center;
            }

            .cliente-header-info h1 {
                font-size: 20px;
                justify-content: center;
            }

            .cliente-header-info p {
                justify-content: center;
            }

            .cliente-header-stats {
                justify-content: center;
                gap: 15px;
            }

            .cliente-header-actions {
                flex-direction: column;
                width: 100%;
                margin-top: 15px;
            }

            .cliente-header-actions .btn {
                width: 100%;
            }

            .cliente-badge {
                font-size: 11px;
            }
        }

        /* ===== CARDS INFO ===== */
        .card-info {
            background: var(--white);
            padding: 25px;
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            transition: all var(--transition-base);
        }

        .card-info:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--gray-300);
        }

        .card-info h2 {
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 18px;
            color: var(--gray-900);
            border-bottom: 2px solid var(--gray-300);
            padding-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .card-info h2 i {
            color: var(--gray-700);
        }

        .info-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-weight: 600;
            color: var(--gray-600);
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: var(--gray-900);
            font-size: 16px;
            word-break: break-word;
            font-weight: 500;
        }

        .info-value.empty {
            color: var(--gray-400);
            font-style: italic;
            font-weight: 400;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
        }

        .btn-group .btn {
            flex: 1;
            min-width: 150px;
        }

        .container-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 1024px) {
            .container-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ===== TABELAS ===== */
        .tabela-vendas {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
        }

        .tabela-vendas thead tr {
            background: var(--gray-100);
            border-bottom: 2px solid var(--gray-200);
        }

        .tabela-vendas th {
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: var(--gray-800);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tabela-vendas td {
            padding: 15px;
            border-bottom: 1px solid var(--gray-200);
            color: var(--gray-700);
            font-size: 14px;
        }

        .tabela-vendas tbody tr {
            transition: background-color var(--transition-fast);
        }

        .tabela-vendas tbody tr:hover {
            background: var(--gray-50);
        }

        .tabela-vendas a {
            color: var(--gray-800);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all var(--transition-fast);
        }

        .tabela-vendas a:hover {
            color: var(--gray-900);
        }

        .tabela-vendas a i {
            opacity: 0.7;
        }

        .tabela-vendas a:hover i {
            opacity: 1;
        }

        /* ===== STATUS BADGES ===== */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: var(--border-radius-full);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-badge.aberta {
            background: var(--warning-light);
            color: var(--warning-dark);
        }

        .status-badge.parcial {
            background: var(--info-light);
            color: var(--info-dark);
        }

        .status-badge.paga,
        .status-badge.pago {
            background: var(--success-light);
            color: var(--success-dark);
        }

        .status-badge.cancelada {
            background: var(--danger-light);
            color: var(--danger-dark);
        }

        .status-badge.ativo {
            background: var(--success-light);
            color: var(--success-dark);
        }

        .status-badge.inativo {
            background: var(--danger-light);
            color: var(--danger-dark);
        }

        /* ===== MODAL ===== */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            max-width: 500px;
            width: 100%;
            box-shadow: var(--shadow-xl);
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid var(--gray-200);
        }

        .modal-content h2 {
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 20px;
            color: var(--gray-900);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: 15px;
        }

        .modal-content h2 i {
            color: var(--gray-700);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--gray-800);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-family: var(--font-family);
            font-size: 14px;
            box-sizing: border-box;
            background-color: var(--white);
            color: var(--gray-800);
            transition: all var(--transition-base);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--gray-400);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--gray-700);
            box-shadow: 0 0 0 3px rgba(31, 41, 55, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            font-family: var(--font-family);
        }

        .form-error {
            color: var(--danger);
            font-size: 12px;
            margin-top: 6px;
            display: block;
            font-weight: 500;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
            flex-wrap: wrap;
        }

        .modal-footer .btn {
            min-width: 120px;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
            color: var(--gray-400);
        }

        .empty-state p {
            margin: 0;
            font-size: 16px;
            color: var(--gray-600);
        }

        /* ===== BREADCRUMB ===== */
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--gray-600);
            font-weight: 500;
        }

        .breadcrumb a {
            color: var(--gray-800);
            text-decoration: none;
            font-weight: 600;
            transition: all var(--transition-fast);
        }

        .breadcrumb a:hover {
            color: var(--gray-900);
            text-decoration: underline;
        }

        /* ===== VALORES MONETARIOS ===== */
        .valor-total {
            color: var(--gray-900);
            font-weight: 700;
            font-size: 16px;
        }

        .valor-pago {
            color: var(--success-dark);
            font-weight: 700;
            font-size: 16px;
        }

        .valor-devido {
            color: var(--danger-dark);
            font-weight: 700;
            font-size: 16px;
        }

        .taxa-pagamento {
            color: var(--gray-800);
            font-weight: 700;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo PUBLIC_URL_RELATIVE; ?>clientes.php">
                <i class="fas fa-users"></i> Clientes
            </a>
            <span> / Detalhes</span>
        </div>

        <!-- HEADER -->
        <div class="cliente-header">
            <div class="cliente-header-conteudo">
                <div class="cliente-header-avatar">
                    <?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?>
                </div>
                <div class="cliente-header-info">
                    <h1>
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($cliente['nome']); ?>
                    </h1>
                    <p>
                        <i class="fas fa-phone"></i>
                        <?php echo htmlspecialchars($cliente['telefone'] ?? 'Não informado'); ?>
                    </p>
                    <p>
                        <i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($cliente['email'] ?? 'Não informado'); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="container-grid">
            <!-- INFORMACOES BASICAS -->
            <div class="card-info">
                <h2>
                    <i class="fas fa-info-circle"></i>
                    Informações Básicas
                </h2>

                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Nome</span>
                        <span class="info-value"><?php echo htmlspecialchars($cliente['nome']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">CPF/CNPJ</span>
                        <span class="info-value <?php echo empty($cliente['cpf_cnpj']) ? 'empty' : ''; ?>">
                            <?php echo htmlspecialchars($cliente['cpf_cnpj'] ?? 'Não informado'); ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value <?php echo empty($cliente['email']) ? 'empty' : ''; ?>">
                            <?php echo htmlspecialchars($cliente['email'] ?? 'Não informado'); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Telefone</span>
                        <span class="info-value <?php echo empty($cliente['telefone']) ? 'empty' : ''; ?>">
                            <?php echo htmlspecialchars($cliente['telefone'] ?? 'Não informado'); ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Endereço</span>
                        <span class="info-value <?php echo empty($cliente['endereco']) ? 'empty' : ''; ?>">
                            <?php echo htmlspecialchars($cliente['endereco'] ?? 'Não informado'); ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="status-badge <?php echo $cliente['ativo'] ? 'ativo' : 'inativo'; ?>">
                                <i class="fas fa-circle"></i>
                                <?php echo $cliente['ativo'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Data de Cadastro</span>
                        <span class="info-value">
                            <?php echo date(DATE_FORMAT, strtotime($cliente['data_criacao'])); ?>
                        </span>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="button" onclick="abrirModalEditar()" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar Cliente
                    </button>
                    <button type="button" onclick="confirmarDeleteCliente(<?php echo $idCliente; ?>)" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Deletar
                    </button>
                </div>
            </div>

            <!-- RESUMO FINANCEIRO -->
            <div class="card-info">
                <h2>
                    <i class="fas fa-chart-pie"></i>
                    Resumo Financeiro
                </h2>

                <?php
                $totalVendas = 0;
                $totalPago = 0;
                $totalDevido = 0;

                if (!empty($vendas)) {
                    foreach ($vendas as $venda) {
                        $totalVendas += $venda['valor_total'];
                        $totalPago += $venda['valor_pago'];
                        $totalDevido += $venda['saldo_devedor'];
                    }
                }
                ?>

                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Total de Vendas</span>
                        <span class="info-value valor-total">
                            R$ <?php echo number_format($totalVendas, CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Pago</span>
                        <span class="info-value valor-pago">
                            R$ <?php echo number_format($totalPago, CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Saldo Devedor</span>
                        <span class="info-value valor-devido">
                            R$ <?php echo number_format($totalDevido, CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Número de Vendas</span>
                        <span class="info-value taxa-pagamento">
                            <?php echo count($vendas); ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Taxa de Pagamento</span>
                        <span class="info-value taxa-pagamento">
                            <?php
                            $taxaPagamento = $totalVendas > 0 ? round(($totalPago / $totalVendas) * 100, 1) : 0;
                            echo $taxaPagamento . '%';
                            ?>
                        </span>
                    </div>
                </div>

                <div class="btn-group">
                    <a href="<?php echo PUBLIC_URL_RELATIVE; ?>nova_venda.php?cliente=<?php echo $idCliente; ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nova Venda
                    </a>
                </div>
            </div>
        </div>

        <!-- HISTORICO DE VENDAS -->
        <div class="card-info">
            <h2>
                <i class="fas fa-history"></i>
                Histórico de Vendas
            </h2>

            <?php if (empty($vendas)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Nenhuma venda registrada para este cliente</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="tabela-vendas">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data</th>
                                <th>Valor Total</th>
                                <th>Valor Pago</th>
                                <th>Saldo Devedor</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendas as $venda): ?>
                                <tr>
                                    <td>#<?php echo $venda['id_venda']; ?></td>
                                    <td><?php echo date(DATE_FORMAT, strtotime($venda['data_venda'])); ?></td>
                                    <td>R$ <?php echo number_format($venda['valor_total'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?></td>
                                    <td>R$ <?php echo number_format($venda['valor_pago'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?></td>
                                    <td>R$ <?php echo number_format($venda['saldo_devedor'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower($venda['status_geral']); ?>">
                                            <i class="fas fa-circle"></i>
                                            <?php
                                            $statusMap = [
                                                STATUS_VENDA_ABERTA => 'Aberta',
                                                STATUS_VENDA_PARCIAL => 'Parcial',
                                                STATUS_VENDA_PAGA => 'Paga',
                                                STATUS_VENDA_CANCELADA => 'Cancelada'
                                            ];
                                            echo $statusMap[$venda['status_geral']] ?? $venda['status_geral'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo PUBLIC_URL_RELATIVE; ?>detalhes_venda.php?id=<?php echo $venda['id_venda']; ?>">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL EDITAR CLIENTE -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <h2>
                <i class="fas fa-edit"></i>
                Editar Cliente
            </h2>

            <form id="formEditarCliente" onsubmit="enviarEdicaoCliente(event)">
                <input type="hidden" id="editIdCliente" value="<?php echo $idCliente; ?>">

                <div class="form-group">
                    <label for="editNome">Nome *</label>
                    <input
                        type="text"
                        id="editNome"
                        required
                        value="<?php echo htmlspecialchars($cliente['nome']); ?>">
                    <span class="form-error" id="erroEditNome"></span>
                </div>

                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input
                        type="email"
                        id="editEmail"
                        value="<?php echo htmlspecialchars($cliente['email'] ?? ''); ?>">
                    <span class="form-error" id="erroEditEmail"></span>
                </div>

                <div class="form-group">
                    <label for="editTelefone">Telefone</label>
                    <input
                        type="text"
                        id="editTelefone"
                        class="input-telefone"
                        value="<?php echo htmlspecialchars($cliente['telefone'] ?? ''); ?>">
                    <span class="form-error" id="erroEditTelefone"></span>
                </div>

                <div class="form-group">
                    <label for="editEndereco">Endereço</label>
                    <input
                        type="text"
                        id="editEndereco"
                        value="<?php echo htmlspecialchars($cliente['endereco'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="editCPF">CPF/CNPJ</label>
                    <input
                        type="text"
                        id="editCPF"
                        class="input-cpf-cnpj"
                        value="<?php echo htmlspecialchars($cliente['cpf_cnpj'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="editObservacoes">Observações</label>
                    <textarea
                        id="editObservacoes"
                        rows="3"><?php echo htmlspecialchars($cliente['observacoes'] ?? ''); ?></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="fecharModalEditar()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" id="btnSubmitEdicao" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?php echo getAssetUrl('js/mascaras.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('js/validacoes.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('js/app.js'); ?>"></script>

    <script>
        function abrirModalEditar() {
            document.getElementById('modalEditar').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function fecharModalEditar() {
            document.getElementById('modalEditar').classList.remove('show');
            document.body.style.overflow = 'auto';
            limparErrosModal();
        }

        function limparErrosModal() {
            document.getElementById('erroEditNome').textContent = '';
            document.getElementById('erroEditEmail').textContent = '';
            document.getElementById('erroEditTelefone').textContent = '';
        }

        function enviarEdicaoCliente(event) {
            event.preventDefault();

            const idCliente = document.getElementById('editIdCliente').value;
            const nome = document.getElementById('editNome').value.trim();
            const email = document.getElementById('editEmail').value.trim();
            const telefone = document.getElementById('editTelefone').value.trim();
            const endereco = document.getElementById('editEndereco').value.trim();
            const cpf = document.getElementById('editCPF').value.trim();
            const observacoes = document.getElementById('editObservacoes').value.trim();

            limparErrosModal();

            // Validar
            let temErro = false;

            if (!nome || nome.length < 3) {
                document.getElementById('erroEditNome').textContent = 'Nome deve ter pelo menos 3 caracteres';
                temErro = true;
            }

            if (email && !email.includes('@')) {
                document.getElementById('erroEditEmail').textContent = 'Email inválido';
                temErro = true;
            }

            if (temErro) return false;

            const dados = {
                id_cliente: idCliente,
                nome: nome,
                email: email,
                telefone: telefone,
                endereco: endereco,
                cpf_cnpj: cpf,
                observacoes: observacoes
            };

            const btn = document.getElementById('btnSubmitEdicao');
            const btnText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            fetch('<?php echo API_URL_RELATIVE; ?>clientes/salvar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dados)
                })
                .then(response => {
                    if (!response.ok) throw new Error('Erro na resposta');
                    return response.json();
                })
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = btnText;

                    if (data.sucesso) {
                        alert('Cliente atualizado com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro: ' + (data.mensagem || 'Erro ao atualizar cliente'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    btn.disabled = false;
                    btn.innerHTML = btnText;
                    alert('Erro ao atualizar cliente: ' + error.message);
                });

            return false;
        }

        function confirmarDeleteCliente(idCliente) {
            if (confirm('Tem certeza que deseja deletar este cliente? Esta ação não pode ser desfeita.')) {
                fetch('<?php echo API_URL_RELATIVE; ?>clientes/deletar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_cliente: idCliente
                        })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Erro na resposta');
                        return response.json();
                    })
                    .then(data => {
                        if (data.sucesso) {
                            alert('Cliente deletado com sucesso!');
                            window.location.href = '<?php echo PUBLIC_URL_RELATIVE; ?>clientes.php';
                        } else {
                            alert('Erro: ' + (data.mensagem || 'Erro ao deletar cliente'));
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao deletar cliente: ' + error.message);
                    });
            }
        }

        // Fechar modal ao clicar fora
        document.getElementById('modalEditar').addEventListener('click', function(event) {
            if (event.target === this) {
                fecharModalEditar();
            }
        });

        // Fechar modal ao pressionar ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                fecharModalEditar();
            }
        });
    </script>
</body>

</html>