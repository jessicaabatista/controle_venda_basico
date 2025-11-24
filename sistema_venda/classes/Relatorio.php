<?php
class Relatorio {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function fluxoCaixa($dataInicio, $dataFim) {
        $query = "SELECT DATE(fm.data_pagamento) as data, SUM(fm.valor_pago) as valor_pago, COUNT(*) as qtd_movimentacoes
                 FROM financeiro_movimentacoes fm
                 WHERE DATE(fm.data_pagamento) BETWEEN ? AND ?
                 GROUP BY DATE(fm.data_pagamento)
                 ORDER BY data";

        $stmt = $this->db->execute($query, 'ss', [$dataInicio, $dataFim]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function desempenhoVendas($dataInicio, $dataFim) {
        $query = "SELECT c.nome, COUNT(v.id_venda) as total_vendas, SUM(v.valor_total) as valor_total, SUM(v.valor_pago) as valor_pago
                 FROM vendas v
                 JOIN clientes c ON v.id_cliente = c.id_cliente
                 WHERE DATE(v.data_venda) BETWEEN ? AND ?
                 GROUP BY c.id_cliente
                 ORDER BY valor_total DESC";

        $stmt = $this->db->execute($query, 'ss', [$dataInicio, $dataFim]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function pendencias() {
        $query = "SELECT v.id_venda, c.nome, SUM(i.saldo_item) as saldo_total, v.data_venda
                 FROM vendas v
                 JOIN clientes c ON v.id_cliente = c.id_cliente
                 JOIN itens_venda i ON v.id_venda = i.id_venda
                 WHERE i.status_pagamento != 'pago'
                 GROUP BY v.id_venda
                 ORDER BY v.data_venda";

        return $this->db->select($query);
    }
}
?>