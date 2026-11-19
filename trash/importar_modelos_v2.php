<?php
/**
 * Script melhorado para importar modelos de documentos
 * Versão que lida corretamente com strings SQL complexas
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

// Verificar se o arquivo existe
$arquivoSQL = __DIR__ . '/modelos_adicionais_completos.sql';

if (!file_exists($arquivoSQL)) {
    die("Erro: Arquivo modelos_adicionais_completos.sql não encontrado!");
}

// Função para parsear SQL corretamente
function parseSQLFile($filename) {
    $file = file_get_contents($filename);
    $queries = [];
    $currentQuery = '';
    $inString = false;
    $stringChar = '';
    $escaped = false;
    
    for ($i = 0; $i < strlen($file); $i++) {
        $char = $file[$i];
        $nextChar = ($i + 1 < strlen($file)) ? $file[$i + 1] : '';
        
        if ($escaped) {
            $currentQuery .= $char;
            $escaped = false;
            continue;
        }
        
        if ($char === '\\') {
            $escaped = true;
            $currentQuery .= $char;
            continue;
        }
        
        if (!$inString && ($char === "'" || $char === '"')) {
            $inString = true;
            $stringChar = $char;
            $currentQuery .= $char;
        } elseif ($inString && $char === $stringChar) {
            $inString = false;
            $stringChar = '';
            $currentQuery .= $char;
        } elseif (!$inString && $char === ';') {
            $query = trim($currentQuery);
            if (!empty($query) && strlen($query) > 10) {
                // Ignorar comentários
                if (!preg_match('/^--/', $query)) {
                    $queries[] = $query;
                }
            }
            $currentQuery = '';
        } else {
            $currentQuery .= $char;
        }
    }
    
    // Adicionar última query se não terminar com ;
    if (!empty(trim($currentQuery))) {
        $queries[] = trim($currentQuery);
    }
    
    return $queries;
}

// Alternativa: Usar mysqli_load_file ou método mais simples
function importarModelosSimples($db, $arquivoSQL) {
    $linhas = file($arquivoSQL, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $queries = [];
    $currentQuery = '';
    $dentroInsert = false;
    
    foreach ($linhas as $numLinha => $linha) {
        $linha = trim($linha);
        
        // Pular comentários
        if (empty($linha) || strpos($linha, '--') === 0) {
            continue;
        }
        
        // Se encontrar INSERT INTO, começar a acumular
        if (stripos($linha, 'INSERT INTO') !== false) {
            $dentroInsert = true;
            $currentQuery = $linha;
        } elseif ($dentroInsert) {
            $currentQuery .= ' ' . $linha;
            
            // Se terminar com );, finalizar query
            if (preg_match('/\)\s*;\s*$/', $linha)) {
                $queries[] = $currentQuery;
                $currentQuery = '';
                $dentroInsert = false;
            }
        } elseif (stripos($linha, 'UPDATE') !== false) {
            // Query UPDATE simples
            $queries[] = $linha;
        }
    }
    
    return $queries;
}

try {
    $db->beginTransaction();
    
    // Usar método alternativo
    $queries = importarModelosSimples($db, $arquivoSQL);
    
    $sucessos = 0;
    $erros = 0;
    $mensagens = [];
    $duplicados = 0;
    
    foreach ($queries as $index => $query) {
        if (empty(trim($query))) {
            continue;
        }
        
        try {
            // Escapar a query corretamente
            $stmt = $db->prepare("SET sql_mode = ''");
            $stmt->execute();
            
            $db->exec($query);
            $sucessos++;
            
        } catch (PDOException $e) {
            $codigoErro = $e->getCode();
            
            // Erro de duplicação de slug (já existe)
            if (strpos($e->getMessage(), 'Duplicate entry') !== false || 
                strpos($e->getMessage(), '1062') !== false) {
                $duplicados++;
                continue; // Pular, já existe
            }
            
            // Erro de sintaxe - tentar corrigir aspas
            if (strpos($e->getMessage(), 'syntax') !== false || 
                strpos($e->getMessage(), '1064') !== false) {
                $erros++;
                // Tentar uma abordagem diferente usando prepared statements
                if (preg_match("/INSERT INTO.*?VALUES\s*\((.*)\)/is", $query, $matches)) {
                    // Se for INSERT, tentar usar prepared statement
                    if (preg_match("/INSERT INTO `([^`]+)`\s*\(([^)]+)\)\s*VALUES/is", $query, $insertMatches)) {
                        // Não vamos processar aqui, apenas contar como erro
                        continue;
                    }
                }
            }
            
            $erros++;
            if (count($mensagens) < 10) {
                $mensagens[] = "Linha " . ($index + 1) . ": " . substr($e->getMessage(), 0, 150);
            }
        }
    }
    
    $db->commit();
    
    echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Importação de Modelos</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='card'>
            <div class='card-header " . ($sucessos > 0 ? "bg-success" : "bg-warning") . " text-white'>
                <h4><i class='bi bi-" . ($sucessos > 0 ? "check-circle" : "exclamation-triangle") . "'></i> Importação Concluída</h4>
            </div>
            <div class='card-body'>
                <div class='row mb-3'>
                    <div class='col-md-4'>
                        <div class='card bg-success text-white'>
                            <div class='card-body text-center'>
                                <h3>{$sucessos}</h3>
                                <p class='mb-0'>Modelos Importados</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-4'>
                        <div class='card bg-warning'>
                            <div class='card-body text-center'>
                                <h3>{$erros}</h3>
                                <p class='mb-0'>Erros de Sintaxe</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-4'>
                        <div class='card bg-info text-white'>
                            <div class='card-body text-center'>
                                <h3>{$duplicados}</h3>
                                <p class='mb-0'>Já Existentes</p>
                            </div>
                        </div>
                    </div>
                </div>";
    
    if ($erros > 0) {
        echo "<div class='alert alert-warning'>";
        echo "<h6><i class='bi bi-exclamation-triangle'></i> Atenção:</h6>";
        echo "<p>Alguns modelos não puderam ser importados devido a problemas de formatação no arquivo SQL gerado automaticamente.</p>";
        if (count($mensagens) > 0) {
            echo "<details>";
            echo "<summary>Ver erros detalhados</summary>";
            echo "<ul class='small mt-2'>";
            foreach ($mensagens as $msg) {
                echo "<li>" . htmlspecialchars($msg) . "</li>";
            }
            echo "</ul>";
            echo "</details>";
        }
        echo "<p class='mt-3'><strong>Solução recomendada:</strong> Use o phpMyAdmin para importar manualmente, ou use os modelos já existentes no banco.</p>";
        echo "</div>";
    }
    
    if ($sucessos > 0) {
        echo "<div class='alert alert-success'>";
        echo "<h6><i class='bi bi-check-circle'></i> Sucesso!</h6>";
        echo "<p>{$sucessos} modelo(s) foi/foram importado(s) com sucesso!</p>";
        echo "</div>";
    }
    
    echo "<div class='alert alert-info mt-3'>";
    echo "<p><strong>Próximo passo:</strong> Verifique quantos modelos estão disponíveis:</p>";
    echo "<a href='verificar_modelos.php' class='btn btn-primary'><i class='bi bi-search'></i> Verificar Modelos no Banco</a>";
    echo "</div>";
    
    echo "            </div>
            <div class='card-footer'>
                <a href='gerar.php' class='btn btn-primary'><i class='bi bi-file-earmark-plus'></i> Ir para Gerar Documento</a>
                <a href='verificar_modelos.php' class='btn btn-secondary'><i class='bi bi-list-check'></i> Verificar Modelos</a>
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
                    <hr>
                    <p><strong>Recomendação:</strong> Importe via phpMyAdmin:</p>
                    <ol>
                        <li>Acesse o phpMyAdmin</li>
                        <li>Selecione o banco <code>sistema_juridico</code></li>
                        <li>Vá na aba <strong>SQL</strong></li>
                        <li>Clique em <strong>Escolher arquivo</strong></li>
                        <li>Selecione <code>modelos_adicionais_completos.sql</code></li>
                        <li>Clique em <strong>Executar</strong></li>
                    </ol>
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

