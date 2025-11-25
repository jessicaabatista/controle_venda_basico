<?php
require_once '../config/config.php';
require_once '../config/auth.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Cliente.php';

$cliente_obj = new Cliente();
$clientes = $cliente_obj->listar();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="list-header">
            <h1><i class="fas fa-users"></i> Gestao de Clientes</h1>
            <button onclick="abrirModalNovoCliente()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Cliente
            </button>
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
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                            <td><?php echo $cliente['total_vendas'] ?? 0; ?></td>
                            <td><?php echo formatarMoeda($cliente['total_gasto'] ?? 0); ?></td>
                            <td style="display: flex; gap: 5px;">
                                <a href="<?php echo PUBLIC_URL; ?>detalhes_cliente.php?id=<?php echo $cliente['id_cliente']; ?>" class="btn-link"><i class="fas fa-eye"></i></a>
                                <!-- <button onclick="abrirModalEditarCliente(<?php //echo $cliente['id_cliente']; ?>)" class="btn-link"><i class="fas fa-edit"></i></button> -->
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
                <h2><i class="fas fa-user-plus"></i> Novo Cliente</h2>
                <button class="modal-close" onclick="fecharModalCliente()">
                    <i class="fas fa-times"></i>
                </button>
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
                    <input type="text" id="telefoneCliente" class="input-telefone" placeholder="(11) 9 9999-9999">
                </div>

                <div class="form-group">
                    <label>Endereco</label>
                    <input type="text" id="enderecoCliente">
                </div>

                <div class="form-group">
                    <label>CPF/CNPJ</label>
                    <input type="text" id="cpfCnpjCliente" class="input-cpf-cnpj" placeholder="000.000.000-00">
                </div>

                <div class="form-group">
                    <label>Observacoes</label>
                    <textarea id="obsCliente" rows="3"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                    <button type="button" class="btn btn-secondary" onclick="fecharModalCliente()"><i class="fas fa-times"></i> Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?php echo ASSETS_URL; ?>js/mascaras.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/validacoes.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/app.js"></script>
    
    <script>
        function formatarMoeda(valor) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor);
        }

        function abrirModalNovoCliente() {
            document.getElementById('modalCliente').classList.add('show');
            document.getElementById('formCliente').reset();
            document.getElementById('nomeCliente').focus();
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

            FeedbackVisual.mostrarLoading(this.querySelector('button[type="submit"]'));

            fetch('<?php echo API_URL; ?>clientes/salvar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dados)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        FeedbackVisual.mostrarNotificacao('sucesso', 'Cliente salvo com sucesso!');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        FeedbackVisual.mostrarNotificacao('erro', data.mensagem || 'Erro ao salvar cliente');
                    }
                })
                .catch(error => {
                    FeedbackVisual.mostrarNotificacao('erro', 'Erro ao salvar cliente');
                    console.error('Erro:', error);
                });
        });

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('modalCliente');
            if (event.target === modal) {
                fecharModalCliente();
            }
        });
    </script>
</body>

</html>