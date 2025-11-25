<?php
/**
 * Configuração Central do Sistema
 * Arquivo: config/config.php
 * Todos os valores hardcoded devem ser definidos aqui
 */

// Iniciar sessão se ainda não iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// CONFIGURAÇÃO DE BANCO DE DADOS
// ============================================================================

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'sistema_vendas');
define('DB_CHARSET', 'utf8mb4');

// ============================================================================
// CONFIGURAÇÃO DE APLICAÇÃO
// ============================================================================

define('APP_NAME', 'Sistema de Controle de Vendas');
define('APP_VERSION', '1.0.0');
define('APP_ENVIRONMENT', getenv('APP_ENV') ?: 'development');

// ============================================================================
// CAMINHOS DO SISTEMA
// ============================================================================

define('BASE_PATH', dirname(dirname(__FILE__)));
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
define('CONFIG_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'config');
define('CLASSES_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'classes');
define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');
define('ASSETS_PATH', PUBLIC_PATH . DIRECTORY_SEPARATOR . 'assets');
define('LOGS_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'logs');
define('CRON_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'cron');

// ============================================================================
// URLs DO SISTEMA
// ============================================================================

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = dirname($scriptName);
$scriptDir = rtrim(str_replace('\\', '/', $scriptDir), '/');

if (strpos($scriptDir, '/public') !== false) {
    $basePath = str_replace('/public', '', $scriptDir);
} else {
    $basePath = $scriptDir;
}

$basePath = ltrim($basePath, '/');

define('BASE_URL', $protocol . '://' . $host . ($basePath ? '/' . $basePath : '') . '/');
define('PUBLIC_URL', $protocol . '://' . $host . ($basePath ? '/' . $basePath : '') . '/public/');
define('ASSETS_URL', $protocol . '://' . $host . ($basePath ? '/' . $basePath : '') . '/public/assets/');
define('API_URL', $protocol . '://' . $host . ($basePath ? '/' . $basePath : '') . '/public/api/');
define('API_PATH', PUBLIC_PATH . DIRECTORY_SEPARATOR . 'api');

define('BASE_URL_RELATIVE', ($basePath ? '/' . $basePath : '') . '/');
define('PUBLIC_URL_RELATIVE', ($basePath ? '/' . $basePath : '') . '/public/');
define('ASSETS_URL_RELATIVE', ($basePath ? '/' . $basePath : '') . '/public/assets/');
define('API_URL_RELATIVE', ($basePath ? '/' . $basePath : '') . '/public/api/');

// ============================================================================
// CONFIGURAÇÕES DE SEGURANÇA
// ============================================================================

define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_ALGORITHM', PASSWORD_BCRYPT);
define('PASSWORD_OPTIONS', ['cost' => 12]);

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 15 * 60);
define('SESSION_TIMEOUT', 30 * 60);

define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600);

// ============================================================================
// CONFIGURAÇÕES FINANCEIRAS
// ============================================================================

define('MAX_PARCELAS', 24);
define('MIN_PARCELAS', 1);
define('DIAS_VENCIMENTO_PADRAO', 30);

define('MOEDA', 'BRL');
define('SEPARADOR_DECIMAL', ',');
define('SEPARADOR_MILHAR', '.');
define('CASAS_DECIMAIS', 2);

// ============================================================================
// CONFIGURAÇÕES DE FORMATAÇÃO
// ============================================================================

define('DATE_FORMAT', 'd/m/Y');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');
define('TIMEZONE', 'America/Sao_Paulo');

date_default_timezone_set(TIMEZONE);

// ============================================================================
// PAGINAÇÃO
// ============================================================================

define('ITEMS_PER_PAGE', 20);
define('MAX_ITEMS_PER_PAGE', 100);

// ============================================================================
// UPLOAD DE ARQUIVOS
// ============================================================================

define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('UPLOAD_PATH', PUBLIC_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx']);

// ============================================================================
// CONFIGURAÇÕES DE EMAIL
// ============================================================================

define('EMAIL_FROM_NAME', 'Sistema de Controle de Vendas');
define('EMAIL_FROM_ADDRESS', 'sistema@empresa.com');
define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');

// ============================================================================
// CONFIGURAÇÕES DE LOGS
// ============================================================================

define('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'info');
define('LOG_FILE', LOGS_PATH . DIRECTORY_SEPARATOR . 'system.log');
define('LOG_ERROR_FILE', LOGS_PATH . DIRECTORY_SEPARATOR . 'error.log');

// ============================================================================
// NOMES DAS TABELAS DO BANCO
// ============================================================================

define('TABLE_USUARIOS', 'usuarios');
define('TABLE_CLIENTES', 'clientes');
define('TABLE_VENDAS', 'vendas');
define('TABLE_ITENS_VENDA', 'itens_venda');
define('TABLE_FINANCEIRO_PARCELAS', 'financeiro_parcelas');
define('TABLE_FINANCEIRO_MOVIMENTACOES', 'financeiro_movimentacoes');
define('TABLE_CONFIGURACOES', 'configuracoes');
define('TABLE_LOGS_AUDITORIA', 'logs_auditoria');

// ============================================================================
// STATUS E ENUMERAÇÕES
// ============================================================================

define('STATUS_VENDA_ABERTA', 'aberta');
define('STATUS_VENDA_PARCIAL', 'parcial');
define('STATUS_VENDA_PAGA', 'paga');
define('STATUS_VENDA_CANCELADA', 'cancelada');

define('STATUS_PARCELA_ABERTA', 'aberta');
define('STATUS_PARCELA_PAGA', 'paga');
define('STATUS_PARCELA_VENCIDA', 'vencida');
define('STATUS_PARCELA_CANCELADA', 'cancelada');

define('STATUS_ITEM_PENDENTE', 'pendente');
define('STATUS_ITEM_PARCIAL', 'parcial');
define('STATUS_ITEM_PAGO', 'pago');

define('FORMAS_PAGAMENTO', [
    'dinheiro' => 'Dinheiro',
    'cartao_credito' => 'Cartao de Credito',
    'cartao_debito' => 'Cartao de Debito',
    'pix' => 'PIX',
    'transferencia' => 'Transferencia Bancaria',
    'boleto' => 'Boleto'
]);

// ============================================================================
// TRATAMENTO DE ERROS
// ============================================================================

if (APP_ENVIRONMENT === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

ini_set('log_errors', '1');
ini_set('error_log', LOG_ERROR_FILE);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $timestamp = date('Y-m-d H:i:s');
    $error_message = "[$timestamp] Erro ($errno): $errstr em $errfile:$errline\n";
    @file_put_contents(LOG_ERROR_FILE, $error_message, FILE_APPEND);
    
    if (APP_ENVIRONMENT !== 'development') {
        if (!headers_sent()) {
            http_response_code(500);
            echo "Ocorreu um erro no sistema. Contacte o administrador.";
        }
        return true;
    }
    return false;
});

set_exception_handler(function($exception) {
    $timestamp = date('Y-m-d H:i:s');
    $error_message = "[$timestamp] Excecao: " . $exception->getMessage() . " em " . $exception->getFile() . ":" . $exception->getLine() . "\n";
    @file_put_contents(LOG_ERROR_FILE, $error_message, FILE_APPEND);
    
    if (APP_ENVIRONMENT !== 'development') {
        if (!headers_sent()) {
            http_response_code(500);
            echo "Ocorreu um erro no sistema. Contacte o administrador.";
        }
        return;
    }
    
    if (!headers_sent()) {
        http_response_code(500);
        echo "<h1>Erro na Aplicacao</h1>";
        echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Linha:</strong> " . $exception->getLine() . "</p>";
        echo "<hr>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    }
});

// ============================================================================
// FUNÇÕES AUXILIARES
// ============================================================================

function getConfig($key, $default = null) {
    $configs = [
        'app_name' => APP_NAME,
        'app_version' => APP_VERSION,
        'db_host' => DB_HOST,
        'max_parcelas' => MAX_PARCELAS,
        'timezone' => TIMEZONE,
        'currency' => MOEDA,
    ];
    
    return isset($configs[$key]) ? $configs[$key] : $default;
}

function getAssetUrl($path = '') {
    $relativeUrl = ASSETS_URL_RELATIVE . ltrim($path, '/');
    return $relativeUrl;
}

function formatarMoeda($valor) {
    return number_format($valor, CASAS_DECIMAIS, SEPARADOR_DECIMAL, SEPARADOR_MILHAR);
}

function formatarData($data) {
    if (is_numeric($data)) {
        return date(DATE_FORMAT, $data);
    }
    return date(DATE_FORMAT, strtotime($data));
}

// ============================================================================
// CRIAR DIRETÓRIOS NECESSÁRIOS
// ============================================================================

$directories = [LOGS_PATH, UPLOAD_PATH];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ============================================================================
// AUTOLOAD DE CLASSES
// ============================================================================

spl_autoload_register(function ($class) {
    $file = CLASSES_PATH . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
?>