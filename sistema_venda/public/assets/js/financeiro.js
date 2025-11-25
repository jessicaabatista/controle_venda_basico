let idVendaAtual, parcelasVenda, itensVenda;

function abrirModalPagamento(idVenda) {
    idVendaAtual = idVenda;
    
    // Limpar formulário e erros anteriores
    limparFormularioPagamento();
    
    // Buscar dados da venda
    fetch(`api/vendas/detalhes.php?id=${idVenda}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar dados');
            }
            return response.json();
        })
        .then(dados => {
            parcelasVenda = dados.parcelas || [];
            itensVenda = dados.itens || [];
            
            // Preencher select de parcelas
            const selectParcela = document.getElementById('idParcela');
            selectParcela.innerHTML = '<option value="">Escolha uma parcela</option>';
            
            const parcelasAbertas = parcelasVenda.filter(p => p.status === 'aberta' || p.status === 'vencida');
            parcelasAbertas.forEach(parcela => {
                const option = document.createElement('option');
                option.value = parcela.id_parcela;
                option.textContent = `Parcela #${parcela.numero_parcela} - Vencimento: ${formatarData(parcela.data_vencimento)} - Saldo: R$ ${parseFloat(parcela.saldo_parcela).toFixed(2)}`;
                option.dataset.saldo = parcela.saldo_parcela;
                selectParcela.appendChild(option);
            });

            // Preencher select de itens
            const selectItem = document.getElementById('idItem');
            selectItem.innerHTML = '<option value="">Escolha um item</option>';
            
            const itensPendentes = itensVenda.filter(i => i.status_pagamento !== 'pago');
            itensPendentes.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id_item;
                option.textContent = `${item.descricao} - Saldo: R$ ${parseFloat(item.saldo_item).toFixed(2)}`;
                option.dataset.saldo = item.saldo_item;
                selectItem.appendChild(option);
            });

            // Atualizar valor padrão (venda total)
            const inputValor = document.getElementById('valorPago');
            const saldoTotal = dados.venda.saldo_devedor || 0;
            inputValor.value = saldoTotal.toFixed(2);
            inputValor.max = saldoTotal;
            
            atualizarSaldoApos();
            
            // Abrir modal
            document.getElementById('modalPagamento').classList.add('show');
        })
        .catch(error => {
            console.error('Erro:', error);
            FeedbackVisual.mostrarNotificacao('erro', 'Erro ao carregar dados. Tente novamente.');
        });
}

function abrirModalPagamentoParcela(idVenda, idParcela) {
    abrirModalPagamento(idVenda);
    
    // Aguardar o modal ser carregado
    setTimeout(() => {
        document.getElementById('tipoPagamento').value = 'parcela';
        atualizarTipoPagamento();
        document.getElementById('idParcela').value = idParcela;
        atualizarValorParcela();
    }, 100);
}

function fecharModal() {
    document.getElementById('modalPagamento').classList.remove('show');
    limparFormularioPagamento();
}

function limparFormularioPagamento() {
    document.getElementById('formPagamento').reset();
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
    document.getElementById('selectParcela').style.display = 'none';
    document.getElementById('selectItem').style.display = 'none';
}

function atualizarTipoPagamento() {
    const tipo = document.getElementById('tipoPagamento').value;

    // Limpar erros
    document.getElementById('erroParcela').textContent = '';
    document.getElementById('erroItem').textContent = '';

    if (tipo === 'parcela') {
        document.getElementById('selectParcela').style.display = 'block';
        document.getElementById('selectItem').style.display = 'none';
    } else if (tipo === 'item') {
        document.getElementById('selectParcela').style.display = 'none';
        document.getElementById('selectItem').style.display = 'block';
    } else {
        document.getElementById('selectParcela').style.display = 'none';
        document.getElementById('selectItem').style.display = 'none';
    }
    
    atualizarSaldoApos();
}

function atualizarValorParcela() {
    const selectParcela = document.getElementById('idParcela');
    const selected = selectParcela.options[selectParcela.selectedIndex];
    
    if (selected.value) {
        const saldo = parseFloat(selected.dataset.saldo);
        document.getElementById('valorPago').value = saldo.toFixed(2);
        document.getElementById('valorPago').max = saldo;
        document.getElementById('erroParcela').textContent = '';
        atualizarSaldoApos();
    }
}

function atualizarValorItem() {
    const selectItem = document.getElementById('idItem');
    const selected = selectItem.options[selectItem.selectedIndex];
    
    if (selected.value) {
        const saldo = parseFloat(selected.dataset.saldo);
        document.getElementById('valorPago').value = saldo.toFixed(2);
        document.getElementById('valorPago').max = saldo;
        document.getElementById('erroItem').textContent = '';
        atualizarSaldoApos();
    }
}

function atualizarSaldoApos() {
    const tipo = document.getElementById('tipoPagamento').value;
    let saldoAtual = 0;

    if (tipo === 'parcela') {
        const selectParcela = document.getElementById('idParcela');
        const selected = selectParcela.options[selectParcela.selectedIndex];
        if (selected.value) {
            saldoAtual = parseFloat(selected.dataset.saldo);
        }
    } else if (tipo === 'item') {
        const selectItem = document.getElementById('idItem');
        const selected = selectItem.options[selectItem.selectedIndex];
        if (selected.value) {
            saldoAtual = parseFloat(selected.dataset.saldo);
        }
    } else {
        // Venda total
        saldoAtual = parcelasVenda.reduce((sum, p) => sum + parseFloat(p.saldo_parcela), 0);
    }

    const valorPago = parseFloat(document.getElementById('valorPago').value) || 0;
    const saldoApos = saldoAtual - valorPago;

    document.getElementById('saldoAtual').textContent = `R$ ${saldoAtual.toFixed(2)}`;
    document.getElementById('valorPagar').textContent = `R$ ${valorPago.toFixed(2)}`;
    document.getElementById('saldoApos').textContent = `R$ ${Math.max(0, saldoApos).toFixed(2)}`;
}

