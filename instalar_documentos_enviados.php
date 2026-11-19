<?php
/**
 * Script para criar tabela de documentos enviados
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

try {
    $sql = file_get_contents('criar_tabela_documentos_enviados.sql');
    
    // Dividir por ponto e vírgula para executar cada comando
    $comandos = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($comandos as $comando) {
        if (!empty($comando)) {
            $db->exec($comando);
        }
    }
    
    echo "✅ Tabela 'documentos_enviados' criada com sucesso!\n";
    echo "✅ Diretório de uploads verificado!\n";
} catch (PDOException $e) {
    echo "❌ Erro ao criar tabela: " . $e->getMessage() . "\n";
}

