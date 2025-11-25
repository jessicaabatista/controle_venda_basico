<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/config.php';
require_once '../../../config/auth.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Venda.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $dados = json_decode($input, true);

    if (!$dados || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'JSON inválido']);
        exit;
    }

    // Validar campos obrigatórios
    $required = ['id_venda', 'codigo_produto', 'descricao', 'quantidade', 'valor_unitario'];
    foreach ($required as $field) {
        if (!isset($dados[$field]) || (is_string($dados[$field]) && empty(trim($dados[$field])))) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Campo obrigatório: ' . $field]);
            exit;
        }
    }

    $venda = new Venda();

    $idItem = $venda->adicionarItem(
        intval($dados['id_venda']),
        trim($dados['codigo_produto']),
        trim($dados['descricao']),
        intval($dados['quantidade']),
        floatval($dados['valor_unitario'])
    );

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Item adicionado com sucesso',
        'id_item' => $idItem
    ]);
} catch (Exception $e) {
    error_log('Erro ao adicionar item: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>