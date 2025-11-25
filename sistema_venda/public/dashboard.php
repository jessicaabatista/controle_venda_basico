<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../config/config.php';
require_once '../config/auth.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Usuario.php';
require_once CLASSES_PATH . '/Financeiro.php';
require_once CLASSES_PATH . '/Venda.php';
require_once CLASSES_PATH . '/Cliente.php';

$usuario = new Usuario();
$financeiro = new Financeiro();
$venda = new Venda();
$cliente = new Cliente();

$usuarioAtual = $usuario->obter($_SESSION['id_usuario']);
$dashboardData = $financeiro->obterDashboard();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/style.css'); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="main-content">
            <div class="container">
                <div class="dashboard-header">
                    <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
                    <p>Bem-vindo, <?php echo htmlspecialchars($usuarioAtual['nome']); ?>!</p>
                    <p style="font-size: 13px; color: #999;">
                        <?php echo date('d/m/Y H:i'); ?> | 
                        <?php
                        $hoje = new DateTime();
                        $ultimoDia = new DateTime($hoje->format('Y-m-t'));
                        $meses = [
                            '01' => 'Janeiro',
                            '02' => 'Fevereiro',
                            '03' => 'Marco',
                            '04' => 'Abril',
                            '05' => 'Maio',
                            '06' => 'Junho',
                            '07' => 'Julho',
                            '08' => 'Agosto',
                            '09' => 'Setembro',
                            '10' => 'Outubro',
                            '11' => 'Novembro',
                            '12' => 'Dezembro'
                        ];
                        $mesAtual = $meses[$hoje->format('m')];
                        echo $mesAtual . ' - ' . $hoje->format('d') . ' a ' . $ultimoDia->format('d') . ' de ' . $hoje->format('Y');
                        ?>
                    </p>
                </div>

                <!-- KPI Cards -->
                <div class="kpi-container">
                    <div class="kpi-card">
                        <div class="kpi-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="kpi-content">
                            <h3>Total de Vendas (Mes)</h3>
                            <p class="kpi-value" id="totalVendas">R$ 0,00</p>
                            <p class="kpi-label" id="qtdVendas">0 vendas</p>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="kpi-content">
                            <h3>Parcelas Vencidas</h3>
                            <p class="kpi-value" id="parcVencidas">R$ 0,00</p>
                            <p class="kpi-label" id="qtdVencidas">0 parcelas</p>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="kpi-content">
                            <h3>Proximas Cobrancas (30 dias)</h3>
                            <p class="kpi-value" id="proximasCobr">R$ 0,00</p>
                            <p class="kpi-label" id="qtdProximas">0 parcelas</p>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="kpi-content">
                            <h3>Saldo a Receber</h3>
                            <p class="kpi-value" id="saldoReceber">R$ 0,00</p>
                            <p class="kpi-label">Total em aberto</p>
                        </div>
                    </div>
                </div>

                <!-- Metricas Adicionais -->
                <div class="metricas-adicionais">
                    <div class="metrica-small">
                        <div class="valor" id="ticketMedio">R$ 0,00</div>
                        <div class="label">Ticket Medio</div>
                    </div>
                    <div class="metrica-small">
                        <div class="valor" id="taxaConversao">0%</div>
                        <div class="label">Taxa de Recebimento</div>
                    </div>
                    <div class="metrica-small">
                        <div class="valor" id="clientesAtivos">0</div>
                        <div class="label">Clientes Ativos</div>
                    </div>
                    <div class="metrica-small">
                        <div class="valor" id="parcelasHoje">0</div>
                        <div class="label">Vencem Hoje</div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="charts-container">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-bar"></i> Vendas por Dia (Mes Atual)</h3>
                        <div class="chart-container">
                            <canvas id="vendasPorDiaChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h3><i class="fas fa-chart-pie"></i> Status das Vendas</h3>
                        <div class="chart-container">
                            <canvas id="statusVendasChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="charts-container">
                    <div class="chart-card">
                        <h3><i class="fas fa-trophy"></i> Top 10 Clientes</h3>
                        <div class="chart-container">
                            <canvas id="topClientesChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h3><i class="fas fa-credit-card"></i> Formas de Pagamento</h3>
                        <div class="chart-container">
                            <canvas id="formasPagamentoChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 3 -->
                <div class="charts-container">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-line"></i> Evolucao Mensal</h3>
                        <div class="chart-container">
                            <canvas id="evolucaoMensalChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h3><i class="fas fa-box"></i> Produtos Mais Vendidos</h3>
                        <div class="chart-container">
                            <canvas id="produtosMaisVendidosChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Proximas Cobrancas -->
                <div class="card">
                    <h2><i class="fas fa-bell"></i> Proximas Cobrancas (30 dias)</h2>
                    <div id="proximasCobrancasLista"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo getAssetUrl('js/app.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('js/dashboard.js'); ?>"></script>
    <script>
        const dashboardData = <?php echo json_encode($dashboardData); ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof carregarDashboard === 'function') {
                carregarDashboard();
            }
        });
    </script>
</body>

</html>
