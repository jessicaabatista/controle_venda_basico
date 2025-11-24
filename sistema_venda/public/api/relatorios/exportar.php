<?php
header('Content-Type: application/json');
require_once '../../config/auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Relatorio.php';

$relatorio = new Relatorio();

$tipo = $_GET['tipo'] ?? 'fluxo_caixa';
$formato = $_GET['formato'] ?? 'csv';
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
$dataFim = $_GET['data_fim'] ?? date('Y-m-d');

try {
    // Obter dados do relatório
    $dados = [];
    $nomeArquivo = '';
    
    if ($tipo === 'fluxo_caixa') {
        $dados = $relatorio->fluxoCaixa($dataInicio, $dataFim);
        $nomeArquivo = 'fluxo_caixa_' . date('d_m_Y', strtotime($dataInicio)) . '_a_' . date('d_m_Y', strtotime($dataFim));
    } elseif ($tipo === 'desempenho_vendas') {
        $dados = $relatorio->desempenhoVendas($dataInicio, $dataFim);
        $nomeArquivo = 'desempenho_vendas_' . date('d_m_Y', strtotime($dataInicio)) . '_a_' . date('d_m_Y', strtotime($dataFim));
    } elseif ($tipo === 'pendencias') {
        $dados = $relatorio->pendencias();
        $nomeArquivo = 'pendencias_' . date('d_m_Y');
    }

    if (empty($dados)) {
        throw new Exception('Nenhum dado encontrado para o relatório solicitado');
    }

    if ($formato === 'csv') {
        exportarCSV($dados, $tipo, $nomeArquivo);
    } elseif ($formato === 'pdf') {
        exportarPDF($dados, $tipo, $nomeArquivo, $dataInicio, $dataFim);
    } else {
        throw new Exception('Formato de exportação inválido');
    }

} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}

function exportarCSV($dados, $tipo, $nomeArquivo) {
    // Configurar headers para download CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '.csv"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    // Criar arquivo CSV em memória
    $output = fopen('php://output', 'w');

    // Adicionar BOM para UTF-8
    fwrite($output, "\xEF\xBB\xBF");

    // Cabeçalho CSV baseado no tipo
    if ($tipo === 'fluxo_caixa') {
        fputcsv($output, ['Data', 'Movimentações', 'Valor Total']);
        foreach ($dados as $linha) {
            fputcsv($output, [
                date('d/m/Y', strtotime($linha['data'])),
                $linha['qtd_movimentacoes'],
                number_format($linha['valor_pago'], 2, ',', '.')
            ]);
        }
    } elseif ($tipo === 'desempenho_vendas') {
        fputcsv($output, ['Cliente', 'Vendas', 'Valor Total', 'Valor Pago', 'Pendente', 'Taxa de Recebimento']);
        foreach ($dados as $linha) {
            fputcsv($output, [
                $linha['nome'],
                $linha['total_vendas'],
                number_format($linha['valor_total'], 2, ',', '.'),
                number_format($linha['valor_pago'], 2, ',', '.'),
                number_format($linha['valor_total'] - $linha['valor_pago'], 2, ',', '.'),
                ($linha['valor_total'] > 0 ? round(($linha['valor_pago'] / $linha['valor_total']) * 100, 1) : 0) . '%'
            ]);
        }
    } elseif ($tipo === 'pendencias') {
        fputcsv($output, ['ID Venda', 'Cliente', 'Data da Venda', 'Saldo Pendente']);
        foreach ($dados as $linha) {
            fputcsv($output, [
                '#' . $linha['id_venda'],
                $linha['nome'],
                date('d/m/Y', strtotime($linha['data_venda'])),
                number_format($linha['saldo_total'], 2, ',', '.')
            ]);
        }
    }

    fclose($output);
    exit;
}

function exportarPDF($dados, $tipo, $nomeArquivo, $dataInicio, $dataFim) {
    // Configurar headers para download PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '.pdf"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    // Criar conteúdo HTML para o PDF
    $html = gerarHTMLPDF($dados, $tipo, $dataInicio, $dataFim);

    // Usar uma biblioteca simples para gerar PDF (usando DOMPDF se disponível, ou fallback para HTML)
    if (class_exists('Dompdf')) {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($nomeArquivo . '.pdf', ['Attachment' => 1]);
    } else {
        // Fallback: gerar HTML estilizado que pode ser salvo como PDF pelo navegador
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '.html"');
        echo $html;
    }
    exit;
}

