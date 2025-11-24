# Sistema de Controle de Vendas de Semi-Joias

Um sistema completo e flexível para controle de vendas de semi-joias com foco em gestão financeira robusta e parcelamento dinâmico.

## Objetivo do Projeto

Desenvolver um Sistema de Controle de Vendas de Semi-Joias focado em flexibilidade no cadastro de pedidos e controle financeiro robusto (pagamentos parciais e dinâmicos).

## Stack Tecnológico

- **Backend**: PHP Estruturado (Sem uso de frameworks pesados, foco em código funcional e limpo)
- **Frontend**: HTML5, CSS3 e JavaScript (Vanilla)
- **Banco de Dados**: MySQL
- **Interatividade**: Uso intensivo de AJAX para operações dinâmicas
- **Arquitetura**: Código organizado, moderno e limpo, com separa��o clara entre l�gica, conex�o com banco e visualiza��o

## Requisitos do Sistema

### Servidor Web
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache ou Nginx
- Extens�es PHP: mysqli, json, mbstring

### Navegadores Suportados
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Instala��o

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

### 3. Configurar Conex�o
Edite o arquivo `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'semi_joias');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 4. Configurar Diret�rios
Garanta que os diret�rios tenham permiss�es de escrita:
```bash
chmod 755 public/
chmod 755 public/assets/
chmod 755 public/uploads/
```

### 5. Acessar o Sistema
Abra o navegador e acesse: `http://seu-dominio/sistema_venda/public/`

## Funcionalidades Principais

### Autentica��o
- Login seguro com sess�o
- Logout automático
- Prote��o contra CSRF

### Gest�o de Clientes
- Cadastro completo de clientes
- Valida��o de CPF/CNPJ
- Formata��o automática de telefone
- Hist�rico de compras

### Gest�o de Vendas
- **Cadastro "On-the-Fly"**: Produtos cadastrados no momento da venda
- **M�ltiplos Itens**: Venda funciona como pedido com diversas pe�as
- **Status por Item**: Controle individual de pagamento por item
- **Parcelamento Dinâmico**: Geraço automática de parcelas

### Controle Financeiro Avan�ado
- **Pagamentos Parciais**: Aceita pagamentos menores que o total
- **Associa��o Flex�vel**: Pagamento para pedido total ou item espec�fico
- **Edi��o de Valor**: Valores de parcelas podem ser editados
- **Rec�lculo Automático**: Ajuste automático do saldo restante

### Dashboard e Relatórios
- **KPIs em Tempo Real**: Vendas, recebimentos, pendências
- **Gráficos Interativos**: Visualiza��o de dados com Chart.js
- **Alertas de Cobran�a**: Pr�ximos vencimentos e parcelas vencidas
- **Relatórios Export�veis**: CSV, PDF e impress�o

## Interface do Usu�rio

### Telas Dispon�veis
1. **Login** (`login.php`) - Autentica��o segura
2. **Dashboard** (`dashboard.php`) - KPIs e gráficos
3. **Nova Venda** (`nova_venda.php`) - PDV completo
4. **Vendas** (`vendas.php`) - Listagem e filtros
5. **Detalhes da Venda** (`detalhes_venda.php`) - Gest�o financeira
6. **Clientes** (`clientes.php`) - Gest�o de clientes
7. **Relatórios** (`relatorios.php`) - Relatórios detalhados

### Design Responsivo
- Layout adapt�vel para desktop, tablet e mobile
- Interface moderna com gradientes e sombras
- Cores consistentes e acess�veis
- �cones intuitivos (emoji para compatibilidade)

## Estrutura do Projeto

```
sistema_venda/
?classes/                 # Classes PHP
?   ?Database.php        # Conex�o com banco
?   ?Venda.php          # L�gica de vendas
?   ?Financeiro.php     # Gest�o financeira
?   ?Cliente.php        # Gest�o de clientes
?   ?...
?config/                 # Arquivos de configura��o
?   ?database.php       # Configura��o do BD
?   ?auth.php          # Autentica��o
?   ?constants.php     # Constantes do sistema
?public/                 # Arquivos p�blicos
?   ?assets/           # CSS, JS, imagens
?   ?api/              # Endpoints AJAX
?   ?modals/           # Modais HTML
?   ?*.php             # Telas do sistema
?sql/                   # Scripts SQL
?   ?schema.sql        # Estrutura do banco
?cron/                  # Tarefas agendadas
    ?processar_lembretes.php
```

## Banco de Dados

### Tabelas Principais
- **`clientes`** - Dados dos compradores
- **`vendas`** - Cabe�alho dos pedidos
- **`itens_venda`** - Produtos das vendas
- **`financeiro_parcelas`** - Parcelas geradas
- **`financeiro_movimentacoes`** - Pagamentos efetivos
- **`usuarios`** - Usu�rios do sistema
- **`configuracoes`** - Configura��es gerais

### Conven��es
- Chaves prim�rias: `id_nomedatabela`
- Campos de data: `data_criacao`, `data_atualizacao`
- Campos monet�rios: `DECIMAL(10,2)`
- �ndices para performance em consultas frequentes

## Fluxo de Trabalho

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
3. Alertas de vencimentos pr�ximos
4. Exporta��o para an�lise externa

## ?Valida��es e Seguran�a

### Valida��es Implementadas
- **CPF/CNPJ**: Algoritmos oficiais de valida��o
- **Email**: Formato v�lido e dom�nio verific�vel
- **Telefone**: Formato brasileiro com DDD
- **Valores**: Positivos e dentro de limites
- **Campos obrigat�rios**: Verifica��o completa

### Seguran�a
- **SQL Injection**: Prepared statements
- **XSS**: Escapamento de sa�da HTML
- **CSRF**: Tokens em formul�rios
- **Sess�o**: Configura��es seguras
- **Senhas**: Hash com password_hash()

## Relatórios Dispon�veis

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

## Suporte

### Contato
- Desenvolvedor: Jéssica Batista
- Linkedin: https://www.linkedin.com/in/jessicaabatista/

## Licença

Este projeto está licenciado sob a MIT License - veja o arquivo LICENSE para detalhes.

---

