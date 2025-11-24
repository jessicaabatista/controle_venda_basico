<?php
header('Content-Type: application/json');
require_once '../../config/auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Cliente.php';

$dados = json_decode(file_get_contents('php://input'), true);

$cliente_obj = new Cliente();

try {
    if (isset($dados['id_cliente'])) {
        // Atualizar
        $cliente_obj->atualizar(
            $dados['id_cliente'],
            $dados['nome'],
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
        // Criar novo
        $idCliente = $cliente_obj->criar(
            $dados['nome'],
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
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>