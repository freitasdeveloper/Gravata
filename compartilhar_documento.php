<?php
/**
 * Compartilhamento de documento
 * Cria link público com hash de segurança
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/documento.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documentoId = intval($_POST['documento_id'] ?? 0);
    
    $documentoGerador = new DocumentoGerador();
    $documento = $documentoGerador->getDocumento($documentoId, $auth->getUsuario()['id']);
    
    if (!$documento) {
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => false, 'mensagem' => 'Documento não encontrado']);
        exit;
    }
    
    // Gerar link compartilhável
    $hash = $documento['hash_seguranca'];
    $linkCompartilhavel = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                          '://' . $_SERVER['HTTP_HOST'] . 
                          dirname($_SERVER['SCRIPT_NAME']) . 
                          '/ver_documento_publico.php?id=' . $documentoId . '&hash=' . $hash;
    
    header('Content-Type: application/json');
    echo json_encode([
        'sucesso' => true,
        'link' => $linkCompartilhavel,
        'hash' => $hash
    ]);
    exit;
}

// GET - retornar link existente
$documentoId = intval($_GET['id'] ?? 0);

$documentoGerador = new DocumentoGerador();
$documento = $documentoGerador->getDocumento($documentoId, $auth->getUsuario()['id']);

if (!$documento) {
    header('Content-Type: application/json');
    echo json_encode(['sucesso' => false, 'mensagem' => 'Documento não encontrado']);
    exit;
}

$hash = $documento['hash_seguranca'];
$linkCompartilhavel = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                      '://' . $_SERVER['HTTP_HOST'] . 
                      dirname($_SERVER['SCRIPT_NAME']) . 
                      '/ver_documento_publico.php?id=' . $documentoId . '&hash=' . $hash;

header('Content-Type: application/json');
echo json_encode([
    'sucesso' => true,
    'link' => $linkCompartilhavel,
    'hash' => $hash
]);

