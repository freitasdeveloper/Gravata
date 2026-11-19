<?php
/**
 * Download do documento em DOCX editável
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

// Se PhpOffice/PhpWord estiver disponível, usar
if (class_exists('PhpOffice\PhpWord\PhpWord')) {
    use PhpOffice\PhpWord\PhpWord;
    use PhpOffice\PhpWord\Style\Font;
    
    $phpWord = new PhpWord();
    
    // Configurações do documento
    $section = $phpWord->addSection([
        'marginTop' => 1134,    // 3cm em twips (1cm = 378 twips)
        'marginRight' => 756,   // 2cm
        'marginBottom' => 756,  // 2cm
        'marginLeft' => 1134    // 3cm
    ]);
    
    // Processar conteúdo
    $conteudo = $documento['conteudo_final'];
    $paragrafos = preg_split('/\n\s*\n/', $conteudo);
    
    foreach ($paragrafos as $paragrafo) {
        $paragrafo = trim($paragrafo);
        if (empty($paragrafo)) continue;
        
        $isTitulo = preg_match('/^[A-ZÁÀÂÃÉÊÍÓÔÕÚÇ\s\-]+$/', $paragrafo) && strlen($paragrafo) < 80;
        $isSubtitulo = preg_match('/^\d+[\.\)]?\s/', $paragrafo);
        
        if ($isTitulo) {
            $section->addText(
                $paragrafo,
                ['bold' => true, 'size' => 14],
                ['alignment' => 'center', 'spaceAfter' => 240]
            );
        } elseif ($isSubtitulo) {
            $section->addText(
                $paragrafo,
                ['bold' => true, 'size' => 12],
                ['spaceAfter' => 120]
            );
        } else {
            $section->addText(
                str_replace("\n", "\n", $paragrafo),
                ['size' => 12],
                ['indentation' => ['firstLine' => 473]] // 1.25cm
            );
        }
    }
    
    $nomeArquivo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $documento['titulo']);
    $nomeArquivo = substr($nomeArquivo, 0, 50);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '.docx"');
    
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
    exit;
}

// Fallback: gerar arquivo RTF simples (mais compatível sem bibliotecas)
$conteudo = $documento['conteudo_final'];
$nomeArquivo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $documento['titulo']);
$nomeArquivo = substr($nomeArquivo, 0, 50);

// Converter para RTF básico
$rtf = "{\\rtf1\\ansi\\deff0\n";
$rtf .= "{\\fonttbl{\\f0 Times New Roman;}}\n";
$rtf .= "\\f0\\fs24\n"; // Fonte 12pt

$paragrafos = preg_split('/\n\s*\n/', $conteudo);
foreach ($paragrafos as $paragrafo) {
    $paragrafo = trim($paragrafo);
    if (empty($paragrafo)) continue;
    
    $paragrafo = str_replace("\n", "\\par ", $paragrafo);
    $paragrafo = str_replace(['{', '}', '\\'], ['\\{', '\\}', '\\\\'], $paragrafo);
    
    $rtf .= $paragrafo . "\\par\n\\par\n";
}

$rtf .= "}";

header('Content-Type: application/rtf');
header('Content-Disposition: attachment; filename="' . $nomeArquivo . '.rtf');
echo $rtf;

