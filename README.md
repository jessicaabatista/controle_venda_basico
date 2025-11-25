# Sistema de Controle de Vendas

Um sistema completo para gestão de vendas desenvolvido em PHP puro com arquitetura orientada a objetos, focado em controle financeiro avançado, parcelamento flexível e dashboard interativo em tempo real.

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 📋 Sobre o Sistema

Sistema empresarial desenvolvido em PHP puro para gestão completa de vendas, permitindo controle total sobre vendas, clientes e finanças. Com interface moderna e intuitiva, oferece funcionalidades avançadas como parcelamento dinâmico, pagamentos parciais e relatórios detalhados.

### 🎯 Diferenciais Principais

- **🔄 Parcelamento Flexível**: Até 24x com recálculo automático inteligente
- **💰 Pagamentos Parciais**: Aceita valores menores que o total com ajuste automático
- **📊 Dashboard Completo**: 6 gráficos interativos em tempo real com Chart.js
- **🔌 API RESTful**: Integração fácil com outros sistemas
- **📧 Notificações Automáticas**: Lembretes de pagamento por email
- **📈 Relatórios Detalhados**: Exportação CSV para análise externa
- **📱 Totalmente Responsivo**: Adaptado para mobile e desktop
- **🔒 Segurança Avançada**: Proteção contra CSRF, XSS e SQL Injection

## ✨ Funcionalidades Principais

### 👥 Gestão de Usuários
- Sistema de autenticação seguro com sessões PHP
- Registro e login de usuários
- Proteção contra brute force
- Timeout de sessão configurável

### 🏪 Gestão de Clientes
- Cadastro completo com validações rigorosas
- Máscaras automáticas para telefone e CPF/CNPJ
- Histórico completo de compras
- Status ativo/inativo
- Observações personalizadas
- Busca instantânea via AJAX

### 💳 Sistema de Vendas Avançado
- **Cadastro "On-the-Fly"**: Produtos cadastrados no momento da venda
- **Múltiplos Itens**: Sistema funciona como pedido com diversas peças
- **Status Individual**: Controle de pagamento por item
- **Cancelamento**: Vendas podem ser canceladas mantendo histórico
- **Validações Rigorosas**: Frontend e backend

### 💰 Controle Financeiro Robusto
- **Pagamentos Parciais**: Aceita valores menores que o total
- **Parcelamento Dinâmico**: Até 24 parcelas com recálculo automático
- **Edição de Valores**: Parcelas podem ter valores editados
- **Múltiplas Formas**: Dinheiro, cartão, PIX, transferência, boleto
- **Recálculo Inteligente**: Ajuste automático de saldos restantes

### 📊 Dashboard Interativo
- **KPIs em Tempo Real**: Vendas, recebimentos, pendências
- **6 Gráficos Diferentes**:
  - Vendas por dia (mês atual)
  - Status das vendas (pizza)
  - Top 10 clientes (barra)
  - Formas de pagamento (pizza)
  - Evolução mensal (linha)
  - Produtos mais vendidos (barra)
- **Alertas de Cobrança**: Próximos vencimentos e vencidas
- **Métricas Adicionais**: Ticket médio, taxa de conversão, etc.

### 📈 Relatórios e Exportação
- **Fluxo de Caixa**: Movimentações por período
- **Desempenho de Vendas**: Análise por cliente
- **Pendências Financeiras**: Vencidas e em aberto
- **Exportação CSV**: Para análise externa
- **Filtros Avançados**: Data, cliente, status

## 🏗️ Arquitetura do Sistema

