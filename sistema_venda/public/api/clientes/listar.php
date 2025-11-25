<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/config.php';
require_once '../../../config/auth.php';
require_once '../../../classes/Database.php';
require_once '../../../classes/Cliente.php';

$cliente_obj = new Cliente();
$clientes = $cliente_obj->listar();

echo json_encode($clientes);
?>