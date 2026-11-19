<?php
/**
 * Verificação direta no banco de dados
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

echo "=== VERIFICAÇÃO DE MODELOS NO BANCO DE DADOS ===\n\n";

try {
    // Contar modelos por categoria
    $stmt = $db->query("
        SELECT 
            cd.id,
            cd.nome as categoria_nome,
            COUNT(md.id) as total_modelos
        FROM categorias_documentos cd
        LEFT JOIN modelos_documentos md ON cd.id = md.categoria_id AND md.ativo = 1
        WHERE cd.ativo = 1
        GROUP BY cd.id, cd.nome
        ORDER BY cd.ordem
    ");
    
    $resultados = $stmt->fetchAll();
    
    echo "Categoria | Total | Faltam\n";
    echo "----------|-------|-------\n";
    
    $totalGeral = 0;
    $faltamTotal = 0;
    
    foreach ($resultados as $row) {
        $total = (int)$row['total_modelos'];
        $faltam = max(0, 30 - $total);
        $totalGeral += $total;
        $faltamTotal += $faltam;
        
        printf("%-9s | %5d | %5d\n", $row['categoria_nome'], $total, $faltam);
    }
    
    echo "\nTOTAL GERAL: {$totalGeral} modelos\n";
    echo "FALTAM: {$faltamTotal} modelos para completar todas as categorias\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

?>

