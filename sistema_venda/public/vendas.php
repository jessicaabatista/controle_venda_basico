<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../config/config.php';
require_once '../config/auth.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Venda.php';

$venda_obj = new Venda();

$filtros = [
    'status' => isset($_GET['status']) ? htmlspecialchars($_GET['status'], ENT_QUOTES, 'UTF-8') : '',
    'cliente' => isset($_GET['cliente']) ? htmlspecialchars($_GET['cliente'], ENT_QUOTES, 'UTF-8') : '',
    'data_inicio' => isset($_GET['data_inicio']) ? htmlspecialchars($_GET['data_inicio'], ENT_QUOTES, 'UTF-8') : '',
    'data_fim' => isset($_GET['data_fim']) ? htmlspecialchars($_GET['data_fim'], ENT_QUOTES, 'UTF-8') : ''
];

$vendas = $venda_obj->listar($filtros);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestao de Vendas - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/style.css'); ?>">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="list-header">
            <h1><i class="fas fa-shopping-cart"></i> Gestao de Vendas</h1>
            <a href="<?php echo PUBLIC_URL_RELATIVE; ?>nova_venda.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Venda
            </a>
        </div>

        <!-- Filtros -->
        <div class="filtros-section">
            <form method="GET" id="formFiltros">
                <div class="filtros-grid">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" onchange="document.getElementById('formFiltros').submit()">
                            <option value="">Todos</option>
                            <option value="<?php echo STATUS_VENDA_ABERTA; ?>" <?php echo $filtros['status'] === STATUS_VENDA_ABERTA ? 'selected' : ''; ?>>Aberta</option>
                            <option value="<?php echo STATUS_VENDA_PARCIAL; ?>" <?php echo $filtros['status'] === STATUS_VENDA_PARCIAL ? 'selected' : ''; ?>>Parcial</option>
                            <option value="<?php echo STATUS_VENDA_PAGA; ?>" <?php echo $filtros['status'] === STATUS_VENDA_PAGA ? 'selected' : ''; ?>>Paga</option>
                            <option value="<?php echo STATUS_VENDA_CANCELADA; ?>" <?php echo $filtros['status'] === STATUS_VENDA_CANCELADA ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Cliente</label>
                        <input type="text" name="cliente" placeholder="Buscar cliente..." value="<?php echo htmlspecialchars($filtros['cliente'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Data Inicio</label>
                        <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filtros['data_inicio'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Data Fim</label>
                        <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filtros['data_fim'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="filtros-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                    <a href="<?php echo PUBLIC_URL_RELATIVE; ?>vendas.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Limpar Filtros</a>
                </div>
            </form>
        </div>

        <!-- Tabela de Vendas -->
        <?php if (empty($vendas)): ?>
            <div class="card" style="text-align: center;">
                <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                <h3 style="color: #999;">Nenhuma venda encontrada</h3>
                <p style="color: #999; margin-bottom: 20px;">Comece criando sua primeira venda</p>
                <a href="<?php echo PUBLIC_URL_RELATIVE; ?>nova_venda.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Criar Primeira Venda
                </a>
            </div>
        <?php else: ?>
            <div class="card">
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
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendas as $venda): ?>
                            <tr>
                                <td>#<?php echo intval($venda['id_venda']); ?></td>
                                <td><?php echo htmlspecialchars($venda['nome_cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date(DATE_FORMAT, strtotime($venda['data_venda'])); ?></td>
                                <td><?php echo intval($venda['qtd_itens'] ?? 0); ?></td>
                                <td><?php echo formatarMoeda($venda['valor_total']); ?></td>
                                <td><?php echo formatarMoeda($venda['valor_pago']); ?></td>
                                <td><?php echo formatarMoeda($venda['saldo_devedor']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo htmlspecialchars($venda['status_geral'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php
                                        $statusMap = [
                                            STATUS_VENDA_ABERTA => 'Aberta',
                                            STATUS_VENDA_PARCIAL => 'Parcial',
                                            STATUS_VENDA_PAGA => 'Paga',
                                            STATUS_VENDA_CANCELADA => 'Cancelada'
                                        ];
                                        echo $statusMap[$venda['status_geral']] ?? htmlspecialchars($venda['status_geral'], ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </span>
                                </td>
                                <td style="display: flex; gap: 5px;">
                                    <a href="<?php echo PUBLIC_URL_RELATIVE; ?>detalhes_venda.php?id=<?php echo intval($venda['id_venda']); ?>" class="btn-link"><i class="fas fa-eye"></i></a>
                                    <a href="<?php echo PUBLIC_URL_RELATIVE; ?>nova_venda.php?id=<?php echo intval($venda['id_venda']); ?>" class="btn-link"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px; text-align: right;">
                    <p style="margin: 0;">Total de vendas: <strong><?php echo count($vendas); ?></strong></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="<?php echo getAssetUrl('js/app.js'); ?>"></script>
    
    <script>
        function formatarMoeda(valor) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor);
        }
    </script>
</body>

</html>
