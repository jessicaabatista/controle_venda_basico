<?php
class Financeiro {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function gerarParcelas($idVenda, $quantidadeParcelas) {
        // Validações
        if (!$idVenda || !is_numeric($idVenda) || $idVenda <= 0) {
            throw new Exception('ID da venda inválido');
        }
        
        if (!$quantidadeParcelas || !is_numeric($quantidadeParcelas) || $quantidadeParcelas <= 0) {
            throw new Exception('Quantidade de parcelas inválida');
        }
        
        if ($quantidadeParcelas > 24) {
            throw new Exception('Máximo de 24 parcelas permitido');
        }
        
        $venda = $this->obterVenda($idVenda);
        if (!$venda) {
            throw new Exception('Venda não encontrada');
        }
        
        if ($venda['valor_total'] <= 0) {
            throw new Exception('Valor total da venda deve ser maior que zero');
        }
        
        // Verificar se já existem parcelas
        $parcelasExistentes = $this->obterParcelas($idVenda);
        if (!empty($parcelasExistentes)) {
            throw new Exception('Esta venda já possui parcelas geradas');
        }
        
        $valorTotal = $venda['valor_total'];
        $dataPrimeira = date('Y-m-d', strtotime('+1 month'));

        $valorBase = floor(($valorTotal / $quantidadeParcelas) * 100) / 100;
        $diferenca = $valorTotal - ($valorBase * $quantidadeParcelas);

        for ($i = 1; $i <= $quantidadeParcelas; $i++) {
            $valor = $valorBase;

            // Adiciona a diferença na última parcela
            if ($i == $quantidadeParcelas) {
                $valor += $diferenca;
            }

            $dataVencimento = date('Y-m-d', strtotime("+$i months"));
            
            // Validar data de vencimento (não pode ser no passado)
            if (strtotime($dataVencimento) < strtotime('today')) {
                throw new Exception('Data de vencimento não pode ser no passado');
            }
                        
            // Validar data de vencimento (não pode ser mais de 5 anos no futuro)
            $dataMaxima = date('Y-m-d', strtotime('+5 years'));
            if (strtotime($dataVencimento) > strtotime($dataMaxima)) {
                throw new Exception('Data de vencimento não pode ser superior a 5 anos');
            }

            $query = "INSERT INTO financeiro_parcelas 
                     (id_venda, numero_parcela, valor_previsto, saldo_parcela, data_vencimento, status)
                     VALUES (?, ?, ?, ?, ?, 'aberta')";

            $this->db->execute($query, 'iidds', [
                $idVenda,
                $i,
                $valor,
                $valor,
                $dataVencimento
            ]);
        }
        
        // Registrar log de auditoria
        $this->registrarLog('GERAR_PARCELAS', $idVenda, "Geradas $quantidadeParcelas parcelas para venda #$idVenda");
    }

