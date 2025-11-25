<?php
// classes/Labels.php - Gerenciar labels do sistema para fácil generalização

class Labels {
    private static $labels = [
        // Sistema geral
        'app_name' => 'Sistema de Controle de Vendas',
        'app_description' => 'Controle completo de vendas, clientes e financeiro',
        
        // Produtos/Itens
        'produto_singular' => 'Produto',
        'produto_plural' => 'Produtos',
        'item_singular' => 'Item',
        'item_plural' => 'Itens',
        'codigo_produto' => 'Código do Produto',
        'descricao_produto' => 'Descrição do Produto',
        'quantidade_produto' => 'Quantidade',
        'preco_unitario' => 'Preço Unitário',
        
        // Vendas
        'venda_singular' => 'Venda',
        'venda_plural' => 'Vendas',
        'nova_venda' => 'Nova Venda',
        'gestao_vendas' => 'Gestão de Vendas',
        
        // Clientes
        'cliente_singular' => 'Cliente',
        'cliente_plural' => 'Clientes',
        'novo_cliente' => 'Novo Cliente',
        'gestao_clientes' => 'Gestão de Clientes',
        
        // Financeiro
        'parcela_singular' => 'Parcela',
        'parcela_plural' => 'Parcelas',
        'valor_total' => 'Valor Total',
        'valor_pago' => 'Valor Pago',
        'saldo_devedor' => 'Saldo Devedor',
        'parcelas_label' => 'Parcelas',
        
        // Ações
        'adicionar' => 'Adicionar',
        'remover' => 'Remover',
        'editar' => 'Editar',
        'deletar' => 'Deletar',
        'salvar' => 'Salvar',
        'cancelar' => 'Cancelar',
        'registrar_pagamento' => 'Registrar Pagamento',
        
        // Mensagens
        'sucesso_salvo' => 'Salvo com sucesso!',
        'sucesso_criado' => 'Criado com sucesso!',
        'sucesso_atualizado' => 'Atualizado com sucesso!',
        'sucesso_deletado' => 'Deletado com sucesso!',
        'erro_generico' => 'Erro ao processar a operação',
        'confirma_deletar' => 'Tem certeza que deseja deletar este registro?',
        'nenhum_resultado' => 'Nenhum resultado encontrado',
    ];

    public static function get($key, $default = '') {
        return self::$labels[$key] ?? $default;
    }

    public static function set($key, $value) {
        self::$labels[$key] = $value;
    }

    public static function all() {
        return self::$labels;
    }
}
?>