```
sistema_venda/
├── classes/                          # Camada de negócio PHP
│   ├── Database.php                  # Conexão e operações MySQL (Singleton)
│   ├── Venda.php                     # Gestão de vendas e pedidos
│   ├── Financeiro.php                # Controle financeiro e parcelas
│   ├── Cliente.php                   # CRUD de clientes
│   ├── Usuario.php                   # Autenticação e usuários
│   ├── Email.php                     # Envio de notificações
│   ├── Relatorio.php                 # Geração de relatórios
│   ├── Configuracao.php              # Configurações do sistema
│   └── Labels.php                    # Labels e textos do sistema
├── config/                           # Arquivos de configuração
│   ├── auth.php                      # Funções de autenticação
│   ├── config.php                    # Configuração central do sistema
│   └── constants.php                 # Constantes globais
├── public/                           # Arquivos públicos acessíveis
│   ├── assets/                       # Recursos estáticos
│   │   ├── css/style.css             # Estilos principais (responsivo)
│   │   └── js/                       # JavaScript modular
│   │       ├── app.js                # Funções globais e máscaras
│   │       ├── dashboard.js          # Lógica dos gráficos
│   │       ├── financeiro.js         # Operações financeiras
│   │       ├── validacoes.js         # Validações de formulários
│   │       ├── vendas.js             # Lógica de vendas
│   │       └── mascaras.js           # Máscaras de formulário
│   ├── api/                          # Endpoints RESTful
│   │   ├── clientes/                 # API de clientes
│   │   ├── dashboard/                # API do dashboard
│   │   ├── financeiro/               # API financeira
│   │   ├── relatorios/               # API de relatórios
│   │   └── vendas/                   # API de vendas
│   ├── includes/navbar.php           # Navegação reutilizável
│   ├── modals/pagamento.html         # Janelas modais HTML
│   ├── dashboard.php                 # Dashboard principal com KPIs
│   ├── login.php                     # Tela de autenticação
│   ├── logout.php                    # Logout do sistema
│   ├── nova_venda.php                # PDV / Nova venda
│   ├── vendas.php                    # Listagem de vendas
│   ├── detalhes_venda.php            # Gestão financeira da venda
│   ├── clientes.php                  # Gestão de clientes
│   ├── detalhes_cliente.php          # Histórico do cliente
│   ├── relatorios.php                # Relatórios detalhados
│   ├── configuracoes.php             # Configurações do sistema
│   └── registro.php                  # Registro de usuários
├── sql/                              # Scripts SQL
│   └── schema.sql                    # Estrutura completa do banco
├── cron/                             # Tarefas agendadas
│   ├── processar_lembretes.php       # Envio de lembretes de pagamento
│   └── logs/                         # Logs das tarefas (criado automaticamente)
├── logs/                             # Logs do sistema
├── uploads/                          # Upload de arquivos
├── .htaccess                         # Configuração Apache
└── SOLUCAO_ASSETS.md                 # Documentação de solução de assets
```

## 🚀 Instalação Rápida

### 1. Clonar o Projeto
```bash
git clone <repositorio-url>
cd sistema_venda
```

### 2. Banco de Dados
```sql
CREATE DATABASE sistema_vendas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql -u root -p sistema_vendas < sql/schema.sql
```

### 3. Configurar Conexão
Edite `config/config.php` ou use variáveis de ambiente:
```bash
export DB_HOST=localhost
export DB_USER=root
export DB_PASS=sua_senha
export DB_NAME=sistema_vendas
```

### 4. Acessar o Sistema
```
http://seu-dominio/sistema_venda/public/
```

## ⚙️ Configuração

### Variáveis de Ambiente
```bash
# Banco de Dados
DB_HOST=localhost
DB_USER=root
DB_PASS=password
DB_NAME=sistema_vendas

# Aplicação
APP_ENV=production
APP_NAME=Sistema de Controle de Vendas

# Email
SMTP_HOST=smtp.seudominio.com
SMTP_PORT=587
SMTP_USER=email@seudominio.com
SMTP_PASS=senha_email
SMTP_SECURE=tls

# Logs
LOG_LEVEL=info
```

