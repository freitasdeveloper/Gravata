<?php
/**
 * Endpoint AJAX para retornar template do modelo
 */

header('Content-Type: application/json');

require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/documento.php';

$auth = new Auth();

// Verificar se está logado
if (!$auth->isLoggedIn()) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autorizado']);
    exit;
}

$modeloId = $_GET['modelo'] ?? 0;

$documento = new DocumentoGerador();
$modelo = $documento->getModelo($modeloId);

if (!$modelo) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Modelo não encontrado']);
    exit;
}

echo json_encode([
    'sucesso' => true,
    'template' => $modelo['conteudo_template']
]);

