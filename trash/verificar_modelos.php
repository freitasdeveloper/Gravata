<?php
/**
 * Script para verificar quantos modelos existem por categoria
 * e ajudar na importação se necessário
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificação de Modelos - Sistema Jurídico</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row'>
            <div class='col-md-12'>
                <div class='card'>
                    <div class='card-header bg-primary text-white'>
                        <h4>Verificação de Modelos de Documentos</h4>
                    </div>
                    <div class='card-body'>";

// Verificar se a tabela existe
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM modelos_documentos");
    $total = $stmt->fetch()['total'];
    
    echo "<div class='alert alert-info'>";
    echo "<h5><i class='bi bi-info-circle'></i> Total de Modelos no Banco de Dados: <strong>{$total}</strong></h5>";
    echo "</div>";
    
    // Contar por categoria
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
    
    $categorias = $stmt->fetchAll();
    
    echo "<h5 class='mb-3'>Modelos por Categoria:</h5>";
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>Categoria</th><th>Total de Modelos</th><th>Status</th></tr></thead>";
    echo "<tbody>";
    
    $todosTem30 = true;
    foreach ($categorias as $cat) {
        $total = (int)$cat['total_modelos'];
        $status = $total >= 30 ? '✅ OK' : '⚠️ Faltam ' . (30 - $total);
        $classe = $total >= 30 ? 'success' : 'warning';
        
        if ($total < 30) $todosTem30 = false;
        
        echo "<tr>";
        echo "<td><strong>{$cat['categoria_nome']}</strong></td>";
        echo "<td><span class='badge bg-{$classe}'>{$total}</span></td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
    
    if ($total == 0) {
        echo "<div class='alert alert-warning mt-4'>";
        echo "<h5>⚠️ Nenhum modelo encontrado no banco de dados!</h5>";
        echo "<p>É necessário importar o arquivo SQL com os modelos.</p>";
        echo "<h6>Como importar:</h6>";
        echo "<ol>";
        echo "<li>Acesse o <strong>phpMyAdmin</strong></li>";
        echo "<li>Selecione o banco de dados <code>" . DB_NAME . "</code></li>";
        echo "<li>Vá na aba <strong>SQL</strong></li>";
        echo "<li>Importe o arquivo <code>modelos_adicionais_completos.sql</code></li>";
        echo "<li>Ou execute o comando:</li>";
        echo "</ol>";
        echo "<pre>mysql -u root -p " . DB_NAME . " < modelos_adicionais_completos.sql</pre>";
        echo "<p class='mt-3'><strong>OU</strong> use o script de importação automática abaixo:</p>";
        echo "<div class='d-flex gap-2'>";
        echo "<a href='importar_modelos_v2.php' class='btn btn-success'><i class='bi bi-download'></i> Importar Modelos (Versão Melhorada)</a>";
        echo "<a href='importar_modelos.php' class='btn btn-outline-success'><i class='bi bi-download'></i> Tentar Versão Anterior</a>";
        echo "</div>";
        echo "</div>";
    } elseif (!$todosTem30) {
        echo "<div class='alert alert-warning mt-4'>";
        echo "<h5>⚠️ Algumas categorias não têm 30 modelos ainda</h5>";
        echo "<p>Execute o arquivo SQL <code>modelos_adicionais_completos.sql</code> para completar.</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success mt-4'>";
        echo "<h5>✅ Perfeito! Todas as categorias têm pelo menos 30 modelos!</h5>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>Erro ao verificar modelos:</h5>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "                    </div>
                    <div class='card-footer'>
                        <a href='gerar.php' class='btn btn-primary'>Voltar para Gerar Documento</a>
                        <a href='dashboard.php' class='btn btn-secondary'>Ir para Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?>

