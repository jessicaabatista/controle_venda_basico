<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../config/config.php';
require_once '../config/auth.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Cliente.php';
require_once CLASSES_PATH . '/Venda.php';
require_once CLASSES_PATH . '/Pagamento.php';

$venda_obj = new Venda();
$cliente_obj = new Cliente();
$pagamento_obj = new Pagamento();

$idVenda = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$idVenda) {
    header('Location: ' . PUBLIC_URL_RELATIVE . 'vendas.php');
    exit;
}

try {
    $venda = $venda_obj->obter($idVenda);

    if (!$venda) {
        header('Location: ' . PUBLIC_URL_RELATIVE . 'vendas.php');
        exit;
    }

    $itens = $venda_obj->obterItens($idVenda);
    $pagamentos = $pagamento_obj->obterPorVenda($idVenda);
    $cliente = $cliente_obj->obter($venda['id_cliente']);
} catch (Exception $e) {
    error_log('Erro ao carregar detalhes da venda: ' . $e->getMessage());
    header('Location: ' . PUBLIC_URL_RELATIVE . 'vendas.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Venda #<?php echo $idVenda; ?> - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/style.css'); ?>">
    <style>
        .venda-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .venda-header h1 {
            margin: 0 0 15px 0;
            font-size: 32px;
            font-weight: 700;
        }

        .venda-header-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .venda-header-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .venda-header-item-label {
            font-size: 12px;
            text-transform: uppercase;
            opacity: 0.9;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .venda-header-item-value {
            font-size: 18px;
            font-weight: 700;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h2 {
            margin: 0;
            font-size: 20px;
            color: #333;
            font-weight: 600;
        }

        .card-content {
            padding: 25px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-section:last-child {
            margin-bottom: 0;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        .info-value {
            color: #333;
            font-size: 15px;
            font-weight: 500;
        }

        .info-value.highlight {
            color: #667eea;
            font-weight: 700;
        }

        .info-value.success {
            color: #28a745;
            font-weight: 700;
        }

        .info-value.danger {
            color: #ff6b6b;
            font-weight: 700;
        }

        .info-value.warning {
            color: #ff9800;
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.aberta {
            background: #e7f5ff;
            color: #0066cc;
        }

        .status-badge.parcial {
            background: #fff3e0;
            color: #ff8800;
        }

        .status-badge.paga {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-badge.cancelada {
            background: #ffebee;
            color: #c62828;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        .table th {
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #666;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-group.vertical {
            flex-direction: column;
            gap: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 10px 0 0 0;
            font-size: 16px;
        }

        .modal-overlay {
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

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: #333;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap;
            background: #f8f9fa;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-error {
            color: #ff6b6b;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .form-group.error input,
        .form-group.error textarea {
            border-color: #ff6b6b;
        }

        .value-display {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            border: 1px solid #e9ecef;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .action-buttons .btn {
            padding: 8px 16px;
            font-size: 13px;
        }

        .section-header {
            margin-bottom: 20px;
        }

        .section-header h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }

        .section-header p {
            margin: 0;
            color: #999;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .venda-header {
                padding: 25px;
            }

            .venda-header h1 {
                font-size: 24px;
            }

            .venda-header-info {
                grid-template-columns: 1fr 1fr;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .table th,
            .table td {
                padding: 10px;
                font-size: 12px;
            }

            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo PUBLIC_URL_RELATIVE; ?>vendas.php">Vendas</a> / Detalhes
        </div>

        <!-- HEADER VENDA -->
        <div class="venda-header">
            <h1>
                <i class="fas fa-receipt"></i> Venda #<?php echo $idVenda; ?>
            </h1>
            <div class="venda-header-info">
                <div class="venda-header-item">
                    <div class="venda-header-item-label">Cliente</div>
                    <div class="venda-header-item-value">
                        <?php echo htmlspecialchars($cliente['nome']); ?>
                    </div>
                </div>
                <div class="venda-header-item">
                    <div class="venda-header-item-label">Status</div>
                    <div class="venda-header-item-value">
                        <span class="status-badge <?php echo strtolower($venda['status_geral']); ?>">
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
                    </div>
                </div>
                <div class="venda-header-item">
                    <div class="venda-header-item-label">Data</div>
                    <div class="venda-header-item-value">
                        <?php echo date(DATE_FORMAT, strtotime($venda['data_venda'])); ?>
                    </div>
                </div>
                <div class="venda-header-item">
                    <div class="venda-header-item-label">Saldo Devedor</div>
                    <div class="venda-header-item-value">
                        R$ <?php echo number_format($venda['saldo_devedor'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid-2">
            <!-- COLUNA 1: DADOS DA VENDA E CLIENTE -->
            <div>
                <!-- DADOS GERAIS -->
                <div class="card">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-info-circle"></i> Dados Gerais
                        </h2>
                    </div>
                    <div class="card-content">
                        <div class="info-section">
                            <div class="info-item">
                                <span class="info-label">ID Venda</span>
                                <span class="info-value">#<?php echo $idVenda; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Data Criacao</span>
                                <span class="info-value">
                                    <?php echo date(DATETIME_FORMAT, strtotime($venda['data_venda'])); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ultima Atualizacao</span>
                                <span class="info-value">
                                    <?php echo date(DATETIME_FORMAT, strtotime($venda['data_atualizacao'])); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="info-value">
                                    <span class="status-badge <?php echo strtolower($venda['status_geral']); ?>">
                                        <?php echo $statusMap[$venda['status_geral']] ?? $venda['status_geral']; ?>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($venda['observacoes'])): ?>
                            <div class="info-section">
                                <div style="margin-bottom: 10px;">
                                    <span class="info-label">Observacoes</span>
                                </div>
                                <div style="background: #f8f9fa; padding: 12px; border-radius: 5px; font-size: 14px; color: #333; line-height: 1.6;">
                                    <?php echo htmlspecialchars($venda['observacoes']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- DADOS DO CLIENTE -->
                <div class="card">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-user"></i> Dados do Cliente
                        </h2>
                        <a href="<?php echo PUBLIC_URL_RELATIVE; ?>detalhes_cliente.php?id=<?php echo $venda['id_cliente']; ?>" class="btn btn-primary" style="text-decoration: none; display: inline-block; padding: 8px 15px; font-size: 13px;">
                            <i class="fas fa-external-link-alt"></i> Ver Cliente
                        </a>
                    </div>
                    <div class="card-content">
                        <div class="info-section">
                            <div class="info-item">
                                <span class="info-label">Nome</span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($cliente['nome']); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($cliente['email'] ?? 'Nao informado'); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Telefone</span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($cliente['telefone'] ?? 'Nao informado'); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">CPF/CNPJ</span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($cliente['cpf_cnpj'] ?? 'Nao informado'); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Endereco</span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($cliente['endereco'] ?? 'Nao informado'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUNA 2: RESUMO FINANCEIRO -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-chart-pie"></i> Resumo Financeiro
                        </h2>
                    </div>
                    <div class="card-content">
                        <div class="info-section">
                            <div class="info-item">
                                <span class="info-label">Valor Total</span>
                                <span class="info-value highlight">
                                    R$ <?php echo number_format($venda['valor_total'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Valor Pago</span>
                                <span class="info-value success">
                                    R$ <?php echo number_format($venda['valor_pago'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Saldo Devedor</span>
                                <span class="info-value <?php echo $venda['saldo_devedor'] > 0 ? 'danger' : 'success'; ?>">
                                    R$ <?php echo number_format($venda['saldo_devedor'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Percentual Pago</span>
                                <span class="info-value highlight">
                                    <?php 
                                    $percentualPago = $venda['valor_total'] > 0 ? round(($venda['valor_pago'] / $venda['valor_total']) * 100, 1) : 0;
                                    echo $percentualPago . '%';
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div style="margin-top: 25px; padding-top: 25px; border-top: 2px solid #e9ecef;">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
                                <div style="font-size: 12px; text-transform: uppercase; margin-bottom: 5px; opacity: 0.9;">Taxa de Pagamento</div>
                                <div style="font-size: 28px; font-weight: 700;">
                                    <?php echo $percentualPago; ?>%
                                </div>
                            </div>
                        </div>

                        <div class="btn-group vertical">
                            <button type="button" onclick="abrirModalAdicionarPagamento(<?php echo $idVenda; ?>)" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Adicionar Pagamento
                            </button>
                            <button type="button" onclick="abrirModalEditarVenda(<?php echo $idVenda; ?>)" class="btn btn-secondary">
                                <i class="fas fa-edit"></i> Editar Venda
                            </button>
                            <a href="<?php echo PUBLIC_URL_RELATIVE; ?>vendas.php" class="btn btn-secondary" style="text-decoration: none; text-align: center;">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ITENS DA VENDA -->
        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="fas fa-box"></i> Itens da Venda
                </h2>
                <button type="button" onclick="abrirModalAdicionarItem(<?php echo $idVenda; ?>)" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar Item
                </button>
            </div>
            <div class="card-content">
                <?php if (empty($itens)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Nenhum item adicionado a esta venda</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Descricao</th>
                                    <th style="text-align: center;">Qtd</th>
                                    <th style="text-align: right;">Valor Unit.</th>
                                    <th style="text-align: right;">Total</th>
                                    <th style="text-align: right;">Pago</th>
                                    <th style="text-align: right;">Saldo</th>
                                    <th style="text-align: center;">Status</th>
                                    <th style="text-align: center;">Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['codigo_produto']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['descricao']); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php echo $item['quantidade']; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            R$ <?php echo number_format($item['valor_unitario'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 600;">
                                            R$ <?php echo number_format($item['valor_total'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                        </td>
                                        <td style="text-align: right; color: #28a745; font-weight: 600;">
                                            R$ <?php echo number_format($item['valor_pago'] ?? 0, CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                        </td>
                                        <td style="text-align: right; color: #ff6b6b; font-weight: 600;">
                                            R$ <?php echo number_format($item['saldo_item'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="status-badge <?php echo strtolower($item['status_pagamento']); ?>">
                                                <?php
                                                $itemStatusMap = [
                                                    STATUS_ITEM_PENDENTE => 'Pendente',
                                                    STATUS_ITEM_PARCIAL => 'Parcial',
                                                    STATUS_ITEM_PAGO => 'Pago'
                                                ];
                                                echo $itemStatusMap[$item['status_pagamento']] ?? $item['status_pagamento'];
                                                ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="action-buttons">
                                                <button type="button" onclick="abrirModalEditarItem(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="btn btn-sm" style="padding: 6px 10px;">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" onclick="confirmarRemoverItem(<?php echo $item['id_item']; ?>)" class="btn btn-sm btn-danger" style="padding: 6px 10px;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PAGAMENTOS -->
        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="fas fa-money-bill-wave"></i> Historico de Pagamentos
                </h2>
                <button type="button" onclick="abrirModalAdicionarPagamento(<?php echo $idVenda; ?>)" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Pagamento
                </button>
            </div>
            <div class="card-content">
                <?php if (empty($pagamentos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Nenhum pagamento registrado</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th style="text-align: right;">Valor</th>
                                    <th>Observacoes</th>
                                    <th style="text-align: center;">Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagamentos as $pag): ?>
                                    <tr>
                                        <td>
                                            <?php echo date(DATETIME_FORMAT, strtotime($pag['data_pagamento'])); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge">
                                                <?php echo htmlspecialchars($pag['tipo_pagamento']); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right; font-weight: 600; color: #28a745;">
                                            R$ <?php echo number_format($pag['valor'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($pag['observacoes'] ?? 'Sem observacoes'); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" onclick="confirmarRemoverPagamento(<?php echo $pag['id_pagamento']; ?>)" class="btn btn-sm btn-danger" style="padding: 6px 10px;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL ADICIONAR ITEM -->
    <div id="modalAdicionarItem" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-plus"></i> Adicionar Item
                </h2>
                <button type="button" onclick="fecharModalAdicionarItem()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formAdicionarItem" onsubmit="enviarAdicionarItem(event)">
                <div class="modal-body">
                    <input type="hidden" id="idVendaItem">

                    <div class="form-group">
                        <label for="codigoProduto">
                            <i class="fas fa-barcode"></i> Codigo do Produto
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="codigoProduto" 
                            placeholder="Ex: PROD001" 
                            required>
                        <span class="form-error" id="erroCodigoProduto"></span>
                    </div>

                    <div class="form-group">
                        <label for="descricaoItem">
                            <i class="fas fa-align-left"></i> Descricao
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="descricaoItem" 
                            placeholder="Ex: Colar de Ouro 18k" 
                            required>
                        <span class="form-error" id="erroDescricaoItem"></span>
                    </div>

                    <div class="form-group">
                        <label for="quantidadeItem">
                            <i class="fas fa-box"></i> Quantidade
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="quantidadeItem" 
                            min="1" 
                            step="1" 
                            value="1" 
                            required
                            onchange="calcularValorTotalItem()">
                        <span class="form-error" id="erroQuantidadeItem"></span>
                    </div>

                    <div class="form-group">
                        <label for="valorUnitarioItem">
                            <i class="fas fa-dollar-sign"></i> Valor Unitario
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="valorUnitarioItem" 
                            min="0.01" 
                            step="0.01" 
                            required
                            onchange="calcularValorTotalItem()">
                        <span class="form-error" id="erroValorUnitarioItem"></span>
                    </div>

                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #667eea;">
                        <div style="font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 5px; font-weight: 600;">
                            Valor Total do Item
                        </div>
                        <div style="font-size: 24px; font-weight: 700; color: #667eea;">
                            R$ <span id="valorTotalItemDisplay">0,00</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="fecharModalAdicionarItem()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" id="btnSubmitItem" class="btn btn-primary">
                        <i class="fas fa-check"></i> Adicionar Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR ITEM -->
    <div id="modalEditarItem" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-edit"></i> Editar Item
                </h2>
                <button type="button" onclick="fecharModalEditarItem()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formEditarItem" onsubmit="enviarEditarItem(event)">
                <div class="modal-body">
                    <input type="hidden" id="editIdItem">
                    <input type="hidden" id="editIdVenda">

                    <div class="form-group">
                        <label>
                            <i class="fas fa-barcode"></i> Codigo do Produto
                        </label>
                        <div class="value-display" id="editCodigoProdutoDisplay"></div>
                    </div>

                    <div class="form-group">
                        <label for="editDescricaoItem">
                            <i class="fas fa-align-left"></i> Descricao
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="editDescricaoItem" 
                            required>
                        <span class="form-error" id="erroEditDescricaoItem"></span>
                    </div>

                    <div class="form-group">
                        <label for="editQuantidadeItem">
                            <i class="fas fa-box"></i> Quantidade
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="editQuantidadeItem" 
                            min="1" 
                            step="1" 
                            required
                            onchange="calcularValorTotalEditItem()">
                        <span class="form-error" id="erroEditQuantidadeItem"></span>
                    </div>

                    <div class="form-group">
                        <label for="editValorUnitarioItem">
                            <i class="fas fa-dollar-sign"></i> Valor Unitario
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="editValorUnitarioItem" 
                            min="0.01" 
                            step="0.01" 
                            required
                            onchange="calcularValorTotalEditItem()">
                        <span class="form-error" id="erroEditValorUnitarioItem"></span>
                    </div>

                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #667eea;">
                        <div style="font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 5px; font-weight: 600;">
                            Valor Total do Item
                        </div>
                        <div style="font-size: 24px; font-weight: 700; color: #667eea;">
                            R$ <span id="valorTotalEditItemDisplay">0,00</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="fecharModalEditarItem()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" id="btnSubmitEditItem" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alteracoes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL ADICIONAR PAGAMENTO -->
    <div id="modalAdicionarPagamento" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-plus"></i> Adicionar Pagamento
                </h2>
                <button type="button" onclick="fecharModalAdicionarPagamento()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formAdicionarPagamento" onsubmit="enviarAdicionarPagamento(event)">
                <div class="modal-body">
                    <input type="hidden" id="idVendaPagamento" value="<?php echo $idVenda; ?>">

                    <div style="background: #e7f5ff; padding: 15px; border-radius: 5px; border-left: 4px solid #0066cc; margin-bottom: 20px;">
                        <div style="font-size: 12px; color: #0066cc; text-transform: uppercase; margin-bottom: 5px; font-weight: 600;">
                            Saldo Devedor
                        </div>
                        <div style="font-size: 24px; font-weight: 700; color: #0066cc;">
                            R$ <?php echo number_format($venda['saldo_devedor'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="valorPagamento">
                            <i class="fas fa-dollar-sign"></i> Valor do Pagamento
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="valorPagamento" 
                            min="0.01" 
                            step="0.01" 
                            max="<?php echo $venda['saldo_devedor']; ?>"
                            required>
                        <span class="form-error" id="erroValorPagamento"></span>
                        <small style="color: #999; margin-top: 5px; display: block;">
                            Maximo: R$ <?php echo number_format($venda['saldo_devedor'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="tipoPagamento">
                            <i class="fas fa-credit-card"></i> Tipo de Pagamento
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <select id="tipoPagamento" required>
                            <option value="">-- Selecione --</option>
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartao Credito">Cartao Credito</option>
                            <option value="Cartao Debito">Cartao Debito</option>
                            <option value="Transferencia">Transferencia Bancaria</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Outro">Outro</option>
                        </select>
                        <span class="form-error" id="erroTipoPagamento"></span>
                    </div>

                    <div class="form-group">
                        <label for="dataPagamento">
                            <i class="fas fa-calendar"></i> Data do Pagamento
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="dataPagamento" 
                            value="<?php echo date('Y-m-d'); ?>"
                            required>
                        <span class="form-error" id="erroDataPagamento"></span>
                    </div>

                    <div class="form-group">
                        <label for="observacoesPagamento">
                            <i class="fas fa-sticky-note"></i> Observacoes
                        </label>
                        <textarea 
                            id="observacoesPagamento" 
                            rows="3" 
                            placeholder="Observacoes adicionais sobre o pagamento..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="fecharModalAdicionarPagamento()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" id="btnSubmitPagamento" class="btn btn-primary">
                        <i class="fas fa-check"></i> Registrar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR VENDA -->
    <div id="modalEditarVenda" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-edit"></i> Editar Venda
                </h2>
                <button type="button" onclick="fecharModalEditarVenda()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formEditarVenda" onsubmit="enviarEditarVenda(event)">
                <div class="modal-body">
                    <input type="hidden" id="editIdVenda" value="<?php echo $idVenda; ?>">

                    <div class="form-group">
                        <label for="editObservacoes">
                            <i class="fas fa-sticky-note"></i> Observacoes
                        </label>
                        <textarea 
                            id="editObservacoes" 
                            rows="5"><?php echo htmlspecialchars($venda['observacoes'] ?? ''); ?></textarea>
                        <small style="color: #999; margin-top: 5px; display: block;">
                            Adicione notas internas sobre a venda
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="editStatusVenda">
                            <i class="fas fa-info-circle"></i> Status
                            <span style="color: #ff6b6b;">*</span>
                        </label>
                        <select id="editStatusVenda" required>
                            <option value="<?php echo STATUS_VENDA_ABERTA; ?>" <?php echo $venda['status_geral'] === STATUS_VENDA_ABERTA ? 'selected' : ''; ?>>Aberta</option>
                            <option value="<?php echo STATUS_VENDA_PARCIAL; ?>" <?php echo $venda['status_geral'] === STATUS_VENDA_PARCIAL ? 'selected' : ''; ?>>Parcial</option>
                            <option value="<?php echo STATUS_VENDA_PAGA; ?>" <?php echo $venda['status_geral'] === STATUS_VENDA_PAGA ? 'selected' : ''; ?>>Paga</option>
                            <option value="<?php echo STATUS_VENDA_CANCELADA; ?>" <?php echo $venda['status_geral'] === STATUS_VENDA_CANCELADA ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                        <span class="form-error" id="erroEditStatusVenda"></span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="fecharModalEditarVenda()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" id="btnSubmitEditVenda" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alteracoes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?php echo getAssetUrl('js/mascaras.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('js/validacoes.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('js/app.js'); ?>"></script>

    <script>
        const DECIMAL_PLACES = <?php echo CASAS_DECIMAIS; ?>;
        const DECIMAL_SEP = '<?php echo SEPARADOR_DECIMAL; ?>';
        const THOUSAND_SEP = '<?php echo SEPARADOR_MILHAR; ?>';

        function formatarMoeda(valor) {
            return new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: DECIMAL_PLACES,
                maximumFractionDigits: DECIMAL_PLACES
            }).format(valor);
        }

        // ========== MODAL ADICIONAR ITEM ==========
        function abrirModalAdicionarItem(idVenda) {
            document.getElementById('idVendaItem').value = idVenda;
            document.getElementById('formAdicionarItem').reset();
            document.getElementById('valorTotalItemDisplay').textContent = formatarMoeda(0);
            document.getElementById('modalAdicionarItem').classList.add('show');
            document.body.style.overflow = 'hidden';
            document.getElementById('codigoProduto').focus();
        }

        function fecharModalAdicionarItem() {
            document.getElementById('modalAdicionarItem').classList.remove('show');
            document.body.style.overflow = 'auto';
            limparErrosAdicionarItem();
        }

        function limparErrosAdicionarItem() {
            document.getElementById('erroCodigoProduto').textContent = '';
            document.getElementById('erroDescricaoItem').textContent = '';
            document.getElementById('erroQuantidadeItem').textContent = '';
            document.getElementById('erroValorUnitarioItem').textContent = '';
        }

        function calcularValorTotalItem() {
            const quantidade = parseFloat(document.getElementById('quantidadeItem').value) || 0;
            const valorUnitario = parseFloat(document.getElementById('valorUnitarioItem').value) || 0;
            const total = quantidade * valorUnitario;
            document.getElementById('valorTotalItemDisplay').textContent = formatarMoeda(total);
        }

        function enviarAdicionarItem(event) {
            event.preventDefault();

            const codigo = document.getElementById('codigoProduto').value.trim();
            const descricao = document.getElementById('descricaoItem').value.trim();
            const quantidade = parseInt(document.getElementById('quantidadeItem').value);
            const valorUnitario = parseFloat(document.getElementById('valorUnitarioItem').value);
            const idVenda = document.getElementById('idVendaItem').value;

            limparErrosAdicionarItem();

            let temErro = false;

            if (!codigo || codigo.length < 2) {
                document.getElementById('erroCodigoProduto').textContent = 'Codigo deve ter pelo menos 2 caracteres';
                temErro = true;
            }

            if (!descricao || descricao.length < 3) {
                document.getElementById('erroDescricaoItem').textContent = 'Descricao deve ter pelo menos 3 caracteres';
                temErro = true;
            }

            if (!quantidade || quantidade < 1) {
                document.getElementById('erroQuantidadeItem').textContent = 'Quantidade deve ser maior que 0';
                temErro = true;
            }

            if (!valorUnitario || valorUnitario <= 0) {
                document.getElementById('erroValorUnitarioItem').textContent = 'Valor deve ser maior que 0';
                temErro = true;
            }

            if (temErro) return false;

            const dados = {
                id_venda: idVenda,
                codigo_produto: codigo,
                descricao: descricao,
                quantidade: quantidade,
                valor_unitario: valorUnitario
            };

            const btn = document.getElementById('btnSubmitItem');
            const btnText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';

            fetch('<?php echo API_URL_RELATIVE; ?>vendas/adicionar_item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
                    fecharModalAdicionarItem();
                    location.reload();
                } else {
                    alert('Erro: ' + (data.mensagem || 'Erro ao adicionar item'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                btn.disabled = false;
                btn.innerHTML = btnText;
                alert('Erro ao adicionar item: ' + error.message);
            });

            return false;
        }

        // ========== MODAL EDITAR ITEM ==========
        function abrirModalEditarItem(itemJson) {
            const item = JSON.parse(itemJson);
            
            document.getElementById('editIdItem').value = item.id_item;
            document.getElementById('editIdVenda').value = item.id_venda;
            document.getElementById('editCodigoProdutoDisplay').textContent = item.codigo_produto;
            document.getElementById('editDescricaoItem').value = item.descricao;
            document.getElementById('editQuantidadeItem').value = item.quantidade;
            document.getElementById('editValorUnitarioItem').value = item.valor_unitario;

            calcularValorTotalEditItem();

            document.getElementById('modalEditarItem').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function fecharModalEditarItem() {
            document.getElementById('modalEditarItem').classList.remove('show');
            document.body.style.overflow = 'auto';
            limparErrosEditarItem();
        }

        function limparErrosEditarItem() {
            document.getElementById('erroEditDescricaoItem').textContent = '';
            document.getElementById('erroEditQuantidadeItem').textContent = '';
            document.getElementById('erroEditValorUnitarioItem').textContent = '';
        }

        function calcularValorTotalEditItem() {
            const quantidade = parseFloat(document.getElementById('editQuantidadeItem').value) || 0;
            const valorUnitario = parseFloat(document.getElementById('editValorUnitarioItem').value) || 0;
            const total = quantidade * valorUnitario;
            document.getElementById('valorTotalEditItemDisplay').textContent = formatarMoeda(total);
        }

        function enviarEditarItem(event) {
            event.preventDefault();

            const idItem = document.getElementById('editIdItem').value;
            const idVenda = document.getElementById('editIdVenda').value;
            const descricao = document.getElementById('editDescricaoItem').value.trim();
            const quantidade = parseInt(document.getElementById('editQuantidadeItem').value);
            const valorUnitario = parseFloat(document.getElementById('editValorUnitarioItem').value);

            limparErrosEditarItem();

            let temErro = false;

            if (!descricao || descricao.length < 3) {
                document.getElementById('erroEditDescricaoItem').textContent = 'Descricao deve ter pelo menos 3 caracteres';
                temErro = true;
            }

            if (!quantidade || quantidade < 1) {
                document.getElementById('erroEditQuantidadeItem').textContent = 'Quantidade deve ser maior que 0';
                temErro = true;
            }

            if (!valorUnitario || valorUnitario <= 0) {
                document.getElementById('erroEditValorUnitarioItem').textContent = 'Valor deve ser maior que 0';
                temErro = true;
            }

            if (temErro) return false;

            const dados = {
                id_item: idItem,
                id_venda: idVenda,
                descricao: descricao,
                quantidade: quantidade,
                valor_unitario: valorUnitario
            };

            const btn = document.getElementById('btnSubmitEditItem');
            const btnText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            fetch('<?php echo API_URL_RELATIVE; ?>vendas/editar_item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
                    fecharModalEditarItem();
                    location.reload();
                } else {
                    alert('Erro: ' + (data.mensagem || 'Erro ao atualizar item'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                btn.disabled = false;
                btn.innerHTML = btnText;
                alert('Erro ao atualizar item: ' + error.message);
            });

            return false;
        }

        function confirmarRemoverItem(idItem) {
            if (confirm('Tem certeza que deseja remover este item? Esta acao nao pode ser desfeita.')) {
                const btn = event.target;
                btn.disabled = true;

                fetch('<?php echo API_URL_RELATIVE; ?>vendas/remover_item.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_item: idItem })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Erro na resposta');
                    return response.json();
                })
                .then(data => {
                    if (data.sucesso) {
                        location.reload();
                    } else {
                        btn.disabled = false;
                        alert('Erro: ' + (data.mensagem || 'Erro ao remover item'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    btn.disabled = false;
                    alert('Erro ao remover item: ' + error.message);
                });
            }
        }

        // ========== MODAL ADICIONAR PAGAMENTO ==========
        function abrirModalAdicionarPagamento(idVenda) {
            document.getElementById('formAdicionarPagamento').reset();
            document.getElementById('dataPagamento').value = new Date().toISOString().split('T')[0];
            document.getElementById('modalAdicionarPagamento').classList.add('show');
            document.body.style.overflow = 'hidden';
            document.getElementById('valorPagamento').focus();
        }

        function fecharModalAdicionarPagamento() {
            document.getElementById('modalAdicionarPagamento').classList.remove('show');
            document.body.style.overflow = 'auto';
            limparErrosPagamento();
        }

        function limparErrosPagamento() {
            document.getElementById('erroValorPagamento').textContent = '';
            document.getElementById('erroTipoPagamento').textContent = '';
            document.getElementById('erroDataPagamento').textContent = '';
        }

        function enviarAdicionarPagamento(event) {
            event.preventDefault();

            const idVenda = document.getElementById('idVendaPagamento').value;
            const valor = parseFloat(document.getElementById('valorPagamento').value);
            const tipo = document.getElementById('tipoPagamento').value.trim();
            const data = document.getElementById('dataPagamento').value;
            const observacoes = document.getElementById('observacoesPagamento').value.trim();

            limparErrosPagamento();

            let temErro = false;

            if (!valor || valor <= 0) {
                document.getElementById('erroValorPagamento').textContent = 'Valor deve ser maior que 0';
                temErro = true;
            }

            if (!tipo) {
                document.getElementById('erroTipoPagamento').textContent = 'Selecione o tipo de pagamento';
                temErro = true;
            }

            if (!data) {
                document.getElementById('erroDataPagamento').textContent = 'Data eh obrigatoria';
                temErro = true;
            }

            if (temErro) return false;

            const dados = {
                id_venda: idVenda,
                valor: valor,
                tipo_pagamento: tipo,
                data_pagamento: data,
                observacoes: observacoes
            };

            const btn = document.getElementById('btnSubmitPagamento');
            const btnText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

            fetch('<?php echo API_URL_RELATIVE; ?>pagamentos/salvar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
                    fecharModalAdicionarPagamento();
                    location.reload();
                } else {
                    alert('Erro: ' + (data.mensagem || 'Erro ao registrar pagamento'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                btn.disabled = false;
                btn.innerHTML = btnText;
                alert('Erro ao registrar pagamento: ' + error.message);
            });

            return false;
        }

        function confirmarRemoverPagamento(idPagamento) {
            if (confirm('Tem certeza que deseja remover este pagamento?')) {
                fetch('<?php echo API_URL_RELATIVE; ?>pagamentos/remover.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_pagamento: idPagamento })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Erro na resposta');
                    return response.json();
                })
                .then(data => {
                    if (data.sucesso) {
                        location.reload();
                    } else {
                        alert('Erro: ' + (data.mensagem || 'Erro ao remover pagamento'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao remover pagamento: ' + error.message);
                });
            }
        }

        // ========== MODAL EDITAR VENDA ==========
        function abrirModalEditarVenda(idVenda) {
            document.getElementById('editIdVenda').value = idVenda;
            document.getElementById('modalEditarVenda').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function fecharModalEditarVenda() {
            document.getElementById('modalEditarVenda').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function enviarEditarVenda(event) {
            event.preventDefault();

            const idVenda = document.getElementById('editIdVenda').value;
            const observacoes = document.getElementById('editObservacoes').value.trim();
            const status = document.getElementById('editStatusVenda').value;

            const dados = {
                id_venda: idVenda,
                observacoes: observacoes,
                status: status
            };

            const btn = document.getElementById('btnSubmitEditVenda');
            const btnText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            fetch('<?php echo API_URL_RELATIVE; ?>vendas/editar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
                    fecharModalEditarVenda();
                    location.reload();
                } else {
                    alert('Erro: ' + (data.mensagem || 'Erro ao atualizar venda'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                btn.disabled = false;
                btn.innerHTML = btnText;
                alert('Erro ao atualizar venda: ' + error.message);
            });

            return false;
        }

        // ========== EVENTOS DE FECHAMENTO ==========
        document.getElementById('modalAdicionarItem').addEventListener('click', function(e) {
            if (e.target === this) fecharModalAdicionarItem();
        });

        document.getElementById('modalEditarItem').addEventListener('click', function(e) {
            if (e.target === this) fecharModalEditarItem();
        });

        document.getElementById('modalAdicionarPagamento').addEventListener('click', function(e) {
            if (e.target === this) fecharModalAdicionarPagamento();
        });

        document.getElementById('modalEditarVenda').addEventListener('click', function(e) {
            if (e.target === this) fecharModalEditarVenda();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                fecharModalAdicionarItem();
                fecharModalEditarItem();
                fecharModalAdicionarPagamento();
                fecharModalEditarVenda();
            }
        });
    </script>
</body>

</html>