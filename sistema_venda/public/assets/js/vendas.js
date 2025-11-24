let itensVenda = [];

function carregarClientes() {
    fetch('api/clientes/listar.php')
        .then(response => response.json())
        .then(clientes => {
            const select = document.getElementById('idCliente');
            clientes.forEach(cliente => {
                const option = document.createElement('option');
                option.value = cliente.id_cliente;
                option.textContent = cliente.nome;
                select.appendChild(option);
            });
        });
}

document.getElementById('btnAdicionarItem', (function() {
    const novoItem = {
        codigo: '',
        descricao: '',
        quantidade: 1,
        valor_unitario: 0
    };
    itensVenda.push(novoItem);
    renderizarItens();
});

function renderizarItens() {
    const tbody = document.getElementById('itensList');
    tbody.innerHTML = '';

    itensVenda.forEach((item, index) => {
        const tr = document.createElement('tr');
        const total = (item.quantidade * item.valor_unitario).toFixed(2);

        tr.innerHTML = `
            <td><input type="text" value="${item.codigo}" onchange="atualizarItem(${index}, 'codigo', this.value)"></td>
            <td><input type="text" value="${item.descricao}" onchange="atualizarItem(${index}, 'descricao', this.value)"></td>
            <td><input type="number" value="${item.quantidade}" min="1" onchange="atualizarItem(${index}, 'quantidade', this.value)"></td>
            <td><input type="number" value="${item.valor_unitario}" step="0.01" onchange="atualizarItem(${index}, 'valor_unitario', this.value)"></td>
            <td>R$ ${total}</td>
            <td><button type="button" onclick="removerItem(${index})" class="btn btn-danger">Remover</button></td>
        `;
        tbody.appendChild(tr);
    });

    calcularTotal();
}

function atualizarItem(index, campo, valor) {
    if (campo === 'quantidade' || campo === 'valor_unitario') {
        itensVenda[index][campo] = parseFloat(valor);
    } else {
        itensVenda[index][campo] = valor;
    }
    renderizarItens();
}

function removerItem(index) {
    itensVenda.splice(index, 1);
    renderizarItens();
}

function calcularTotal() {
    const total = itensVenda.reduce((sum, item) => {
        return sum + (item.quantidade * item.valor_unitario);
    }, 0);

    document.getElementById('totalVenda').textContent = 'R$ ' + total.toFixed(2);
}

document.getElementById('formVenda').addEventListener('submit', function(e) {
    e.preventDefault();

    if (itensVenda.length === 0) {
        alert('Adicione pelo menos um item');
        return;
    }

    const dados = {
        id_cliente: document.getElementById('idCliente').value,
        observacoes: document.getElementById('observacoes').value,
        quantidade_parcelas: parseInt(document.getElementById('quantidadeParcelas').value),
        itens: itensVenda
    };

    fetch('api/vendas/salvar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            alert('Venda criada com sucesso!');
            window.location.href = `detalhes_venda.php?id=${data.id_venda}`;
        } else {
            alert('Erro: ' + data.mensagem);
        }
    });
});