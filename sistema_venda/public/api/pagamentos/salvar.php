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

if (!$dados || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'JSON invalido']);
    exit;
}

// ✅ VALIDAR CAMPOS OBRIGATÓRIOS
$required = ['id_venda', 'valor', 'tipo_pagamento', 'data_pagamento'];
foreach ($required as $field) {
    if (empty($dados[$field])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Campo obrigatorio: ' . $field
        ]);
        exit;
    }
}

$pagamento = new Pagamento();
$idUsuario = intval($_SESSION['id_usuario'] ?? 1);

try {
    $idPagamento = $pagamento->criar(
        intval($dados['id_venda']),
        $idUsuario,
        floatval($dados['valor']),
        htmlspecialchars(trim($dados['tipo_pagamento']), ENT_QUOTES, 'UTF-8'),
        $dados['data_pagamento'],
        htmlspecialchars(trim($dados['observacoes'] ?? ''), ENT_QUOTES, 'UTF-8')
    );

    http_response_code(201);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Pagamento registrado com sucesso',
        'id_pagamento' => $idPagamento
    ]);
} catch (Exception $e) {
    error_log('Erro ao registrar pagamento: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>