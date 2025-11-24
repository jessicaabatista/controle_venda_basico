<?php
class Venda {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function criar($idCliente, $obs = '') {
        $query = "INSERT INTO vendas (id_cliente, id_usuario, data_venda, status_geral, observacoes_pagamento, valor_total, valor_pago, saldo_devedor)
                 VALUES (?, ?, NOW(), 'aberta', ?, 0, 0, 0)";

        $stmt = $this->db->execute($query, 'iis', [$idCliente, $_SESSION['id_usuario'], $obs]);
        return $this->db->getConnection()->insert_id;
    }

    public function adicionarItem($idVenda, $codigoProduto, $descricao, $quantidade, $valorUnitario) {
        $valorTotal = $quantidade * $valorUnitario;

        $query = "INSERT INTO itens_venda (id_venda, codigo_produto, descricao, quantidade, valor_unitario, valor_total, status_pagamento, saldo_item)
                 VALUES (?, ?, ?, ?, ?, ?, 'pendente', ?)";

        $stmt = $this->db->execute($query, 'isidddd', [
            $idVenda,
            $codigoProduto,
            $descricao,
            $quantidade,
            $valorUnitario,
            $valorTotal,
            $valorTotal
        ]);

        // Atualizar totais da venda
        $this->atualizarTotaisVenda($idVenda);

        return $this->db->getConnection()->insert_id;
    }

    public function atualizarTotaisVenda($idVenda) {
        // Calcular totais
        $query = "SELECT SUM(valor_total) as valor_total, SUM(saldo_item) as saldo_devedor FROM itens_venda WHERE id_venda = ?";
        $stmt = $this->db->execute($query, 'i', [$idVenda]);
        $totais = $stmt->get_result()->fetch_assoc();

        $valorTotal = $totais['valor_total'] ?? 0;
        $saldoDevedor = $totais['saldo_devedor'] ?? 0;

        // Determinar status
        $status = 'aberta';
        if ($saldoDevedor == 0 && $valorTotal > 0) {
            $status = 'paga';
        } elseif ($saldoDevedor < $valorTotal && $saldoDevedor > 0) {
            $status = 'parcial';
        }

        $query = "UPDATE vendas SET valor_total = ?, saldo_devedor = ?, status_geral = ? WHERE id_venda = ?";
        $this->db->execute($query, 'ddsi', [$valorTotal, $saldoDevedor, $status, $idVenda]);
    }

    public function listar($filtros = []) {
        $query = "SELECT v.*, c.nome as nome_cliente, c.email, c.telefone, COUNT(i.id_item) as qtd_itens
                 FROM vendas v
                 JOIN clientes c ON v.id_cliente = c.id_cliente
                 LEFT JOIN itens_venda i ON v.id_venda = i.id_venda
                 WHERE 1=1";

        $params = [];
        $tipos = '';

        if (!empty($filtros['status'])) {
            $query .= " AND v.status_geral = ?";
            $tipos .= 's';
            $params[] = $filtros['status'];
        }

        if (!empty($filtros['cliente'])) {
            $query .= " AND c.nome LIKE ?";
            $tipos .= 's';
            $params[] = '%' . $filtros['cliente'] . '%';
        }

        if (!empty($filtros['data_inicio'])) {
            $query .= " AND DATE(v.data_venda) >= ?";
            $tipos .= 's';
            $params[] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $query .= " AND DATE(v.data_venda) <= ?";
            $tipos .= 's';
            $params[] = $filtros['data_fim'];
        }

        $query .= " GROUP BY v.id_venda ORDER BY v.data_venda DESC";

        if (!empty($tipos)) {
            $stmt = $this->db->execute($query, $tipos, $params);
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return $this->db->select($query);
    }

    public function obter($idVenda) {
        $query = "SELECT v.*, c.nome as nome_cliente, c.email, c.telefone
                 FROM vendas v
                 JOIN clientes c ON v.id_cliente = c.id_cliente
                 WHERE v.id_venda = ?";

        $stmt = $this->db->execute($query, 'i', [$idVenda]);
        return $stmt->get_result()->fetch_assoc();
    }

    public function obterItens($idVenda) {
        $query = "SELECT * FROM itens_venda WHERE id_venda = ? ORDER BY id_item";
        $stmt = $this->db->execute($query, 'i', [$idVenda]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function removerItem($idItem) {
        $query = "SELECT id_venda FROM itens_venda WHERE id_item = ?";
        $stmt = $this->db->execute($query, 'i', [$idItem]);
        $resultado = $stmt->get_result()->fetch_assoc();
        $idVenda = $resultado['id_venda'];

        $query = "DELETE FROM itens_venda WHERE id_item = ?";
        $this->db->execute($query, 'i', [$idItem]);

        $this->atualizarTotaisVenda($idVenda);

        return true;
    }

    public function atualizar($idVenda, $obs = '') {
        $query = "UPDATE vendas SET observacoes_pagamento = ? WHERE id_venda = ?";
        $this->db->execute($query, 'si', [$obs, $idVenda]);
    }

    public function cancelar($idVenda) {
        $query = "UPDATE vendas SET status_geral = 'cancelada' WHERE id_venda = ?";
        $this->db->execute($query, 'i', [$idVenda]);
    }
}
?>
