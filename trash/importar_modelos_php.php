<?php
mb_internal_encoding('UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();
$db->exec("DELETE FROM modelos_documentos");

echo "Importando modelos...\n\n";

// Função para processar arquivo SQL
function importarSQL($arquivo, $db) {
    if (!file_exists($arquivo)) {
        echo "Arquivo não encontrado: $arquivo\n";
        return 0;
    }
    
    $conteudo = file_get_contents($arquivo);
    
    // Converter para UTF-8
    if (!mb_check_encoding($conteudo, 'UTF-8')) {
        $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'ISO-8859-1');
    }
    
    // Separar por ponto e vírgula
    $queries = explode(';', $conteudo);
    $importados = 0;
    
    foreach ($queries as $query) {
        $query = trim($query);
        
        if (empty($query) || strpos($query, '--') === 0 || strpos($query, 'UPDATE') === 0) {
            continue;
        }
        
        if (strpos($query, 'INSERT INTO') === 0) {
            try {
                $db->exec($query);
                $importados++;
            } catch (PDOException $e) {
                // Ignorar erros de duplicação
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "Erro: " . substr($e->getMessage(), 0, 200) . "\n";
                }
            }
        } elseif (strlen($query) > 50 && strpos($query, '--') === false) {
            // Debug: mostrar queries longas que não foram reconhecidas
            if ($importados === 0) {
                echo "Query ignorada: " . substr($query, 0, 100) . "...\n";
            }
        }
    }
    
    return $importados;
}

$total = 0;
$arquivos = ['modelos_completos.sql', 'modelos_extensos_part1.sql', 'modelos_extensos_part2.sql', 'modelos_extensos_part3.sql'];

foreach ($arquivos as $arquivo) {
    $count = importarSQL($arquivo, $db);
    echo "Importados de $arquivo: $count\n";
    $total += $count;
}

echo "\nTotal importado: $total modelos\n";

// Verificar modelo corrompidos
$corrompidos = $db->query("SELECT COUNT(*) FROM modelos_documentos WHERE nome LIKE '%??%' OR nome LIKE '%??%'")->fetchColumn();
echo "Modelos corrompidos: $corrompidos\n";

if ($corrompidos > 0) {
    echo "Corrigindo acentos...\n";
    
    $modelos = $db->query("SELECT id, nome FROM modelos_documentos WHERE nome LIKE '%??%' OR nome LIKE '%??%'")->fetchAll();
    
    $correcoes = [
        '??' => 'ç', '???' => 'ção', '??o' => 'ção',
        '??s' => 'ões', '??' => 'ã', '??' => 'á',
        '??' => 'é', '??' => 'í', '??' => 'ó',
        '??' => 'ú', '??' => 'ü', '??' => 'º',
        '??' => 'ª', '??' => 'à', '??' => 'ô',
        '??' => 'â', '??' => 'ê'
    ];
    
    foreach ($modelos as $modelo) {
        $nome_corrigido = $modelo['nome'];
        foreach ($correcoes as $erro => $correto) {
            $nome_corrigido = str_replace($erro, $correto, $nome_corrigido);
        }
        
        $stmt = $db->prepare("UPDATE modelos_documentos SET nome = ? WHERE id = ?");
        $stmt->execute([$nome_corrigido, $modelo['id']]);
    }
    
    echo "Correção concluída!\n";
}

echo "Processo finalizado!\n";
?>

