<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/config.php';
require_once '../../../config/auth.php';
require_once '../../../classes/Database.php';
require_once '../../../classes/Venda.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Metodo nao permitido']);
    exit;
}

$input = file_get_contents('php://input');
$dados = json_decode($input, true);

if (!$dados || !isset($dados['id_venda'])) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID da venda eh obrigatorio']);
    exit;
}

$venda = new Venda();

try {
    $venda->atualizar(
        $dados['id_venda'],
        $dados['observacoes'] ?? '',
        $dados['status'] ?? null
    );

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Venda atualizada com sucesso'
    ]);
} catch (Exception $e) {
    error_log('Erro ao atualizar venda: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>