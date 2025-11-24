<?php
require_once '../config/auth.php';
require_once '../classes/Database.php';
require_once '../classes/Cliente.php';

$cliente_obj = new Cliente();
$clientes = $cliente_obj->listar();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Sistema de Semi-Joias</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
        <div class="list-header">
            <h1>Gestão de Clientes</h1>
            <button onclick="abrirModalNovoCliente()" class="btn btn-primary">+ Novo Cliente</button>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Total de Vendas</th>
                        <th>Valor Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                        <td><?php echo $cliente['total_vendas'] ?? 0; ?></td>
                        <td>R$ <?php echo number_format($cliente['total_gasto'] ?? 0, 2, ',', '.'); ?></td>
                        <td>
                            <a href="detalhes_cliente.php?id=<?php echo $cliente['id_cliente']; ?>" class="btn-link">Ver</a>
                            <button onclick="abrirModalEditarCliente(<?php echo $cliente['id_cliente']; ?>)" class="btn-link">Editar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Novo Cliente -->
    <div id="modalCliente" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Novo Cliente</h2>
                <span class="modal-close" onclick="fecharModalCliente()">&times;</span>
            </div>

            <form id="formCliente" class="modal-body">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" id="nomeCliente" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="emailCliente">
                </div>

                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" id="telefoneCliente">
                </div>

                <div class="form-group">
                    <label>Endereço</label>
                    <input type="text" id="enderecoCliente">
                </div>

                <div class="form-group">
                    <label>CPF/CNPJ</label>
                    <input type="text" id="cpfCnpjCliente">
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <textarea id="obsCliente" rows="3"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" class="btn btn-secondary" onclick="fecharModalCliente()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalNovoCliente() {
            document.getElementById('modalCliente').classList.add('show');
            document.getElementById('formCliente').reset();
        }

        function fecharModalCliente() {
            document.getElementById('modalCliente').classList.remove('show');
        }

        document.getElementById('formCliente').addEventListener('submit', function(e) {
            e.preventDefault();

            const dados = {
                nome: document.getElementById('nomeCliente').value,
                email: document.getElementById('emailCliente').value,
                telefone: document.getElementById('telefoneCliente').value,
                endereco: document.getElementById('enderecoCliente').value,
                cpf_cnpj: document.getElementById('cpfCnpjCliente').value,
                observacoes: document.getElementById('obsCliente').value
            };

            fetch('api/clientes/salvar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    alert('Cliente salvo com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.mensagem);
                }
            });
        });

        // Fechar modal ao clicar fora
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('modalCliente');
            if (event.target === modal) {
                fecharModalCliente();
            }
        });
    </script>
</body>
</html>