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

if (!$dados || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'JSON invalido']);
    exit;
}

$required = ['id_item', 'id_venda', 'descricao', 'quantidade', 'valor_unitario'];
foreach ($required as $field) {
    if (empty($dados[$field])) {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Campo obrigatorio: ' . $field]);
        exit;
    }
}

$venda = new Venda();

try {
    $venda->atualizarItem(
        $dados['id_item'],
        $dados['descricao'],
        $dados['quantidade'],
        $dados['valor_unitario']
    );

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Item atualizado com sucesso'
    ]);
} catch (Exception $e) {
    error_log('Erro ao atualizar item: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>