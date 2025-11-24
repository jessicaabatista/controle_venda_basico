<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Financeiro.php';
require_once '../../classes/Venda.php';

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido'
    ]);
    exit;
}

// Obter e validar dados de entrada
$jsonInput = file_get_contents('php://input');
if ($jsonInput === false) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao ler dados da requisição'
    ]);
    exit;
}

$dados = json_decode($jsonInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'JSON inválido: ' . json_last_error_msg()
    ]);
    exit;
}

// Validar campos obrigatórios
$camposObrigatorios = ['id_venda', 'valor_pago', 'forma_pagamento'];
foreach ($camposObrigatorios as $campo) {
    if (!isset($dados[$campo]) || $dados[$campo] === '') {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => "Campo obrigatório ausente: $campo"
        ]);
        exit;
    }
}

// Sanitizar dados
$idVenda = filter_var($dados['id_venda'], FILTER_VALIDATE_INT);
$valorPago = filter_var($dados['valor_pago'], FILTER_VALIDATE_FLOAT);
$formaPagamento = htmlspecialchars(trim($dados['forma_pagamento']), ENT_QUOTES, 'UTF-8');
$observacoes = isset($dados['observacoes']) ? htmlspecialchars(trim($dados['observacoes']), ENT_QUOTES, 'UTF-8') : '';
$idParcela = isset($dados['id_parcela']) ? filter_var($dados['id_parcela'], FILTER_VALIDATE_INT) : null;
$idItem = isset($dados['id_item']) ? filter_var($dados['id_item'], FILTER_VALIDATE_INT) : null;

// Validar valores sanitizados
if ($idVenda === false || $idVenda <= 0) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID da venda inválido'
    ]);
    exit;
}

if ($valorPago === false || $valorPago <= 0) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Valor do pagamento inválido'
    ]);
    exit;
}

if ($idParcela !== null && ($idParcela === false || $idParcela <= 0)) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID da parcela inválido'
    ]);
    exit;
}

if ($idItem !== null && ($idItem === false || $idItem <= 0)) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID do item inválido'
    ]);
    exit;
}

$financeiro = new Financeiro();
$venda_obj = new Venda();

try {
    $resultado = $financeiro->registrarPagamento(
        $idVenda,
        $valorPago,
        $formaPagamento,
        $observacoes,
        $idParcela,
        $idItem
    );

    // Atualizar totais da venda
    $venda_obj->atualizarTotaisVenda($idVenda);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Pagamento registrado com sucesso',
        'id_pagamento' => $resultado
    ]);
} catch (Exception $e) {
    // Log do erro para debugging
    error_log("Erro ao processar pagamento: " . $e->getMessage());
    
    // Determinar código de status baseado no tipo de erro
    $mensagem = $e->getMessage();
    $statusCode = 400;
    
    if (strpos($mensagem, 'não encontrada') !== false || strpos($mensagem, 'não encontrado') !== false) {
        $statusCode = 404;
    } elseif (strpos($mensagem, 'já está') !== false) {
        $statusCode = 409; // Conflict
    } elseif (strpos($mensagem, 'inválido') !== false) {
        $statusCode = 422; // Unprocessable Entity
    }
    
    http_response_code($statusCode);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $mensagem
    ]);
}
?>
