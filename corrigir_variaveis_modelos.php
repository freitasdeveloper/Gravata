<?php
/**
 * Script para verificar e corrigir modelos sem variáveis configuradas
 * Extrai variáveis dos templates e atualiza o banco de dados
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

// Função para extrair variáveis do template corretamente
function extrairVariaveisDoTemplate($template) {
    // Buscar padrões {variavel} no template
    preg_match_all('/\{(\w+)\}/', $template, $matches);
    $variaveisEncontradas = array_unique($matches[1]);
    
    $variaveisArray = [];
    foreach ($variaveisEncontradas as $var) {
        // Determinar tipo baseado no nome da variável
        $tipo = 'texto';
        if (stripos($var, 'data') !== false || stripos($var, 'date') !== false) {
            $tipo = 'data';
        } elseif (stripos($var, 'valor') !== false || stripos($var, 'preco') !== false || stripos($var, 'quantia') !== false || stripos($var, 'total') !== false) {
            $tipo = 'numero';
        } elseif (stripos($var, 'cpf') !== false) {
            $tipo = 'cpf';
        } elseif (stripos($var, 'cnpj') !== false) {
            $tipo = 'cnpj';
        } elseif (stripos($var, 'cep') !== false) {
            $tipo = 'cep';
        } elseif (stripos($var, 'telefone') !== false || stripos($var, 'celular') !== false || stripos($var, 'fone') !== false) {
            $tipo = 'telefone';
        } elseif (stripos($var, 'email') !== false || stripos($var, 'e-mail') !== false) {
            $tipo = 'email';
        } elseif (stripos($var, 'descricao') !== false || stripos($var, 'desc') !== false || stripos($var, 'conteudo') !== false || stripos($var, 'texto') !== false || stripos($var, 'fatos') !== false || stripos($var, 'direito') !== false || stripos($var, 'pedidos') !== false || stripos($var, 'observacao') !== false || stripos($var, 'obs') !== false) {
            $tipo = 'textarea';
        }
        
        // Criar nome amigável
        $nome = ucwords(str_replace('_', ' ', $var));
        
        // Campos esperados pelo sistema
        $variaveisArray[] = [
            'codigo' => $var,
            'nome' => $nome,
            'tipo' => $tipo,
            'obrigatorio' => true,
            'descricao' => $nome,
            'valor_padrao' => '',
            'exemplo' => ''
        ];
    }
    
    // Retornar no formato esperado pelo sistema
    return [
        'variaveis' => $variaveisArray
    ];
}

// Função para validar estrutura de variáveis
function validarEstruturaVariaveis($variaveisJson) {
    if (empty($variaveisJson) || $variaveisJson === 'null') {
        return false;
    }
    
    $variaveis = json_decode($variaveisJson, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }
    
    if (!isset($variaveis['variaveis']) || !is_array($variaveis['variaveis'])) {
        return false;
    }
    
    if (empty($variaveis['variaveis'])) {
        return false;
    }
    
    // Verificar se tem campo 'codigo' (formato novo) ou 'nome' (formato antigo)
    $primeiraVar = $variaveis['variaveis'][0] ?? null;
    if ($primeiraVar && !isset($primeiraVar['codigo'])) {
        // Formato antigo, precisa converter
        return false;
    }
    
    return true;
}

echo "========================================\n";
echo "CORREÇÃO DE VARIÁVEIS DOS MODELOS\n";
echo "========================================\n\n";

// Buscar todos os modelos
$stmt = $db->query("SELECT id, nome, conteudo_template, variaveis FROM modelos_documentos WHERE ativo = 1 ORDER BY id");
$modelos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalModelos = count($modelos);
$modelosCorrigidos = 0;
$modelosComErro = 0;
$modelosSemVariaveis = 0;
$modelosOk = 0;

echo "Total de modelos a verificar: {$totalModelos}\n\n";

foreach ($modelos as $modelo) {
    $modeloId = $modelo['id'];
    $modeloNome = $modelo['nome'];
    $template = $modelo['conteudo_template'];
    $variaveisAtuais = $modelo['variaveis'];
    
    // Verificar se as variáveis estão válidas
    $variaveisValidas = validarEstruturaVariaveis($variaveisAtuais);
    
    if (!$variaveisValidas) {
        // Tentar extrair variáveis do template
        $novasVariaveis = extrairVariaveisDoTemplate($template);
        
        if (!empty($novasVariaveis['variaveis'])) {
            // Converter para JSON
            $variaveisJson = json_encode($novasVariaveis, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
            // Atualizar no banco
            try {
                $stmtUpdate = $db->prepare("UPDATE modelos_documentos SET variaveis = ? WHERE id = ?");
                $stmtUpdate->execute([$variaveisJson, $modeloId]);
                
                $modelosCorrigidos++;
                echo "✓ {$modeloNome} (ID: {$modeloId}) - Corrigido com " . count($novasVariaveis['variaveis']) . " variável(is)\n";
            } catch (PDOException $e) {
                $modelosComErro++;
                echo "✗ {$modeloNome} (ID: {$modeloId}) - Erro ao atualizar: {$e->getMessage()}\n";
            }
        } else {
            // Template não tem variáveis {variavel}
            $modelosSemVariaveis++;
            echo "⊘ {$modeloNome} (ID: {$modeloId}) - Template não possui variáveis\n";
        }
    } else {
        $modelosOk++;
        // Não precisa fazer nada
    }
}

echo "\n========================================\n";
echo "RESUMO DA CORREÇÃO:\n";
echo "========================================\n";
echo "Total de modelos: {$totalModelos}\n";
echo "Modelos OK: {$modelosOk}\n";
echo "Modelos corrigidos: {$modelosCorrigidos}\n";
echo "Modelos sem variáveis no template: {$modelosSemVariaveis}\n";
echo "Erros ao corrigir: {$modelosComErro}\n";
echo "========================================\n";

if ($modelosSemVariaveis > 0) {
    echo "\nATENÇÃO: {$modelosSemVariaveis} modelo(s) não possuem variáveis no template.\n";
    echo "Estes modelos podem não funcionar corretamente.\n";
    echo "Considere revisar os templates para incluir variáveis como {variavel}.\n";
}

