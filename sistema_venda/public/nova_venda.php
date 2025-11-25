<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../config/config.php';
require_once '../config/auth.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Cliente.php';
require_once CLASSES_PATH . '/Venda.php';

$cliente_obj = new Cliente();
$venda_obj = new Venda();

$idClientePre = $_GET['cliente'] ?? '';
$idVenda = $_GET['id'] ?? 0;
$erro = '';

$venda = null;
$itens = [];

if ($idVenda) {
    $venda = $venda_obj->obter($idVenda);
    if (!$venda) {
        header('Location: ' . PUBLIC_URL_RELATIVE . 'vendas.php');
        exit;
    }
    $itens = $venda_obj->obterItens($idVenda);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Venda - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/style.css'); ?>">
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

        .form-error {
            color: #ff6b6b;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .input-invalid {
            border-color: #ff6b6b !important;
        }

        .sugestoes-container {
            max-height: 250px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            display: none;
            position: absolute;
            width: 100%;
            z-index: 1000;
            border-radius: 0 0 5px 5px;
            top: 100%;
            left: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .sugestoes-container.show {
            display: block;
        }

        .sugestao-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .sugestao-item:hover {
            background-color: #f5f5f5;
        }

        .sugestao-item:last-child {
            border-bottom: none;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: var(--font-family);
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .sucesso-msg {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .cliente-selecionado {
            background: #e7f5ff;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
            margin-bottom: 20px;
        }

        .cliente-selecionado strong {
            color: #667eea;
        }

        .cliente-selecionado button {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            margin-left: 10px;
        }

        .info-box {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .info-box p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        @media (max-width: 1024px) {
            .venda-form-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo PUBLIC_URL_RELATIVE; ?>vendas.php">Vendas</a> / Nova Venda
        </div>

        <?php if (!$idVenda): ?>
            <!-- CRIAR NOVA VENDA -->
            <div class="venda-form-container">
                <!-- Painel 1: Selecionar Cliente -->
                <div class="form-card">
                    <h2><i class="fas fa-user-check"></i> Selecione o Cliente</h2>

                    <div id="clienteSelecionadoDiv" class="cliente-selecionado" style="display: none;">
                        <div>
                            <strong id="clienteSelecionadoNome"></strong>
                            <button type="button" onclick="limparClienteSelecionado()" title="Remover selecao">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <small id="clienteSelecionadoTelefone" style="color: #666; display: block; margin-top: 5px;"></small>
                    </div>

                    <form id="formSelecionarCliente" onsubmit="criarVendaAjax(event)">
                        <div class="form-group">
                            <label for="searchCliente">Cliente *</label>
                            <input
                                type="text"
                                id="searchCliente"
                                placeholder="Digite o nome do cliente..."
                                autocomplete="off">
                            <input type="hidden" id="idCliente" name="id_cliente" required>
                            <div id="clienteSugestoes" class="sugestoes-container"></div>
                            <small class="form-error" id="erroCliente"></small>
                        </div>

                        <div class="form-group">
                            <label for="quantidadeParcelas">Quantidade de Parcelas</label>
                            <select id="quantidadeParcelas" name="quantidade_parcelas">
                                <option value="1">À Vista (1x)</option>
                                <option value="2">2x</option>
                                <option value="3">3x</option>
                                <option value="6">6x</option>
                                <option value="12">12x</option>
                                <option value="24">24x</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="observacoes">Observações</label>
                            <textarea
                                id="observacoes"
                                name="observacoes"
                                rows="5"
                                placeholder="Observações sobre o pagamento..."></textarea>
                        </div>

                        <button type="submit" id="btnCriarVenda" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-plus"></i> Criar Venda
                        </button>
                    </form>
                </div>

                <!-- Painel 2: Criar Novo Cliente -->
                <div class="form-card">
                    <h2><i class="fas fa-user-plus"></i> Novo Cliente</h2>
                    <p style="color: #999; margin-bottom: 20px; font-size: 14px;">Ou adicione um novo cliente e comece a venda</p>

                    <form id="formNovoCliente" onsubmit="executarCriarNovoCliente(event)">
                        <div class="form-group">
                            <label for="novoClienteNome">Nome *</label>
                            <input
                                type="text"
                                id="novoClienteNome"
                                placeholder="Nome completo do cliente"
                                required>
                            <small class="form-error" id="erroNomeCliente"></small>
                        </div>

                        <div class="form-group">
                            <label for="novoClienteEmail">Email</label>
                            <input
                                type="email"
                                id="novoClienteEmail"
                                placeholder="email@exemplo.com">
                            <small class="form-error" id="erroEmailCliente"></small>
                        </div>

                        <div class="form-group">
                            <label for="novoClienteTelefone">Telefone</label>
                            <input
                                type="text"
                                id="novoClienteTelefone"
                                class="input-telefone"
                                placeholder="(11) 9 9999-9999">
                            <small class="form-error" id="erroTelefoneCliente"></small>
                        </div>

                        <div class="form-group">
                            <label for="novoClienteEndereco">Endereco</label>
                            <input
                                type="text"
                                id="novoClienteEndereco"
                                placeholder="Endereco completo">
                        </div>

                        <div class="form-group">
                            <label for="novoClienteCPF">CPF/CNPJ</label>
                            <input
                                type="text"
                                id="novoClienteCPF"
                                class="input-cpf-cnpj"
                                placeholder="000.000.000-00">
                        </div>

                        <button type="submit" id="btnSalvarCliente" class="btn btn-secondary" style="width: 100%;">
                            <i class="fas fa-save"></i> Salvar Cliente
                        </button>
                    </form>

                    <div id="sucessoNovoCliente" class="sucesso-msg" style="display: none; margin-top: 20px;">
                        <i class="fas fa-check-circle"></i> Cliente criado com sucesso! Selecionado automaticamente.
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- EDITAR VENDA EXISTENTE -->
            <div class="venda-form-container">
                <div class="form-card">
                    <h2><i class="fas fa-info-circle"></i> Dados da Venda #<?php echo $idVenda; ?></h2>

                    <div class="form-group">
                        <label>Cliente</label>
                        <p style="font-weight: 600; color: #333; padding: 10px 0;">
                            <?php echo htmlspecialchars($venda['nome_cliente']); ?>
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <p style="color: #666; padding: 10px 0;">
                            <?php echo htmlspecialchars($venda['email']); ?>
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Telefone</label>
                        <p style="color: #666; padding: 10px 0;">
                            <?php echo htmlspecialchars($venda['telefone']); ?>
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Data Criacao</label>
                        <p style="color: #666; padding: 10px 0;">
                            <?php echo date(DATETIME_FORMAT, strtotime($venda['data_venda'])); ?>
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <p style="padding: 10px 0;">
                            <span class="status-badge <?php echo $venda['status_geral']; ?>">
                                <?php
                                $statusMap = [
                                    STATUS_VENDA_ABERTA => 'Aberta',
                                    STATUS_VENDA_PARCIAL => 'Parcial',
                                    STATUS_VENDA_PAGA => 'Paga',
                                    STATUS_VENDA_CANCELADA => 'Cancelada'
                                ];
                                echo $statusMap[$venda['status_geral']] ?? $venda['status_geral'];
                                ?>
                            </span>
                        </p>
                    </div>

                    <a href="<?php echo PUBLIC_URL_RELATIVE; ?>detalhes_venda.php?id=<?php echo $idVenda; ?>" class="btn btn-primary" style="width: 100%; display: block; text-align: center; padding: 12px 0;">
                        <i class="fas fa-eye"></i> Ver Detalhes da Venda
                    </a>
                </div>

                <div class="form-card">
                    <h3><i class="fas fa-chart-pie"></i> Resumo Financeiro</h3>

                    <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #eee;">
                        <label style="color: #666;">Valor Total:</label>
                        <span style="font-weight: 600; color: #333;">R$ <?php echo number_format($venda['valor_total'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #eee;">
                        <label style="color: #666;">Valor Pago:</label>
                        <span style="font-weight: 600; color: #333;">R$ <?php echo number_format($venda['valor_pago'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #eee;">
                        <label style="color: #666;">Saldo Devedor:</label>
                        <span style="font-weight: 600; color: #333;">R$ <?php echo number_format($venda['saldo_devedor'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding: 15px 0; font-weight: bold; font-size: 18px; color: #667eea;">
                        <label>Percentual Pago:</label>
                        <span><?php echo $venda['valor_total'] > 0 ? round(($venda['valor_pago'] / $venda['valor_total']) * 100, 1) : 0; ?>%</span>
                    </div>
                </div>
            </div>

            <!-- ITENS DA VENDA -->
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2><i class="fas fa-box"></i> Itens da Venda</h2>
                    <button type="button" onclick="abrirModalAdicionarItem(<?php echo $idVenda; ?>)" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Adicionar Item
                    </button>
                </div>

                <?php if (empty($itens)): ?>
                    <p style="text-align: center; color: #999; padding: 40px;">
                        <i class="fas fa-inbox"></i><br>
                        Nenhum item adicionado ainda
                    </p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f5f5f5;">
                                    <th style="padding: 15px; text-align: left; font-weight: 600; color: #666; border-bottom: 1px solid #ddd;">Descricao</th>
                                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #666; border-bottom: 1px solid #ddd;">Quantidade</th>
                                    <th style="padding: 15px; text-align: right; font-weight: 600; color: #666; border-bottom: 1px solid #ddd;">Valor Unit.</th>
                                    <th style="padding: 15px; text-align: right; font-weight: 600; color: #666; border-bottom: 1px solid #ddd;">Total</th>
                                    <th style="padding: 15px; text-align: right; font-weight: 600; color: #666; border-bottom: 1px solid #ddd;">Saldo</th>
                                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #666; border-bottom: 1px solid #ddd;">Status</th>
                                    <th style="padding: 15px; text-align: center; font-weight: 600; color: #666; border-bottom: 1px solid #ddd;">Acao</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 15px;">
                                            <?php echo htmlspecialchars($item['descricao']); ?>
                                        </td>
                                        <td style="padding: 15px; text-align: center;">
                                            <?php echo $item['quantidade']; ?>
                                        </td>
                                        <td style="padding: 15px; text-align: right;">
                                            R$ <?php echo number_format($item['valor_unitario'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                        </td>
                                        <td style="padding: 15px; text-align: right;">
                                            R$ <?php echo number_format($item['valor_total'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                        </td>
                                        <td style="padding: 15px; text-align: right;">
                                            R$ <?php echo number_format($item['saldo_item'], CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR); ?>
                                        </td>
                                        <td style="padding: 15px; text-align: center;">
                                            <span class="status-badge <?php echo $item['status_pagamento']; ?>">
                                                <?php
                                                $statusMap = [
                                                    STATUS_ITEM_PENDENTE => 'Pendente',
                                                    STATUS_ITEM_PARCIAL => 'Parcial',
                                                    STATUS_ITEM_PAGO => 'Pago'
                                                ];
                                                echo $statusMap[$item['status_pagamento']];
                                                ?>
                                            </span>
                                        </td>
                                        <td style="padding: 15px; text-align: center;">
                                            <button type="button" onclick="confirmarRemoverItem(<?php echo $item['id_item']; ?>)" class="btn-link">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- MODAL ADICIONAR ITEM -->
    <div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 998;" onclick="fecharModalItem()"></div>

    <div id="modalItem" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); z-index: 999; max-width: 500px; width: 95%; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #ddd;">
            <h2 style="margin: 0;"><i class="fas fa-box"></i> Adicionar Item</h2>
            <button type="button" onclick="fecharModalItem()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formAdicionarItem" onsubmit="enviarItem(event)" style="padding: 20px;">
            <input type="hidden" id="idVendaItem">

            <div class="form-group">
                <label for="codigoProdutoItem">Codigo do Produto *</label>
                <input
                    type="text"
                    id="codigoProdutoItem"
                    placeholder="Ex: PROD001"
                    required>
                <small class="form-error" id="erroCodigo"></small>
            </div>

            <div class="form-group">
                <label for="descricaoItem">Descricao do Item *</label>
                <input
                    type="text"
                    id="descricaoItem"
                    placeholder="Ex: Colar de Ouro"
                    required>
                <small class="form-error" id="erroDescricao"></small>
            </div>

            <div class="form-group">
                <label for="quantidadeItem">Quantidade *</label>
                <input
                    type="number"
                    id="quantidadeItem"
                    min="1"
                    step="1"
                    value="1"
                    required
                    onchange="calcularValorTotal()">
                <small class="form-error" id="erroQuantidade"></small>
            </div>

            <div class="form-group">
                <label for="valorUnitarioItem">Valor Unitario *</label>
                <input
                    type="number"
                    id="valorUnitarioItem"
                    min="0.01"
                    step="0.01"
                    required
                    onchange="calcularValorTotal()">
                <small class="form-error" id="erroValor"></small>
            </div>

            <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <p style="margin: 0;">Valor Total: <strong id="valorTotalItem">R$ 0,00</strong></p>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #ddd;">
                <button type="button" onclick="fecharModalItem()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" id="btnSubmitItem" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar Item
                </button>
            </div>
        </form>
    </div>

    <script src="<?php echo getAssetUrl('js/mascaras.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('js/validacoes.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('js/app.js'); ?>"></script>

    <script>
        let clienteSelecionado = null;
        let timerBusca = null;

        // ========== BUSCA DE CLIENTES ==========
        document.getElementById('searchCliente').addEventListener('input', function() {
            const termo = this.value.trim();
            const container = document.getElementById('clienteSugestoes');

            clearTimeout(timerBusca);

            if (termo.length < 2) {
                container.classList.remove('show');
                return;
            }

            timerBusca = setTimeout(() => {
                fetch('<?php echo API_URL_RELATIVE; ?>clientes/pesquisa.php?termo=' + encodeURIComponent(termo))
                    .then(response => {
                        if (!response.ok) throw new Error('Erro na busca');
                        return response.json();
                    })
                    .then(clientes => {
                        container.innerHTML = '';

                        if (clientes.length === 0) {
                            container.innerHTML = '<div style="padding: 12px 15px; color: #999;"><i class="fas fa-search"></i> Nenhum cliente encontrado</div>';
                        } else {
                            clientes.forEach(cliente => {
                                const div = document.createElement('div');
                                div.className = 'sugestao-item';
                                div.innerHTML = `
                                    <strong>${escapeHtml(cliente.nome)}</strong>
                                    ${cliente.telefone ? '<br><small style="color: #999;">' + escapeHtml(cliente.telefone) + '</small>' : ''}
                                `;
                                div.onclick = function(e) {
                                    e.stopPropagation();
                                    selecionarCliente(cliente);
                                };
                                container.appendChild(div);
                            });
                        }

                        container.classList.add('show');
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        container.innerHTML = '<div style="padding: 12px 15px; color: #cc0000;"><i class="fas fa-exclamation-circle"></i> Erro ao buscar clientes</div>';
                        container.classList.add('show');
                    });
            }, 300);
        });

        function selecionarCliente(cliente) {
            document.getElementById('searchCliente').value = cliente.nome;
            document.getElementById('idCliente').value = cliente.id_cliente;
            clienteSelecionado = cliente;
            document.getElementById('clienteSugestoes').classList.remove('show');
            document.getElementById('erroCliente').textContent = '';

            // Mostrar cliente selecionado
            document.getElementById('clienteSelecionadoDiv').style.display = 'block';
            document.getElementById('clienteSelecionadoNome').textContent = cliente.nome;
            document.getElementById('clienteSelecionadoTelefone').textContent = cliente.telefone || '';
        }

        function limparClienteSelecionado() {
            document.getElementById('searchCliente').value = '';
            document.getElementById('idCliente').value = '';
            document.getElementById('clienteSelecionadoDiv').style.display = 'none';
            clienteSelecionado = null;
            document.getElementById('searchCliente').focus();
        }

        // Fechar sugestoes ao clicar fora
        document.addEventListener('click', function(event) {
            const container = document.getElementById('clienteSugestoes');
            const searchInput = document.getElementById('searchCliente');

            if (!container.contains(event.target) && event.target !== searchInput) {
                container.classList.remove('show');
            }
        });

        // ========== CRIAR NOVO CLIENTE ==========
        function executarCriarNovoCliente(event) {
            event.preventDefault();

            const nome = document.getElementById('novoClienteNome').value.trim();
            const email = document.getElementById('novoClienteEmail').value.trim();
            const telefone = document.getElementById('novoClienteTelefone').value.trim();
            const endereco = document.getElementById('novoClienteEndereco').value.trim();
            const cpf = document.getElementById('novoClienteCPF').value.trim();

            // Limpar erros
            document.getElementById('erroNomeCliente').textContent = '';
            document.getElementById('erroEmailCliente').textContent = '';
            document.getElementById('erroTelefoneCliente').textContent = '';

            // Validar nome
            if (!nome || nome.length < 3) {
                document.getElementById('erroNomeCliente').textContent = 'Nome deve ter pelo menos 3 caracteres';
                document.getElementById('novoClienteNome').focus();
                return false;
            }

            // Validar email se preenchido
            if (email && !email.includes('@')) {
                document.getElementById('erroEmailCliente').textContent = 'Email invalido';
                document.getElementById('novoClienteEmail').focus();
                return false;
            }

            const dados = {
                nome: nome,
                email: email,
                telefone: telefone,
                endereco: endereco,
                cpf_cnpj: cpf,
                observacoes: ''
            };

            const btn = document.getElementById('btnSalvarCliente');
            const btnText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            fetch('<?php echo API_URL_RELATIVE; ?>clientes/salvar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dados)
                })
                .then(response => {
                    if (!response.ok) throw new Error('Erro na resposta do servidor');
                    return response.json();
                })
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = btnText;

                    if (data.sucesso) {
                        // Preencher cliente automaticamente
                        document.getElementById('searchCliente').value = nome;
                        document.getElementById('idCliente').value = data.id_cliente;
                        clienteSelecionado = {
                            id_cliente: data.id_cliente,
                            nome: nome,
                            email: email,
                            telefone: telefone
                        };

                        // Mostrar cliente selecionado
                        document.getElementById('clienteSelecionadoDiv').style.display = 'block';
                        document.getElementById('clienteSelecionadoNome').textContent = nome;
                        document.getElementById('clienteSelecionadoTelefone').textContent = telefone || '';

                        // Mostrar mensagem de sucesso
                        document.getElementById('sucessoNovoCliente').style.display = 'block';

                        // Limpar formulario
                        document.getElementById('formNovoCliente').reset();

                        // Scroll para o botao de criar venda
                        setTimeout(() => {
                            document.getElementById('btnCriarVenda').scrollIntoView({
                                behavior: 'smooth'
                            });
                        }, 300);
                    } else {
                        alert('Erro: ' + (data.mensagem || 'Erro ao criar cliente'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    btn.disabled = false;
                    btn.innerHTML = btnText;
                    alert('Erro ao criar cliente. Verifique a conexao.');
                });

            return false;
        }

        // ========== CRIAR VENDA ==========
        function criarVendaAjax(event) {
            event.preventDefault();

            const idCliente = document.getElementById('idCliente').value;
            const observacoes = document.getElementById('observacoes').value.trim();
            const quantidadeParcelas = parseInt(document.getElementById('quantidadeParcelas').value) || 1;
            const erroCliente = document.getElementById('erroCliente');

            if (!idCliente) {
                erroCliente.textContent = 'Selecione um cliente da lista ou crie um novo';
                document.getElementById('searchCliente').focus();
                return false;
            }

            erroCliente.textContent = '';

            const dados = {
                id_cliente: parseInt(idCliente),
                observacoes: observacoes,
                quantidade_parcelas: quantidadeParcelas,
                itens: []
            };

            console.log('Enviando dados da venda:', dados);

            const btn = document.getElementById('btnCriarVenda');
            const btnText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando venda...';

            fetch('<?php echo API_URL_RELATIVE; ?>vendas/salvar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dados)
                })
                .then(response => {
                    console.log('Status da resposta:', response.status);
                    
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                    }
                    
                    return response.json().then(data => {
                        if (!data) {
                            throw new Error('Resposta vazia do servidor');
                        }
                        return data;
                    });
                })
                .then(data => {
                    console.log('Resposta recebida:', data);
                    btn.disabled = false;
                    btn.innerHTML = btnText;

                    if (data.sucesso && data.id_venda) {
                        FeedbackVisual.mostrarNotificacao('sucesso', 'Venda criada com sucesso!');
                        setTimeout(() => {
                            window.location.href = '<?php echo PUBLIC_URL_RELATIVE; ?>nova_venda.php?id=' + data.id_venda;
                        }, 1500);
                    } else {
                        FeedbackVisual.mostrarNotificacao('erro', data.mensagem || 'Erro ao criar venda');
                    }
                })
                .catch(error => {
                    console.error('Erro completo:', error);
                    btn.disabled = false;
                    btn.innerHTML = btnText;
                    FeedbackVisual.mostrarNotificacao('erro', 'Erro ao criar venda: ' + error.message);
                });

            return false;
        }

        // ========== MODAL ADICIONAR ITEM ==========
        function abrirModalAdicionarItem(idVenda) {
            document.getElementById('modalItem').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
            document.getElementById('idVendaItem').value = idVenda;
            document.getElementById('formAdicionarItem').reset();
            document.getElementById('codigoProdutoItem').focus();
            document.body.style.overflow = 'hidden';
        }

        function fecharModalItem() {
            document.getElementById('modalItem').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
            document.body.style.overflow = 'auto';
            limparErrosModal();
        }

        function limparErrosModal() {
            document.getElementById('erroCodigo').textContent = '';
            document.getElementById('erroDescricao').textContent = '';
            document.getElementById('erroQuantidade').textContent = '';
            document.getElementById('erroValor').textContent = '';
        }

        function calcularValorTotal() {
            const quantidade = parseFloat(document.getElementById('quantidadeItem').value) || 0;
            const valorUnitario = parseFloat(document.getElementById('valorUnitarioItem').value) || 0;
            const total = quantidade * valorUnitario;

            document.getElementById('valorTotalItem').textContent = 'R$ ' + total.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function enviarItem(event) {
            event.preventDefault();

            const idVenda = document.getElementById('idVendaItem').value;
            const codigo = document.getElementById('codigoProdutoItem').value.trim();
            const descricao = document.getElementById('descricaoItem').value.trim();
            const quantidade = parseInt(document.getElementById('quantidadeItem').value);
            const valorUnitario = parseFloat(document.getElementById('valorUnitarioItem').value);

            limparErrosModal();

            let temErro = false;

            if (!codigo || codigo.length < 2) {
                document.getElementById('erroCodigo').textContent = 'Codigo deve ter pelo menos 2 caracteres';
                temErro = true;
            }

            if (!descricao || descricao.length < 3) {
                document.getElementById('erroDescricao').textContent = 'Descricao deve ter pelo menos 3 caracteres';
                temErro = true;
            }

            if (!quantidade || quantidade < 1) {
                document.getElementById('erroQuantidade').textContent = 'Quantidade deve ser maior que 0';
                temErro = true;
            }

            if (!valorUnitario || valorUnitario <= 0) {
                document.getElementById('erroValor').textContent = 'Valor deve ser maior que 0';
                temErro = true;
            }

            if (temErro) return false;

            const dados = {
                id_venda: parseInt(idVenda),
                codigo_produto: codigo,
                descricao: descricao,
                quantidade: quantidade,
                valor_unitario: valorUnitario
            };

            const btn = document.getElementById('btnSubmitItem');
            const btnText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';

            fetch('<?php echo API_URL_RELATIVE; ?>vendas/adicionar_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dados)
                })
                .then(response => {
                    if (!response.ok) throw new Error('Erro na resposta');
                    return response.json();
                })
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = btnText;

                    if (data.sucesso) {
                        FeedbackVisual.mostrarNotificacao('sucesso', 'Item adicionado com sucesso!');
                        fecharModalItem();
                        location.reload();
                    } else {
                        alert('Erro: ' + (data.mensagem || 'Erro ao adicionar item'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    btn.disabled = false;
                    btn.innerHTML = btnText;
                    alert('Erro ao adicionar item: ' + error.message);
                });

            return false;
        }

        function confirmarRemoverItem(idItem) {
            if (confirm('Tem certeza que deseja remover este item?')) {
                fetch('<?php echo API_URL_RELATIVE; ?>vendas/remover_item.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_item: idItem
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            FeedbackVisual.mostrarNotificacao('sucesso', 'Item removido com sucesso!');
                            location.reload();
                        } else {
                            alert('Erro: ' + (data.mensagem || 'Erro ao remover item'));
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao remover item: ' + error.message);
                    });
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Fechar modal ao pressionar ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                fecharModalItem();
            }
        });
    </script>
</body>

</html>