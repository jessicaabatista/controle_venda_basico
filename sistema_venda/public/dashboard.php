<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../config/auth.php';
require_once '../classes/Database.php';
require_once '../classes/Usuario.php';
require_once '../classes/Financeiro.php';
require_once '../classes/Venda.php';
require_once '../classes/Cliente.php';

$usuario = new Usuario();
$financeiro = new Financeiro();
$venda = new Venda();
$cliente = new Cliente();

$usuarioAtual = $usuario->obter($_SESSION['id_usuario']);

// Obter dados do dashboard
$dashboardData = $financeiro->obterDashboard();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Semi-Joias</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            background: #f5f7fa;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }

        .dashboard-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .dashboard-header p {
            color: #666;
            font-size: 16px;
        }

        .kpi-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .kpi-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .kpi-icon {
            font-size: 40px;
            margin-right: 20px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .kpi-card:nth-child(1) .kpi-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .kpi-card:nth-child(2) .kpi-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .kpi-card:nth-child(3) .kpi-icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .kpi-card:nth-child(4) .kpi-icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .kpi-content h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .kpi-value {
            color: #333;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .kpi-label {
            color: #999;
            font-size: 12px;
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .chart-card h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .cobrancas-lista {
            max-height: 400px;
            overflow-y: auto;
        }

        .cobranca-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s ease;
        }

        .cobranca-item:hover {
            background: #f8f9fa;
        }

        .cobranca-item.vencida {
            background: #fff5f5;
            border-left: 4px solid #f56565;
        }

        .cobranca-item.hoje {
            background: #fffbf0;
            border-left: 4px solid #ed8936;
        }

        .cobranca-info {
            flex: 1;
        }

        .cobranca-cliente {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .cobranca-data {
            color: #666;
            font-size: 14px;
        }

        .cobranca-valor {
            font-weight: bold;
            color: #333;
            font-size: 16px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 10px;
        }

        .status-badge.vencida {
            background: #fed7d7;
            color: #c53030;
        }

        .status-badge.hoje {
            background: #feebc8;
            color: #c05621;
        }

        .status-badge.futura {
            background: #e6fffa;
            color: #047481;
        }

        .metricas-adicionais {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .metrica-small {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            text-align: center;
        }

        .metrica-small .valor {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .metrica-small .label {
            font-size: 12px;
            color: #666;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
            color: #666;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }

            .kpi-container {
                grid-template-columns: 1fr;
            }

            .charts-container {
                grid-template-columns: 1fr;
            }

            .metricas-adicionais {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="navbar-brand">
                <h2>Semi-Joias</h2>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="vendas.php" class="nav-link">Vendas</a>
                <a href="nova_venda.php" class="nav-link">+ Nova Venda</a>
                <a href="clientes.php" class="nav-link">Clientes</a>
                <a href="relatorios.php" class="nav-link">Relatórios</a>
                <div class="nav-user">
                    <span><?php echo htmlspecialchars($usuarioAtual['nome']); ?></span>
                    <a href="logout.php" class="nav-logout">Sair</a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
                <p>Bem-vindo(a), <?php echo htmlspecialchars($usuarioAtual['nome']); ?>! <i class="fas fa-smile"></i></p>
                <p style="font-size: 14px; color: #999; margin-top: 5px;">
                    <?php echo date('d/m/Y H:i'); ?> | 
                    <?php 
                    $hoje = new DateTime();
                    $ultimoDia = new DateTime($hoje->format('Y-m-t'));
                    echo $hoje->format('F') . ' - ' . $hoje->format('d') . ' a ' . $ultimoDia->format('d') . ' de ' . $hoje->format('Y');
                    ?>
                </p>
            </div>

            <!-- KPI Cards Principais -->
            <div class="kpi-container">
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="kpi-content">
                        <h3>Total de Vendas (Mês)</h3>
                        <p class="kpi-value" id="totalVendas">R$ 0,00</p>
                        <p class="kpi-label" id="qtdVendas">0 vendas</p>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="kpi-content">
                        <h3>Parcelas Vencidas</h3>
                        <p class="kpi-value" id="parcVencidas">R$ 0,00</p>
                        <p class="kpi-label" id="qtdVencidas">0 parcelas</p>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="kpi-content">
                        <h3>Próximas Cobranças (30 dias)</h3>
                        <p class="kpi-value" id="proximasCobr">R$ 0,00</p>
                        <p class="kpi-label" id="qtdProximas">0 parcelas</p>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="kpi-content">
                        <h3>Saldo a Receber</h3>
                        <p class="kpi-value" id="saldoReceber">R$ 0,00</p>
                        <p class="kpi-label">Total em aberto</p>
                    </div>
                </div>
            </div>

            <!-- Métricas Adicionais -->
            <div class="metricas-adicionais">
                <div class="metrica-small">
                    <div class="valor" id="ticketMedio">R$ 0,00</div>
                    <div class="label">Ticket Médio</div>
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
                    <h3><i class="fas fa-chart-bar"></i> Vendas por Dia (Mês Atual)</h3>
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
                    <h3><i class="fas fa-chart-line"></i> Evolução Mensal</h3>
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

            <!-- Próximas Cobranças -->
            <div class="chart-card">
                <h3><i class="fas fa-bell"></i> Próximas Cobranças (30 dias)</h3>
                <div id="proximasCobrancasLista" class="cobrancas-lista">
                    <div class="loading">
                        <p>Carregando cobranças...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script>
        // Dados iniciais do PHP
        const dashboardData = <?php echo json_encode($dashboardData); ?>;
        
        // Inicializar dashboard com dados do PHP
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof carregarDashboard === 'function') {
                carregarDashboard();
            }
        });
    </script>
</body>
</html>