    public function registrarPagamento($idVenda, $valorPago, $formaPagamento = 'dinheiro', $observacoes = '', $idParcela = null, $idItem = null) {
        // Validações básicas
        if (!$idVenda || !is_numeric($idVenda)) {
            throw new Exception('ID da venda inválido');
        }
        
        if (!$valorPago || $valorPago <= 0) {
            throw new Exception('Valor do pagamento deve ser maior que zero');
        }
        
        if (!in_array($formaPagamento, ['dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'transferencia', 'boleto'])) {
            throw new Exception('Forma de pagamento inválida');
        }
        
        // Verificar se a venda existe
        $venda = $this->obterVenda($idVenda);
        if (!$venda) {
            throw new Exception('Venda não encontrada');
        }
        
        // Validar parcela se especificada
        if ($idParcela) {
            $query = "SELECT id_parcela, saldo_parcela, status FROM financeiro_parcelas WHERE id_parcela = ? AND id_venda = ?";
            $stmt = $this->db->execute($query, 'ii', [$idParcela, $idVenda]);
            $parcela = $stmt->get_result()->fetch_assoc();
            
            if (!$parcela) {
                throw new Exception('Parcela não encontrada');
            }
            
            if ($parcela['status'] === 'paga') {
                throw new Exception('Esta parcela já está paga');
            }
            
            if ($valorPago > $parcela['saldo_parcela']) {
                throw new Exception('Valor do pagamento não pode ser maior que o saldo da parcela');
            }
        }
        
        // Validar item se especificado
        if ($idItem) {
            $query = "SELECT id_item, saldo_item, status_pagamento FROM itens_venda WHERE id_item = ? AND id_venda = ?";
            $stmt = $this->db->execute($query, 'ii', [$idItem, $idVenda]);
            $item = $stmt->get_result()->fetch_assoc();
            
            if (!$item) {
                throw new Exception('Item não encontrado');
            }
            
            if ($item['status_pagamento'] === 'pago') {
                throw new Exception('Este item já está pago');
            }
            
            if ($valorPago > $item['saldo_item']) {
                throw new Exception('Valor do pagamento não pode ser maior que o saldo do item');
            }
        }
        
        // Validar saldo total da venda
        if (!$idParcela && !$idItem && $valorPago > $venda['saldo_devedor']) {
            throw new Exception('Valor do pagamento não pode ser maior que o saldo devedor da venda');
        }

        $connection = $this->db->getConnection();
        $connection->begin_transaction();

        try {
            // Registrar movimentação
            $query = "INSERT INTO financeiro_movimentacoes 
                     (id_venda, id_parcela, id_item, valor_pago, forma_pagamento, observacoes)
                     VALUES (?, ?, ?, ?, ?, ?)";

            $this->db->execute($query, 'iiidss', [
                $idVenda,
                $idParcela,
                $idItem,
                $valorPago,
                $formaPagamento,
                $observacoes
            ]);

            // Atualizar parcela
            if ($idParcela) {
                $query = "UPDATE financeiro_parcelas 
                         SET valor_efetivo = COALESCE(valor_efetivo, 0) + ?,
                             saldo_parcela = GREATEST(0, valor_previsto - (COALESCE(valor_efetivo, 0) + ?)),
                             status = CASE 
                                WHEN GREATEST(0, valor_previsto - (COALESCE(valor_efetivo, 0) + ?)) <= 0 THEN 'paga'
                                ELSE 'aberta'
                             END,
                             data_pagamento = CASE 
                                WHEN GREATEST(0, valor_previsto - (COALESCE(valor_efetivo, 0) + ?)) <= 0 THEN NOW()
                                ELSE data_pagamento
                             END
                         WHERE id_parcela = ?";

                $this->db->execute($query, 'ddddi', [
                    $valorPago,
                    $valorPago,
                    $valorPago,
                    $valorPago,
                    $idParcela
                ]);

                // Recalcular parcelas restantes se o valor pago for diferente do previsto
                $this->recalcularParcelasRestantes($idVenda, $idParcela);
            }

            // Atualizar item (se aplicável)
            if ($idItem) {
                $query = "UPDATE itens_venda 
                         SET valor_pago = COALESCE(valor_pago, 0) + ?,
                             saldo_item = GREATEST(0, valor_total - (COALESCE(valor_pago, 0) + ?)),
                             status_pagamento = CASE 
                                WHEN GREATEST(0, valor_total - (COALESCE(valor_pago, 0) + ?)) <= 0 THEN 'pago'
                                WHEN (COALESCE(valor_pago, 0) + ?) > 0 THEN 'parcial'
                                ELSE 'pendente'
                             END
                         WHERE id_item = ?";

                $this->db->execute($query, 'ddddi', [
                    $valorPago,
                    $valorPago,
                    $valorPago,
                    $valorPago,
                    $idItem
                ]);
            }

            // Atualizar venda
            $this->atualizarStatusVenda($idVenda);

            $connection->commit();
            
            // Registrar log de auditoria
            $this->registrarLog('REGISTRAR_PAGAMENTO', $idVenda, "Pagamento de R$ $valorPago via $formaPagamento");
            
            return $this->db->getConnection()->insert_id;
        } catch (Exception $e) {
            $connection->rollback();
            throw new Exception('Erro ao processar pagamento: ' . $e->getMessage());
        }
    }

    private function recalcularParcelasRestantes($idVenda, $idParcelaPaga) {
        // Obter informações da parcela paga
        $query = "SELECT numero_parcela, valor_previsto, valor_efetivo FROM financeiro_parcelas WHERE id_parcela = ?";
        $stmt = $this->db->execute($query, 'i', [$idParcelaPaga]);
        $parcelaPaga = $stmt->get_result()->fetch_assoc();

        if (!$parcelaPaga) {
            return;
        }

        $diferenca = $parcelaPaga['valor_previsto'] - $parcelaPaga['valor_efetivo'];
        
        // Se não houve diferença, não precisa recalcular
        if (abs($diferenca) < 0.01) {
            return;
        }

        // Obter parcelas restantes (abertas) com número maior que a parcela paga
        $query = "SELECT id_parcela, numero_parcela, valor_previsto, saldo_parcela 
                 FROM financeiro_parcelas 
                 WHERE id_venda = ? AND status = 'aberta' AND numero_parcela > ? 
                 ORDER BY numero_parcela";
        $stmt = $this->db->execute($query, 'ii', [$idVenda, $parcelaPaga['numero_parcela']]);
        $parcelasRestantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($parcelasRestantes)) {
            return;
        }

        // Distribuir a diferença nas parcelas restantes
        $quantidadeParcelas = count($parcelasRestantes);
        $ajustePorParcela = $diferenca / $quantidadeParcelas;
        $ajusteAcumulado = 0;

        foreach ($parcelasRestantes as $index => $parcela) {
            $novoValor = $parcela['valor_previsto'] + $ajustePorParcela;
            
            // Na última parcela, ajustar para compensar arredondamentos
            if ($index === $quantidadeParcelas - 1) {
                $novoValor = $parcela['valor_previsto'] + ($diferenca - $ajusteAcumulado);
            } else {
                $ajusteAcumulado += $ajustePorParcela;
            }

            // Atualizar valor previsto e saldo da parcela
            $query = "UPDATE financeiro_parcelas 
                     SET valor_previsto = ?, 
                         saldo_parcela = ?
                     WHERE id_parcela = ?";

            $this->db->execute($query, 'ddi', [
                round($novoValor, 2),
                round($novoValor, 2),
                $parcela['id_parcela']
            ]);
        }
    }

    public function atualizarStatusVenda($idVenda) {
        $query = "SELECT valor_total, SUM(valor_pago) as valor_total_pago FROM itens_venda WHERE id_venda = ? GROUP BY id_venda";
        $stmt = $this->db->execute($query, 'i', [$idVenda]);
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $valorTotal = $result['valor_total'];
            $valorPago = $result['valor_total_pago'] ?? 0;
            $saldoDevedor = $valorTotal - $valorPago;

            $status = 'aberta';
            if ($saldoDevedor <= 0) {
                $status = 'paga';
            } elseif ($valorPago > 0) {
                $status = 'parcial';
            }

            $query = "UPDATE vendas SET valor_pago = ?, saldo_devedor = ?, status_geral = ? WHERE id_venda = ?";
            $this->db->execute($query, 'ddsi', [
                $valorPago,
                $saldoDevedor,
                $status,
                $idVenda
            ]);
        }
    }