### Configurações via Banco
```sql
-- Configurações da empresa
UPDATE configuracoes SET valor = 'Sua Empresa Ltda' WHERE chave = 'nome_empresa';
UPDATE configuracoes SET valor = 'contato@empresa.com' WHERE chave = 'email_empresa';
UPDATE configuracoes SET valor = '(11) 9999-9999' WHERE chave = 'telefone_empresa';

-- Configurações financeiras
UPDATE configuracoes SET valor = '2.00' WHERE chave = 'multa_atraso';
UPDATE configuracoes SET valor = '1.00' WHERE chave = 'juros_mensais';
UPDATE configuracoes SET valor = '5' WHERE chave = 'dias_carencia';
```

### 🔧 Solução de Problemas com Assets
Se você encontrar problemas com CSS/JS não carregando, consulte o documento:
**[SOLUCAO_ASSETS.md](sistema_venda/SOLUCAO_ASSETS.md)**

Este documento contém soluções completas para:
- Configuração de URLs em desenvolvimento
- Problemas com .htaccess
- Testes de diagnóstico
- Configuração para produção

## 📊 Dashboard e Relatórios

### KPIs Principais
- **Total de Vendas (Mês)**: Valor e quantidade
- **Parcelas Vencidas**: Alertas de cobrança
- **Próximas Cobranças (30 dias)**: Previsão de recebimento
- **Saldo a Receber**: Total em aberto

### Métricas Adicionais
- **Ticket Médio**: Valor médio por venda
- **Taxa de Recebimento**: Percentual pago vs total
- **Clientes Ativos**: Quantidade de clientes com compras
- **Vencem Hoje**: Parcelas com vencimento no dia

### Gráficos Interativos
1. **Vendas por Dia**: Evolução diária do mês atual
2. **Status das Vendas**: Distribuição por situação
3. **Top 10 Clientes**: Maiores compradores
4. **Formas de Pagamento**: Distribuição por método
5. **Evolução Mensal**: Tendência de crescimento
6. **Produtos Mais Vendidos**: Ranking de itens

## 🔐 Segurança

### Implementações de Segurança
- **SQL Injection**: Uso de prepared statements em todas as queries
- **XSS**: Escapamento HTML com `htmlspecialchars()`
- **CSRF**: Tokens em formulários sensíveis
- **Session Hijacking**: Regeneração de ID de sessão
- **Password Security**: Hash com `password_hash()` (bcrypt)
- **Input Validation**: Validação rigorosa no frontend e backend
- **File Upload**: Restrição de extensões e tamanho
- **Access Control**: Verificação de autenticação em páginas restritas

### Configurações de Segurança
```php
// Configurações de senha
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_ALGORITHM', PASSWORD_BCRYPT);
define('PASSWORD_OPTIONS', ['cost' => 12]);

// Proteção contra brute force
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 15 * 60); // 15 minutos

// Timeout de sessão
define('SESSION_TIMEOUT', 30 * 60); // 30 minutos
```

## 📡 API REST

### Autenticação
A API utiliza sessões PHP para autenticação. O usuário deve estar logado no sistema para acessar os endpoints.

### Clientes
```http
POST /api/clientes/salvar.php
GET /api/clientes/pesquisa.php?termo=joao
DELETE /api/clientes/deletar.php?id=123
```

### Vendas
```http
POST /api/vendas/salvar.php
POST /api/vendas/adicionar_item.php
DELETE /api/vendas/remover_item.php?id=789
POST /api/vendas/gerar_parcelas.php
GET /api/vendas/detalhes.php?id=123
```

### Financeiro
```http
POST /api/financeiro/processar_pagamento.php
```

### Dashboard
```http
GET /api/dashboard/dados.php
```

### Relatórios
```http
GET /api/relatorios/exportar.php?tipo=fluxo_caixa&data_inicio=2024-01-01&data_fim=2024-12-31&formato=csv
```

## ⏰ Tarefas Agendadas

### Lembretes de Pagamento
Configurar no crontab do servidor:

```bash
# Editar crontab
crontab -e

# Executar diariamente às 9h
0 9 * * * /usr/bin/php /caminho/completo/sistema_venda/cron/processar_lembretes.php

# Executar a cada hora para testes
0 * * * * /usr/bin/php /caminho/completo/sistema_venda/cron/processar_lembretes.php
```

