<?php
header('Content-Type: application/json');
require_once '../../config/auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Financeiro.php';

$financeiro = new Financeiro();
$dados = $financeiro->obterDashboard();

// Dados para gráfico de vendas do mês
$query = "SELECT DATE_FORMAT(data_venda, '%d') as dia, COUNT(*) as total, SUM(valor_total) as valor
         FROM vendas
         WHERE MONTH(data_venda) = MONTH(NOW()) AND YEAR(data_venda) = YEAR(NOW())
         GROUP BY DATE_FORMAT(data_venda, '%d')
         ORDER BY dia";

$db = new Database();
$vendas_por_dia = $db->select($query);

// Dados para gráfico de status de vendas
$query = "SELECT status_geral, COUNT(*) as total, SUM(valor_total) as valor
         FROM vendas
         WHERE MONTH(data_venda) = MONTH(NOW()) AND YEAR(data_venda) = YEAR(NOW())
         GROUP BY status_geral";

$status_vendas = $db->select($query);

// Top clientes
$query = "SELECT c.id_cliente, c.nome, COUNT(v.id_venda) as total_vendas, SUM(v.valor_total) as valor_total
         FROM clientes c
         LEFT JOIN vendas v ON c.id_cliente = v.id_cliente
         WHERE MONTH(v.data_venda) = MONTH(NOW()) AND YEAR(v.data_venda) = YEAR(NOW())
         GROUP BY c.id_cliente
         ORDER BY valor_total DESC
         LIMIT 10";

$top_clientes = $db->select($query);

echo json_encode([
    'vendas' => $dados['vendas'],
    'vencidas' => $dados['vencidas'],
    'proximas' => $dados['proximas'],
    'vendas_por_dia' => $vendas_por_dia,
    'status_vendas' => $status_vendas,
    'top_clientes' => $top_clientes
]);
?>
