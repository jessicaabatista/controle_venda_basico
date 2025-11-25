<?php
header('Content-Type: application/json; charset=utf-8');

// ✅ GARANTIR QUE CONFIG ESTÁ CARREGADO ANTES DE QUALQUER COISA
if (!defined('DB_HOST')) {
    require_once '../../../config/config.php';
}

// ✅ REQUERER AUTH PARA GARANTIR SESSÃO ATIVA
require_once '../../../config/auth.php';

// ✅ REQUERER CLASSES
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Venda.php';

// ✅ LOG DETALHADO INICIAL
error_log('=== INICIAR: CRIAR VENDA ===');
error_log('Hora: ' . date('Y-m-d H:i:s'));
error_log('IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'DESCONHECIDO'));
error_log('User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'DESCONHECIDO'));

// ✅ VALIDAR MÉTODO HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('ERRO: Método HTTP não é POST. Recebido: ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido. Use POST.',
        'metodo_recebido' => $_SERVER['REQUEST_METHOD']
    ]);
    exit;
}

// ✅ LER INPUT JSON
$input = file_get_contents('php://input');
error_log('Input bruto recebido: ' . strlen($input) . ' bytes');
error_log('Conteúdo: ' . substr($input, 0, 500));

if (empty($input)) {
    error_log('ERRO: Input vazio');
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Nenhum dado foi enviado',
        'debug' => 'Input PHP vazio'
    ]);
    exit;
}

// ✅ DECODIFICAR JSON
$dados = json_decode($input, true);
error_log('JSON decoded: ' . json_encode($dados));

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('ERRO JSON: ' . json_last_error_msg());
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'JSON inválido',
        'erro_json' => json_last_error_msg(),
        'input_recebido' => $input
    ]);
    exit;
}

// ✅ VALIDAR DADOS OBRIGATÓRIOS
if (!is_array($dados)) {
    error_log('ERRO: Dados não é um array após JSON decode');
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados devem ser um objeto JSON',
        'tipo_recebido' => gettype($dados)
    ]);
    exit;
}

// ✅ VALIDAR ID_CLIENTE
if (empty($dados['id_cliente'])) {
    error_log('ERRO: id_cliente vazio. Dados recebidos: ' . json_encode($dados));
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID do cliente é obrigatório',
        'campos_recebidos' => array_keys($dados),
        'dados_debug' => $dados
    ]);
    exit;
}

// ✅ CONVERTER ID_CLIENTE PARA INT
$idCliente = intval($dados['id_cliente']);
if ($idCliente <= 0) {
    error_log('ERRO: id_cliente inválido: ' . $dados['id_cliente']);
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID do cliente deve ser um número maior que 0',
        'id_cliente_recebido' => $dados['id_cliente'],
        'id_cliente_convertido' => $idCliente
    ]);
    exit;
}

// ✅ OBTER ID_USUARIO DA SESSÃO
if (!isset($_SESSION['id_usuario'])) {
    error_log('ERRO: Sessão não contém id_usuario');
    http_response_code(401);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Sessão expirada. Faça login novamente.',
        'sessao_debug' => [
            'existe_id_usuario' => isset($_SESSION['id_usuario']),
            'chaves_sessao' => array_keys($_SESSION)
        ]
    ]);
    exit;
}

$idUsuario = intval($_SESSION['id_usuario']);
error_log('Usuario autenticado: ' . $idUsuario);

// ✅ EXTRAIR OUTROS PARÂMETROS
$observacoes = $dados['observacoes'] ?? '';
$quantidadeParcelas = intval($dados['quantidade_parcelas'] ?? 1);
$itens = $dados['itens'] ?? [];

error_log("Parâmetros da venda:");
error_log("  - idCliente: $idCliente");
error_log("  - idUsuario: $idUsuario");
error_log("  - observacoes: " . strlen($observacoes) . " chars");
error_log("  - quantidadeParcelas: $quantidadeParcelas");
error_log("  - itens: " . count($itens) . " itens");

// ✅ CRIAR VENDA
try {
    $venda_obj = new Venda();
    
    error_log('Chamando $venda_obj->criar()...');
    
    $idVenda = $venda_obj->criar(
        $idCliente,
        $idUsuario,
        $observacoes,
        $quantidadeParcelas
    );
    
    error_log('Venda criada com sucesso. ID: ' . $idVenda);

    // ✅ ADICIONAR ITENS (SE HOUVER)
    if (is_array($itens) && count($itens) > 0) {
        error_log('Adicionando ' . count($itens) . ' itens à venda...');
        
        foreach ($itens as $index => $item) {
            try {
                error_log("Adicionando item $index: " . json_encode($item));
                
                $venda_obj->adicionarItem(
                    $idVenda,
                    $item['codigo_produto'] ?? 'INDEFINIDO',
                    $item['descricao'] ?? 'Descrição não informada',
                    intval($item['quantidade'] ?? 1),
                    floatval($item['valor_unitario'] ?? 0)
                );
                
                error_log("Item $index adicionado com sucesso");
            } catch (Exception $eItem) {
                error_log('AVISO: Erro ao adicionar item ' . $index . ': ' . $eItem->getMessage());
                // Continuar com próximos itens
            }
        }
    } else {
        error_log('Nenhum item para adicionar');
    }

    // ✅ RESPOSTA DE SUCESSO
    error_log('=== FIM: CRIAR VENDA (SUCESSO) ===');
    
    http_response_code(201);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Venda criada com sucesso',
        'id_venda' => $idVenda,
        'debug' => [
            'id_cliente' => $idCliente,
            'id_usuario' => $idUsuario,
            'quantidade_itens' => count($itens),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    error_log('=== FIM: CRIAR VENDA (ERRO) ===');
    error_log('ERRO GERAL: ' . $e->getMessage());
    error_log('File: ' . $e->getFile() . ':' . $e->getLine());
    error_log('Trace: ' . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage(),
        'debug' => [
            'arquivo' => $e->getFile(),
            'linha' => $e->getLine(),
            'id_cliente' => $idCliente ?? 'INDEFINIDO',
            'id_usuario' => $idUsuario ?? 'INDEFINIDO'
        ]
    ]);
}
?>