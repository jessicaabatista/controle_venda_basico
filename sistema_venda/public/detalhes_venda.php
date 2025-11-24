<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../config/auth.php';
require_once '../classes/Database.php';
require_once '../classes/Venda.php';
require_once '../classes/Financeiro.php';

$venda_obj = new Venda();
$financeiro = new Financeiro();

$idVenda = $_GET['id'] ?? 0;
if (!$idVenda) {
    header('Location: vendas.php');
    exit;
}

$venda = $venda_obj->obter($idVenda);
$itens = $venda_obj->obterItens($idVenda);
$parcelas = $financeiro->obterParcelas($idVenda);

if (!$venda) {
    header('Location: vendas.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Venda - Sistema de Semi-Joias</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .venda-header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 30px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .venda-info h1 {
            margin-bottom: 20px;
            color: #333;
        }

        .venda-info p {
            margin-bottom: 10px;
            color: #666;
        }

        .venda-info strong {
            color: #333;
        }

        .venda-status {
            display: flex;
            align-items: center;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .card h2 {
            margin-bottom: 20px;
            font-size: 20px;
            color: #333;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-header h2 {
            margin: 0;
        }

        .cards-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card-summary {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .card-summary h3 {
            margin-bottom: 10px;
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
        }

        .card-summary .valor {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-badge.aberta {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.parcial {
            background: #cce5ff;
            color: #004085;
        }

        .status-badge.paga {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.cancelada {
            background: #f8d7da;
            color: #721c24;
        }

        .status-badge.pendente {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.vencida {
            background: #f8d7da;
            color: #721c24;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        table thead {
            background: #f5f5f5;
        }

        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        table tbody tr:hover {
            background: #f9f9f9;
        }

        .btn-link {
            background: none;
            color: #667eea;
            text-decoration: none;
            padding: 5px 10px;
            cursor: pointer;
            border: 1px solid #667eea;
            border-radius: 4px;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-link:hover {
            background: #667eea;
            color: white;
        }

        @media (max-width: 768px) {
            .venda-header {
                grid-template-columns: 1fr;
            }

            .cards-row {
                grid-template-columns: 1fr;
            }

            .card-header {
                flex-direction: column;
                gap: 15px;
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
            <a href="vendas.php" class="nav-link active">Vendas</a>
            <a href="nova_venda.php" class="nav-link">+ Nova Venda</a>
            <a href="clientes.php" class="nav-link">Clientes</a>
            <div class="nav-user">
                <a href="logout.php" class="nav-logout">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="breadcrumb">
            <a href="vendas.php">Vendas</a> > Venda #<?php echo $idVenda; ?>
        </div>

        <div class="venda-header">
            <div class="venda-info">
                <h1>Venda #<?php echo $idVenda; ?></h1>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($venda['nome_cliente']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($venda['email']); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($venda['telefone']); ?></p>
                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></p>
            </div>

            <div class="venda-status">
                <div class="status-badge <?php echo $venda['status_geral']; ?>">
                    <?php
                    $statusMap = [
                        'aberta' => 'Aberta',
                        'parcial' => 'Parcial',
                        'paga' => 'Paga',
                        'cancelada' => 'Cancelada'
                    ];
                    echo $statusMap[$venda['status_geral']];
                    ?>
                </div>
            </div>
        </div>

        <!-- Itens da Venda -->
        <div class="card">
            <h2>Itens da Venda</h2>
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Quantidade</th>
                        <th>Valor Unitário</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['codigo_produto']); ?></td>
                        <td><?php echo htmlspecialchars($item['descricao']); ?></td>
                        <td><?php echo $item['quantidade']; ?></td>
                        <td>R$ <?php echo number_format($item['valor_unitario'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="status-badge <?php echo $item['status_pagamento']; ?>">
                                <?php
                                $statusMap = [
                                    'pendente' => 'Pendente',
                                    'parcial' => 'Parcial',
                                    'pago' => 'Pago'
                                ];
                                echo $statusMap[$item['status_pagamento']];
                                ?>
                            </span>
                        </td>
                        <td>R$ <?php echo number_format($item['saldo_item'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Resumo Financeiro -->
        <div class="cards-row">
            <div class="card-summary">
                <h3>Valor Total</h3>
                <p class="valor">R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></p>
            </div>
            <div class="card-summary">
                <h3>Valor Pago</h3>
                <p class="valor">R$ <?php echo number_format($venda['valor_pago'], 2, ',', '.'); ?></p>
            </div>
            <div class="card-summary">
                <h3>Saldo Devedor</h3>
                <p class="valor"><?php echo $venda['saldo_devedor'] > 0 ? 'R$ ' . number_format($venda['saldo_devedor'], 2, ',', '.') : 'PAGO'; ?></p>
            </div>
        </div>

        <!-- Parcelas -->
        <div class="card">
            <div class="card-header">
                <h2>Parcelas</h2>
                <button onclick="abrirModalPagamento(<?php echo $idVenda; ?>)" class="btn btn-primary">+ Registrar Pagamento</button>
            </div>

            <?php if (empty($parcelas)): ?>
                <p style="text-align: center; color: #999; padding: 40px;">Nenhuma parcela gerada ainda</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Parcela</th>
                            <th>Valor Previsto</th>
                            <th>Valor Pago</th>
                            <th>Saldo</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parcelas as $parcela): ?>
                        <tr>
                            <td>#<?php echo $parcela['numero_parcela']; ?></td>
                            <td>R$ <?php echo number_format($parcela['valor_previsto'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($parcela['valor_efetivo'] ?? 0, 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($parcela['saldo_parcela'], 2, ',', '.'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($parcela['data_vencimento'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $parcela['status']; ?>">
                                    <?php
                                    $statusMap = [
                                        'aberta' => 'Aberta',
                                        'paga' => 'Paga',
                                        'vencida' => 'Vencida',
                                        'cancelada' => 'Cancelada'
                                    ];
                                    echo $statusMap[$parcela['status']];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($parcela['status'] === 'aberta' || $parcela['status'] === 'vencida'): ?>
                                    <button onclick="abrirModalPagamentoParcela(<?php echo $idVenda; ?>, <?php echo $parcela['id_parcela']; ?>)" class="btn-link">Pagar</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Observações -->
        <?php if ($venda['observacoes_pagamento']): ?>
        <div class="card">
            <h3>Observações de Pagamento</h3>
            <p><?php echo nl2br(htmlspecialchars($venda['observacoes_pagamento'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="card-actions">
            <a href="vendas.php" class="btn btn-secondary">Voltar</a>
            <a href="nova_venda.php?id=<?php echo $idVenda; ?>" class="btn btn-primary">Editar Venda</a>
        </div>
    </div>

    <?php include 'modals/pagamento.html'; ?>

    <script src="assets/js/app.js"></script>
    <script src="assets/js/financeiro.js"></script>
</body>
</html>
