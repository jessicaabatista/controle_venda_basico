<?php
/**
 * Classe Pagamento - Gerenciamento de Pagamentos
 * Arquivo: classes/Pagamento.php
 * ✅ REFATORADO PARA USAR MySQLi (Consistente com resto do projeto)
 */

class Pagamento {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    /**
     * Obter pagamentos de uma venda
     */
    public function obterPorVenda($idVenda) {
        try {
            // ✅ Tabela pagamentos pode não existir, criar se necessário
            $this->criarTabelaPagamentosSeNecessario();

            $sql = "SELECT 
                        id_pagamento,
                        id_venda,
                        valor,
                        tipo_pagamento,
                        data_pagamento,
                        observacoes,
                        data_criacao
                    FROM pagamentos
                    WHERE id_venda = ?
                    ORDER BY data_pagamento DESC";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param('i', $idVenda);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $pagamentos = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $pagamentos ?: [];
        } catch (Exception $e) {
            error_log('Erro ao obter pagamentos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter um pagamento pelo ID
     */
    public function obter($idPagamento) {
        try {
            $this->criarTabelaPagamentosSeNecessario();

            $sql = "SELECT * FROM pagamentos WHERE id_pagamento = ?";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param('i', $idPagamento);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $pagamento = $resultado->fetch_assoc();
            $stmt->close();

            return $pagamento;
        } catch (Exception $e) {
            error_log('Erro ao obter pagamento: ' . $e->getMessage());
            throw new Exception('Erro ao obter pagamento: ' . $e->getMessage());
        }
    }

    /**
     * Criar novo pagamento
     */
    public function criar($idVenda, $idUsuario, $valor, $tipoPagamento, $dataPagamento, $observacoes = '') {
        try {
            $this->criarTabelaPagamentosSeNecessario();

            // Validações
            if (!$idVenda || $idVenda <= 0) {
                throw new Exception('ID da venda inválido');
            }

            if ($valor <= 0) {
                throw new Exception('Valor do pagamento deve ser maior que zero');
            }

            if (empty($tipoPagamento)) {
                throw new Exception('Tipo de pagamento é obrigatório');
            }

            if (empty($dataPagamento)) {
                throw new Exception('Data do pagamento é obrigatória');
            }

            // Verificar se a venda existe
            $sqlVenda = "SELECT id_venda, saldo_devedor FROM vendas WHERE id_venda = ?";
            $stmtVenda = $this->db->prepare($sqlVenda);
            if (!$stmtVenda) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmtVenda->bind_param('i', $idVenda);
            $stmtVenda->execute();
            $resultadoVenda = $stmtVenda->get_result();
            $venda = $resultadoVenda->fetch_assoc();
            $stmtVenda->close();

            if (!$venda) {
                throw new Exception('Venda não encontrada');
            }

            // Validar se o valor não excede o saldo devedor
            if ($valor > $venda['saldo_devedor']) {
                throw new Exception('Valor do pagamento não pode exceder o saldo devedor de R$ ' . 
                    number_format($venda['saldo_devedor'], 2, ',', '.'));
            }

            // Inserir pagamento
            $sql = "INSERT INTO pagamentos (
                        id_venda,
                        id_usuario,
                        valor,
                        tipo_pagamento,
                        data_pagamento,
                        observacoes,
                        data_criacao
                    ) VALUES (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        NOW()
                    )";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $valor = floatval($valor);
            $stmt->bind_param('iidss', $idVenda, $idUsuario, $valor, $tipoPagamento, $dataPagamento);

            if (!$stmt->execute()) {
                throw new Exception('Erro ao executar inserção: ' . $stmt->error);
            }

            $idPagamento = $this->db->insert_id;
            $stmt->close();

            error_log("Pagamento criado: ID=$idPagamento, Venda=$idVenda, Valor=$valor");

            // Atualizar status da venda
            $this->atualizarStatusVenda($idVenda);

            return $idPagamento;
        } catch (Exception $e) {
            error_log('Erro ao criar pagamento: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Deletar um pagamento
     */
    public function deletar($idPagamento) {
        try {
            $this->criarTabelaPagamentosSeNecessario();

            if (!$idPagamento || $idPagamento <= 0) {
                throw new Exception('ID do pagamento inválido');
            }

            // Obter dados do pagamento para atualizar a venda depois
            $pagamento = $this->obter($idPagamento);

            if (!$pagamento) {
                throw new Exception('Pagamento não encontrado');
            }

            $idVenda = $pagamento['id_venda'];

            // Deletar pagamento
            $sql = "DELETE FROM pagamentos WHERE id_pagamento = ?";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param('i', $idPagamento);

            if (!$stmt->execute()) {
                throw new Exception('Erro ao deletar pagamento');
            }

            $stmt->close();

            // Atualizar status da venda
            $this->atualizarStatusVenda($idVenda);

            return true;
        } catch (Exception $e) {
            error_log('Erro ao deletar pagamento: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter valor total pago de uma venda
     */
    public function obterTotalPago($idVenda) {
        try {
            $this->criarTabelaPagamentosSeNecessario();

            $sql = "SELECT COALESCE(SUM(valor), 0) as total FROM pagamentos WHERE id_venda = ?";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Erro ao preparar statement: ' . $this->db->error);
            }

            $stmt->bind_param('i', $idVenda);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $dados = $resultado->fetch_assoc();
            $stmt->close();

            return floatval($dados['total'] ?? 0);
        } catch (Exception $e) {
            error_log('Erro ao obter total pago: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Atualizar status da venda baseado nos pagamentos
     */
    private function atualizarStatusVenda($idVenda) {
        try {
            // Obter dados da venda
            $sqlVenda = "SELECT id_venda, valor_total FROM vendas WHERE id_venda = ?";
            $stmtVenda = $this->db->prepare($sqlVenda);
            if (!$stmtVenda) {
                return;
            }

            $stmtVenda->bind_param('i', $idVenda);
            $stmtVenda->execute();
            $resultadoVenda = $stmtVenda->get_result();
            $venda = $resultadoVenda->fetch_assoc();
            $stmtVenda->close();

            if (!$venda) {
                return;
            }

            // Obter total pago
            $totalPago = $this->obterTotalPago($idVenda);

            // Determinar novo status
            if ($totalPago >= $venda['valor_total']) {
                $novoStatus = STATUS_VENDA_PAGA;
            } elseif ($totalPago > 0) {
                $novoStatus = STATUS_VENDA_PARCIAL;
            } else {
                $novoStatus = STATUS_VENDA_ABERTA;
            }

            // Atualizar venda
            $sqlUpdate = "UPDATE vendas 
                         SET status_geral = ?,
                             valor_pago = ?,
                             saldo_devedor = ?,
                             data_atualizacao = NOW()
                         WHERE id_venda = ?";

            $stmtUpdate = $this->db->prepare($sqlUpdate);
            if (!$stmtUpdate) {
                return;
            }

            $saldoDevedor = $venda['valor_total'] - $totalPago;

            $stmtUpdate->bind_param('sddi', $novoStatus, $totalPago, $saldoDevedor, $idVenda);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            error_log("Status da venda $idVenda atualizado: $novoStatus (Pago: $totalPago, Saldo: $saldoDevedor)");
        } catch (Exception $e) {
            error_log('Erro ao atualizar status da venda: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NOVO: Criar tabela pagamentos se não existir
     */
    private function criarTabelaPagamentosSeNecessario() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS pagamentos (
                id_pagamento INT PRIMARY KEY AUTO_INCREMENT,
                id_venda INT NOT NULL,
                id_usuario INT,
                valor DECIMAL(10, 2) NOT NULL,
                tipo_pagamento VARCHAR(50),
                data_pagamento DATE NOT NULL,
                observacoes TEXT,
                data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_venda) REFERENCES vendas(id_venda) ON DELETE CASCADE,
                FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
                INDEX idx_venda (id_venda),
                INDEX idx_data (data_pagamento)
            )";

            $this->db->query($sql);
            error_log('Tabela pagamentos verificada/criada com sucesso');
        } catch (Exception $e) {
            error_log('Erro ao criar tabela pagamentos: ' . $e->getMessage());
        }
    }
}
?>