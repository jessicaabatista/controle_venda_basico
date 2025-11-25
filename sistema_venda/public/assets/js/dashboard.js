let chartVendas, chartStatus, chartClientes, chartFormas, chartEvolucao, chartProdutos;

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
        .catch(erro => {
            console.error('Erro ao carregar dados:', erro);
            FeedbackVisual.mostrarNotificacao('erro', 'Erro ao carregar dados do dashboard');
        });
}

function atualizarKPIs(dados) {
    const totalVendas = dados.vendas.valor_total || 0;
    const qtdVendas = dados.vendas.total_vendas || 0;
    document.getElementById('totalVendas').textContent = formatarMoeda(totalVendas);
    document.getElementById('qtdVendas').textContent = qtdVendas + ' vendas';

    const vencidas = dados.vencidas.valor_vencido || 0;
    const qtdVencidas = dados.vencidas.total_vencidas || 0;
    document.getElementById('parcVencidas').textContent = formatarMoeda(vencidas);
    document.getElementById('qtdVencidas').textContent = qtdVencidas + ' parcelas';

    const totalProximas = dados.proximas.reduce((sum, item) => sum + parseFloat(item.saldo_parcela || 0), 0);
    document.getElementById('proximasCobr').textContent = formatarMoeda(totalProximas);
    document.getElementById('qtdProximas').textContent = dados.proximas.length + ' parcelas';

    const saldoTotal = totalVendas - dados.proximas.reduce((sum, item) => sum + parseFloat(item.valor_previsto || 0), 0);
    document.getElementById('saldoReceber').textContent = formatarMoeda(Math.max(0, saldoTotal));

    const ticketMedio = qtdVendas > 0 ? totalVendas / qtdVendas : 0;
    document.getElementById('ticketMedio').textContent = formatarMoeda(ticketMedio);

    const taxaRecebimento = totalVendas > 0 ? ((totalVendas - saldoTotal) / totalVendas * 100).toFixed(1) : 0;
    document.getElementById('taxaConversao').textContent = taxaRecebimento + '%';
}

function renderizarGraficoVendas(dados) {
    const ctx = document.getElementById('vendasPorDiaChart');
    if (!ctx) return;
    
    if (chartVendas) {
        chartVendas.destroy();
    }

    const dias = dados.map(item => 'Dia ' + item.dia);
    const valores = dados.map(item => item.valor);

    chartVendas = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: dias,
            datasets: [{
                label: 'Valor de Vendas (R$)',
                data: valores,
                borderColor: '#1c813c',
                backgroundColor: 'rgba(28, 129, 60, 0.05)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#1c813c',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: { family: "'Poppins', sans-serif", size: 13 },
                        usePointStyle: true,
                        padding: 15
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { family: "'Poppins', sans-serif", size: 12 },
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(0);
                        }
                    },
                    grid: {
                        drawBorder: false,
                        color: '#f1f1f1'
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: { family: "'Poppins', sans-serif", size: 12 }
                    }
                }
            }
        }
    });
}

function renderizarGraficoStatus(dados) {
    const ctx = document.getElementById('statusVendasChart');
    if (!ctx) return;
    
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
    const cores = ['#ffc107', '#0094e1', '#1c813c', '#6c757d'];

    chartStatus = new Chart(ctx.getContext('2d'), {
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
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { family: "'Poppins', sans-serif", size: 13 },
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });
}

function renderizarGraficoClientes(dados) {
    const ctx = document.getElementById('topClientesChart');
    if (!ctx) return;
    
    if (chartClientes) {
        chartClientes.destroy();
    }

    const clientes = dados.map(item => item.nome);
    const valores = dados.map(item => item.valor_total);

    chartClientes = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: clientes,
            datasets: [{
                label: 'Valor Total de Vendas (R$)',
                data: valores,
                backgroundColor: '#0094e1',
                borderColor: '#0094e1',
                borderWidth: 0,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        font: { family: "'Poppins', sans-serif", size: 13 }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: '#f1f1f1'
                    },
                    ticks: {
                        font: { family: "'Poppins', sans-serif", size: 12 },
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(0);
                        }
                    }
                },
                y: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: { family: "'Poppins', sans-serif", size: 12 }
                    }
                }
            }
        }
    });
}

function renderizarProximasCobrancas(dados) {
    const container = document.getElementById('proximasCobrancasLista');
    if (!container) return;
    
    container.innerHTML = '';

    if (dados.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px;"><p style="color: #999;"><i class="fas fa-check-circle" style="margin-right: 10px;"></i>Nenhuma cobranca proxima</p></div>';
        return;
    }

    const tabela = document.createElement('table');
    tabela.innerHTML = `
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Parcela</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Acao</th>
            </tr>
        </thead>
        <tbody>
            ${dados.map(item => `
                <tr>
                    <td>${htmlEscape(item.nome)}</td>
                    <td>#${item.numero_parcela}</td>
                    <td>${formatarMoeda(item.saldo_parcela)}</td>
                    <td>${formatarData(item.data_vencimento)}</td>
                    <td><a href="detalhes_venda.php?id=${item.id_venda}" class="btn-link"><i class="fas fa-eye"></i></a></td>
                </tr>
            `).join('')}
        </tbody>
    `;
    
    container.appendChild(tabela);
}

function formatarData(data) {
    if (!data) return '';
    try {
        return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
    } catch (e) {
        return '';
    }
}

function htmlEscape(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}