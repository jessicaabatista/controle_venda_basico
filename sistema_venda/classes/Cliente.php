<?php
class Cliente {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function criar($nome, $email = '', $telefone = '', $endereco = '', $cpfCnpj = '', $obs = '') {
        $query = "INSERT INTO clientes (nome, email, telefone, endereco, cpf_cnpj, observacoes)
                 VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->execute($query, 'ssssss', [
            $nome,
            $email,
            $telefone,
            $endereco,
            $cpfCnpj,
            $obs
        ]);

        return $this->db->getConnection()->insert_id;
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
        $query = "SELECT * FROM clientes WHERE id_cliente = ?";
        $stmt = $this->db->execute($query, 'i', [$idCliente]);
        return $stmt->get_result()->fetch_assoc();
    }

    public function atualizar($idCliente, $nome, $email = '', $telefone = '', $endereco = '', $cpfCnpj = '', $obs = '') {
        $query = "UPDATE clientes SET nome = ?, email = ?, telefone = ?, endereco = ?, cpf_cnpj = ?, observacoes = ?
                 WHERE id_cliente = ?";

        $this->db->execute($query, 'ssssssi', [
            $nome,
            $email,
            $telefone,
            $endereco,
            $cpfCnpj,
            $obs,
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
                 WHERE v.id_cliente = ?
                 GROUP BY v.id_venda
                 ORDER BY v.data_venda DESC";

        $stmt = $this->db->execute($query, 'i', [$idCliente]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>