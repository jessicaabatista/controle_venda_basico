# Sistema de Controle de Vendas de Semi-Joias

Um sistema completo e flexível para controle de vendas de semi-joias com foco em gestão financeira robusta e parcelamento dinâmico.

## ?? Objetivo do Projeto

Desenvolver um Sistema de Controle de Vendas de Semi-Joias focado em flexibilidade no cadastro de pedidos e controle financeiro robusto (pagamentos parciais e dinâmicos).

## ?? Stack Tecnológico

- **Backend**: PHP Estruturado (Sem uso de frameworks pesados, foco em código funcional e limpo)
- **Frontend**: HTML5, CSS3 e JavaScript (Vanilla)
- **Banco de Dados**: MySQL
- **Interatividade**: Uso intensivo de AJAX para operações dinâmicas
- **Arquitetura**: Código organizado, moderno e limpo, com separação clara entre lógica, conexão com banco e visualização

## ?? Requisitos do Sistema

### Servidor Web
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache ou Nginx
- Extensões PHP: mysqli, json, mbstring

### Navegadores Suportados
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## ?? Instalação

### 1. Clonar o Projeto
```bash
git clone <repositorio-do-projeto>
cd sistema_venda
```

### 2. Configurar Banco de Dados
```sql
-- Criar banco de dados
CREATE DATABASE semi_joias;

-- Importar schema
mysql -u usuario -p semi_joias < sql/schema.sql
```

### 3. Configurar Conexão
Edite o arquivo `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'semi_joias');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 4. Configurar Diretórios
Garanta que os diretórios tenham permissões de escrita:
```bash
chmod 755 public/
chmod 755 public/assets/
chmod 755 public/uploads/
```

### 5. Acessar o Sistema
Abra o navegador e acesse: `http://seu-dominio/sistema_venda/public/`

## ?? Funcionalidades Principais

### ?? Autenticação
- Login seguro com sessão
- Logout automático
- Proteção contra CSRF

### ?? Gestão de Clientes
- Cadastro completo de clientes
- Validação de CPF/CNPJ
- Formatação automática de telefone
- Histórico de compras

### ?? Gestão de Vendas
- **Cadastro "On-the-Fly"**: Produtos cadastrados no momento da venda
- **Múltiplos Itens**: Venda funciona como pedido com diversas peças
- **Status por Item**: Controle individual de pagamento por item
- **Parcelamento Dinâmico**: Geração automática de parcelas

### ?? Controle Financeiro Avançado
- **Pagamentos Parciais**: Aceita pagamentos menores que o total
- **Associação Flexível**: Pagamento para pedido total ou item específico
- **Edição de Valor**: Valores de parcelas podem ser editados
- **Recálculo Automático**: Ajuste automático do saldo restante

### ?? Dashboard e Relatórios
- **KPIs em Tempo Real**: Vendas, recebimentos, pendências
- **Gráficos Interativos**: Visualização de dados com Chart.js
- **Alertas de Cobrança**: Próximos vencimentos e parcelas vencidas
- **Relatórios Exportáveis**: CSV, PDF e impressão

## ?? Interface do Usuário

### Telas Disponíveis
1. **Login** (`login.php`) - Autenticação segura
2. **Dashboard** (`dashboard.php`) - KPIs e gráficos
3. **Nova Venda** (`nova_venda.php`) - PDV completo
4. **Vendas** (`vendas.php`) - Listagem e filtros
5. **Detalhes da Venda** (`detalhes_venda.php`) - Gestão financeira
6. **Clientes** (`clientes.php`) - Gestão de clientes
7. **Relatórios** (`relatorios.php`) - Relatórios detalhados

### Design Responsivo
- Layout adaptável para desktop, tablet e mobile
- Interface moderna com gradientes e sombras
- Cores consistentes e acessíveis
- Ícones intuitivos (emoji para compatibilidade)

## ?? Estrutura do Projeto

```
sistema_venda/
??? classes/                 # Classes PHP
?   ??? Database.php        # Conexão com banco
?   ??? Venda.php          # Lógica de vendas
?   ??? Financeiro.php     # Gestão financeira
?   ??? Cliente.php        # Gestão de clientes
?   ??? ...
??? config/                 # Arquivos de configuração
?   ??? database.php       # Configuração do BD
?   ??? auth.php          # Autenticação
?   ??? constants.php     # Constantes do sistema
??? public/                 # Arquivos públicos
?   ??? assets/           # CSS, JS, imagens
?   ??? api/              # Endpoints AJAX
?   ??? modals/           # Modais HTML
?   ??? *.php             # Telas do sistema
??? sql/                   # Scripts SQL
?   ??? schema.sql        # Estrutura do banco
??? cron/                  # Tarefas agendadas
    ??? processar_lembretes.php
```

