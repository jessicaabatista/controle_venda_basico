<?php
class Cliente {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function criar($nome, $email = '', $telefone = '', $endereco = '', $cpfCnpj = '', $obs = '') {
        // Validações básicas
        if (empty($nome) || strlen(trim($nome)) < 3) {
            throw new Exception('Nome deve ter pelo menos 3 caracteres');
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }

        // Verificar duplicata de email
        if (!empty($email)) {
            $query = "SELECT id_cliente FROM clientes WHERE email = ? AND ativo = 1";
            $stmt = $this->db->execute($query, 's', [$email]);
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception('Este email já está registrado');
            }
        }

        $query = "INSERT INTO clientes (nome, email, telefone, endereco, cpf_cnpj, observacoes, ativo, data_criacao)
                 VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";

        try {
            $stmt = $this->db->execute($query, 'ssssss', [
                htmlspecialchars(trim($nome), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(trim($email), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(trim($telefone), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(trim($endereco), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(trim($cpfCnpj), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(trim($obs), ENT_QUOTES, 'UTF-8')
            ]);

            return $this->db->getConnection()->insert_id;
        } catch (Exception $e) {
            throw new Exception('Erro ao criar cliente: ' . $e->getMessage());
        }
    }

    public function listar() {
        $query = "SELECT c.*, COUNT(v.id_venda) as total_vendas, SUM(v.valor_total) as total_gasto
                 FROM clientes c
                 LEFT JOIN vendas v ON c.id_cliente = v.id_cliente
                 WHERE c.ativo = 1
                 GROUP BY c.id_cliente
                 ORDER BY c.nome";

        return $this->db->select($query);
    }

    public function obter($idCliente) {
        $query = "SELECT * FROM clientes WHERE id_cliente = ? AND ativo = 1";
        $stmt = $this->db->execute($query, 'i', [$idCliente]);
        return $stmt->get_result()->fetch_assoc();
    }

    public function atualizar($idCliente, $nome, $email = '', $telefone = '', $endereco = '', $cpfCnpj = '', $obs = '') {
        if (empty($nome) || strlen(trim($nome)) < 3) {
            throw new Exception('Nome deve ter pelo menos 3 caracteres');
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }

        // Verificar duplicata de email (excluindo o cliente atual)
        if (!empty($email)) {
            $query = "SELECT id_cliente FROM clientes WHERE email = ? AND id_cliente != ? AND ativo = 1";
            $stmt = $this->db->execute($query, 'si', [$email, $idCliente]);
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception('Este email já está registrado para outro cliente');
            }
        }

        $query = "UPDATE clientes SET nome = ?, email = ?, telefone = ?, endereco = ?, cpf_cnpj = ?, observacoes = ?
                 WHERE id_cliente = ?";

        $this->db->execute($query, 'ssssssi', [
            htmlspecialchars(trim($nome), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(trim($email), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(trim($telefone), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(trim($endereco), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(trim($cpfCnpj), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(trim($obs), ENT_QUOTES, 'UTF-8'),
            $idCliente
        ]);
    }

    public function deletar($idCliente) {
        $query = "UPDATE clientes SET ativo = 0 WHERE id_cliente = ?";
        $this->db->execute($query, 'i', [$idCliente]);
    }

    public function obterHistorico($idCliente) {
        $query = "SELECT v.*, COUNT(i.id_item) as qtd_itens
                 FROM vendas v
                 LEFT JOIN itens_venda i ON v.id_venda = i.id_venda
                 WHERE v.id_cliente = ? AND v.status_geral != 'cancelada'
                 GROUP BY v.id_venda
                 ORDER BY v.data_venda DESC";

        $stmt = $this->db->execute($query, 'i', [$idCliente]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>