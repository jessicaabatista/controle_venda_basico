<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Função auxiliar para verificar autenticação
function verificarAutenticacao() {
    return isset($_SESSION['id_usuario']);
}

// Função para obter usuário autenticado
function obterUsuarioAutenticado() {
    return [
        'id_usuario' => $_SESSION['id_usuario'],
        'nome_usuario' => $_SESSION['nome_usuario'],
        'email_usuario' => $_SESSION['email_usuario']
    ];
}

// Função para logout
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>