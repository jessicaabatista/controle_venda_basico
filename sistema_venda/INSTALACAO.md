# Guia de Instalação - Sistema de Gestão de Semi-Joias

## Requisitos
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite ativado
- Composer (opcional, para PHPMailer)

## Passo 1: Preparação do Servidor

1. Clonar ou fazer download do projeto
2. Criar um banco de dados no MySQL
3. Executar o script SQL de criação das tabelas (ver arquivo database.sql)

## Passo 2: Configuração do Projeto

1. Editar arquivo `config/database.php` com suas credenciais
2. Criar pasta `public/uploads` com permissão 755
3. Criar pasta `cron/logs` com permissão 755

## Passo 3: Configurar o Cron (Opcional)

Para enviar lembretes automáticos de pagamento:

```bash
crontab -e