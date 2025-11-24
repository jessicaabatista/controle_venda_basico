<?php
header('Content-Type: application/json');
require_once '../../config/auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Cliente.php';

$dados = json_decode(file_get_contents('php://input'), true);
$idCliente = $dados['id_cliente'] ?? 0;

if (!$idCliente) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID do cliente inválido'
    ]);
    exit;
}

$cliente_obj = new Cliente();

try {
    $cliente_obj->deletar($idCliente);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Cliente deletado com sucesso'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>