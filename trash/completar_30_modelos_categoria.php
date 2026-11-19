<?php
/**
 * Script para completar EXATAMENTE 30 modelos em cada categoria
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

// Função para gerar um modelo SQL completo e correto
function gerarModeloSQLCompleto($categoriaId, $nome, $slug, $descricao, $plano, $template, $variaveis) {
    // Escapar aspas simples para SQL
    $nomeEscapado = str_replace("'", "''", $nome);
    $slugEscapado = str_replace("'", "''", $slug);
    $descEscapado = str_replace("'", "''", $descricao);
    $templateEscapado = str_replace("'", "''", $template);
    $variaveisEscapado = str_replace("'", "''", $variaveis);
    
    $sql = "INSERT INTO `modelos_documentos` \n";
    $sql .= "(`categoria_id`, `nome`, `slug`, `descricao`, `tipo_plano_necessario`, `conteudo_template`, `variaveis`) \n";
    $sql .= "VALUES \n";
    $sql .= "({$categoriaId}, '{$nomeEscapado}', '{$slugEscapado}', '{$descEscapado}', '{$plano}',\n";
    $sql .= "'{$templateEscapado}',\n";
    $sql .= "'{$variaveisEscapado}');\n\n";
    
    return $sql;
}

// Modelos faltantes por categoria
$modelosFaltantes = [
    1 => [ // CIVIL - faltam 7
        ['Ação de Despejo por Falta de Pagamento', 'acao-despejo-falta-pagamento-v2', 'Modelo de ação de despejo por falta de pagamento', 'pro1'],
        ['Contrato de Representação Comercial', 'contrato-representacao-comercial-v2', 'Contrato de representação comercial', 'basico'],
        ['Ação de Perdas e Danos', 'acao-perdas-danos-v2', 'Ação de perdas e danos', 'pro1'],
        ['Contrato de Locação de Equipamentos', 'contrato-locacao-equipamentos-v2', 'Contrato de locação de equipamentos', 'basico'],
        ['Ação de Indenização por Danos Estéticos', 'acao-indenizacao-danos-esteticos', 'Ação de indenização por danos estéticos', 'pro1'],
        ['Contrato de Prestação de Serviços Técnicos', 'contrato-servicos-tecnicos-v2', 'Contrato de prestação de serviços técnicos', 'basico'],
        ['Ação de Nulidade de Contrato', 'acao-nulidade-contrato-v2', 'Ação de nulidade de contrato', 'pro1'],
    ],
    2 => [ // CRIMINAL - faltam 4
        ['Queixa-Crime por Lesão Corporal', 'queixa-crime-lesao-corporal', 'Queixa-crime por lesão corporal', 'pro1'],
        ['Pedido de Relaxamento de Prisão', 'pedido-relaxamento-prisao', 'Pedido de relaxamento de prisão', 'pro1'],
        ['Recurso contra Decisão Interlocutória', 'recurso-decisao-interlocutoria-v2', 'Recurso contra decisão interlocutória', 'pro2'],
        ['Pedido de Suspensão Condicional da Pena', 'pedido-suspensao-condicional-v2', 'Pedido de suspensão condicional da pena', 'pro1'],
    ],
    3 => [ // TRABALHISTA - faltam 6 (mostra 4 na tela, então faltam mais)
        ['Reclamação por Salário Família', 'reclamacao-salario-familia-v2', 'Reclamação por salário família não pago', 'pro1'],
        ['Pedido de Licença Maternidade', 'pedido-licenca-maternidade-v2', 'Pedido de licença maternidade', 'basico'],
        ['Reclamação por Adicional de Periculosidade', 'reclamacao-adicional-periculosidade', 'Reclamação por adicional de periculosidade', 'pro1'],
        ['Pedido de Seguro Desemprego', 'pedido-seguro-desemprego-v2', 'Pedido de seguro desemprego', 'basico'],
        ['Reclamação por Hora Extra 100%', 'reclamacao-hora-extra-100', 'Reclamação por hora extra 100%', 'pro1'],
        ['Pedido de Intervalo Interjornada', 'pedido-intervalo-interjornada', 'Pedido de intervalo interjornada', 'pro1'],
    ],
    4 => [ // TRIBUTÁRIO - faltam 3
        ['Ação de Consignação Tributária', 'acao-consignacao-tributaria-v2', 'Ação de consignação tributária', 'pro1'],
        ['Pedido de Compensação de Créditos', 'pedido-compensacao-creditos', 'Pedido de compensação de créditos tributários', 'pro1'],
        ['Recurso contra Notificação de Débito', 'recurso-notificacao-debito', 'Recurso contra notificação de débito', 'pro1'],
    ],
    5 => [ // ADMINISTRATIVO - faltam 3
        ['Ação de Revisão de Ato Administrativo', 'acao-revisao-ato-administrativo-v2', 'Ação de revisão de ato administrativo', 'pro1'],
        ['Pedido de Certidão Positiva', 'pedido-certidao-positiva', 'Pedido de certidão positiva', 'basico'],
        ['Recurso contra Edital de Concurso', 'recurso-edital-concurso', 'Recurso contra edital de concurso', 'pro1'],
    ],
    6 => [ // FAMÍLIA - faltam 6
        ['Ação de Separação de Corpos', 'acao-separacao-corpos-v2', 'Ação de separação de corpos', 'basico'],
        ['Pedido de Regime de Visitas Ampliado', 'pedido-visitas-ampliado-v2', 'Pedido de regime de visitas ampliado', 'pro1'],
        ['Ação de Reconhecimento de União Estável', 'acao-reconhecimento-uniao-estavel-v2', 'Ação de reconhecimento de união estável', 'basico'],
        ['Pedido de Autorização para Viagem', 'pedido-autorizacao-viagem-v2', 'Pedido de autorização para viagem de menor', 'basico'],
        ['Ação de Alimentos Definitivos', 'acao-alimentos-definitivos-v2', 'Ação de alimentos definitivos', 'basico'],
        ['Pedido de Revisão de Pensão', 'pedido-revisao-pensao-v2', 'Pedido de revisão de pensão alimentícia', 'basico'],
    ]
];

// Template padrão para modelos
function getTemplatePadrao($nome) {
    return "DOCUMENTO: {$nome}

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
}

function getVariaveisPadrao() {
    $variaveis = [
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
    ];
    return json_encode($variaveis, JSON_UNESCAPED_UNICODE);
}

// Gerar SQL
$sql = "-- Modelos para completar 30 modelos em cada categoria\n";
$sql .= "-- Gerado em " . date('d/m/Y H:i:s') . "\n\n";

$totalAdicionados = 0;

foreach ($modelosFaltantes as $categoriaId => $modelos) {
    foreach ($modelos as $modelo) {
        $template = getTemplatePadrao($modelo[0]);
        $variaveis = getVariaveisPadrao();
        
        $sql .= gerarModeloSQLCompleto(
            $categoriaId,
            $modelo[0],
            $modelo[1],
            $modelo[2],
            $modelo[3],
            $template,
            $variaveis
        );
        $totalAdicionados++;
    }
}

$sql .= "\nUPDATE `modelos_documentos` SET `autor` = 'Sistema Jurídico', `versao` = 1.00 WHERE `autor` IS NULL;\n";

// Salvar arquivo
file_put_contents(__DIR__ . '/completar_30_modelos.sql', $sql);

echo "Arquivo SQL gerado: completar_30_modelos.sql\n";
echo "Total de modelos a adicionar: {$totalAdicionados}\n\n";

// Agora importar diretamente
echo "Importando no banco de dados...\n";

try {
    $db->beginTransaction();
    
    $sucessos = 0;
    $erros = 0;
    
    foreach ($modelosFaltantes as $categoriaId => $modelos) {
        foreach ($modelos as $modelo) {
            $template = getTemplatePadrao($modelo[0]);
            $variaveis = getVariaveisPadrao();
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO modelos_documentos 
                    (categoria_id, nome, slug, descricao, tipo_plano_necessario, conteudo_template, variaveis, autor, versao) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Sistema Jurídico', 1.00)
                ");
                
                $stmt->execute([
                    $categoriaId,
                    $modelo[0],
                    $modelo[1],
                    $modelo[2],
                    $modelo[3],
                    $template,
                    $variaveis
                ]);
                
                $sucessos++;
                echo "✓ {$modelo[0]} adicionado\n";
                
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    $erros++;
                    echo "✗ Erro ao adicionar {$modelo[0]}: " . substr($e->getMessage(), 0, 50) . "\n";
                } else {
                    echo "- {$modelo[0]} já existe (pulando)\n";
                }
            }
        }
    }
    
    $db->commit();
    
    echo "\n=== RESULTADO ===\n";
    echo "Sucessos: {$sucessos}\n";
    echo "Erros: {$erros}\n";
    echo "\nVerificando total por categoria...\n\n";
    
    // Verificar novamente
    $stmt = $db->query("
        SELECT 
            cd.nome as categoria_nome,
            COUNT(md.id) as total_modelos
        FROM categorias_documentos cd
        LEFT JOIN modelos_documentos md ON cd.id = md.categoria_id AND md.ativo = 1
        WHERE cd.ativo = 1
        GROUP BY cd.id, cd.nome
        ORDER BY cd.ordem
    ");
    
    $resultados = $stmt->fetchAll();
    
    echo "Categoria | Total | Status\n";
    echo "----------|-------|--------\n";
    
    foreach ($resultados as $row) {
        $total = (int)$row['total_modelos'];
        $status = $total >= 30 ? '✅ OK' : '⚠️ Faltam ' . (30 - $total);
        printf("%-9s | %5d | %s\n", $row['categoria_nome'], $total, $status);
    }
    
} catch (Exception $e) {
    $db->rollBack();
    echo "ERRO GERAL: " . $e->getMessage() . "\n";
}

?>

