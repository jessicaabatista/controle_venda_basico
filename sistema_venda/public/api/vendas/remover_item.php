<?php
header('Content-Type: application/json');
require_once '../../config/auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Venda.php';

$dados = json_decode(file_get_contents('php://input'), true);
$idItem = $dados['id_item'] ?? 0;

if (!$idItem) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID do item inválido'
    ]);
    exit;
}

$venda_obj = new Venda();

try {
    $venda_obj->removerItem($idItem);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Item removido com sucesso'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>