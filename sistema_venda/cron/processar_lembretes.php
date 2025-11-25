<?php
// Este script deve ser executado diariamente via cron
// Adicionar no cron: 0 9 * * * /usr/bin/php /caminho/para/sistema/cron/processar_lembretes.php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Configuracao.php';
require_once __DIR__ . '/../classes/Email.php';
require_once __DIR__ . '/../classes/Cliente.php';

$db = new Database();
$config = new Configuracao();
$email = new Email();
$cliente_obj = new Cliente();

// Buscar parcelas que vencem nos próximos 5 dias
$query = "SELECT p.*, v.id_cliente, c.nome, c.email
         FROM financeiro_parcelas p
         JOIN vendas v ON p.id_venda = v.id_venda
         JOIN clientes c ON v.id_cliente = c.id_cliente
         WHERE p.status != 'paga'
         AND DATE(p.data_vencimento) BETWEEN DATE_ADD(NOW(), INTERVAL 0 DAY) AND DATE_ADD(NOW(), INTERVAL 5 DAY)
         AND p.lembrete_enviado = 0";

$stmt = $db->execute($query, '', []);
$parcelas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($parcelas as $parcela) {
    // Enviar email de lembrete
    $sucesso = $email->enviarLembretePagamento(
        $parcela['id_parcela'],
        $parcela['email'],
        $parcela['nome'],
        $parcela['data_vencimento'],
        $parcela['saldo_parcela']
    );

    if ($sucesso) {
        // Marcar como enviado
        $updateQuery = "UPDATE financeiro_parcelas SET lembrete_enviado = 1 WHERE id_parcela = ?";
        $db->execute($updateQuery, 'i', [$parcela['id_parcela']]);
    }
}

// Log
$logFile = __DIR__ . '/logs/lembretes.log';
@mkdir(dirname($logFile), 0755, true);
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Processados " . count($parcelas) . " lembretes\n", FILE_APPEND);
?>
