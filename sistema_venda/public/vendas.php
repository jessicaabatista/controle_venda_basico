<?php
require_once '../config/auth.php';
require_once '../classes/Database.php';
require_once '../classes/Venda.php';

$venda_obj = new Venda();

// Aplicar filtros
$filtros = [
    'status' => $_GET['status'] ?? '',
    'cliente' => $_GET['cliente'] ?? '',
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_fim' => $_GET['data_fim'] ?? ''
];

$vendas = $venda_obj->listar($filtros);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas - Sistema de Semi-Joias</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .filtros-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filtros-grid .form-group {
            margin-bottom: 0;
        }

        .filtros-actions {
            display: flex;
            gap: 10px;
        }

        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .list-header h1 {
            font-size: 28px;
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.aberta {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-badge.parcial {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-badge.paga {
            background: #e8f5e9;
            color: #388e3c;
        }

        .status-badge.cancelada {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .tabela-vendas {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        table td {
            font-size: 14px;
        }

        .sem-resultados {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .sem-resultados p {
            font-size: 16px;
            margin-bottom: 20px;
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
        <div class="list-header">
            <h1>Gestão de Vendas</h1>
            <a href="nova_venda.php" class="btn btn-primary">+ Nova Venda</a>
        </div>

        <!-- Filtros -->
        <div class="filtros-section">
            <form method="GET" id="formFiltros">
                <div class="filtros-grid">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" onchange="document.getElementById('formFiltros').submit()">
                            <option value="">Todos</option>
                            <option value="aberta" <?php echo $filtros['status'] === 'aberta' ? 'selected' : ''; ?>>Aberta</option>
                            <option value="parcial" <?php echo $filtros['status'] === 'parcial' ? 'selected' : ''; ?>>Parcial</option>
                            <option value="paga" <?php echo $filtros['status'] === 'paga' ? 'selected' : ''; ?>>Paga</option>
                            <option value="cancelada" <?php echo $filtros['status'] === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Cliente</label>
                        <input type="text" name="cliente" placeholder="Buscar cliente..." value="<?php echo htmlspecialchars($filtros['cliente']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Data Início</label>
                        <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filtros['data_inicio']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Data Fim</label>
                        <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filtros['data_fim']); ?>">
                    </div>
                </div>

                <div class="filtros-actions">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="vendas.php" class="btn btn-secondary">Limpar Filtros</a>
                </div>
            </form>
        </div>

        <!-- Tabela de Vendas -->
        <?php if (empty($vendas)): ?>
            <div class="sem-resultados">
                <p>?? Nenhuma venda encontrada</p>
                <a href="nova_venda.php" class="btn btn-primary">Criar primeira venda</a>
            </div>
        <?php else: ?>
            <div class="tabela-vendas">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Itens</th>
                            <th>Valor Total</th>
                            <th>Valor Pago</th>
                            <th>Saldo</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendas as $venda): ?>
                        <tr>
                            <td>#<?php echo $venda['id_venda']; ?></td>
                            <td><?php echo htmlspecialchars($venda['nome_cliente']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($venda['data_venda'])); ?></td>
                            <td><?php echo $venda['qtd_itens'] ?? 0; ?></td>
                            <td>R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($venda['valor_pago'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($venda['saldo_devedor'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="status-badge <?php echo $venda['status_geral']; ?>">
                                    <?php
                                    $statusMap = [
                                        'aberta' => 'Aberta',
                                        'parcial' => 'Parcial',
                                        'paga' => 'Paga',
                                        'cancelada' => 'Cancelada'
                                    ];
                                    echo $statusMap[$venda['status_geral']];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <a href="detalhes_venda.php?id=<?php echo $venda['id_venda']; ?>" class="btn-link">Ver</a>
                                <a href="editar_venda.php?id=<?php echo $venda['id_venda']; ?>" class="btn-link">Editar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px; text-align: right;">
                <p>Total de vendas: <strong><?php echo count($vendas); ?></strong></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>