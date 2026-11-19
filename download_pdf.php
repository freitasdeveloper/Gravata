<?php
/**
 * Download do documento em PDF
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/documento.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    die('Não autorizado');
}

$documentoId = intval($_GET['id'] ?? 0);
$hash = $_GET['hash'] ?? '';

$documentoGerador = new DocumentoGerador();
$documento = $documentoGerador->getDocumento($documentoId, $auth->getUsuario()['id']);

if (!$documento || ($hash && $documento['hash_seguranca'] !== $hash)) {
    http_response_code(404);
    die('Documento não encontrado');
}

// Gerar PDF usando DomPDF ou HTML2PDF
// Para simplificar, vamos usar uma abordagem que converte HTML para PDF
// Nota: Para produção, recomenda-se instalar DomPDF via Composer

// Preparar conteúdo HTML formatado
$conteudo = $documento['conteudo_final'];
$paragrafos = preg_split('/\n\s*\n/', $conteudo);
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 3cm 2cm 2cm 3cm;
            size: A4;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            text-align: justify;
        }
        .abnt-paragrafo {
            text-indent: 1.25cm;
            margin-bottom: 0.5cm;
            margin-top: 0;
        }
        .abnt-paragrafo:first-child,
        .abnt-titulo + .abnt-paragrafo,
        .abnt-subtitulo + .abnt-paragrafo {
            text-indent: 0;
        }
        .abnt-titulo {
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin: 1.5cm 0 1cm 0;
            font-size: 14pt;
        }
        .abnt-subtitulo {
            font-weight: bold;
            text-align: left;
            margin: 1cm 0 0.5cm 0;
            font-size: 12pt;
            text-indent: 0;
        }
    </style>
</head>
<body>';

foreach ($paragrafos as $paragrafo) {
    $paragrafo = trim($paragrafo);
    if (empty($paragrafo)) continue;
    
    $isTitulo = preg_match('/^[A-ZÁÀÂÃÉÊÍÓÔÕÚÇ\s\-]+$/', $paragrafo) && strlen($paragrafo) < 80;
    $isSubtitulo = preg_match('/^\d+[\.\)]?\s/', $paragrafo);
    
    if ($isTitulo) {
        $html .= '<div class="abnt-titulo">' . htmlspecialchars($paragrafo) . '</div>';
    } elseif ($isSubtitulo) {
        $html .= '<div class="abnt-subtitulo">' . nl2br(htmlspecialchars($paragrafo)) . '</div>';
    } else {
        $html .= '<div class="abnt-paragrafo">' . nl2br(htmlspecialchars($paragrafo)) . '</div>';
    }
}

$html .= '</body></html>';

// Tentar usar biblioteca de PDF se disponível, senão retornar HTML para download
// Para produção: instalar DomPDF via Composer
// composer require dompdf/dompdf

$nomeArquivo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $documento['titulo']);
$nomeArquivo = substr($nomeArquivo, 0, 50);

// Se DomPDF estiver disponível, usar
if (class_exists('Dompdf\Dompdf')) {
    use Dompdf\Dompdf;
    use Dompdf\Options;
    
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '.pdf"');
    echo $dompdf->output();
} else {
    // Fallback: retornar HTML com indicação de impressão
    // O navegador pode converter para PDF usando "Imprimir > Salvar como PDF"
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: inline; filename="' . $nomeArquivo . '.html"');
    
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($documento['titulo']) . '</title>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    <style>
        @media print {
            @page {
                margin: 3cm 2cm 2cm 3cm;
                size: A4;
            }
            body {
                margin: 0;
            }
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            text-align: justify;
            padding: 20px;
        }
        .abnt-paragrafo {
            text-indent: 1.25cm;
            margin-bottom: 0.5cm;
            margin-top: 0;
        }
        .abnt-paragrafo:first-child,
        .abnt-titulo + .abnt-paragrafo,
        .abnt-subtitulo + .abnt-paragrafo {
            text-indent: 0;
        }
        .abnt-titulo {
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin: 1.5cm 0 1cm 0;
            font-size: 14pt;
        }
        .abnt-subtitulo {
            font-weight: bold;
            text-align: left;
            margin: 1cm 0 0.5cm 0;
            font-size: 12pt;
            text-indent: 0;
        }
    </style>
</head>
<body>' . $html . '</body></html>';
}

