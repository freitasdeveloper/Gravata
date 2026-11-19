<?php
/**
 * Script para importar apenas os modelos faltantes
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

$arquivoSQL = __DIR__ . '/modelos_faltantes.sql';

if (!file_exists($arquivoSQL)) {
    die("Erro: Arquivo modelos_faltantes.sql não encontrado! Execute primeiro verificar_e_completar_modelos.php");
}

// Usar a mesma função de importação melhorada
require_once 'importar_modelos_v2.php';

// Ler e processar arquivo
$linhas = file($arquivoSQL, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$queries = [];
$currentQuery = '';
$dentroInsert = false;

foreach ($linhas as $linha) {
    $linha = trim($linha);
    
    if (empty($linha) || strpos($linha, '--') === 0) {
        continue;
    }
    
    if (stripos($linha, 'INSERT INTO') !== false) {
        $dentroInsert = true;
        $currentQuery = $linha;
    } elseif ($dentroInsert) {
        $currentQuery .= ' ' . $linha;
        
        if (preg_match('/\)\s*;\s*$/', $linha)) {
            $queries[] = $currentQuery;
            $currentQuery = '';
            $dentroInsert = false;
        }
    } elseif (stripos($linha, 'UPDATE') !== false) {
        $queries[] = $linha;
    }
}

$sucessos = 0;
$erros = 0;
$duplicados = 0;
$mensagens = [];

try {
    $db->beginTransaction();
    
    foreach ($queries as $query) {
        if (empty(trim($query))) continue;
        
        try {
            $db->exec($query);
            $sucessos++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false || 
                strpos($e->getMessage(), '1062') !== false) {
                $duplicados++;
                continue;
            }
            $erros++;
            if (count($mensagens) < 5) {
                $mensagens[] = substr($e->getMessage(), 0, 100);
            }
        }
    }
    
    $db->commit();
    
    header('Location: verificar_e_completar_modelos.php?importado=1');
    exit;
    
} catch (Exception $e) {
    $db->rollBack();
    die("Erro: " . $e->getMessage());
}

?>

