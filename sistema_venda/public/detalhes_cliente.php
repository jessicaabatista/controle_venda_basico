<?php
require_once '../config/auth.php';
require_once '../classes/Database.php';
require_once '../classes/Cliente.php';
require_once '../classes/Venda.php';

$cliente_obj = new Cliente();
$venda_obj = new Venda();

$idCliente = $_GET['id'] ?? 0;
if (!$idCliente) {
    header('Location: clientes.php');
    exit;
}

$cliente = $cliente_obj->obter($idCliente);
if (!$cliente) {
    header('Location: clientes.php');
    exit;
}

$historico = $cliente_obj->obterHistorico($idCliente);

// Calcular estatísticas
$totalGasto = 0;
$totalPago = 0;
$saldoDevendo = 0;

foreach ($historico as $venda) {
    $totalGasto += $venda['valor_total'];
    $totalPago += $venda['valor_pago'];
    $saldoDevendo += ($venda['valor_total'] - $venda['valor_pago']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Cliente - Sistema de Semi-Joias</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cliente-header {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .cliente-info {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .cliente-info h1 {
            margin-bottom: 20px;
            color: #333;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
        }

        .info-value {
            color: #333;
        }

        .cliente-stats {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #999;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-card.warning .stat-value {
            color: #ffd93d;
        }

        .stat-card.danger .stat-value {
            color: #ff6b6b;
        }

        .actions-bar {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .cliente-header {
                grid-template-columns: 1fr;
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
            <a href="nova_venda.php" class="nav-link">+ Nova Venda</a>
            <a href="clientes.php" class="nav-link active">Clientes</a>
            <div class="nav-user">
                <a href="logout.php" class="nav-logout">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="breadcrumb">
            <a href="clientes.php">Clientes</a> > <?php echo htmlspecialchars($cliente['nome']); ?>
        </div>

        <div class="cliente-header">
            <div class="cliente-info">
                <h1><?php echo htmlspecialchars($cliente['nome']); ?></h1>

                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['email'] ?? '-'); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Telefone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['telefone'] ?? '-'); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Endereço:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['endereco'] ?? '-'); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">CPF/CNPJ:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['cpf_cnpj'] ?? '-'); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Cliente Desde:</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($cliente['data_cadastro'])); ?></span>
                </div>

                <?php if ($cliente['observacoes']): ?>
                <div class="info-row">
                    <span class="info-label">Observações:</span>
                </div>
                <p><?php echo nl2br(htmlspecialchars($cliente['observacoes'])); ?></p>
                <?php endif; ?>

                <div class="actions-bar">
                    <button onclick="abrirModalEditarCliente(<?php echo $idCliente; ?>)" class="btn btn-secondary">Editar</button>
                    <a href="nova_venda.php?cliente=<?php echo $idCliente; ?>" class="btn btn-primary">+ Nova Venda</a>
                </div>
            </div>

            <div class="cliente-stats">
                <div class="stat-card">
                    <h3>Total Gasto</h3>
                    <p class="stat-value">R$ <?php echo number_format($totalGasto, 2, ',', '.'); ?></p>
                </div>

                <div class="stat-card">
                    <h3>Total Pago</h3>
                    <p class="stat-value">R$ <?php echo number_format($totalPago, 2, ',', '.'); ?></p>
                </div>

                <div class="stat-card <?php echo $saldoDevendo > 0 ? 'danger' : ''; ?>">
                    <h3>Saldo Devendo</h3>
                    <p class="stat-value">R$ <?php echo number_format($saldoDevendo, 2, ',', '.'); ?></p>
                </div>

                <div class="stat-card">
                    <h3>Total de Vendas</h3>
                    <p class="stat-value"><?php echo count($historico); ?></p>
                </div>
            </div>
        </div>

        <!-- Histórico de Vendas -->
        <div class="card">
            <h2>Histórico de Vendas</h2>
            <?php if (empty($historico)): ?>
                <p style="text-align: center; color: #999; padding: 30px;">Nenhuma venda registrada para este cliente</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Venda</th>
                        <th>Data</th>
                        <th>Itens</th>
                        <th>Valor Total</th>
                        <th>Valor Pago</th>
                        <th>Saldo</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historico as $venda): ?>
                    <tr>
                        <td>#<?php echo $venda['id_venda']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($venda['data_venda'])); ?></td>
                        <td><?php echo $venda['qtd_itens'] ?? 0; ?></td>
                        <td>R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($venda['valor_pago'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($venda['valor_total'] - $venda['valor_pago'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="status-badge <?php echo $venda['status_geral']; ?>">
                                <?php
                                $statusMap = [
                                    'aberta' => 'Aberta',
                                    'parcial' => 'Parcial',
                                    'paga' => 'Paga',
                                    'cancelada' => 'Cancelada'
                                ];
                                echo $statusMap[$venda['status_geral']] ?? $venda['status_geral'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <a href="detalhes_venda.php?id=<?php echo $venda['id_venda']; ?>" class="btn-link">Ver Detalhes</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="card-actions">
            <a href="clientes.php" class="btn btn-secondary">Voltar</a>
        </div>
    </div>

    <!-- Modal Editar Cliente -->
    <div id="modalCliente" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Cliente</h2>
                <span class="modal-close" onclick="fecharModalCliente()">&times;</span>
            </div>

            <form id="formCliente" class="modal-body">
                <input type="hidden" id="idCliente" value="<?php echo $idCliente; ?>">

                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" id="nomeCliente" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="emailCliente" value="<?php echo htmlspecialchars($cliente['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" id="telefoneCliente" value="<?php echo htmlspecialchars($cliente['telefone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Endereço</label>
                    <input type="text" id="enderecoCliente" value="<?php echo htmlspecialchars($cliente['endereco'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>CPF/CNPJ</label>
                    <input type="text" id="cpfCnpjCliente" value="<?php echo htmlspecialchars($cliente['cpf_cnpj'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea id="obsCliente" rows="3"><?php echo htmlspecialchars($cliente['observacoes'] ?? ''); ?></textarea>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" class="btn btn-secondary" onclick="fecharModalCliente()">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="confirmarDeletarCliente(<?php echo $idCliente; ?>)">Deletar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalEditarCliente(idCliente) {
            document.getElementById('modalCliente').classList.add('show');
        }

        function fecharModalCliente() {
            document.getElementById('modalCliente').classList.remove('show');
        }

        document.getElementById('formCliente').addEventListener('submit', function(e) {
            e.preventDefault();

            const dados = {
                id_cliente: document.getElementById('idCliente').value,
                nome: document.getElementById('nomeCliente').value,
                email: document.getElementById('emailCliente').value,
                telefone: document.getElementById('telefoneCliente').value,
                endereco: document.getElementById('enderecoCliente').value,
                cpf_cnpj: document.getElementById('cpfCnpjCliente').value,
                observacoes: document.getElementById('obsCliente').value
            };

            fetch('../api/clientes/salvar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    alert('Cliente atualizado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.mensagem);
                }
            });
        });

        function confirmarDeletarCliente(idCliente) {
            if (confirm('Tem certeza que deseja deletar este cliente? Esta ação não pode ser desfeita.')) {
                fetch('../api/clientes/deletar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_cliente: idCliente })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        alert('Cliente deletado com sucesso!');
                        window.location.href = 'clientes.php';
                    } else {
                        alert('Erro: ' + data.mensagem);
                    }
                });
            }
        }

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('modalCliente');
            if (event.target === modal) {
                fecharModalCliente();
            }
        });
    </script>

    <link rel="stylesheet" href="assets/css/style.css">
</body>
</html>