<?php
/**
 * Script para adicionar ainda mais modelos jurídicos
 * Focando em contratos, defesas, recursos, termos e outros documentos essenciais
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

// Obter categorias
$stmt = $db->query("SELECT id, nome FROM categorias_documentos WHERE ativo = 1 ORDER BY id");
$categorias = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $cat) {
    $categorias[$cat['nome']] = $cat['id'];
}

// Função para criar slug
function criarSlug($nome, $db) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome)));
    $contador = 1;
    $slugOriginal = $slug;
    while (true) {
        $stmt = $db->prepare("SELECT id FROM modelos_documentos WHERE slug = ?");
        $stmt->execute([$slug]);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $slugOriginal . '-' . $contador;
        $contador++;
    }
}

// Função para extrair variáveis
function extrairVariaveis($template) {
    preg_match_all('/\{(\w+)\}/', $template, $matches);
    $variaveis = array_unique($matches[1]);
    $variaveisArray = [];
    foreach ($variaveis as $var) {
        $variaveisArray[] = [
            'nome' => $var,
            'tipo' => 'texto',
            'obrigatorio' => true,
            'descricao' => ucfirst(str_replace('_', ' ', $var))
        ];
    }
    return json_encode($variaveisArray, JSON_UNESCAPED_UNICODE);
}

$modelos = [];

// ========== MAIS MODELOS CIVIS ==========
$civil = $categorias['Civil'] ?? null;
if ($civil) {
    $modelos_civil_extras = [
        ['Contrato de Prestação de Serviços', 'Contrato para prestação de serviços profissionais ou técnicos'],
        ['Contrato de Compra e Venda de Imóvel', 'Contrato de compra e venda de imóvel residencial ou comercial'],
        ['Contrato de Locação Residencial', 'Contrato de locação de imóvel residencial'],
        ['Contrato de Locação Comercial', 'Contrato de locação de imóvel comercial'],
        ['Contrato de Empréstimo', 'Contrato de empréstimo com garantia ou sem garantia'],
        ['Contrato de Comodato', 'Contrato de empréstimo gratuito de bem móvel'],
        ['Contrato de Doação', 'Contrato de doação com ou sem encargos'],
        ['Contrato de Mandato', 'Contrato de mandato para representação'],
        ['Contrato de Parceria', 'Contrato de parceria comercial ou empresarial'],
        ['Contrato de Confiança', 'Contrato de depósito em garantia'],
        ['Termo de Compromisso', 'Termo de compromisso extrajudicial'],
        ['Termo de Ajustamento de Conduta', 'Termo de ajustamento de conduta - TAC'],
        ['Acordo Extrajudicial', 'Acordo extrajudicial para resolução de conflitos'],
        ['Contrato de Fiança', 'Contrato de fiança para garantia'],
        ['Termo de Quitação', 'Termo de quitação e liberação'],
        ['Petição Inicial - Ação de Danos Materiais', 'Ação indenizatória por danos materiais'],
        ['Petição Inicial - Ação de Danos Estéticos', 'Ação indenizatória por danos estéticos'],
        ['Petição Inicial - Ação de Revisão de Contrato', 'Ação para revisar cláusulas contratuais'],
        ['Petição Inicial - Ação de Rescisão por Inadimplemento', 'Ação para rescindir contrato por inadimplemento'],
        ['Petição Inicial - Ação de Vícios Redibitórios', 'Ação por vícios redibitórios em compra e venda'],
        ['Petição Inicial - Ação de Evicção', 'Ação por evicção em compra e venda'],
        ['Petição Inicial - Ação de Nunciação de Obra Nova', 'Ação para notificar obra nova'],
        ['Petição Inicial - Ação de Nunciação de Obra Velha', 'Ação para notificar obra velha'],
        ['Petição Inicial - Ação de Imissão na Posse', 'Ação para obter posse de imóvel'],
        ['Petição Inicial - Ação de Reintegração de Posse', 'Ação para reintegrar posse de imóvel'],
        ['Petição Inicial - Ação de Interdito Proibitório', 'Ação de interdito proibitório'],
        ['Petição Inicial - Ação de Ação Monitória', 'Ação monitória para cobrança'],
        ['Contestação - Ação Civil', 'Contestação em ação civil genérica'],
        ['Reconvenção - Ação Civil', 'Reconvenção em ação civil'],
        ['Réplica - Ação Civil', 'Réplica em ação civil'],
        ['Embargos de Terceiro', 'Embargos de terceiro para defender direito sobre bem'],
        ['Recurso de Apelação Civil', 'Apelação em processo civil'],
        ['Recurso Especial Civil', 'Recurso especial para STJ em matéria civil'],
        ['Agravo de Instrumento Civil', 'Agravo de instrumento em processo civil'],
        ['Embargos de Declaração Civil', 'Embargos de declaração em processo civil'],
        ['Recurso Extraordinário', 'Recurso extraordinário para STF'],
    ];
    
    foreach ($modelos_civil_extras as $modelo) {
        $modelos[] = [
            'categoria_id' => $civil,
            'nome' => $modelo[0],
            'descricao' => $modelo[1],
            'tipo_plano' => 'basico',
            'template' => 'Documento jurídico civil: ' . strtolower($modelo[1]) . '.

{conteudo_documento}

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
            'variaveis' => null
        ];
    }
}

// ========== MAIS MODELOS CRIMINAIS ==========
$criminal = $categorias['Criminal'] ?? null;
if ($criminal) {
    $modelos_criminal_extras = [
        ['Alegações Finais Criminais', 'Alegações finais em processo criminal'],
        ['Contestação em Ação Penal', 'Contestação em ação penal'],
        ['Exceção de Coisa Julgada Criminal', 'Exceção de coisa julgada em processo criminal'],
        ['Exceção de Incompetência Criminal', 'Exceção de incompetência em processo criminal'],
        ['Recurso em Habeas Corpus', 'Recurso contra decisão de habeas corpus'],
        ['Reclamação Criminal ao STJ', 'Reclamação criminal para STJ'],
        ['Reclamação Criminal ao STF', 'Reclamação criminal para STF'],
        ['Agravo Regimental Criminal', 'Agravo regimental em processo criminal'],
        ['Petição de Pronúncia com Pedido de Absolvição', 'Petição de Pronúncia solicitando absolvição'],
        ['Petição de Absolvição por Insuficiência de Prova', 'Petição para absolver por insuficiência de prova'],
        ['Recurso em Liberdade', 'Recurso para obter liberdade provisória'],
        ['Pedido de Conversão de Pena', 'Pedido para converter pena privativa de liberdade'],
        ['Recurso contra Medida Cautelar', 'Recurso contra medida cautelar criminal'],
        ['Impugnação à Acusação', 'Impugnação à acusação formulada'],
        ['Petição de Pronúncia Imprópria', 'Petição para pronunciar imprópria'],
    ];
    
    foreach ($modelos_criminal_extras as $modelo) {
        $modelos[] = [
            'categoria_id' => $criminal,
            'nome' => $modelo[0],
            'descricao' => $modelo[1],
            'tipo_plano' => 'basico',
            'template' => 'Documento jurídico criminal: ' . strtolower($modelo[1]) . '.

{conteudo}

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
            'variaveis' => null
        ];
    }
}

// ========== MAIS MODELOS TRABALHISTAS ==========
$trabalhista = $categorias['Trabalhista'] ?? null;
if ($trabalhista) {
    $modelos_trabalhista_extras = [
        ['Reclamação Trabalhista - Multa do art. 477 §8º', 'Reclamação por multa do artigo 477 parágrafo 8º CLT'],
        ['Reclamação Trabalhista - Multa de 40% FGTS', 'Reclamação por multa de 40% sobre FGTS'],
        ['Reclamação Trabalhista - Sucumbência Trabalhista', 'Reclamação por sucumbência trabalhista'],
        ['Reclamação Trabalhista - Estabilidade Gestante', 'Reclamação por estabilidade de gestante'],
        ['Reclamação Trabalhista - Estabilidade Acidente', 'Reclamação por estabilidade após acidente'],
        ['Reclamação Trabalhista - Direito de Greve', 'Reclamação relacionada ao direito de greve'],
        ['Contestação Trabalhista', 'Contestação em reclamação trabalhista'],
        ['Reconvenção Trabalhista', 'Reconvenção em reclamação trabalhista'],
        ['Recurso Trabalhista Ordinário', 'Recurso ordinário trabalhista'],
        ['Recurso de Revista Trabalhista', 'Recurso de revista para TST'],
        ['Embargos de Declaração Trabalhista', 'Embargos de declaração trabalhista'],
        ['Agravo de Instrumento Trabalhista', 'Agravo de instrumento trabalhista'],
        ['Recurso Adesivo Trabalhista', 'Recurso adesivo trabalhista'],
        ['Pedido de Reconsideração Trabalhista', 'Pedido de reconsideração de decisão trabalhista'],
        ['Impugnação à Sentença Trabalhista', 'Impugnação à sentença trabalhista'],
        ['Pedido de Uniformização Trabalhista', 'Pedido de uniformização de jurisprudência'],
        ['Recurso Especial Trabalhista', 'Recurso especial trabalhista para STJ'],
        ['Contrato de Trabalho CLT', 'Contrato de trabalho sob regime CLT'],
        ['Contrato de Trabalho Temporário', 'Contrato de trabalho temporário'],
        ['Contrato de Trabalho Intermitente', 'Contrato de trabalho intermitente'],
        ['Contrato de Estágio', 'Contrato de estágio'],
        ['Termo de Rescisão Trabalhista', 'Termo de rescisão contratual de trabalho'],
        ['Acordo Trabalhista Extrajudicial', 'Acordo trabalhista extrajudicial'],
        ['Termo de Quitação Trabalhista', 'Termo de quitação trabalhista'],
        ['Recibo de Quitação Trabalhista', 'Recibo de quitação trabalhista'],
    ];
    
    foreach ($modelos_trabalhista_extras as $modelo) {
        $modelos[] = [
            'categoria_id' => $trabalhista,
            'nome' => $modelo[0],
            'descricao' => $modelo[1],
            'tipo_plano' => 'basico',
            'template' => 'Documento jurídico trabalhista: ' . strtolower($modelo[1]) . '.

{conteudo}

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
            'variaveis' => null
        ];
    }
}

// ========== MAIS MODELOS TRIBUTÁRIOS ==========
$tributario = $categorias['Tributário'] ?? null;
if ($tributario) {
    $modelos_tributario_extras = [
        ['Recurso Especial Tributário', 'Recurso especial tributário para STJ'],
        ['Recurso Extraordinário Tributário', 'Recurso extraordinário tributário para STF'],
        ['Agravo de Instrumento Fiscal', 'Agravo de instrumento em processo fiscal'],
        ['Embargos de Declaração Fiscal', 'Embargos de declaração em processo fiscal'],
        ['Recurso Voluntário Fiscal', 'Recurso voluntário em processo fiscal'],
        ['Impugnação à Certidão de Dívida Ativa', 'Impugnação à certidão de dívida ativa'],
        ['Petição de Suspensão de Limitação', 'Petição para suspender limitação ao direito de pleitear'],
        ['Recurso contra Multa Tributária', 'Recurso administrativo contra multa tributária'],
        ['Pedido de Restituição de ICMS', 'Pedido de restituição de ICMS pago indevidamente'],
        ['Pedido de Restituição de IPI', 'Pedido de restituição de IPI pago indevidamente'],
        ['Pedido de Restituição de ISS', 'Pedido de restituição de ISS pago indevidamente'],
        ['Ação de Repetição de Indébito de ICMS', 'Ação para repetir indébito de ICMS'],
        ['Ação de Repetição de Indébito de IPI', 'Ação para repetir indébito de IPI'],
        ['Ação Anulatória de Lançamento ICMS', 'Ação para anular lançamento de ICMS'],
        ['Ação Anulatória de Lançamento IPI', 'Ação para anular lançamento de IPI'],
        ['Mandado de Segurança - Tributário', 'Mandado de segurança contra ato fiscal'],
        ['Habeas Corpus Tributário', 'Habeas corpus para proteger liberdade contra coação fiscal'],
        ['Ação Declaratória de Inconstitucionalidade Tributária', 'Ação para declarar inconstitucionalidade de lei tributária'],
        ['Recurso Administrativo ICMS', 'Recurso administrativo contra lançamento de ICMS'],
        ['Recurso Administrativo IPI', 'Recurso administrativo contra lançamento de IPI'],
        ['Defesa em Auto de Infração ICMS', 'Defesa em auto de infração de ICMS'],
        ['Defesa em Auto de Infração IPI', 'Defesa em auto de infração de IPI'],
        ['Pedido de Parcelamento Fiscal', 'Pedido de parcelamento de dívida fiscal'],
        ['Acordo de Parcelamento Fiscal', 'Acordo para parcelar dívida fiscal'],
    ];
    
    foreach ($modelos_tributario_extras as $modelo) {
        $modelos[] = [
            'categoria_id' => $tributario,
            'nome' => $modelo[0],
            'descricao' => $modelo[1],
            'tipo_plano' => 'basico',
            'template' => 'Documento jurídico tributário: ' . strtolower($modelo[1]) . '.

{conteudo}

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
            'variaveis' => null
        ];
    }
}

// ========== MAIS MODELOS ADMINISTRATIVOS ==========
$administrativo = $categorias['Administrativo'] ?? null;
if ($administrativo) {
    $modelos_administrativo_extras = [
        ['Recurso Especial Administrativo', 'Recurso especial administrativo para STJ'],
        ['Recurso Extraordinário Administrativo', 'Recurso extraordinário administrativo para STF'],
        ['Agravo de Instrumento Administrativo', 'Agravo de instrumento administrativo'],
        ['Embargos de Declaração Administrativo', 'Embargos de declaração administrativo'],
        ['Ação de Obrigação de Fazer Administrativa', 'Ação para obrigar administração a fazer'],
        ['Ação de Obrigação de Não Fazer Administrativa', 'Ação para obrigar administração a não fazer'],
        ['Mandado de Segurança Coletivo', 'Mandado de segurança coletivo'],
        ['Mandado de Segurança com Pedido de Liminar', 'Mandado de segurança com pedido liminar'],
        ['Recurso Administrativo Hierárquico', 'Recurso administrativo hierárquico'],
        ['Recurso Administrativo Especial', 'Recurso administrativo especial'],
        ['Pedido de Revisão Administrativa', 'Pedido de revisão de decisão administrativa'],
        ['Ação de Ato Declaratório', 'Ação para declarar direito administrativo'],
        ['Ação de Anulação de Ato Administrativo', 'Ação para anular ato administrativo'],
        ['Petição de Licença Ambiental', 'Petição relacionada a licença ambiental'],
        ['Recurso contra Licitação', 'Recurso contra ato licitatório'],
        ['Impugnação a Licitação', 'Impugnação a procedimento licitatório'],
        ['Pedido de Anulação de Licitação', 'Pedido para anular licitação'],
        ['Recurso contra Desclassificação em Licitação', 'Recurso contra desclassificação em licitação'],
        ['Habeas Data Administrativo', 'Habeas data para acesso a dados administrativos'],
    ];
    
    foreach ($modelos_administrativo_extras as $modelo) {
        $modelos[] = [
            'categoria_id' => $administrativo,
            'nome' => $modelo[0],
            'descricao' => $modelo[1],
            'tipo_plano' => 'basico',
            'template' => 'Documento jurídico administrativo: ' . strtolower($modelo[1]) . '.

{conteudo}

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
            'variaveis' => null
        ];
    }
}

// ========== MAIS MODELOS FAMÍLIA ==========
$familia = $categorias['Família'] ?? null;
if ($familia) {
    $modelos_familia_extras = [
        ['Petição Inicial - Conversão de Separação em Divórcio', 'Petição para converter separação em divórcio'],
        ['Petição Inicial - Revisão de Partilha', 'Petição para revisar partilha de bens'],
        ['Petição Inicial - Anulação de Partilha', 'Petição para anular partilha realizada'],
        ['Petição Inicial - Adjudicação de Bens', 'Petição para adjudicação de bens em partilha'],
        ['Petição Inicial - Sucessão Hereditária', 'Petição relacionada a sucessão hereditária'],
        ['Petição Inicial - Herança Jacente', 'Petição para declarar herança jacente'],
        ['Petição Inicial - Declaração de Ausência', 'Petição para declarar ausência'],
        ['Petição Inicial - Suprimento de Idade', 'Petição para suprir idade de menor'],
        ['Petição Inicial - Emancipação', 'Petição para emancipar menor'],
        ['Petição Inicial - Retificação de Nome', 'Petição para retificar nome em registro'],
        ['Petição Inicial - Adição de Sobrenome', 'Petição para adicionar sobrenome'],
        ['Petição Inicial - Alteração de Nome em Registro', 'Petição para alterar nome em registro civil'],
        ['Petição Inicial - Declaração de Morte Presumida', 'Petição para declarar morte presumida'],
        ['Petição Inicial - Abertura de Testamentária', 'Petição para abrir testamento'],
        ['Petição Inicial - Cumprimento de Sentença Alimentos', 'Petição para cumprimento de sentença de alimentos'],
        ['Petição Inicial - Execução de Alimentos', 'Petição para executar alimentos em atraso'],
        ['Petição Inicial - Prisão Civil por Alimentos', 'Petição para decretar prisão civil por alimentos'],
        ['Petição Inicial - Sursis de Execução Alimentar', 'Petição para sursis em execução de alimentos'],
        ['Contestação - Ação de Família', 'Contestação em ação de família'],
        ['Reconvenção - Ação de Família', 'Reconvenção em ação de família'],
        ['Recurso de Apelação Família', 'Apelação em processo de família'],
        ['Embargos de Declaração Família', 'Embargos de declaração em processo de família'],
        ['Agravo de Instrumento Família', 'Agravo de instrumento em processo de família'],
        ['Acordo de Regulamentação', 'Acordo extrajudicial de regulamentação'],
        ['Acordo de Divórcio Amigável', 'Acordo amigável para divórcio'],
    ];
    
    foreach ($modelos_familia_extras as $modelo) {
        $modelos[] = [
            'categoria_id' => $familia,
            'nome' => $modelo[0],
            'descricao' => $modelo[1],
            'tipo_plano' => 'basico',
            'template' => 'Documento jurídico de família: ' . strtolower($modelo[1]) . '.

{conteudo}

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
            'variaveis' => null
        ];
    }
}

// Inserir modelos no banco
$inseridos = 0;
$erros = 0;

echo "Iniciando inserção de modelos extras...\n\n";

foreach ($modelos as $modelo) {
    try {
        $slug = criarSlug($modelo['nome'], $db);
        $variaveis = extrairVariaveis($modelo['template']);
        
        $sql = "INSERT INTO modelos_documentos 
                (categoria_id, nome, slug, descricao, tipo_plano_necessario, conteudo_template, variaveis, ativo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $modelo['categoria_id'],
            $modelo['nome'],
            $slug,
            $modelo['descricao'],
            $modelo['tipo_plano'],
            $modelo['template'],
            $variaveis
        ]);
        
        $inseridos++;
        echo "✓ {$modelo['nome']}\n";
        
    } catch (PDOException $e) {
        $erros++;
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo "⊘ {$modelo['nome']} (já existe)\n";
        } else {
            echo "✗ {$modelo['nome']} - Erro: {$e->getMessage()}\n";
        }
    }
}

echo "\n========================================\n";
echo "Inserção concluída!\n";
echo "Modelos inseridos: {$inseridos}\n";
echo "Erros/Duplicados: {$erros}\n";
echo "========================================\n";

