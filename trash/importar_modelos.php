<?php
/**
 * Script para importar modelos de documentos automaticamente
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

// Verificar se o arquivo existe
$arquivoSQL = __DIR__ . '/modelos_adicionais_completos.sql';

if (!file_exists($arquivoSQL)) {
    die("Erro: Arquivo modelos_adicionais_completos.sql não encontrado!");
}

// Ler o arquivo SQL
$sql = file_get_contents($arquivoSQL);

// Remover comentários SQL que podem causar problemas
$sql = preg_replace('/--.*$/m', '', $sql);

// Separar comandos SQL
$comandos = array_filter(array_map('trim', explode(';', $sql)));

$sucessos = 0;
$erros = 0;
$mensagens = [];

try {
    $db->beginTransaction();
    
    foreach ($comandos as $comando) {
        if (empty($comando) || strlen(trim($comando)) < 10) {
            continue;
        }
        
        try {
            $db->exec($comando);
            $sucessos++;
        } catch (PDOException $e) {
            // Se for erro de duplicação (slug já existe), pular
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                continue; // Já existe, pular
            }
            $erros++;
            $mensagens[] = "Erro: " . substr($e->getMessage(), 0, 100);
        }
    }
    
    $db->commit();
    
    echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Importação de Modelos</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='card'>
            <div class='card-header bg-success text-white'>
                <h4>✅ Importação Concluída!</h4>
            </div>
            <div class='card-body'>
                <p><strong>Comandos executados com sucesso:</strong> {$sucessos}</p>
                <p><strong>Erros:</strong> {$erros}</p>";
    
    if ($erros > 0 && count($mensagens) > 0) {
        echo "<div class='alert alert-warning'>";
        echo "<h6>Alguns erros ocorreram (pode ser normal se modelos já existem):</h6>";
        echo "<ul>";
        foreach (array_slice($mensagens, 0, 5) as $msg) {
            echo "<li>" . htmlspecialchars($msg) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<div class='alert alert-info mt-3'>";
    echo "<p>Verifique a quantidade de modelos importados:</p>";
    echo "<a href='verificar_modelos.php' class='btn btn-primary'>Verificar Modelos</a>";
    echo "</div>";
    
    echo "            </div>
            <div class='card-footer'>
                <a href='gerar.php' class='btn btn-primary'>Ir para Gerar Documento</a>
            </div>
        </div>
    </div>
</body>
</html>";
    
} catch (Exception $e) {
    $db->rollBack();
    
    echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Erro na Importação</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='card'>
            <div class='card-header bg-danger text-white'>
                <h4>❌ Erro na Importação</h4>
            </div>
            <div class='card-body'>
                <div class='alert alert-danger'>
                    <p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                    <p>Por favor, importe manualmente via phpMyAdmin ou linha de comando.</p>
                </div>
            </div>
            <div class='card-footer'>
                <a href='verificar_modelos.php' class='btn btn-secondary'>Voltar</a>
            </div>
        </div>
    </div>
</body>
</html>";
}
?>