function gerarHTMLPDF($dados, $tipo, $dataInicio, $dataFim) {
    $titulo = '';
    $periodo = '';
    
    if ($tipo === 'fluxo_caixa') {
        $titulo = 'Fluxo de Caixa';
        $periodo = date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim));
    } elseif ($tipo === 'desempenho_vendas') {
        $titulo = 'Desempenho de Vendas';
        $periodo = date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim));
    } elseif ($tipo === 'pendencias') {
        $titulo = 'Pendências de Recebimento';
        $periodo = 'Data de geração: ' . date('d/m/Y H:i');
    }

    $html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>' . $titulo . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; text-align: center; }
        h2 { color: #666; margin-bottom: 10px; }
        .periodo { text-align: center; color: #666; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f5f5f5; padding: 10px; text-align: left; font-weight: bold; border: 1px solid #ddd; }
        td { padding: 8px; border: 1px solid #ddd; }
        .total { background: #f9f9f9; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <h1>' . $titulo . '</h1>
    <p class="periodo">' . $periodo . '</p>';

    // Adicionar tabela baseada no tipo
    if ($tipo === 'fluxo_caixa') {
        $html .= '<table>
            <tr>
                <th>Data</th>
                <th>Movimentações</th>
                <th>Valor Total</th>
            </tr>';
        
        $totalValor = 0;
        $totalMovimentacoes = 0;
        
        foreach ($dados as $linha) {
            $html .= '<tr>
                <td>' . date('d/m/Y', strtotime($linha['data'])) . '</td>
                <td>' . $linha['qtd_movimentacoes'] . '</td>
                <td>R$ ' . number_format($linha['valor_pago'], 2, ',', '.') . '</td>
            </tr>';
            $totalValor += $linha['valor_pago'];
            $totalMovimentacoes += $linha['qtd_movimentacoes'];
        }
        
        $html .= '<tr class="total">
            <td>TOTAL</td>
            <td>' . $totalMovimentacoes . '</td>
            <td>R$ ' . number_format($totalValor, 2, ',', '.') . '</td>
        </tr>';
        
    } elseif ($tipo === 'desempenho_vendas') {
        $html .= '<table>
            <tr>
                <th>Cliente</th>
                <th>Vendas</th>
                <th>Valor Total</th>
                <th>Valor Pago</th>
                <th>Pendente</th>
                <th>Taxa de Recebimento</th>
            </tr>';
        
        $totalVendas = 0;
        $totalPago = 0;
        $totalPendente = 0;
        
        foreach ($dados as $linha) {
            $pendente = $linha['valor_total'] - $linha['valor_pago'];
            $html .= '<tr>
                <td>' . htmlspecialchars($linha['nome']) . '</td>
                <td>' . $linha['total_vendas'] . '</td>
                <td>R$ ' . number_format($linha['valor_total'], 2, ',', '.') . '</td>
                <td>R$ ' . number_format($linha['valor_pago'], 2, ',', '.') . '</td>
                <td>R$ ' . number_format($pendente, 2, ',', '.') . '</td>
                <td>' . ($linha['valor_total'] > 0 ? round(($linha['valor_pago'] / $linha['valor_total']) * 100, 1) : 0) . '%</td>
            </tr>';
            $totalVendas += $linha['valor_total'];
            $totalPago += $linha['valor_pago'];
            $totalPendente += $pendente;
        }
        
        $html .= '<tr class="total">
            <td>TOTAL</td>
            <td>-</td>
            <td>R$ ' . number_format($totalVendas, 2, ',', '.') . '</td>
            <td>R$ ' . number_format($totalPago, 2, ',', '.') . '</td>
            <td>R$ ' . number_format($totalPendente, 2, ',', '.') . '</td>
            <td>' . ($totalVendas > 0 ? round(($totalPago / $totalVendas) * 100, 1) : 0) . '%</td>
        </tr>';
        
    } elseif ($tipo === 'pendencias') {
        $html .= '<table>
            <tr>
                <th>ID Venda</th>
                <th>Cliente</th>
                <th>Data da Venda</th>
                <th>Saldo Pendente</th>
            </tr>';
        
        $totalPendente = 0;
        
        foreach ($dados as $linha) {
            $html .= '<tr>
                <td>#' . $linha['id_venda'] . '</td>
                <td>' . htmlspecialchars($linha['nome']) . '</td>
                <td>' . date('d/m/Y', strtotime($linha['data_venda'])) . '</td>
                <td>R$ ' . number_format($linha['saldo_total'], 2, ',', '.') . '</td>
            </tr>';
            $totalPendente += $linha['saldo_total'];
        }
        
        $html .= '<tr class="total">
            <td>TOTAL</td>
            <td>-</td>
            <td>-</td>
            <td>R$ ' . number_format($totalPendente, 2, ',', '.') . '</td>
        </tr>';
    }

    $html .= '</table>
    <div class="footer">
        <p>Relatório gerado em ' . date('d/m/Y H:i:s') . ' pelo Sistema de Semi-Joias</p>
    </div>
</body>
</html>';

    return $html;
}
?>
