<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/config.php';
require_once '../../../classes/Database.php';

$termo = $_GET['termo'] ?? '';

if (strlen($termo) < 2) {
    echo json_encode([]);
    exit;
}

$db = new Database();
$query = "SELECT id_cliente, nome, email, telefone FROM clientes 
         WHERE ativo = 1 AND (nome LIKE ? OR email LIKE ? OR telefone LIKE ?)
         LIMIT 10";

$termo_busca = '%' . $termo . '%';

try {
    $stmt = $db->execute($query, 'sss', [$termo_busca, $termo_busca, $termo_busca]);
    $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($resultado);
} catch (Exception $e) {
    error_log("Erro na busca de clientes: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar clientes']);
}
?>