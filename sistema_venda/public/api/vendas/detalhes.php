<?php
header('Content-Type: application/json');
require_once '../../classes/Database.php';
require_once '../../classes/Venda.php';
require_once '../../classes/Financeiro.php';

$idVenda = $_GET['id'] ?? 0;

$venda_obj = new Venda();
$financeiro = new Financeiro();

$venda = $venda_obj->obter($idVenda);
$itens = $venda_obj->obterItens($idVenda);
$parcelas = $financeiro->obterParcelas($idVenda);

echo json_encode([
    'venda' => $venda,
    'itens' => $itens,
    'parcelas' => $parcelas
]);
?>