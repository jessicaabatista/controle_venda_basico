<?php
class Email {
    private $config;
    private $db;

    public function __construct() {
        $this->db = new Database();
        require_once __DIR__ . '/../classes/Configuracao.php';
        $configuracao = new Configuracao();
        $this->config = $configuracao->obterConfiguracoesEmail();
    }

    public function enviarNotificacaoPagamento($idVenda, $idCliente) {
        require_once __DIR__ . '/../classes/Cliente.php';
        require_once __DIR__ . '/../classes/Venda.php';

        $cliente_obj = new Cliente();
        $venda_obj = new Venda();

        $cliente = $cliente_obj->obter($idCliente);
        $venda = $venda_obj->obter($idVenda);

        if (!$cliente['email']) {
            return false;
        }

        $assunto = 'Confirmação de Pagamento - Semi-Joias';

        $corpo = <<<HTML
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 5px; }
                .content { padding: 20px; }
                .footer { color: #999; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Semi-Joias</h1>
                    <p>Confirmação de Pagamento Recebido</p>
                </div>

                <div class="content">
                    <p>Olá <strong>{$cliente['nome']}</strong>,</p>

                    <p>Confirmamos o recebimento do seu pagamento referente à venda #<strong>{$venda['id_venda']}</strong>.</p>

                    <h3>Detalhes da Venda:</h3>
                    <ul>
                        <li><strong>Data:</strong> {$this->formatarData($venda['data_venda'])}</li>
                        <li><strong>Valor Total:</strong> R$ {$this->formatarMoeda($venda['valor_total'])}</li>
                        <li><strong>Valor Pago:</strong> R$ {$this->formatarMoeda($venda['valor_pago'])}</li>
                        <li><strong>Saldo Devedor:</strong> R$ {$this->formatarMoeda($venda['saldo_devedor'])}</li>
                    </ul>

                    <p>Obrigado por sua confiança!</p>
                </div>

                <div class="footer">
                    <p>Este é um email automático. Não responda a este email.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;

        return $this->enviar($cliente['email'], $assunto, $corpo);
    }

    public function enviarLembretePagamento($idParcela, $email, $nomeCliente, $dataVencimento, $saldo) {
        $assunto = 'Lembrete de Pagamento - Semi-Joias';

        $corpo = <<<HTML
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 5px; }
                .content { padding: 20px; }
                .alerta { background: #fff3e0; padding: 15px; border-left: 4px solid #ff9800; margin: 20px 0; }
                .footer { color: #999; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Semi-Joias</h1>
                    <p>Lembrete de Pagamento</p>
                </div>

                <div class="content">
                    <p>Olá <strong>{$nomeCliente}</strong>,</p>

                    <div class="alerta">
                        <strong>? Atenção!</strong> Você tem uma parcela a vencer.
                    </div>

                    <h3>Detalhes da Parcela:</h3>
                    <ul>
                        <li><strong>Data de Vencimento:</strong> {$this->formatarData($dataVencimento)}</li>
                        <li><strong>Valor:</strong> R$ {$this->formatarMoeda($saldo)}</li>
                    </ul>

                    <p>Por favor, realize o pagamento até a data de vencimento para evitar cobranças de multa e juros.</p>

                    <p>Dúvidas? Entre em contato conosco!</p>
                </div>

                <div class="footer">
                    <p>Este é um email automático. Não responda a este email.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;

        return $this->enviar($email, $assunto, $corpo);
    }

    private function enviar($para, $assunto, $corpo) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $this->config['email_de'] . "\r\n";

        // Se tiver configuração de SMTP, usar PHPMailer
        if ($this->config['email_host']) {
            return $this->enviarSMTP($para, $assunto, $corpo);
        }

        // Caso contrário, usar mail nativo do PHP
        return mail($para, $assunto, $corpo, $headers);
    }

    private function enviarSMTP($para, $assunto, $corpo) {
        // Necessário instalar via Composer: composer require phpmailer/phpmailer
        // require_once __DIR__ . '/../vendor/autoload.php';

        // use PHPMailer\PHPMailer\PHPMailer;
        // use PHPMailer\PHPMailer\Exception;

        // $mail = new PHPMailer(true);

        // try {
        //     $mail->isSMTP();
        //     $mail->Host = $this->config['email_host'];
        //     $mail->SMTPAuth = true;
        //     $mail->Username = $this->config['email_user'];
        //     $mail->Password = $this->config['email_pass'];
        //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        //     $mail->Port = $this->config['email_port'];

        //     $mail->setFrom($this->config['email_de']);
        //     $mail->addAddress($para);
        //     $mail->isHTML(true);
        //     $mail->Subject = $assunto;
        //     $mail->Body = $corpo;

        //     return $mail->send();
        // } catch (Exception $e) {
        //     return false;
        // }

        return true;
    }

    private function formatarData($data) {
        return date('d/m/Y', strtotime($data));
    }

    private function formatarMoeda($valor) {
        return number_format($valor, 2, ',', '.');
    }
}
?>