## ?? Banco de Dados

### Tabelas Principais
- **`clientes`** - Dados dos compradores
- **`vendas`** - Cabeçalho dos pedidos
- **`itens_venda`** - Produtos das vendas
- **`financeiro_parcelas`** - Parcelas geradas
- **`financeiro_movimentacoes`** - Pagamentos efetivos
- **`usuarios`** - Usuários do sistema
- **`configuracoes`** - Configurações gerais

### Convenções
- Chaves primárias: `id_nomedatabela`
- Campos de data: `data_criacao`, `data_atualizacao`
- Campos monetários: `DECIMAL(10,2)`
- Índices para performance em consultas frequentes

## ?? Fluxo de Trabalho

### 1. Criar Venda
1. Acessar "Nova Venda"
2. Selecionar cliente (existente ou novo)
3. Adicionar itens dinamicamente
4. Configurar parcelamento
5. Gerar parcelas automaticamente

### 2. Gerenciar Pagamentos
1. Acessar detalhes da venda
2. Clicar em "Registrar Pagamento"
3. Escolher tipo: Total, Parcela ou Item
4. Informar valor (pode ser editado)
5. Sistema recalcula saldos automaticamente

### 3. Acompanhar Financeiro
1. Dashboard mostra KPIs em tempo real
2. Relatórios detalhados por período
3. Alertas de vencimentos próximos
4. Exportação para análise externa

## ??? Validações e Segurança

### Validações Implementadas
- **CPF/CNPJ**: Algoritmos oficiais de validação
- **Email**: Formato válido e domínio verificável
- **Telefone**: Formato brasileiro com DDD
- **Valores**: Positivos e dentro de limites
- **Campos obrigatórios**: Verificação completa

### Segurança
- **SQL Injection**: Prepared statements
- **XSS**: Escapamento de saída HTML
- **CSRF**: Tokens em formulários
- **Sessão**: Configurações seguras
- **Senhas**: Hash com password_hash()

## ?? Relatórios Disponíveis

### Fluxo de Caixa
- Movimentações por dia
- Total recebido no período
- Média diária de recebimentos
- Gráfico de barras interativo

### Desempenho de Vendas
- Vendas por cliente
- Taxa de recebimento
- Valores pendentes
- Comparativo total vs pago

### Pendências
- Vendas com saldo devedor
- Parcelas vencidas
- Clientes inadimplentes
- Valores totais em aberto

## ?? Melhorias Implementadas

### ? Concluídas
- Correção de codificação UTF-8 em todos os arquivos
- Sistema de validações robusto (CPF, CNPJ, telefone, email)
- Formatação automática de campos
- Interface responsiva e moderna
- Sistema financeiro completo com pagamentos parciais
- Dashboard interativo com gráficos
- Relatórios exportáveis

### ?? Futuras (Sugestões)
- Sistema de notificações por email
- Integração com gateways de pagamento
- Aplicativo mobile (PWA)
- Multi-idioma
- Backup automático
- API REST para integrações

## ?? Solução de Problemas

### Issues Comuns
1. **Codificação UTF-8**: ? Resolvido
2. **Validação de CPF/CNPJ**: ? Implementado
3. **Formatação de campos**: ? Automatizada
4. **Responsividade**: ? Mobile-first

### Debug
- Ativar modo debug: `define('DEBUG', true);`
- Logs de erro: `cron/logs/`
- Console JavaScript para validações

## ?? Suporte

### Documentação
- Leia `INSTALACAO.md` para detalhes técnicos
- Consulte os comentários no código para exemplos
- Verifique o console do navegador para erros JavaScript

### Contato
- Desenvolvedor: [Seu Nome]
- Email: [seu@email.com]
- Documentação: [Link para documentação]

## ?? Licença

Este projeto está licenciado sob a MIT License - veja o arquivo LICENSE para detalhes.

---

## ?? Próximos Passos

1. **Testar o sistema** completamente
2. **Configurar ambiente** de produção
3. **Definir backup** automático
4. **Treinar usuários** finais
5. **Monitorar performance** e usabilidade