**Critérios de Envio:**
- Parcelas vencendo nos próximos 5 dias
- Apenas parcelas não pagas
- Marca como enviado para evitar duplicidade

### Log do Sistema
```bash
# Verificar logs
tail -f /caminho/completo/sistema_venda/cron/logs/lembretes.log

# Logs de erro do PHP
tail -f /var/log/php_errors.log
```

## 🔧 Manutenção e Troubleshooting

### Problemas Comuns

#### Conexão com Banco Falhou
```bash
# Verificar se o banco existe
mysql -u root -p -e "SHOW DATABASES LIKE 'sistema_vendas'"

# Testar conexão manualmente
mysql -u root -p sistema_vendas

# Verificar se o MySQL está rodando
sudo systemctl status mysql
```

#### Login Não Funciona
```bash
# Verificar tabela usuarios
mysql -u root -p sistema_vendas -e "SELECT * FROM usuarios;"

# Limpar sessões PHP
sudo rm -rf /var/lib/php/sessions/*
```

#### Gráficos Não Aparecem
1. Verificar conexão com internet (CDN Chart.js)
2. Console do navegador (F12) para erros JavaScript
3. Verificar se há dados no dashboard
4. Limpar cache do navegador

#### Permissões Negadas
```bash
# Configurar permissões corretas
sudo chmod 755 sistema_venda/public/
sudo chmod 755 sistema_venda/public/assets/
sudo chmod 755 -R sistema_venda/cron/logs/
sudo chown www-data:www-data sistema_venda/ -R
```

#### Erro 500 - Internal Server Error
```bash
# Verificar log de erro do Apache/Nginx
tail -f /var/log/apache2/error.log

# Verificar log de erro do PHP
tail -f /var/log/php_errors.log
```

### Debug Mode
Para habilitar debug temporariamente:
```php
// No início do arquivo config/config.php
define('APP_ENVIRONMENT', 'development');
```

## 📁 Estrutura de Banco de Dados

### Tabelas Principais

#### `vendas`
- id_venda, id_cliente, id_usuario
- data_venda, valor_total, valor_pago, saldo_devedor
- quantidade_parcelas, status_geral, observacoes_pagamento

#### `itens_venda`
- id_item, id_venda, codigo_produto, descricao
- quantidade, valor_unitario, valor_total
- status_pagamento, valor_pago, saldo_item

#### `financeiro_parcelas`
- id_parcela, id_venda, id_item, numero_parcela
- valor_previsto, valor_efetivo, data_vencimento, data_pagamento
- status, saldo_parcela, lembrete_enviado

#### `financeiro_movimentacoes`
- id_movimentacao, id_venda, id_parcela, id_item
- valor_pago, forma_pagamento, data_pagamento, observacoes

#### `clientes`
- id_cliente, nome, email, telefone, endereco
- cpf_cnpj, data_criacao, ativo, observacoes

#### `usuarios`
- id_usuario, nome, email, senha, ativo, data_criacao

#### `configuracoes`
- id, chave, valor, criado_em, atualizado_em

## 🤝 Contribuição

### Como Contribuir
1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

### Padrões de Código
- **PHP**: PSR-12
- **Indentação**: 4 espaços
- **Comentários**: PHPDoc para classes e métodos
- **Nomenclatura**: camelCase para variáveis, PascalCase para classes

## 📄 Licença

Este projeto está licenciado sob a **MIT License**.

```
MIT License

Copyright (c) 2024 Sistema de Controle de Vendas

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 📞 Suporte

Para suporte técnico ou dúvidas:
- 📧 Email: contato@empresa.com
- 📱 Telefone: (11) 9999-9999
- 🌐 Website: https://www.empresa.com
- 🐛 Issues: [GitHub Issues](https://github.com/usuario/sistema_venda/issues)

**Desenvolvido com ❤️ pela equipe de desenvolvimento**
