<?php
header('Content-Type: application/json');
require_once '../../config/auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Financeiro.php';

$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados['id_venda'] || !$dados['quantidade_parcelas']) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos']);
    exit;
}

$financeiro = new Financeiro();

try {
    $financeiro->gerarParcelas($dados['id_venda'], $dados['quantidade_parcelas']);
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Parcelas geradas com sucesso'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>
