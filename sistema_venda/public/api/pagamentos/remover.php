<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/config.php';
require_once '../../../config/auth.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Pagamento.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Metodo nao permitido']);
    exit;
}

$input = file_get_contents('php://input');
$dados = json_decode($input, true);

if (!isset($dados['id_pagamento'])) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID do pagamento eh obrigatorio']);
    exit;
}

$pagamento = new Pagamento();

try {
    $pagamento->deletar(intval($dados['id_pagamento']));

    http_response_code(200);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Pagamento removido com sucesso'
    ]);
} catch (Exception $e) {
    error_log('Erro ao remover pagamento: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>