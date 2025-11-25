<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../config/config.php';
require_once '../config/auth.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Relatorio.php';

$relatorio = new Relatorio();

// Parametros padrao
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
$dataFim = $_GET['data_fim'] ?? date('Y-m-d');
$tipoRelatorio = $_GET['tipo'] ?? 'fluxo_caixa';

$dados = [];

if ($tipoRelatorio === 'fluxo_caixa') {
    $dados = $relatorio->fluxoCaixa($dataInicio, $dataFim);
} elseif ($tipoRelatorio === 'desempenho_vendas') {
    $dados = $relatorio->desempenhoVendas($dataInicio, $dataFim);
} elseif ($tipoRelatorio === 'pendencias') {
    $dados = $relatorio->pendencias();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatorios - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/style.css'); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        .relatorios-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .filtros-relatorio {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .tab-button {
            padding: 10px 20px;
            background: #f0f0f0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            color: #666;
        }

        .tab-button.active {
            background: #667eea;
            color: white;
        }

        .tab-button:hover {
            background: #667eea;
            color: white;
        }

        .relatorio-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .relatorio-card h2 {
            margin-bottom: 20px;
            font-size: 20px;
            color: #333;
        }

        .relatorio-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .relatorio-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-box h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .stat-box .valor {
            font-size: 28px;
            font-weight: bold;
        }

        .tabela-relatorio {
            width: 100%;
            margin-top: 20px;
        }

        .tabela-relatorio thead {
            background: #f5f5f5;
        }

        .tabela-relatorio th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #ddd;
        }

        .tabela-relatorio td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .tabela-relatorio tbody tr:hover {
            background: #f9f9f9;
        }

        .grafico-container {
            position: relative;
            height: 400px;
            margin-top: 30px;
        }

        .btn-exportar {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-exportar:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .tab-buttons {
                flex-direction: column;
            }

            .tab-button {
                width: 100%;
            }

            .filtros-grid {
                grid-template-columns: 1fr;
            }

            .relatorio-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1 style="margin-bottom: 30px;">Relatorios</h1>

        <!-- Filtros -->
        <div class="filtros-relatorio">
            <div class="tab-buttons">
                <button class="tab-button <?php echo $tipoRelatorio === 'fluxo_caixa' ? 'active' : ''; ?>"
                    onclick="mudarRelatorio('fluxo_caixa')">
                    <i class="fas fa-chart-line"></i> Fluxo de Caixa
                </button>
                <button class="tab-button <?php echo $tipoRelatorio === 'desempenho_vendas' ? 'active' : ''; ?>"
                    onclick="mudarRelatorio('desempenho_vendas')">
                    <i class="fas fa-chart-bar"></i> Desempenho de Vendas
                </button>
                <button class="tab-button <?php echo $tipoRelatorio === 'pendencias' ? 'active' : ''; ?>"
                    onclick="mudarRelatorio('pendencias')">
                    <i class="fas fa-exclamation-triangle"></i> Pendencias
                </button>
            </div>

            <form method="GET" id="formFiltros" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <input type="hidden" name="tipo" id="tipoRelatorio" value="<?php echo htmlspecialchars($tipoRelatorio); ?>">

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Data Inicio</label>
                    <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($dataInicio); ?>">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Data Fim</label>
                    <input type="date" name="data_fim" value="<?php echo htmlspecialchars($dataFim); ?>">
                </div>

                <div style="display: flex; gap: 10px; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Gerar Relatorio
                    </button>
                    <a href="relatorios.php" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Relatorio Fluxo de Caixa -->
        <?php if ($tipoRelatorio === 'fluxo_caixa'): ?>
            <div class="relatorio-card">
                <h2>Fluxo de Caixa (<?php echo date('d/m/Y', strtotime($dataInicio)); ?> a <?php echo date('d/m/Y', strtotime($dataFim)); ?>)</h2>

                <div class="relatorio-actions">
                    <button onclick="exportarCSV('fluxo_caixa', '<?php echo htmlspecialchars($dataInicio); ?>', '<?php echo htmlspecialchars($dataFim); ?>')" class="btn-exportar">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </button>
                    <button onclick="exportarPDF('fluxo_caixa', '<?php echo htmlspecialchars($dataInicio); ?>', '<?php echo htmlspecialchars($dataFim); ?>')" class="btn-exportar">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </button>
                    <button onclick="imprimirRelatorio()" class="btn-exportar" style="background: #0069d9;">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>

                <?php if (!empty($dados)): ?>
                    <?php
                    $totalPago = array_sum(array_column($dados, 'valor_pago'));
                    $totalMovimentacoes = array_sum(array_column($dados, 'qtd_movimentacoes'));
                    $mediaDiaria = count($dados) > 0 ? $totalPago / count($dados) : 0;
                    ?>

                    <div class="relatorio-stats">
                        <div class="stat-box">
                            <h4>Total Movimentado</h4>
                            <p class="valor">R$ <?php echo number_format($totalPago, 2, ',', '.'); ?></p>
                        </div>
                        <div class="stat-box">
                            <h4>Media Diaria</h4>
                            <p class="valor">R$ <?php echo number_format($mediaDiaria, 2, ',', '.'); ?></p>
                        </div>
                        <div class="stat-box">
                            <h4>Total de Movimentacoes</h4>
                            <p class="valor"><?php echo $totalMovimentacoes; ?></p>
                        </div>
                        <div class="stat-box">
                            <h4>Dias com Movimento</h4>
                            <p class="valor"><?php echo count($dados); ?></p>
                        </div>
                    </div>

                    <div class="grafico-container">
                        <canvas id="graficoFluxo"></canvas>
                    </div>

                    <table class="tabela-relatorio">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Movimentacoes</th>
                                <th>Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dados as $linha): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($linha['data'])); ?></td>
                                    <td><?php echo intval($linha['qtd_movimentacoes']); ?></td>
                                    <td>R$ <?php echo number_format(floatval($linha['valor_pago']), 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <script>
                        const ctxFluxo = document.getElementById('graficoFluxo').getContext('2d');
                        const fluxoDados = <?php echo json_encode($dados); ?>;

                        new Chart(ctxFluxo, {
                            type: 'bar',
                            data: {
                                labels: fluxoDados.map(d => new Date(d.data).toLocaleDateString('pt-BR')),
                                datasets: [{
                                    label: 'Valor Movimentado (R$)',
                                    data: fluxoDados.map(d => parseFloat(d.valor_pago)),
                                    backgroundColor: '#667eea',
                                    borderColor: '#764ba2',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'R$ ' + value.toFixed(2);
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 40px;">Nenhum dado encontrado para o periodo selecionado</p>
                <?php endif; ?>
            </div>

        <!-- Relatorio Desempenho de Vendas -->
        <?php elseif ($tipoRelatorio === 'desempenho_vendas'): ?>
            <div class="relatorio-card">
                <h2>Desempenho de Vendas (<?php echo date('d/m/Y', strtotime($dataInicio)); ?> a <?php echo date('d/m/Y', strtotime($dataFim)); ?>)</h2>

                <div class="relatorio-actions">
                    <button onclick="exportarCSV('desempenho_vendas', '<?php echo htmlspecialchars($dataInicio); ?>', '<?php echo htmlspecialchars($dataFim); ?>')" class="btn-exportar">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </button>
                    <button onclick="exportarPDF('desempenho_vendas', '<?php echo htmlspecialchars($dataInicio); ?>', '<?php echo htmlspecialchars($dataFim); ?>')" class="btn-exportar">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </button>
                    <button onclick="imprimirRelatorio()" class="btn-exportar" style="background: #0069d9;">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>

                <?php if (!empty($dados)): ?>
                    <?php
                    $totalVendas = array_sum(array_column($dados, 'valor_total'));
                    $totalPago = array_sum(array_column($dados, 'valor_pago'));
                    $totalPendente = $totalVendas - $totalPago;
                    ?>

                    <div class="relatorio-stats">
                        <div class="stat-box">
                            <h4>Total em Vendas</h4>
                            <p class="valor">R$ <?php echo number_format($totalVendas, 2, ',', '.'); ?></p>
                        </div>
                        <div class="stat-box">
                            <h4>Total Recebido</h4>
                            <p class="valor">R$ <?php echo number_format($totalPago, 2, ',', '.'); ?></p>
                        </div>
                        <div class="stat-box">
                            <h4>Total Pendente</h4>
                            <p class="valor">R$ <?php echo number_format($totalPendente, 2, ',', '.'); ?></p>
                        </div>
                        <div class="stat-box">
                            <h4>Indice de Recebimento</h4>
                            <p class="valor"><?php echo $totalVendas > 0 ? round(($totalPago / $totalVendas) * 100, 1) : 0; ?>%</p>
                        </div>
                    </div>

                    <div class="grafico-container">
                        <canvas id="graficoDesempenho"></canvas>
                    </div>

                    <table class="tabela-relatorio">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Vendas</th>
                                <th>Valor Total</th>
                                <th>Valor Pago</th>
                                <th>Pendente</th>
                                <th>Taxa de Recebimento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dados as $linha): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($linha['nome']); ?></td>
                                    <td><?php echo intval($linha['total_vendas']); ?></td>
                                    <td>R$ <?php echo number_format(floatval($linha['valor_total']), 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format(floatval($linha['valor_pago']), 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format(floatval($linha['valor_total']) - floatval($linha['valor_pago']), 2, ',', '.'); ?></td>
                                    <td><?php echo floatval($linha['valor_total']) > 0 ? round((floatval($linha['valor_pago']) / floatval($linha['valor_total'])) * 100, 1) : 0; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <script>
                        const ctxDesempenho = document.getElementById('graficoDesempenho').getContext('2d');
                        const desempenhoDados = <?php echo json_encode($dados); ?>;

                        new Chart(ctxDesempenho, {
                            type: 'bar',
                            data: {
                                labels: desempenhoDados.map(d => d.nome),
                                datasets: [{
                                        label: 'Total de Vendas',
                                        data: desempenhoDados.map(d => parseFloat(d.valor_total)),
                                        backgroundColor: '#667eea',
                                        borderColor: '#764ba2',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Total Pago',
                                        data: desempenhoDados.map(d => parseFloat(d.valor_pago)),
                                        backgroundColor: '#6bcf7f',
                                        borderColor: '#4db85b',
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'R$ ' + value.toFixed(2);
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 40px;">Nenhum dado encontrado para o periodo selecionado</p>
                <?php endif; ?>
            </div>

        <!-- Relatorio Pendencias -->
        <?php elseif ($tipoRelatorio === 'pendencias'): ?>
            <div class="relatorio-card">
                <h2>Pendencias de Recebimento</h2>

                <div class="relatorio-actions">
                    <button onclick="exportarCSV('pendencias')" class="btn-exportar">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </button>
                    <button onclick="exportarPDF('pendencias')" class="btn-exportar">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </button>
                    <button onclick="imprimirRelatorio()" class="btn-exportar" style="background: #0069d9;">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>

                <?php if (!empty($dados)): ?>
                    <?php $totalPendente = array_sum(array_column($dados, 'saldo_total')); ?>

                    <div class="relatorio-stats">
                        <div class="stat-box">
                            <h4>Total em Pendencia</h4>
                            <p class="valor">R$ <?php echo number_format($totalPendente, 2, ',', '.'); ?></p>
                        </div>
                        <div class="stat-box">
                            <h4>Total de Vendas Pendentes</h4>
                            <p class="valor"><?php echo count($dados); ?></p>
                        </div>
                        <div class="stat-box">
                            <h4>Media por Venda</h4>
                            <p class="valor">R$ <?php echo count($dados) > 0 ? number_format($totalPendente / count($dados), 2, ',', '.') : '0,00'; ?></p>
                        </div>
                    </div>

                    <table class="tabela-relatorio">
                        <thead>
                            <tr>
                                <th>ID Venda</th>
                                <th>Cliente</th>
                                <th>Data da Venda</th>
                                <th>Saldo Pendente</th>
                                <th>Acao</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dados as $linha): ?>
                                <tr>
                                    <td>#<?php echo intval($linha['id_venda']); ?></td>
                                    <td><?php echo htmlspecialchars($linha['nome']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($linha['data_venda'])); ?></td>
                                    <td>R$ <?php echo number_format(floatval($linha['saldo_total']), 2, ',', '.'); ?></td>
                                    <td>
                                        <a href="detalhes_venda.php?id=<?php echo intval($linha['id_venda']); ?>" class="btn-link">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 40px;">
                        <i class="fas fa-check-circle"></i> Nenhuma pendencia encontrada
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="<?php echo getAssetUrl('js/app.js'); ?>"></script>
    <script>
        function mudarRelatorio(tipo) {
            document.getElementById('tipoRelatorio').value = tipo;
            document.getElementById('formFiltros').submit();
        }

        function exportarCSV(tipo, dataInicio = '', dataFim = '') {
            let url = '<?php echo API_URL_RELATIVE; ?>relatorios/exportar.php?tipo=' + tipo + '&formato=csv';
            if (dataInicio) url += '&data_inicio=' + encodeURIComponent(dataInicio);
            if (dataFim) url += '&data_fim=' + encodeURIComponent(dataFim);
            window.location.href = url;
        }

        function exportarPDF(tipo, dataInicio = '', dataFim = '') {
            let url = '<?php echo API_URL_RELATIVE; ?>relatorios/exportar.php?tipo=' + tipo + '&formato=pdf';
            if (dataInicio) url += '&data_inicio=' + encodeURIComponent(dataInicio);
            if (dataFim) url += '&data_fim=' + encodeURIComponent(dataFim);
            window.location.href = url;
        }

        function imprimirRelatorio() {
            window.print();
        }
    </script>

    <style media="print">
        .navbar,
        .relatorio-actions,
        .filtros-relatorio {
            display: none !important;
        }

        body {
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 100%;
            padding: 0;
        }
    </style>
</body>

</html>