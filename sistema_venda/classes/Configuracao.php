<?php
class Configuracao {
    private $db;
    private $configuracoes = [];

    public function __construct() {
        $this->db = new Database();
        $this->carregar();
    }

    private function carregar() {
        $query = "SELECT chave, valor FROM configuracoes";
        $resultado = $this->db->select($query);

        foreach ($resultado as $item) {
            $this->configuracoes[$item['chave']] = $item['valor'];
        }
    }

    public function obter($chave, $padrao = '') {
        return $this->configuracoes[$chave] ?? $padrao;
    }

    public function definir($chave, $valor) {
        // Verificar se existe
        $query = "SELECT id FROM configuracoes WHERE chave = ?";
        $stmt = $this->db->execute($query, 's', [$chave]);
        
        if ($stmt->get_result()->num_rows > 0) {
            // Atualizar
            $query = "UPDATE configuracoes SET valor = ? WHERE chave = ?";
            $this->db->execute($query, 'ss', [$valor, $chave]);
        } else {
            // Inserir
            $query = "INSERT INTO configuracoes (chave, valor) VALUES (?, ?)";
            $this->db->execute($query, 'ss', [$chave, $valor]);
        }

        $this->configuracoes[$chave] = $valor;
        return true;
    }

    public function obterTodas() {
        return $this->configuracoes;
    }

    public function obterConfiguracoesEmpresa() {
        return [
            'nome_empresa' => $this->obter('nome_empresa', 'Minha Empresa'),
            'email_empresa' => $this->obter('email_empresa', ''),
            'telefone_empresa' => $this->obter('telefone_empresa', ''),
            'endereco_empresa' => $this->obter('endereco_empresa', ''),
            'cnpj_empresa' => $this->obter('cnpj_empresa', '')
        ];
    }

    public function obterConfiguracoesPagamento() {
        return [
            'multa_atraso' => (float) $this->obter('multa_atraso', '0'),
            'juros_mensais' => (float) $this->obter('juros_mensais', '0'),
            'dias_carencia' => (int) $this->obter('dias_carencia', '0')
        ];
    }

    public function obterConfiguracoesEmail() {
        return [
            'email_host' => $this->obter('email_host', ''),
            'email_port' => $this->obter('email_port', ''),
            'email_user' => $this->obter('email_user', ''),
            'email_pass' => $this->obter('email_pass', ''),
            'email_de' => $this->obter('email_de', ''),
            'notificar_pagamento' => $this->obter('notificar_pagamento', '1')
        ];
    }
}
?>