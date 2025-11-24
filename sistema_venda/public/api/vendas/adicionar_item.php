<?php
header('Content-Type: application/json');
require_once '../../config/auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Venda.php';

$dados = json_decode(file_get_contents('php://input'), true);

$venda_obj = new Venda();

try {
    $venda_obj->adicionarItem(
        $dados['id_venda'],
        $dados['codigo_produto'],
        $dados['descricao'],
        $dados['quantidade'],
        $dados['valor_unitario']
    );

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Item adicionado com sucesso'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>