    public function obterParcelas($idVenda) {
        $query = "SELECT * FROM financeiro_parcelas WHERE id_venda = ? ORDER BY numero_parcela";
        $stmt = $this->db->execute($query, 'i', [$idVenda]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obterVenda($idVenda) {
        $query = "SELECT * FROM vendas WHERE id_venda = ?";
        $stmt = $this->db->execute($query, 'i', [$idVenda]);
        return $stmt->get_result()->fetch_assoc();
    }

    public function obterDashboard() {
        // Vendas do mês
        $query = "SELECT COUNT(*) as total_vendas, SUM(valor_total) as valor_total
                 FROM vendas
                 WHERE MONTH(data_venda) = MONTH(NOW()) AND YEAR(data_venda) = YEAR(NOW())";
        $vendas = $this->db->select($query)[0];

        // Parcelas vencidas
        $query = "SELECT COUNT(*) as total_vencidas, SUM(saldo_parcela) as valor_vencido
                 FROM financeiro_parcelas
                 WHERE data_vencimento < CURDATE() AND status = 'aberta'";
        $vencidas = $this->db->select($query)[0];

        // Próximas cobranças
        $query = "SELECT fp.*, v.id_cliente, c.nome
                 FROM financeiro_parcelas fp
                 JOIN vendas v ON fp.id_venda = v.id_venda
                 JOIN clientes c ON v.id_cliente = c.id_cliente
                 WHERE fp.status = 'aberta' AND fp.data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                 ORDER BY fp.data_vencimento";
        $proximas = $this->db->select($query);

        return [
            'vendas' => $vendas,
            'vencidas' => $vencidas,
            'proximas' => $proximas
        ];
    }
    
    private function registrarLog($acao, $idVenda, $detalhes) {
        // Criar tabela de logs se não existir
        $query = "CREATE TABLE IF NOT EXISTS logs_auditoria (
            id_log INT PRIMARY KEY AUTO_INCREMENT,
            acao VARCHAR(50) NOT NULL,
            id_venda INT,
            detalhes TEXT,
            id_usuario INT,
            data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip VARCHAR(45)
        )";
        $this->db->execute($query);
        
        // Inserir registro de log
        $query = "INSERT INTO logs_auditoria (acao, id_venda, detalhes, id_usuario, ip)
                 VALUES (?, ?, ?, ?, ?)";
        
        $this->db->execute($query, 'sisis', [
            $acao,
            $idVenda,
            $detalhes,
            $_SESSION['id_usuario'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? 'localhost'
        ]);
    }
    
    public function obterLogsAuditoria($idVenda = null, $dataInicio = null, $dataFim = null) {
        $query = "SELECT la.*, u.nome as nome_usuario 
                 FROM logs_auditoria la 
                 LEFT JOIN usuarios u ON la.id_usuario = u.id_usuario 
                 WHERE 1=1";
        
        $params = [];
        $tipos = '';
        
        if ($idVenda) {
            $query .= " AND la.id_venda = ?";
            $tipos .= 'i';
            $params[] = $idVenda;
        }
        
        if ($dataInicio) {
            $query .= " AND DATE(la.data_hora) >= ?";
            $tipos .= 's';
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $query .= " AND DATE(la.data_hora) <= ?";
            $tipos .= 's';
            $params[] = $dataFim;
        }
        
        $query .= " ORDER BY la.data_hora DESC LIMIT 100";
        
        if (!empty($tipos)) {
            $stmt = $this->db->execute($query, $tipos, $params);
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        
        return $this->db->select($query);
    }
}
?>
