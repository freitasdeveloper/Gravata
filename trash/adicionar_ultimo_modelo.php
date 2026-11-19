<?php
require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

$template = "DOCUMENTO: Ação de Reconhecimento de União Estável

EXCELENTÍSSIMO SENHOR DOUTOR JUIZ DE DIREITO DA __ VARA DA COMARCA DE __

{{requerente_nome}}, brasileiro(a), __, portador(a) do RG nº __, inscrito(a) no CPF sob nº __, residente e domiciliado(a) à __, vem respeitosamente propor a presente ação, pelos fatos e fundamentos que passa a expor.

DOS FATOS

{{relato_fatos}}

DO DIREITO

{{fundamentos_juridicos}}

DO PEDIDO

Ante o exposto, requer V.Sa.:

a) {{pedido1}};
b) {{pedido2}};
c) Os ônus da sucumbência.

{{local}}, __/__/____

{{advogado_nome}}
OAB/{{estado_oab}} nº {{oab_numero}}";

$variaveis = json_encode([
    'variaveis' => [
        ['codigo' => 'requerente_nome', 'nome' => 'Nome do Requerente', 'tipo' => 'texto'],
        ['codigo' => 'relato_fatos', 'nome' => 'Relato dos Fatos', 'tipo' => 'textarea'],
        ['codigo' => 'fundamentos_juridicos', 'nome' => 'Fundamentos Jurídicos', 'tipo' => 'textarea'],
        ['codigo' => 'pedido1', 'nome' => 'Pedido 1', 'tipo' => 'textarea'],
        ['codigo' => 'pedido2', 'nome' => 'Pedido 2', 'tipo' => 'textarea'],
        ['codigo' => 'advogado_nome', 'nome' => 'Nome do Advogado', 'tipo' => 'texto'],
        ['codigo' => 'estado_oab', 'nome' => 'Estado OAB', 'tipo' => 'texto'],
        ['codigo' => 'oab_numero', 'nome' => 'Número OAB', 'tipo' => 'texto']
    ]
], JSON_UNESCAPED_UNICODE);

try {
    $stmt = $db->prepare("
        INSERT INTO modelos_documentos 
        (categoria_id, nome, slug, descricao, tipo_plano_necessario, conteudo_template, variaveis, autor, versao) 
        VALUES (6, ?, ?, ?, ?, ?, ?, 'Sistema Jurídico', 1.00)
    ");
    
    $stmt->execute([
        'Ação de Reconhecimento de União Estável',
        'acao-reconhecimento-uniao-estavel-basico-final',
        'Ação de reconhecimento de união estável',
        'basico',
        $template,
        $variaveis
    ]);
    
    echo "✅ Último modelo adicionado com sucesso!\n";
    
    // Verificar
    $stmt = $db->query("
        SELECT COUNT(*) as total 
        FROM modelos_documentos 
        WHERE categoria_id = 6 AND tipo_plano_necessario = 'basico' AND ativo = 1
    ");
    $result = $stmt->fetch();
    echo "Total de modelos básicos na categoria Família: " . $result['total'] . "\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo "Modelo já existe. Verificando total...\n";
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM modelos_documentos 
            WHERE categoria_id = 6 AND tipo_plano_necessario = 'basico' AND ativo = 1
        ");
        $result = $stmt->fetch();
        echo "Total de modelos básicos na categoria Família: " . $result['total'] . "\n";
    } else {
        echo "Erro: " . $e->getMessage() . "\n";
    }
}

?>

