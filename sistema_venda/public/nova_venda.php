<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../config/auth.php';
require_once '../classes/Database.php';
require_once '../classes/Cliente.php';
require_once '../classes/Venda.php';

$cliente_obj = new Cliente();
$venda_obj = new Venda();

$idClientePre = $_GET['cliente'] ?? '';
$novaVenda = false;
$idVenda = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_venda'])) {
    $idCliente = $_POST['id_cliente'];
    $observacoes = $_POST['observacoes'] ?? '';

    try {
        $novaVendaId = $venda_obj->criar($idCliente, $observacoes);
        header('Location: nova_venda.php?id=' . $novaVendaId);
        exit;
    } catch (Exception $e) {
        $erro = 'Erro ao criar venda: ' . $e->getMessage();
    }
}

$venda = null;
$itens = [];

if ($idVenda) {
    $venda = $venda_obj->obter($idVenda);
    $itens = $venda_obj->obterItens($idVenda);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Venda - Sistema de Semi-Joias</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .venda-form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .form-card h2 {
            margin-bottom: 20px;
            font-size: 20px;
            color: #333;
        }

        .itens-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .itens-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .itens-header h2 {
            font-size: 20px;
            color: #333;
        }

        .form-item {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }

        .form-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-item-header h4 {
            color: #666;
            font-size: 14px;
        }

        .btn-remover-item {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-remover-item:hover {
            background: #ff5555;
        }

        .resumo-venda {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 20px;
        }

        .resumo-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .resumo-row.total {
            border-bottom: none;
            font-weight: bold;
            font-size: 18px;
            color: #667eea;
            padding-top: 15px;
        }

        .resumo-row label {
            color: #666;
        }

        .resumo-row .valor {
            color: #333;
            font-weight: 500;
        }

        .tabela-itens {
            width: 100%;
            margin-top: 20px;
        }

        .tabela-itens thead {
            background: #f5f5f5;
        }

        .tabela-itens th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #666;
        }

        .tabela-itens td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .form-inline {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 1024px) {
            .venda-form-container {
                grid-template-columns: 1fr;
            }

            .resumo-venda {
                position: static;
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
            <a href="vendas.php" class="nav-link">Vendas</a>
            <a href="nova_venda.php" class="nav-link active">+ Nova Venda</a>
            <a href="clientes.php" class="nav-link">Clientes</a>
            <div class="nav-user">
                <a href="logout.php" class="nav-logout">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="breadcrumb">
            <a href="vendas.php">Vendas</a> > Nova Venda
        </div>

        <?php if (!$idVenda): ?>
        <!-- Criar Nova Venda -->
        <form method="POST" class="venda-form-container" onsubmit="return validarFormularioVenda()">
            <div class="form-card">
                <h2>Dados da Venda</h2>

                <div class="form-group">
                    <label>Cliente *</label>
                    <input type="hidden" name="id_cliente" id="idCliente" required>
                    <input type="text" id="searchCliente" placeholder="Buscar cliente..." autocomplete="off" required>
                    <div id="clienteSugestoes" style="max-height: 200px; overflow-y: auto; background: white; border: 1px solid #ddd; border-radius: 5px; display: none; margin-top: 5px;"></div>
                    <small class="form-error" id="erroCliente"></small>
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="observacoes" rows="5" placeholder="Observações sobre o pagamento..."></textarea>
                </div>

                <button type="submit" name="criar_venda" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Criar Venda</button>
            </div>

            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);">
                <h2>Novo Cliente</h2>
                <p style="color: #999; margin-bottom: 20px;">Ou adicione um novo cliente</p>

                <div class="form-group">
                    <label>Nome *</label>
                    <input type="text" id="novoClienteNome" placeholder="Nome do cliente" required>
                    <small class="form-error" id="erroNomeCliente"></small>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="novoClienteEmail" placeholder="email@exemplo.com">
                    <small class="form-error" id="erroEmailCliente"></small>
                </div>

                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" id="novoClienteTelefone" placeholder="(11) 99999-9999">
                    <small class="form-error" id="erroTelefoneCliente"></small>
                </div>

                <button type="button" onclick="criarNovoCliente()" class="btn btn-secondary" style="width: 100%; margin-top: 20px;">Adicionar Cliente</button>
            </div>
        </form>

        <?php else: ?>
        <!-- Editar Venda Existente -->
        <div class="venda-form-container">
            <div class="form-card">
                <h2>Dados da Venda #<?php echo $idVenda; ?></h2>

                <div class="form-group">
                    <label>Cliente</label>
                    <p style="font-weight: 600; color: #333;"><?php echo htmlspecialchars($venda['nome_cliente']); ?></p>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <p style="color: #666;"><?php echo htmlspecialchars($venda['email']); ?></p>
                </div>

                <div class="form-group">
                    <label>Telefone</label>
                    <p style="color: #666;"><?php echo htmlspecialchars($venda['telefone']); ?></p>
                </div>

                <div class="form-group">
                    <label>Data Criação</label>
                    <p style="color: #666;"><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></p>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <p>
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
                    </p>
                </div>

                <a href="detalhes_venda.php?id=<?php echo $idVenda; ?>" class="btn btn-primary" style="width: 100%; display: block; text-align: center; padding: 12px 0; margin-top: 20px;">Ver Detalhes da Venda</a>
            </div>

            <div class="resumo-venda">
                <h3 style="margin-bottom: 20px;">Resumo Financeiro</h3>

                <div class="resumo-row">
                    <label>Valor Total:</label>
                    <span class="valor">R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></span>
                </div>

                <div class="resumo-row">
                    <label>Valor Pago:</label>
                    <span class="valor">R$ <?php echo number_format($venda['valor_pago'], 2, ',', '.'); ?></span>
                </div>

                <div class="resumo-row">
                    <label>Saldo Devedor:</label>
                    <span class="valor">R$ <?php echo number_format($venda['saldo_devedor'], 2, ',', '.'); ?></span>
                </div>

                <div class="resumo-row total">
                    <label>Percentual Pago:</label>
                    <span><?php echo $venda['valor_total'] > 0 ? round(($venda['valor_pago'] / $venda['valor_total']) * 100, 1) : 0; ?>%</span>
                </div>
            </div>
        </div>

        <!-- Itens da Venda -->
        <div class="itens-section">
            <div class="itens-header">
                <h2>Itens da Venda</h2>
                <button type="button" onclick="abrirModalAdicionarItem(<?php echo $idVenda; ?>)" class="btn btn-primary">+ Adicionar Item</button>
            </div>

            <?php if (empty($itens)): ?>
                <p style="text-align: center; color: #999; padding: 40px;">Nenhum item adicionado ainda</p>
            <?php else: ?>
                <table class="tabela-itens">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Quantidade</th>
                            <th>Valor Unitário</th>
                            <th>Total</th>
                            <th>Saldo</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['descricao']); ?></td>
                            <td><?php echo $item['quantidade']; ?></td>
                            <td>R$ <?php echo number_format($item['valor_unitario'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($item['saldo_item'], 2, ',', '.'); ?></td>
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
                            <td>
                                <button type="button" onclick="removerItem(<?php echo $item['id_item']; ?>)" class="btn-remover-item">Remover</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Opções de Parcelamento -->
        <?php if (!empty($itens) && $venda['valor_total'] > 0): ?>
        <div class="card">
            <h2>Opções de Parcelamento</h2>
            <div class="form-group">
                <label>Quantidade de Parcelas</label>
                <select id="quantidadeParcelas" onchange="atualizarResumoParcelas()">
                    <option value="1">1x - R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></option>
                    <option value="2">2x - R$ <?php echo number_format($venda['valor_total'] / 2, 2, ',', '.'); ?></option>
                    <option value="3">3x - R$ <?php echo number_format($venda['valor_total'] / 3, 2, ',', '.'); ?></option>
                    <option value="4">4x - R$ <?php echo number_format($venda['valor_total'] / 4, 2, ',', '.'); ?></option>
                    <option value="5">5x - R$ <?php echo number_format($venda['valor_total'] / 5, 2, ',', '.'); ?></option>
                    <option value="6">6x - R$ <?php echo number_format($venda['valor_total'] / 6, 2, ',', '.'); ?></option>
                    <option value="12">12x - R$ <?php echo number_format($venda['valor_total'] / 12, 2, ',', '.'); ?></option>
                </select>
            </div>
            <div id="resumoParcelas" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <p>Valor da parcela: <strong id="valorParcela">R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></strong></p>
                <p>Primeiro vencimento: <strong id="primeiroVencimento"><?php echo date('d/m/Y', strtotime('+1 month')); ?></strong></p>
            </div>
            <button type="button" onclick="gerarParcelas(<?php echo $idVenda; ?>)" class="btn btn-primary">Gerar Parcelas</button>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="vendas.php" class="btn btn-secondary">Voltar</a>
            <?php if (!empty($itens)): ?>
            <a href="detalhes_venda.php?id=<?php echo $idVenda; ?>" class="btn btn-primary">Ver Detalhes</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal Adicionar Item -->
    <div id="modalItem" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Adicionar Item</h2>
                <span class="modal-close" onclick="fecharModalItem()">&times;</span>
            </div>

            <form id="formItem" class="modal-body" onsubmit="return validarFormularioItem()">
                <input type="hidden" id="idVendaItem" value="<?php echo $idVenda; ?>">

                <div class="form-group">
                    <label>Código do Produto *</label>
                    <input type="text" id="codigoProdutoItem" placeholder="Ex: PROD001" required>
                    <small class="form-error" id="erroCodigo"></small>
                </div>

                <div class="form-group">
                    <label>Descrição do Item *</label>
                    <input type="text" id="descricaoItem" placeholder="Ex: Colar de Ouro" required>
                    <small class="form-error" id="erroDescricao"></small>
                </div>

                <div class="form-group">
                    <label>Quantidade *</label>
                    <input type="number" id="quantidadeItem" min="1" step="1" value="1" required>
                    <small class="form-error" id="erroQuantidade"></small>
                </div>

                <div class="form-group">
                    <label>Valor Unitário *</label>
                    <input type="number" id="valorUnitarioItem" min="0.01" step="0.01" required>
                    <small class="form-error" id="erroValor"></small>
                </div>

                <div id="resumoItem" style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p>Valor Total: <strong id="valorTotalItem">R$ 0,00</strong></p>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                    <button type="button" class="btn btn-secondary" onclick="fecharModalItem()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/validacoes.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        // Validações do formulário
        function validarFormularioVenda() {
            let valido = true;
            
            // Limpar erros anteriores
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
            
            // Validar cliente
            const idCliente = document.getElementById('idCliente').value;
            if (!idCliente) {
                document.getElementById('erroCliente').textContent = 'Selecione um cliente';
                valido = false;
            }
            
            return valido;
        }

        function validarFormularioItem() {
            let valido = true;
            
            // Limpar erros anteriores
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
            
            // Validar código
            const codigo = document.getElementById('codigoProdutoItem').value.trim();
            if (!codigo) {
                document.getElementById('erroCodigo').textContent = 'Código é obrigatório';
                valido = false;
            }
            
            // Validar descrição
            const descricao = document.getElementById('descricaoItem').value.trim();
            if (!descricao) {
                document.getElementById('erroDescricao').textContent = 'Descrição é obrigatória';
                valido = false;
            }
            
            // Validar quantidade
            const quantidade = parseInt(document.getElementById('quantidadeItem').value);
            if (!quantidade || quantidade < 1) {
                document.getElementById('erroQuantidade').textContent = 'Quantidade deve ser maior que zero';
                valido = false;
            }
            
            // Validar valor
            const valor = parseFloat(document.getElementById('valorUnitarioItem').value);
            if (!valor || valor <= 0) {
                document.getElementById('erroValor').textContent = 'Valor deve ser maior que zero';
                valido = false;
            }
            
            return valido;
        }

        function validarNovoCliente() {
            let valido = true;
            
            // Limpar erros anteriores
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
            
            // Validar nome
            const nome = document.getElementById('novoClienteNome').value.trim();
            if (!nome || nome.length < 3) {
                document.getElementById('erroNomeCliente').textContent = 'Nome deve ter pelo menos 3 caracteres';
                valido = false;
            }
            
            // Validar email se preenchido
            const email = document.getElementById('novoClienteEmail').value.trim();
            if (email && !Validador.isValidEmail(email)) {
                document.getElementById('erroEmailCliente').textContent = 'Email inválido';
                valido = false;
            }
            
            return valido;
        }

        // Pesquisa de clientes
        document.getElementById('searchCliente')?.addEventListener('input', function() {
            const termo = this.value;

            if (termo.length < 2) {
                document.getElementById('clienteSugestoes').style.display = 'none';
                return;
            }

            fetch(`../api/clientes/pesquisa.php?termo=${encodeURIComponent(termo)}`)
                .then(response => response.json())
                .then(clientes => {
                    const sugestoes = document.getElementById('clienteSugestoes');
                    sugestoes.innerHTML = '';

                    if (clientes.length === 0) {
                        sugestoes.innerHTML = '<div style="padding: 10px; color: #999;">Nenhum cliente encontrado</div>';
                    } else {
                        clientes.forEach(cliente => {
                            const div = document.createElement('div');
                            div.style.cssText = 'padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;';
                            div.textContent = `${cliente.nome} (${cliente.email || 'sem email'})`;
                            div.onclick = () => {
                                document.getElementById('idCliente').value = cliente.id_cliente;
                                document.getElementById('searchCliente').value = cliente.nome;
                                document.getElementById('erroCliente').textContent = '';
                                sugestoes.style.display = 'none';
                            };
                            sugestoes.appendChild(div);
                        });
                    }

                    sugestoes.style.display = 'block';
                })
                .catch(error => {
                    console.error('Erro ao buscar clientes:', error);
                    document.getElementById('clienteSugestoes').innerHTML = '<div style="padding: 10px; color: #ff6b6b;">Erro ao buscar clientes</div>';
                    document.getElementById('clienteSugestoes').style.display = 'block';
                });
        });

        function criarNovoCliente() {
            if (!validarNovoCliente()) {
                return;
            }

            const nome = document.getElementById('novoClienteNome').value.trim();
            const email = document.getElementById('novoClienteEmail').value.trim();
            const telefone = document.getElementById('novoClienteTelefone').value.trim();

            const dados = { nome, email, telefone };

            fetch('../api/clientes/salvar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    document.getElementById('idCliente').value = data.id_cliente;
                    document.getElementById('searchCliente').value = nome;
                    document.getElementById('erroCliente').textContent = '';
                    
                    // Limpar formulário de novo cliente
                    document.getElementById('novoClienteNome').value = '';
                    document.getElementById('novoClienteEmail').value = '';
                    document.getElementById('novoClienteTelefone').value = '';
                    
                    FeedbackVisual.mostrarNotificacao('sucesso', 'Cliente criado com sucesso!');
                } else {
                    FeedbackVisual.mostrarNotificacao('erro', data.mensagem);
                }
            })
            .catch(error => {
                console.error('Erro ao criar cliente:', error);
                FeedbackVisual.mostrarNotificacao('erro', 'Erro ao criar cliente. Tente novamente.');
            });
        }

        function abrirModalAdicionarItem(idVenda) {
            document.getElementById('modalItem').classList.add('show');
        }

        function fecharModalItem() {
            document.getElementById('modalItem').classList.remove('show');
            document.getElementById('formItem').reset();
            // Limpar mensagens de erro
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
        }

        document.getElementById('quantidadeItem')?.addEventListener('input', atualizarTotalItem);
        document.getElementById('valorUnitarioItem')?.addEventListener('input', atualizarTotalItem);

        function atualizarTotalItem() {
            const quantidade = parseFloat(document.getElementById('quantidadeItem').value) || 0;
            const valor = parseFloat(document.getElementById('valorUnitarioItem').value) || 0;
            const total = quantidade * valor;

            document.getElementById('valorTotalItem').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
        }

        document.getElementById('formItem')?.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validarFormularioItem()) {
                return;
            }

            const dados = {
                id_venda: document.getElementById('idVendaItem').value,
                codigo_produto: document.getElementById('codigoProdutoItem').value.trim(),
                descricao: document.getElementById('descricaoItem').value.trim(),
                quantidade: document.getElementById('quantidadeItem').value,
                valor_unitario: document.getElementById('valorUnitarioItem').value
            };

            const submitBtn = this.querySelector('button[type="submit"]');
            FeedbackVisual.mostrarLoading(submitBtn, 'Adicionando item...');

            fetch('../api/vendas/adicionar_item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    FeedbackVisual.mostrarNotificacao('sucesso', 'Item adicionado com sucesso!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    FeedbackVisual.mostrarNotificacao('erro', data.mensagem);
                }
            })
            .catch(error => {
                console.error('Erro ao adicionar item:', error);
                FeedbackVisual.mostrarNotificacao('erro', 'Erro ao adicionar item. Tente novamente.');
            })
            .finally(() => {
                FeedbackVisual.restaurarElemento(submitBtn);
            });
        });

        function removerItem(idItem) {
            mostrarConfirmacao('Tem certeza que deseja remover este item?', () => {
                fetch('../api/vendas/remover_item.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_item: idItem })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        FeedbackVisual.mostrarNotificacao('sucesso', 'Item removido com sucesso!');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        FeedbackVisual.mostrarNotificacao('erro', data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro ao remover item:', error);
                    FeedbackVisual.mostrarNotificacao('erro', 'Erro ao remover item. Tente novamente.');
                });
            });
        }

        function atualizarResumoParcelas() {
            const select = document.getElementById('quantidadeParcelas');
            const valorTotal = <?php echo $venda['valor_total'] ?? 0; ?>;
            const parcelas = parseInt(select.value);
            const valorParcela = valorTotal / parcelas;

            document.getElementById('valorParcela').textContent = `R$ ${valorParcela.toFixed(2).replace('.', ',')}`;
        }

        function gerarParcelas(idVenda) {
            const quantidadeParcelas = document.getElementById('quantidadeParcelas').value;

            mostrarConfirmacao(`Deseja gerar ${quantidadeParcelas} parcelas para esta venda?`, () => {
                const btn = event.target;
                FeedbackVisual.mostrarLoading(btn, 'Gerando parcelas...');

                fetch('../api/vendas/gerar_parcelas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_venda: idVenda,
                        quantidade_parcelas: quantidadeParcelas
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        FeedbackVisual.mostrarNotificacao('sucesso', 'Parcelas geradas com sucesso!');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        FeedbackVisual.mostrarNotificacao('erro', data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro ao gerar parcelas:', error);
                    FeedbackVisual.mostrarNotificacao('erro', 'Erro ao gerar parcelas. Tente novamente.');
                })
                .finally(() => {
                    FeedbackVisual.restaurarElemento(btn);
                });
            });
        }

        // Fechar modal ao clicar fora
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('modalItem');
            if (event.target === modal) {
                fecharModalItem();
            }
        });

        // Fechar sugestões ao clicar fora
        document.addEventListener('click', function(event) {
            const searchCliente = document.getElementById('searchCliente');
            const sugestoes = document.getElementById('clienteSugestoes');
            
            if (!searchCliente.contains(event.target) && !sugestoes.contains(event.target)) {
                sugestoes.style.display = 'none';
            }
        });
    </script>

    <style>
        .form-error {
            color: #ff6b6b;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal.show {
            display: block;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .modal-close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-close:hover {
            color: #000;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
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
    </style>
</body>
</html>
