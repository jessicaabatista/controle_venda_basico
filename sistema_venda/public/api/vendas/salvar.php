<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Venda.php';
require_once '../../classes/Financeiro.php';

// Verificar m?todo HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'M?todo n?o permitido'
    ]);
    exit;
}

// Obter e validar dados de entrada
$jsonInput = file_get_contents('php://input');
if ($jsonInput === false) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao ler dados da requisi??o'
    ]);
    exit;
}

$dados = json_decode($jsonInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'JSON inv?lido: ' . json_last_error_msg()
    ]);
    exit;
}

// Validar campos obrigat?rios
$camposObrigatorios = ['id_cliente'];
foreach ($camposObrigatorios as $campo) {
    if (!isset($dados[$campo]) || $dados[$campo] === '') {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => "Campo obrigat?rio ausente: $campo"
        ]);
        exit;
    }
}

// Sanitizar e validar dados
$idCliente = filter_var($dados['id_cliente'], FILTER_VALIDATE_INT);
$quantidadeParcelas = isset($dados['quantidade_parcelas']) ? filter_var($dados['quantidade_parcelas'], FILTER_VALIDATE_INT) : 1;
$observacoes = isset($dados['observacoes']) ? htmlspecialchars(trim($dados['observacoes']), ENT_QUOTES, 'UTF-8') : '';

// Validar valores
if ($idCliente === false || $idCliente <= 0) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID do cliente inv?lido'
    ]);
    exit;
}

if ($quantidadeParcelas === false || $quantidadeParcelas < 1 || $quantidadeParcelas > 24) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Quantidade de parcelas deve ser entre 1 e 24'
    ]);
    exit;
}

// Validar itens
if (!isset($dados['itens']) || !is_array($dados['itens']) || empty($dados['itens'])) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Adicione pelo menos um item ? venda'
    ]);
    exit;
}

// Validar cada item
foreach ($dados['itens'] as $index => $item) {
    if (!isset($item['codigo']) || !isset($item['descricao']) || !isset($item['valor_unitario'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => "Item {$index}: campos c?digo, descri??o e valor_unit?rio s?o obrigat?rios"
        ]);
        exit;
    }
    
    $quantidade = isset($item['quantidade']) ? filter_var($item['quantidade'], FILTER_VALIDATE_INT) : 1;
    $valorUnitario = filter_var($item['valor_unitario'], FILTER_VALIDATE_FLOAT);
    
    if ($quantidade === false || $quantidade < 1 || $quantidade > 9999) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => "Item {$index}: quantidade deve ser entre 1 e 9999"
        ]);
        exit;
    }
    
    if ($valorUnitario === false || $valorUnitario <= 0 || $valorUnitario > 999999.99) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => "Item {$index}: valor unit?rio deve ser maior que zero e menor que R$ 999.999,99"
        ]);
        exit;
    }
    
    if (strlen(trim($item['codigo'])) < 2) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => "Item {$index}: c?digo deve ter pelo menos 2 caracteres"
        ]);
        exit;
    }
    
    if (strlen(trim($item['descricao'])) < 3 || strlen(trim($item['descricao'])) > 255) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => "Item {$index}: descri??o deve ter entre 3 e 255 caracteres"
        ]);
        exit;
    }
}

$venda = new Venda();
$financeiro = new Financeiro();

try {
    // Usar transação através da classe Database
    $database = new Database();
    $connection = $database->getConnection();
    $connection->begin_transaction();
    
    // Criar venda
    $idVenda = $venda->criar(
        $idCliente,
        $_SESSION['id_usuario'] ?? 1,
        $observacoes,
        $quantidadeParcelas
    );

    // Adicionar itens
    foreach ($dados['itens'] as $item) {
        $venda->adicionarItem(
            $idVenda,
            htmlspecialchars(trim($item['codigo']), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(trim($item['descricao']), ENT_QUOTES, 'UTF-8'),
            $item['quantidade'] ?? 1,
            $item['valor_unitario']
        );
    }

    // Gerar parcelas
    $financeiro->gerarParcelas($idVenda, $quantidadeParcelas);

    $connection->commit();
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Venda criada com sucesso',
        'id_venda' => $idVenda
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    
    // Log do erro para debugging
    error_log("Erro ao criar venda: " . $e->getMessage());
    
    // Determinar c?digo de status baseado no tipo de erro
    $mensagem = $e->getMessage();
    $statusCode = 400;
    
    if (strpos($mensagem, 'n?o encontrad') !== false) {
        $statusCode = 404;
    } elseif (strpos($mensagem, 'j? exist') !== false) {
        $statusCode = 409; // Conflict
    } elseif (strpos($mensagem, 'inv?lido') !== false) {
        $statusCode = 422; // Unprocessable Entity
    }
    
    http_response_code($statusCode);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $mensagem
    ]);
}
?>
