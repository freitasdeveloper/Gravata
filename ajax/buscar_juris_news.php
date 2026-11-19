<?php
/**
 * Endpoint para buscar notícias e jurisprudências reais dos tribunais
 * IMPORTANTE: Configure feeds RSS oficiais ou APIs antes de usar em produção
 * 
 * Fontes recomendadas:
 * - DataJud (CNJ): https://datajud.cnj.jus.br/
 * - Jusbrasil: https://api.jusbrasil.com.br/
 * - Feeds RSS dos tribunais
 */

if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Verificar se estamos em ambiente de teste ou produção
$configPath = file_exists('config.php') ? 'config.php' : '../config.php';

require_once $configPath;

// Verificar autenticação se não for teste
if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
    require_once '../includes/database.php';
    
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Não autorizado',
            'dados' => []
        ]);
        exit;
    }
}

$tipo = $_GET['tipo'] ?? 'todas';
$retorno = [
    'sucesso' => true,
    'timestamp' => date('Y-m-d H:i:s'),
        'fonte' => 'Legatus',
    'observacao' => '',
    'dados' => []
];

// ========================================
// SEÇÃO 1: CONFIGURAR FEEDS RSS OFICIAIS
// ========================================

$feedsOficiais = [
    'oab' => 'https://www.oab.org.br/noticias/rss',
    // Adicionar mais feeds conforme disponíveis
];

foreach ($feedsOficiais as $tribunal => $url) {
    $xml = @simplexml_load_file($url);
    if ($xml && isset($xml->channel->item)) {
        foreach ($xml->channel->item as $item) {
            $conteudo = strip_tags((string)$item->description);
            $resumo = mb_substr($conteudo, 0, 150);
            if (mb_strlen($conteudo) > 150) {
                $resumo .= '...';
            }
            
            $retorno['dados'][] = [
                'titulo' => (string)$item->title,
                'link' => (string)$item->link,
                'data' => date('Y-m-d H:i:s', strtotime((string)$item->pubDate)),
                'resumo' => $resumo,
                'fonte' => strtoupper($tribunal) . ' - Oficial'
            ];
        }
    }
}

// ========================================
// SEÇÃO 2: CONFIGURAR API DATAJUD
// ========================================
// Descomente após obter credenciais da API DataJud:

/*
function buscarDataJud($endpoint, $token) {
    $url = 'https://api-publica.datajud.cnj.jus.br/' . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        return json_decode($response, true);
    }
    
    return null;
}

// Exemplo de uso
$dataJudToken = 'SEU_TOKEN_AQUI';
$dados = buscarDataJud('v1/cnj/jurisprudencia', $dataJudToken);
if ($dados) {
    foreach ($dados as $item) {
        $retorno['dados'][] = [
            'titulo' => $item['titulo'],
            'link' => $item['link'],
            'data' => $item['data'],
            'tribunal' => $item['tribunal'],
            'fonte' => 'DataJud - CNJ'
        ];
    }
}
*/

// ========================================
// SEÇÃO 3: CONFIGURAR API JUSBRASIL
// ========================================
// Descomente após obter API Key do Jusbrasil:

/*
function buscarJusbrasil($endpoint, $apiKey) {
    $url = 'https://api.jusbrasil.com.br/v1/' . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        return json_decode($response, true);
    }
    
    return null;
}

// Exemplo de uso
$jusbrasilKey = 'SUA_API_KEY_AQUI';
$dados = buscarJusbrasil('diarios', $jusbrasilKey);
if ($dados) {
    foreach ($dados as $item) {
        $retorno['dados'][] = [
            'titulo' => $item['titulo'],
            'link' => $item['url'],
            'data' => $item['data'],
            'fonte' => $item['fonte'] ?? 'Jusbrasil'
        ];
    }
}
*/

// ========================================
// FILTRAR POR TIPO SE SOLICITADO
// ========================================
if ($tipo !== 'todas' && !empty($retorno['dados'])) {
    // Filtrar dados conforme o tipo solicitado
    switch ($tipo) {
        case 'juris':
            // Para jurisprudências, manter todos os dados
            break;
        case 'stf':
            // Filtrar apenas STF (quando houver)
            $retorno['dados'] = array_filter($retorno['dados'], function($item) {
                return stripos($item['fonte'], 'STF') !== false;
            });
            break;
        case 'novidades':
            // Para novidades, manter todos
            break;
        case 'tribunais':
            // Processar para formato de tribunais
            $tribunais = [
                ['nome' => 'OAB Nacional', 'informacao' => 'Notícias atualizadas', 'fonte' => 'OAB', 'icone' => 'bi-scale'],
            ];
            
            // Se houver dados, usar o primeiro como referência
            if (!empty($retorno['dados'])) {
                $primeiro = reset($retorno['dados']);
                $retorno['dados'] = [
                    [
                        'nome' => 'OAB Nacional',
                        'status' => 'online',
                        'ultimaAtualizacao' => $primeiro['data'],
                        'informacao' => count($retorno['dados']) . ' notícias recentes',
                        'icone' => 'bi-scale'
                    ]
                ];
            }
            break;
    }
    
    // Reindexar array após filtro
    $retorno['dados'] = array_values($retorno['dados']);
}

// Limitar quantidade de resultados
$retorno['dados'] = array_slice($retorno['dados'], 0, 10);

// Retornar JSON
echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
