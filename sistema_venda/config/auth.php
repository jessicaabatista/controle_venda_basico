<?php
/**
 * Autenticacao e Verificacao de Sessao
 * Arquivo: config/auth.php
 * Use este arquivo em todas as paginas/APIs que requerem autenticacao
 */

if (!defined('APP_NAME')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

// Verificar se usuario esta autenticado
if (!isset($_SESSION['id_usuario'])) {
    // Se for uma requisição AJAX, retornar JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Sessao expirada. Faca login novamente.'
        ]);
        exit;
    }
    
    // Caso contrario, redirecionar para login
    header('Location: ' . PUBLIC_URL_RELATIVE . 'login.php');
    exit;
}

// Função auxiliar para verificar autenticacao
function verificarAutenticacao() {
    return isset($_SESSION['id_usuario']);
}

// Função para obter usuario autenticado
function obterUsuarioAutenticado() {
    return [
        'id_usuario' => $_SESSION['id_usuario'] ?? null,
        'nome_usuario' => $_SESSION['nome_usuario'] ?? '',
        'email_usuario' => $_SESSION['email_usuario'] ?? ''
    ];
}

// Função para logout
function logout() {
    session_destroy();
    header('Location: ' . PUBLIC_URL_RELATIVE . 'login.php');
    exit;
}
?>