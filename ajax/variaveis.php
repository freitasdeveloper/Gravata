<?php
/**
 * Endpoint AJAX para retornar variáveis de um modelo
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

// Parse das variáveis JSON
$variaveis = json_decode($modelo['variaveis'], true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('Erro JSON: ' . json_last_error_msg() . ' - Modelo ID: ' . $modeloId);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao processar variáveis: ' . json_last_error_msg()]);
    exit;
}

if (!isset($variaveis['variaveis'])) {
    error_log('Variáveis não encontradas - Modelo ID: ' . $modeloId);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Estrutura de variáveis inválida']);
    exit;
}

echo json_encode([
    'sucesso' => true,
    'variaveis' => $variaveis['variaveis']
]);

