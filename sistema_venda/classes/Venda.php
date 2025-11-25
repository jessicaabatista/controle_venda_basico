<?php
// classes/Venda.php

class Venda
{
    private $db;

    public function __construct()
    {
        // ✅ Obter a conexão MySQLi da classe Database
        $this->db = Database::conectar();
    }

    /**
     * Criar nova venda
     */
    public function criar($idCliente, $idUsuario, $obs = '', $quantidadeParcelas = 1)
    {
        try {
            // ✅ ADICIONAR: Validação com erro claro
            error_log("Parâmetros recebidos: idCliente=$idCliente, idUsuario=$idUsuario");

            if (empty($idCliente)) {
                throw new Exception('ID do cliente não pode ser vazio');
            }

            if (empty($idUsuario)) {
                throw new Exception('ID do usuário não pode ser vazio');
            }

            $idCliente = intval($idCliente);
            $idUsuario = intval($idUsuario);

            if ($idCliente <= 0) {
                throw new Exception('ID do cliente deve ser maior que 0 (recebido: ' . $idCliente . ')');
            }

            if ($idUsuario <= 0) {
                throw new Exception('ID do usuário deve ser maior que 0 (recebido: ' . $idUsuario . ')');
            }

            // ✅ ADICIONAR: Verificar se cliente existe
            $stmtVerificaCliente = $this->db->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ?");
            if (!$stmtVerificaCliente) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmtVerificaCliente->bind_param('i', $idCliente);
            $stmtVerificaCliente->execute();
            $resultadoCliente = $stmtVerificaCliente->get_result();

            if ($resultadoCliente->num_rows === 0) {
                throw new Exception('Cliente não encontrado (ID: ' . $idCliente . ')');
            }
            $stmtVerificaCliente->close();

            $quantidadeParcelas = intval($quantidadeParcelas);
            if ($quantidadeParcelas < 1 || $quantidadeParcelas > 24) {
                throw new Exception('Quantidade de parcelas deve ser entre 1 e 24');
            }

            $observacoes = htmlspecialchars(trim($obs), ENT_QUOTES, 'UTF-8');

            $sql = "INSERT INTO vendas (
                    id_cliente,
                    id_usuario,
                    data_venda,
                    status_geral,
                    observacoes,
                    valor_total,
                    valor_pago,
                    saldo_devedor,
                    quantidade_parcelas,
                    data_atualizacao
                ) VALUES (
                    ?,
                    ?,
                    NOW(),
                    ?,
                    ?,
                    0,
                    0,
                    0,
                    ?,
                    NOW()
                )";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $status = STATUS_VENDA_ABERTA; // ✅ VERIFICAR se constante existe

            error_log("Executando INSERT com: cliente=$idCliente, usuario=$idUsuario, status=$status");

            $stmt->bind_param('iissi', $idCliente, $idUsuario, $status, $observacoes, $quantidadeParcelas);

            if (!$stmt->execute()) {
                throw new Exception('Erro ao executar inserção: ' . $stmt->error);
            }

            $idVenda = $this->db->insert_id;
            error_log("Venda criada com ID: $idVenda");
            $stmt->close();

            return $idVenda;
        } catch (Exception $e) {
            error_log('Erro ao criar venda: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Adicionar item à venda
     */
    public function adicionarItem($idVenda, $codigoProduto, $descricao, $quantidade, $valorUnitario)
    {
        try {
            // Validações obrigatórias
            if (empty($codigoProduto) || strlen(trim($codigoProduto)) < 2) {
                throw new Exception('Código do produto é obrigatório e deve ter pelo menos 2 caracteres');
            }

            if (empty($descricao) || strlen(trim($descricao)) < 3 || strlen(trim($descricao)) > 255) {
                throw new Exception('Descrição é obrigatória e deve ter entre 3 e 255 caracteres');
            }

            $quantidade = (int)$quantidade;
            if (!$quantidade || $quantidade < 1 || $quantidade > 9999) {
                throw new Exception('Quantidade deve ser entre 1 e 9999');
            }

            $valorUnitario = (float)$valorUnitario;
            if ($valorUnitario <= 0 || $valorUnitario > 999999.99) {
                throw new Exception('Valor unitário deve ser entre R$ 0,01 e R$ 999.999,99');
            }

            if (!$idVenda || $idVenda <= 0) {
                throw new Exception('Venda inválida');
            }

            // ✅ CORRIGIDO: Verificar se venda existe com MySQLi
            $stmtVerifica = $this->db->prepare("SELECT id_venda FROM vendas WHERE id_venda = ?");
            if (!$stmtVerifica) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmtVerifica->bind_param('i', $idVenda);
            $stmtVerifica->execute();
            $resultadoVerifica = $stmtVerifica->get_result();

            if ($resultadoVerifica->num_rows === 0) {
                throw new Exception('Venda não encontrada');
            }
            $stmtVerifica->close();

            $valorTotal = $quantidade * $valorUnitario;
            $saldoItem = $valorTotal;
            $statusPagamento = STATUS_ITEM_PENDENTE;
            $codigoProduto = htmlspecialchars(trim($codigoProduto), ENT_QUOTES, 'UTF-8');
            $descricao = htmlspecialchars(trim($descricao), ENT_QUOTES, 'UTF-8');

            // ✅ CORRIGIDO: INSERT com MySQLi syntax
            $sql = "INSERT INTO itens_venda (
                        id_venda,
                        codigo_produto,
                        descricao,
                        quantidade,
                        valor_unitario,
                        valor_total,
                        valor_pago,
                        saldo_item,
                        status_pagamento,
                        data_criacao
                    ) VALUES (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        0,
                        ?,
                        ?,
                        NOW()
                    )";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            // ✅ CORRIGIDO: Tipos corretos (i=int, s=string, d=double)
            $stmt->bind_param(
                'issiidss',
                $idVenda,
                $codigoProduto,
                $descricao,
                $quantidade,
                $valorUnitario,
                $valorTotal,
                $saldoItem,
                $statusPagamento
            );

            if (!$stmt->execute()) {
                throw new Exception('Erro ao executar inserção: ' . $stmt->error);
            }

            $idItem = $this->db->insert_id;
            $stmt->close();

            // Atualizar totais da venda
            $this->atualizarTotaisVenda($idVenda);

            return $idItem;
        } catch (Exception $e) {
            error_log('Erro ao adicionar item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualizar item da venda
     */
    public function atualizarItem($idItem, $descricao, $quantidade, $valorUnitario)
    {
        try {
            // Validações
            if (empty($descricao) || strlen(trim($descricao)) < 3) {
                throw new Exception('Descrição deve ter pelo menos 3 caracteres');
            }

            $quantidade = (int)$quantidade;
            if ($quantidade < 1) {
                throw new Exception('Quantidade deve ser maior que 0');
            }

            $valorUnitario = (float)$valorUnitario;
            if ($valorUnitario <= 0) {
                throw new Exception('Valor unitário deve ser maior que 0');
            }

            // ✅ CORRIGIDO: Obter item com MySQLi
            $stmtGet = $this->db->prepare("SELECT id_venda FROM itens_venda WHERE id_item = ?");
            if (!$stmtGet) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmtGet->bind_param('i', $idItem);
            $stmtGet->execute();
            $resultadoGet = $stmtGet->get_result();
            $item = $resultadoGet->fetch_assoc();
            $stmtGet->close();

            if (!$item) {
                throw new Exception('Item não encontrado');
            }

            $valorTotal = $quantidade * $valorUnitario;
            $saldoItem = $valorTotal;
            $descricao = htmlspecialchars(trim($descricao), ENT_QUOTES, 'UTF-8');

            // ✅ CORRIGIDO: UPDATE com MySQLi
            $sql = "UPDATE itens_venda SET 
                        descricao = ?,
                        quantidade = ?,
                        valor_unitario = ?,
                        valor_total = ?,
                        saldo_item = ?
                    WHERE id_item = ?";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param(
                'siddi',
                $descricao,
                $quantidade,
                $valorUnitario,
                $valorTotal,
                $saldoItem,
                $idItem
            );

            if (!$stmt->execute()) {
                throw new Exception('Erro ao atualizar item: ' . $stmt->error);
            }
            $stmt->close();

            // Recalcular valor da venda
            $this->atualizarTotaisVenda($item['id_venda']);

            return true;
        } catch (Exception $e) {
            error_log('Erro ao atualizar item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualizar totais da venda baseado nos itens
     */
    public function atualizarTotaisVenda($idVenda)
    {
        try {
            // ✅ CORRIGIDO: SELECT com MySQLi
            $stmtCalc = $this->db->prepare("SELECT 
                        COALESCE(SUM(valor_total), 0) as valor_total,
                        COALESCE(SUM(valor_pago), 0) as valor_pago,
                        COALESCE(SUM(saldo_item), 0) as saldo_devedor
                    FROM itens_venda 
                    WHERE id_venda = ?");

            if (!$stmtCalc) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmtCalc->bind_param('i', $idVenda);
            $stmtCalc->execute();
            $resultado = $stmtCalc->get_result();
            $totais = $resultado->fetch_assoc();
            $stmtCalc->close();

            $valorTotal = (float)($totais['valor_total'] ?? 0);
            $valorPago = (float)($totais['valor_pago'] ?? 0);
            $saldoDevedor = (float)($totais['saldo_devedor'] ?? 0);

            // Determinar status baseado nos totais
            $status = STATUS_VENDA_ABERTA;
            if ($valorTotal > 0) {
                if ($saldoDevedor <= 0) {
                    $status = STATUS_VENDA_PAGA;
                } elseif ($valorPago > 0) {
                    $status = STATUS_VENDA_PARCIAL;
                }
            }

            // ✅ CORRIGIDO: UPDATE com MySQLi
            $sql = "UPDATE vendas SET 
                            valor_total = ?,
                            valor_pago = ?,
                            saldo_devedor = ?,
                            status_geral = ?,
                            data_atualizacao = NOW()
                        WHERE id_venda = ?";

            $stmtUpdate = $this->db->prepare($sql);
            if (!$stmtUpdate) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmtUpdate->bind_param(
                'ddssi',
                $valorTotal,
                $valorPago,
                $saldoDevedor,
                $status,
                $idVenda
            );

            if (!$stmtUpdate->execute()) {
                throw new Exception('Erro ao atualizar vendas: ' . $stmtUpdate->error);
            }
            $stmtUpdate->close();

            return true;
        } catch (Exception $e) {
            error_log('Erro ao atualizar totais da venda: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Listar vendas com filtros
     */
    public function listar($filtros = [])
    {
        try {
            $sql = "SELECT 
                        v.*,
                        c.nome as nome_cliente,
                        c.email,
                        c.telefone,
                        COUNT(i.id_item) as qtd_itens
                    FROM vendas v
                    JOIN clientes c ON v.id_cliente = c.id_cliente
                    LEFT JOIN itens_venda i ON v.id_venda = i.id_venda
                    WHERE 1=1";

            // ✅ CORRIGIDO: Construir query dinamicamente com MySQLi
            $params_types = '';
            $params_values = [];

            if (!empty($filtros['status'])) {
                $sql .= " AND v.status_geral = ?";
                $params_types .= 's';
                $params_values[] = $filtros['status'];
            }

            if (!empty($filtros['cliente'])) {
                $sql .= " AND c.nome LIKE ?";
                $params_types .= 's';
                $params_values[] = '%' . $filtros['cliente'] . '%';
            }

            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND DATE(v.data_venda) >= ?";
                $params_types .= 's';
                $params_values[] = $filtros['data_inicio'];
            }

            if (!empty($filtros['data_fim'])) {
                $sql .= " AND DATE(v.data_venda) <= ?";
                $params_types .= 's';
                $params_values[] = $filtros['data_fim'];
            }

            $sql .= " GROUP BY v.id_venda ORDER BY v.data_venda DESC";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            if (!empty($params_types)) {
                $stmt->bind_param($params_types, ...$params_values);
            }

            if (!$stmt->execute()) {
                throw new Exception('Erro ao executar query: ' . $stmt->error);
            }

            $resultado = $stmt->get_result();
            $vendas = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $vendas;
        } catch (Exception $e) {
            error_log('Erro ao listar vendas: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter venda por ID
     */
    public function obter($idVenda)
    {
        try {
            $stmt = $this->db->prepare("SELECT v.*, c.nome, c.email, c.telefone, c.id_cliente
                    FROM vendas v
                    JOIN clientes c ON v.id_cliente = c.id_cliente
                    WHERE v.id_venda = ?");

            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param('i', $idVenda);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $venda = $resultado->fetch_assoc();
            $stmt->close();

            return $venda;
        } catch (Exception $e) {
            error_log('Erro ao obter venda: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter vendas de um cliente específico
     */
    public function obterPorCliente($idCliente)
    {
        try {
            $stmt = $this->db->prepare("SELECT v.*, COUNT(i.id_item) as qtd_itens
                    FROM vendas v
                    LEFT JOIN itens_venda i ON v.id_venda = i.id_venda
                    WHERE v.id_cliente = ?
                    GROUP BY v.id_venda
                    ORDER BY v.data_venda DESC");

            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param('i', $idCliente);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $vendas = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $vendas;
        } catch (Exception $e) {
            error_log('Erro ao obter vendas do cliente: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter itens da venda
     */
    public function obterItens($idVenda)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM itens_venda WHERE id_venda = ? ORDER BY id_item DESC");

            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param('i', $idVenda);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $itens = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $itens;
        } catch (Exception $e) {
            error_log('Erro ao obter itens da venda: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter um item específico
     */
    public function obterItem($idItem)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM itens_venda WHERE id_item = ?");

            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param('i', $idItem);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $item = $resultado->fetch_assoc();
            $stmt->close();

            return $item;
        } catch (Exception $e) {
            error_log('Erro ao obter item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remover item da venda
     */
    public function removerItem($idItem)
    {
        try {
            // Obter id_venda do item
            $stmtGet = $this->db->prepare("SELECT id_venda FROM itens_venda WHERE id_item = ?");

            if (!$stmtGet) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmtGet->bind_param('i', $idItem);
            $stmtGet->execute();
            $resultado = $stmtGet->get_result();
            $item = $resultado->fetch_assoc();
            $stmtGet->close();

            if (!$item) {
                throw new Exception('Item não encontrado');
            }

            $idVenda = $item['id_venda'];

            // Deletar item
            $stmtDelete = $this->db->prepare("DELETE FROM itens_venda WHERE id_item = ?");

            if (!$stmtDelete) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmtDelete->bind_param('i', $idItem);

            if (!$stmtDelete->execute()) {
                throw new Exception('Erro ao deletar item: ' . $stmtDelete->error);
            }
            $stmtDelete->close();

            // Atualizar totais da venda
            $this->atualizarTotaisVenda($idVenda);

            return true;
        } catch (Exception $e) {
            error_log('Erro ao remover item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualizar venda (observações e status)
     */
    public function atualizar($idVenda, $observacoes = '', $status = null)
    {
        try {
            $campos = [];
            $tipos = '';
            $valores = [];

            if (!empty($observacoes)) {
                $campos[] = "observacoes = ?";
                $tipos .= 's';
                $valores[] = htmlspecialchars(trim($observacoes), ENT_QUOTES, 'UTF-8');
            }

            if (!empty($status)) {
                $campos[] = "status_geral = ?";
                $tipos .= 's';
                $valores[] = $status;
            }

            if (empty($campos)) {
                return true;
            }

            $sql = "UPDATE vendas SET " . implode(", ", $campos) . ", data_atualizacao = NOW() WHERE id_venda = ?";
            $tipos .= 'i';
            $valores[] = $idVenda;

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param($tipos, ...$valores);

            if (!$stmt->execute()) {
                throw new Exception('Erro ao atualizar venda: ' . $stmt->error);
            }

            $stmt->close();
            return true;
        } catch (Exception $e) {
            error_log('Erro ao atualizar venda: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancelar venda
     */
    public function cancelar($idVenda)
    {
        try {
            $status = STATUS_VENDA_CANCELADA;
            $sql = "UPDATE vendas SET 
                        status_geral = ?,
                        data_atualizacao = NOW()
                    WHERE id_venda = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param('si', $status, $idVenda);

            if (!$stmt->execute()) {
                throw new Exception('Erro ao cancelar venda: ' . $stmt->error);
            }

            $stmt->close();
            return true;
        } catch (Exception $e) {
            error_log('Erro ao cancelar venda: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter estatísticas de vendas
     */
    public function obterEstatisticas($periodo = 'mes')
    {
        try {
            $dataFiltro = '';
            switch ($periodo) {
                case 'hoje':
                    $dataFiltro = "DATE(v.data_venda) = CURDATE()";
                    break;
                case 'mes':
                    $dataFiltro = "YEAR(v.data_venda) = YEAR(NOW()) AND MONTH(v.data_venda) = MONTH(NOW())";
                    break;
                case 'ano':
                    $dataFiltro = "YEAR(v.data_venda) = YEAR(NOW())";
                    break;
                default:
                    $dataFiltro = "1=1";
            }

            $sql = "SELECT 
                        COUNT(v.id_venda) as total_vendas,
                        SUM(v.valor_total) as valor_total,
                        SUM(v.valor_pago) as valor_pago,
                        SUM(v.saldo_devedor) as saldo_devedor,
                        SUM(CASE WHEN v.status_geral = '" . STATUS_VENDA_PAGA . "' THEN 1 ELSE 0 END) as vendas_pagas,
                        SUM(CASE WHEN v.status_geral = '" . STATUS_VENDA_PARCIAL . "' THEN 1 ELSE 0 END) as vendas_parcial,
                        SUM(CASE WHEN v.status_geral = '" . STATUS_VENDA_ABERTA . "' THEN 1 ELSE 0 END) as vendas_abertas
                    FROM vendas v
                    WHERE " . $dataFiltro;

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->execute();
            $resultado = $stmt->get_result();
            $stats = $resultado->fetch_assoc();
            $stmt->close();

            return $stats;
        } catch (Exception $e) {
            error_log('Erro ao obter estatísticas: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verificar se venda tem itens
     */
    public function temItens($idVenda)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as qtd FROM itens_venda WHERE id_venda = ?");

            if (!$stmt) {
                return false;
            }

            $stmt->bind_param('i', $idVenda);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $dados = $resultado->fetch_assoc();
            $stmt->close();

            return $dados['qtd'] > 0;
        } catch (Exception $e) {
            error_log('Erro ao verificar itens: ' . $e->getMessage());
            return false;
        }
    }
}
