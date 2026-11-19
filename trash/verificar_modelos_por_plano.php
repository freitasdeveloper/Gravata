<?php
require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->query("
    SELECT 
        cd.nome as categoria_nome,
        md.tipo_plano_necessario,
        COUNT(*) as total
    FROM modelos_documentos md 
    JOIN categorias_documentos cd ON md.categoria_id = cd.id 
    WHERE md.ativo = 1
    GROUP BY cd.id, cd.nome, md.tipo_plano_necessario 
    ORDER BY cd.ordem, md.tipo_plano_necessario
");

echo "=== MODELOS POR CATEGORIA E PLANO ===\n\n";

$categorias = [];
foreach ($stmt->fetchAll() as $row) {
    $cat = $row['categoria_nome'];
    if (!isset($categorias[$cat])) {
        $categorias[$cat] = [];
    }
    $categorias[$cat][$row['tipo_plano_necessario']] = (int)$row['total'];
}

foreach ($categorias as $cat => $planos) {
    echo "{$cat}:\n";
    $total = 0;
    foreach ($planos as $plano => $qtd) {
        echo "  - {$plano}: {$qtd} modelos\n";
        $total += $qtd;
    }
    echo "  TOTAL: {$total} modelos\n";
    $basicos = $planos['basico'] ?? 0;
    if ($basicos < 30) {
        echo "  ⚠️ FALTAM " . (30 - $basicos) . " modelos BÁSICOS para usuários do plano gratis verem 30!\n";
    }
    echo "\n";
}

?>