function validarFormularioPagamento() {
    let valido = true;
    
    // Limpar erros anteriores
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
    
    const tipo = document.getElementById('tipoPagamento').value;
    const valorPago = parseFloat(document.getElementById('valorPago').value);
    
    // Validar valor
    if (!valorPago || valorPago <= 0) {
        document.getElementById('erroValor').textContent = 'Valor deve ser maior que zero';
        valido = false;
    }
    
    // Validar tipo específico
    if (tipo === 'parcela') {
        const idParcela = document.getElementById('idParcela').value;
        if (!idParcela) {
            document.getElementById('erroParcela').textContent = 'Selecione uma parcela';
            valido = false;
        }
    } else if (tipo === 'item') {
        const idItem = document.getElementById('idItem').value;
        if (!idItem) {
            document.getElementById('erroItem').textContent = 'Selecione um item';
            valido = false;
        }
    }
    
    // Validar forma de pagamento
    const formaPagamento = document.getElementById('formaPagamento').value;
    if (!formaPagamento) {
        document.getElementById('erroForma').textContent = 'Selecione uma forma de pagamento';
        valido = false;
    }
    
    return valido;
}

// Adicionar campos de erro ao modal
document.addEventListener('DOMContentLoaded', function() {
    const formPagamento = document.getElementById('formPagamento');
    if (formPagamento && !document.getElementById('erroValor')) {
        const valorPago = document.getElementById('valorPago');
        valorPago.insertAdjacentHTML('afterend', '<small class="form-error" id="erroValor"></small>');
        
        const tipoPagamento = document.getElementById('tipoPagamento');
        tipoPagamento.insertAdjacentHTML('afterend', '<small class="form-error" id="erroTipo"></small>');
        
        const selectParcela = document.getElementById('idParcela');
        selectParcela.insertAdjacentHTML('afterend', '<small class="form-error" id="erroParcela"></small>');
        
        const selectItem = document.getElementById('idItem');
        selectItem.insertAdjacentHTML('afterend', '<small class="form-error" id="erroItem"></small>');
        
        const formaPagamento = document.getElementById('formaPagamento');
        formaPagamento.insertAdjacentHTML('afterend', '<small class="form-error" id="erroForma"></small>');
    }
});

document.getElementById('formPagamento')?.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!validarFormularioPagamento()) {
        return;
    }

    const tipo = document.getElementById('tipoPagamento').value;
    const valorPago = parseFloat(document.getElementById('valorPago').value);

    const dados = {
        id_venda: idVendaAtual,
        valor_pago: valorPago,
        forma_pagamento: document.getElementById('formaPagamento').value,
        observacoes: document.getElementById('observacoesPagamento').value
    };

    if (tipo === 'parcela') {
        dados.id_parcela = document.getElementById('idParcela').value;
    } else if (tipo === 'item') {
        dados.id_item = document.getElementById('idItem').value;
    }

    // Usar feedback visual melhorado
    const submitBtn = this.querySelector('button[type="submit"]');
    FeedbackVisual.mostrarLoading(submitBtn, 'Processando pagamento...');

    fetch('api/financeiro/processar_pagamento.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na resposta do servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.sucesso) {
            FeedbackVisual.mostrarNotificacao('sucesso', 'Pagamento registrado com sucesso!');
            fecharModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            FeedbackVisual.mostrarNotificacao('erro', data.mensagem || 'Erro ao processar pagamento');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        FeedbackVisual.mostrarNotificacao('erro', 'Erro ao processar pagamento. Verifique sua conexão.');
    })
    .finally(() => {
        FeedbackVisual.restaurarElemento(submitBtn);
    });
});

// Fechar modal ao clicar no X
document.querySelector('.modal-close')?.addEventListener('click', fecharModal);

// Fechar modal ao clicar fora dele
window.addEventListener('click', function(event) {
    const modal = document.getElementById('modalPagamento');
    if (event.target === modal) {
        fecharModal();
    }
});

// Atualizar saldo quando digitar valor
document.getElementById('valorPago')?.addEventListener('input', function() {
    document.getElementById('erroValor').textContent = '';
    atualizarSaldoApos();
});

// Validar valor máximo
document.getElementById('valorPago')?.addEventListener('change', function() {
    const valor = parseFloat(this.value);
    const maximo = parseFloat(this.max);
    
    if (valor > maximo) {
        this.value = maximo.toFixed(2);
        FeedbackVisual.mostrarNotificacao('aviso', `Valor máximo é R$ ${maximo.toFixed(2)}`);
    }
});

function formatarData(dataStr) {
    const data = new Date(dataStr);
    return data.toLocaleDateString('pt-BR');
}

// Adicionar estilos para mensagens de erro
const style = document.createElement('style');
style.textContent = `
    .form-error {
        color: #ff6b6b;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }
    
    #formPagamento .form-group {
        margin-bottom: 25px;
    }
    
    #formPagamento input:invalid,
    #formPagamento select:invalid {
        border-color: #ff6b6b;
    }
`;
document.head.appendChild(style);