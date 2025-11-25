<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/config.php';
require_once '../../../config/auth.php';
require_once '../../../classes/Database.php';
require_once '../../../classes/Cliente.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Metodo nao permitido'
    ]);
    exit;
}

$jsonInput = file_get_contents('php://input');
if (!$jsonInput) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Nenhum dado foi enviado'
    ]);
    exit;
}

$dados = json_decode($jsonInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'JSON invalido'
    ]);
    exit;
}

$cliente_obj = new Cliente();

try {
    if (isset($dados['id_cliente']) && $dados['id_cliente']) {
        // Atualizar cliente existente
        $cliente_obj->atualizar(
            $dados['id_cliente'],
            $dados['nome'] ?? '',
            $dados['email'] ?? '',
            $dados['telefone'] ?? '',
            $dados['endereco'] ?? '',
            $dados['cpf_cnpj'] ?? '',
            $dados['observacoes'] ?? ''
        );
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Cliente atualizado com sucesso',
            'id_cliente' => $dados['id_cliente']
        ]);
    } else {
        // Criar novo cliente
        $idCliente = $cliente_obj->criar(
            $dados['nome'] ?? '',
            $dados['email'] ?? '',
            $dados['telefone'] ?? '',
            $dados['endereco'] ?? '',
            $dados['cpf_cnpj'] ?? '',
            $dados['observacoes'] ?? ''
        );
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Cliente criado com sucesso',
            'id_cliente' => $idCliente
        ]);
    }
} catch (Exception $e) {
    error_log("Erro ao salvar cliente: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>