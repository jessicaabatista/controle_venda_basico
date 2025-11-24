<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Funчуo auxiliar para verificar autenticaчуo
function verificarAutenticacao() {
    return isset($_SESSION['id_usuario']);
}

// Funчуo para obter usuсrio autenticado
function obterUsuarioAutenticado() {
    return [
        'id_usuario' => $_SESSION['id_usuario'],
        'nome_usuario' => $_SESSION['nome_usuario'],
        'email_usuario' => $_SESSION['email_usuario']
    ];
}

// Funчуo para logout
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>