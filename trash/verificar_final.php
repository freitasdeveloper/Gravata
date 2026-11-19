<?php
require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

echo "=== VERIFICAÇÃO FINAL - MODELOS BÁSICOS ===\n\n";

$stmt = $db->query("
    SELECT 
        cd.nome,
        COUNT(md.id) as total_basicos
    FROM categorias_documentos cd
    LEFT JOIN modelos_documentos md ON cd.id = md.categoria_id 
        AND md.ativo = 1 
        AND md.tipo_plano_necessario = 'basico'
    WHERE cd.ativo = 1
    GROUP BY cd.id, cd.nome
    ORDER BY cd.ordem
");

$todosOk = true;
foreach ($stmt->fetchAll() as $row) {
    $status = $row['total_basicos'] >= 30 ? '✅ OK' : '⚠️ FALTAM';
    if ($row['total_basicos'] < 30) $todosOk = false;
    printf("%-12s: %2d modelos %s\n", $row['nome'], $row['total_basicos'], $status);
}

echo "\n";
if ($todosOk) {
    echo "✅ PERFEITO! Todas as categorias têm 30+ modelos BÁSICOS!\n";
    echo "✅ Usuários do plano GRATUITO verão 30 modelos em cada categoria!\n";
} else {
    echo "⚠️ Algumas categorias ainda precisam de mais modelos básicos.\n";
}

?>

