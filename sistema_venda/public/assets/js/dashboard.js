let chartVendas, chartStatus, chartClientes;

document.addEventListener('DOMContentLoaded', function() {
    carregarDadosDashboard();
});

function carregarDadosDashboard() {
    fetch('api/dashboard/dados.php')
        .then(response => response.json())
        .then(dados => {
            atualizarKPIs(dados);
            renderizarGraficoVendas(dados.vendas_por_dia);
            renderizarGraficoStatus(dados.status_vendas);
            renderizarGraficoClientes(dados.top_clientes);
            renderizarProximasCobrancas(dados.proximas);
        })
        .catch(erro => console.error('Erro ao carregar dados:', erro));
}

function atualizarKPIs(dados) {
    // KPI de Vendas
    const totalVendas = dados.vendas.valor_total || 0;
    const qtdVendas = dados.vendas.total_vendas || 0;
    document.getElementById('totalVendas').textContent = formatarMoeda(totalVendas);
    document.getElementById('qtdVendas').textContent = qtdVendas + ' vendas';

    // KPI de Parcelas Vencidas
    const vencidas = dados.vencidas.valor_vencido || 0;
    const qtdVencidas = dados.vencidas.total_vencidas || 0;
    document.getElementById('parcVencidas').textContent = formatarMoeda(vencidas);
    document.getElementById('qtdVencidas').textContent = qtdVencidas + ' parcelas';

    // KPI de Próximas Cobranças
    const totalProximas = dados.proximas.reduce((sum, item) => sum + parseFloat(item.saldo_parcela), 0);
    document.getElementById('proximasCobr').textContent = formatarMoeda(totalProximas);
    document.getElementById('qtdProximas').textContent = dados.proximas.length + ' parcelas';

    // KPI de Saldo a Receber
    const saldoTotal = totalVendas - dados.proximas.reduce((sum, item) => sum + parseFloat(item.valor_previsto), 0);
    document.getElementById('saldoReceber').textContent = formatarMoeda(Math.max(0, saldoTotal));
}

function renderizarGraficoVendas(dados) {
    const ctx = document.getElementById('vendasPorDiaChart').getContext('2d');
    
    if (chartVendas) {
        chartVendas.destroy();
    }

    const dias = dados.map(item => 'Dia ' + item.dia);
    const valores = dados.map(item => item.valor);

    chartVendas = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dias,
            datasets: [{
                label: 'Valor de Vendas (R$)',
                data: valores,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
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
}

function renderizarGraficoStatus(dados) {
    const ctx = document.getElementById('statusVendasChart').getContext('2d');
    
    if (chartStatus) {
        chartStatus.destroy();
    }

    const labels = dados.map(item => {
        const statusMap = {
            'aberta': 'Aberta',
            'parcial': 'Parcial',
            'paga': 'Paga',
            'cancelada': 'Cancelada'
        };
        return statusMap[item.status_geral] || item.status_geral;
    });
    const valores = dados.map(item => item.total);
    const cores = ['#ff6b6b', '#ffd93d', '#6bcf7f', '#999'];

    chartStatus = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: valores,
                backgroundColor: cores,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function renderizarGraficoClientes(dados) {
    const ctx = document.getElementById('topClientesChart').getContext('2d');
    
    if (chartClientes) {
        chartClientes.destroy();
    }

    const clientes = dados.map(item => item.nome);
    const valores = dados.map(item => item.valor_total);

    chartClientes = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: clientes,
            datasets: [{
                label: 'Valor Total de Vendas (R$)',
                data: valores,
                backgroundColor: '#764ba2',
                borderColor: '#667eea',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            scales: {
                x: {
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
}

function renderizarProximasCobrancas(dados) {
    const container = document.getElementById('proximasCobransasLista');
    container.innerHTML = '';

    if (dados.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #999;">Nenhuma cobrança próxima</p>';
        return;
    }

    const tabela = document.createElement('table');
    tabela.className = 'tabela-cobrancas';
    tabela.innerHTML = `
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Parcela</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            ${dados.map(item => `
                <tr>
                    <td>${item.nome}</td>
                    <td>#${item.numero_parcela}</td>
                    <td>${formatarMoeda(item.saldo_parcela)}</td>
                    <td>${formatarData(item.data_vencimento)}</td>
                    <td>
                        <a href="detalhes_venda.php?id=${item.id_venda}" class="btn-link">Ver</a>
                    </td>
                </tr>
            `).join('')}
        </tbody>
    `;
    
    container.appendChild(tabela);
}

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR');
}