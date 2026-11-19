<?php
/**
 * Script para adicionar modelos extensivos de documentos jurídicos
 * Baseado em pesquisa sobre documentos mais utilizados na advocacia brasileira
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

// Função para processar variáveis do template
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

// Array extensivo de modelos por categoria
$modelos = [];

// ========== CATEGORIA CIVIL ==========
$civil = $categorias['Civil'] ?? null;
if ($civil) {
    $modelos[] = [
        'categoria_id' => $civil,
        'nome' => 'Petição Inicial - Ação de Cobrança',
        'descricao' => 'Petição inicial para ação de cobrança de dívida com base em contrato ou título de crédito',
        'tipo_plano' => 'basico',
        'template' => 'EXCELENTÍSSIMO(A) SENHOR(A) DOUTOR(A) JUIZ(A) DE DIREITO DA {vara_comarca}

{advogado_nome}, OAB/{oab_estado} nº {oab_numero}, com escritório na {endereco_escritorio}, vem, respeitosamente, à presença de Vossa Excelência, propor em face de {reu_nome}, {reu_qualificacao}, AÇÃO DE COBRANÇA, pelo rito ordinário, pelas razões de fato e de direito que passa a expor:

I - DOS FATOS

O autor, {autor_nome}, possui em seu favor título de crédito/dívida no valor de R$ {valor_devido}, decorrente de {origem_divida}, conforme {documento_prova}.

A dívida encontra-se em atraso desde {data_vencimento}, tendo sido requerida a quitação, sem sucesso.

II - DO DIREITO

A matéria encontra-se respaldada pelos arts. {artigos_codigo_civil} do Código Civil, bem como na legislação pertinente.

O autor possui direito líquido e certo à restituição do valor devido, acrescido de juros de mora e correção monetária.

III - DOS PEDIDOS

Diante do exposto, requer:

a) o recebimento da presente petição inicial;
b) a citação do réu para, querendo, apresentar contestação no prazo legal;
c) a condenação do réu ao pagamento da quantia de R$ {valor_devido}, atualizada monetariamente e acrescida de juros de mora desde {data_vencimento} até o efetivo pagamento;
d) a condenação do réu ao pagamento de honorários advocatícios no percentual legal;
e) a produção de todas as provas necessárias, especialmente documental e testemunhal;
f) o deferimento de tutela provisória de urgência, se cabível, determinando o sequestro de bens do réu;
g) a condenação do réu nas custas processuais e honorários periciais, se houver;
h) a condenação em honorários de sucumbência;
i) a condenação aos juros moratórios e correção monetária nos termos da lei;
j) os benefícios da Justiça Gratuita, se for o caso.

PROCESSO COM VALOR DA CAUSA ESTIMADO EM R$ {valor_causa}.

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
        'variaveis' => null
    ];
    
    $modelos[] = [
        'categoria_id' => $civil,
        'nome' => 'Petição Inicial - Ação de Despejo',
        'descricao' => 'Petição inicial para despejo por falta de pagamento ou término de contrato',
        'tipo_plano' => 'basico',
        'template' => 'EXCELENTÍSSIMO(A) SENHOR(A) DOUTOR(A) JUIZ(A) DE DIREITO DA {vara_comarca}

{advogado_nome}, OAB/{oab_estado} nº {oab_numero}, com escritório na {endereco_escritorio}, vem, respeitosamente, à presença de Vossa Excelência, propor em face de {reu_nome}, {reu_qualificacao}, AÇÃO DE DESPEJO POR FALTA DE PAGAMENTO/TÉRMINO DE CONTRATO, pelo rito sumário, pelas razões que passa a expor:

I - DOS FATOS

O autor é proprietário do imóvel situado em {endereco_imovel}, conforme {documento_proprietario}.

Foi celebrado contrato de locação em {data_inicio_contrato}, pelo prazo de {prazo_contrato}, com pagamento mensal de R$ {valor_aluguel}.

O locatário encontra-se inadimplente desde {data_inadimplencia}, não pagando as parcelas de aluguel e demais encargos, totalizando o valor de R$ {valor_total_devido}.

Ainda, o contrato findou em {data_fim_contrato}, permanecendo o réu no imóvel após o término.

II - DO DIREITO

Os fatos narrados configuram a hipótese de despejo prevista na Lei nº 8.245/91, especificamente no art. {artigo_lei_locacao}.

O autor possui direito líquido e certo à retomada do imóvel e ao recebimento dos valores em atraso.

III - DOS PEDIDOS

Diante do exposto, requer:

a) o recebimento da presente petição inicial;
b) a citação do réu para, querendo, apresentar contestação no prazo legal;
c) o despejo do réu e de quem mais de direito do imóvel localizado em {endereco_imovel};
d) a condenação do réu ao pagamento das parcelas de aluguel em atraso, no valor total de R$ {valor_total_devido}, corrigidas monetariamente e acrescidas de juros;
e) a condenação ao pagamento de aluguel durante a tramitação do processo;
f) a condenação aos honorários advocatícios e demais encargos;
g) a produção de provas documentais e testemunhais;
h) o deferimento de liminar, determinando o despejo imediato.

PROCESSO COM VALOR DA CAUSA ESTIMADO EM R$ {valor_causa}.

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
        'variaveis' => null
    ];
    
    $modelos[] = [
        'categoria_id' => $civil,
        'nome' => 'Petição Inicial - Ação Indenizatória por Dano Moral',
        'descricao' => 'Petição inicial para ação indenizatória por danos morais',
        'tipo_plano' => 'basico',
        'template' => 'EXCELENTÍSSIMO(A) SENHOR(A) DOUTOR(A) JUIZ(A) DE DIREITO DA {vara_comarca}

{advogado_nome}, OAB/{oab_estado} nº {oab_numero}, com escritório na {endereco_escritorio}, vem, respeitosamente, à presença de Vossa Excelência, propor em face de {reu_nome}, {reu_qualificacao}, AÇÃO INDENIZATÓRIA POR DANO MORAL, pelo rito ordinário, pelas razões que passa a expor:

I - DOS FATOS

O autor, {autor_nome}, sofreu ofensa grave a sua honra, imagem e integridade moral em decorrência de {descricao_fato}, ocorrido em {data_fato}, no local {local_fato}.

A conduta do réu caracterizou-se por {caracterizacao_conduta}, causando ao autor intenso sofrimento psíquico, angústia, constrangimento e danos à sua reputação.

II - DO DIREITO

A matéria encontra fundamento nos arts. 186 e 927 do Código Civil, bem como no art. 5º, incisos V e X da Constituição Federal.

A conduta do réu configura ato ilícito, gerando o dever de indenizar os danos causados.

III - DOS PEDIDOS

Diante do exposto, requer:

a) o recebimento da presente petição inicial;
b) a citação do réu para, querendo, apresentar contestação no prazo legal;
c) a condenação do réu ao pagamento de indenização por danos morais no valor de R$ {valor_indenizacao};
d) a condenação aos honorários advocatícios e demais encargos;
e) a produção de provas testemunhais e documentais;
f) a condenação em honorários de sucumbência.

PROCESSO COM VALOR DA CAUSA ESTIMADO EM R$ {valor_causa}.

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
        'variaveis' => null
    ];
    
    // Adicionar mais modelos civis...
    $modelos_civil_extra = [
        ['Petição Inicial - Ação de Reconhecimento de Paternidade', 'Petição para reconhecimento de paternidade'],
        ['Petição Inicial - Ação de Usucapião', 'Petição para ação de usucapião de imóvel'],
        ['Petição Inicial - Ação de Inventário', 'Petição para ação de inventário e partilha'],
        ['Petição Inicial - Ação de Divórcio Consensual', 'Petição para divórcio consensual'],
        ['Petição Inicial - Ação de Divórcio Litigioso', 'Petição para divórcio litigioso'],
        ['Petição Inicial - Ação de Alimentos', 'Petição para fixação de alimentos'],
        ['Petição Inicial - Ação de Regulamentação de Visitas', 'Petição para regulamentação de visitação'],
        ['Petição Inicial - Ação de Guarda', 'Petição para ação de guarda de menores'],
        ['Petição Inicial - Ação de Anulação de Contrato', 'Petição para anular contrato com vício'],
        ['Petição Inicial - Ação de Rescisão de Contrato', 'Petição para rescisão contratual'],
        ['Petição Inicial - Ação de Cobrança de Prestação Alimentícia', 'Petição para cobrar alimentos em atraso'],
        ['Petição Inicial - Ação de Pensão Alimentícia Revisional', 'Petição para revisão de valor de pensão alimentícia'],
        ['Petição Inicial - Ação de Exoneração de Alimentos', 'Petição para exoneração de obrigação alimentar'],
        ['Petição Inicial - Ação de Nomeação de Inventariante', 'Petição para nomear inventariante'],
        ['Petição Inicial - Ação de Interdição', 'Petição para interdição judicial'],
        ['Petição Inicial - Ação de Efetivação de Testamento', 'Petição para efetivar testamento'],
        ['Petição Inicial - Ação de Anulação de Testamento', 'Petição para anular testamento'],
        ['Petição Inicial - Ação de Retificação de Registro Civil', 'Petição para retificar registro civil'],
        ['Petição Inicial - Ação de Busca e Apreensão', 'Petição para busca e apreensão de bem'],
        ['Petição Inicial - Ação de Consignação em Pagamento', 'Petição para consignação em pagamento'],
    ];
    
    foreach ($modelos_civil_extra as $modelo) {
        $modelos[] = [
            'categoria_id' => $civil,
            'nome' => $modelo[0],
            'descricao' => $modelo[1],
            'tipo_plano' => 'basico',
            'template' => 'Petição Inicial - Modelo padrão para ' . strtolower($modelo[1]) . '. Este é um template básico que deve ser adaptado conforme o caso concreto.

EXCELENTÍSSIMO(A) SENHOR(A) DOUTOR(A) JUIZ(A) DE DIREITO DA {vara_comarca}

{advogado_nome}, OAB/{oab_estado} nº {oab_numero}, vem, respeitosamente, propor em face de {reu_nome}, AÇÃO DE {tipo_acao}, pelas razões que passa a expor:

I - DOS FATOS

{fatos}

II - DO DIREITO

{direito}

III - DOS PEDIDOS

Requer:

a) o recebimento da presente petição inicial;
b) a citação do réu;
c) {pedidos_especificos};
d) a produção de provas;
e) a condenação em honorários e custas.

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
            'variaveis' => null
        ];
    }
}

// ========== CATEGORIA CRIMINAL ==========
$criminal = $categorias['Criminal'] ?? null;
if ($criminal) {
    $modelos[] = [
        'categoria_id' => $criminal,
        'nome' => 'Habeas Corpus - Preventivo',
        'descricao' => 'Habeas corpus preventivo para evitar constrangimento ilegal',
        'tipo_plano' => 'basico',
        'template' => 'EXCELENTÍSSIMO(A) SENHOR(A) DOUTOR(A) DESEMBARGADOR(A) DO E. TRIBUNAL DE JUSTIÇA DO ESTADO DE {estado}

HABEAS CORPUS PREVENTIVO Nº {numero_processo}

{advogado_nome}, OAB/{oab_estado} nº {oab_numero}, vem, respeitosamente, à presença de Vossa Excelência, impetrar HABEAS CORPUS em favor de {paciente_nome}, {paciente_qualificacao}, pelas razões que passa a expor:

I - DO IMPETRANTE E DO PACIENTE

O impetrante é {advogado_nome}, inscrito na OAB/{oab_estado} sob o nº {oab_numero}, com escritório na {endereco_escritorio}.

O paciente é {paciente_nome}, {paciente_qualificacao}.

II - DOS FATOS

O paciente encontra-se ameaçado de sofrer constrangimento ilegal na sua liberdade de locomoção, na medida em que {descricao_ameaca}.

{descricao_detalhada_fatos}

III - DO DIREITO

A situação narrada caracteriza constrangimento ilegal à liberdade de locomoção, nos termos do art. 5º, inciso LXVIII, da Constituição Federal.

O habeas corpus é remédio constitucional adequado para prevenir a violação iminente do direito de liberdade.

IV - DO PEDIDO

Diante do exposto, requer:

a) a concessão da ordem de habeas corpus;
b) a determinação para que cesse o constrangimento ilegal;
c) as providências necessárias ao restabelecimento do direito violado ou ameaçado;
d) a produção de provas necessárias.

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
        'variaveis' => null
    ];
    
    $modelos_criminal_extra = [
        ['Queixa-Crime - Crime Contra a Honra', 'Queixa-crime para crimes contra a honra (calúnia, difamação, injúria)'],
        ['Queixa-Crime - Ameaça', 'Queixa-crime para crime de ameaça'],
        ['Petição Inicial - Ação Penal Privada Subsidiária', 'Petição para ação penal privada subsidiária'],
        ['Recurso em Sentido Estrito', 'Recurso contra decisão de pronúncia ou impronúncia'],
        ['Apelação Criminal', 'Apelação contra sentença condenatória ou absolutória'],
        ['Revisão Criminal', 'Petição para revisão de processo criminal transitado em julgado'],
        ['Embargos de Declaração Criminal', 'Embargos contra decisão criminal com vícios'],
        ['Habeas Corpus - Liberatório', 'Habeas corpus para soltar preso'],
        ['Requerimento de Liberdade Provisória', 'Requerimento para conceder liberdade provisória'],
        ['Memorial em Defesa Criminal', 'Memorial de defesa em processo criminal'],
        ['Petição de Pronúncia', 'Petição para pronunciar réu em processo criminal'],
        ['Agravo em Execução Penal', 'Agravo contra decisão em execução penal'],
        ['Recurso Especial Criminal', 'Recurso especial para STJ em matéria criminal'],
        ['Reclamação Criminal', 'Reclamação contra decisão criminal'],
        ['Embargos Infringentes', 'Embargos infringentes em processo criminal'],
        ['Agravo de Instrumento Criminal', 'Agravo de instrumento em processo criminal'],
        ['Petição de Absolvição Sumária', 'Petição para absolvição sumária do réu'],
        ['Requerimento de Quebra de Sigilo', 'Requerimento para quebrar sigilo em investigação'],
        ['Defesa Prévia', 'Defesa prévia em inquérito policial'],
        ['Parecer Defensivo', 'Parecer defensivo em processo criminal'],
    ];
    
    foreach ($modelos_criminal_extra as $modelo) {
        $modelos[] = [
            'categoria_id' => $criminal,
            'nome' => $modelo[0],
            'descricao' => $modelo[1],
            'tipo_plano' => 'basico',
            'template' => 'Documento jurídico do tipo ' . strtolower($modelo[1]) . '. Este é um template básico que deve ser adaptado conforme o caso concreto.

{conteudo_documento}

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
            'variaveis' => null
        ];
    }
}

// ========== CATEGORIA TRABALHISTA ==========
$trabalhista = $categorias['Trabalhista'] ?? null;
if ($trabalhista) {
    $modelos_trabalhista = [
        ['Reclamação Trabalhista - Rescisão Indireta', 'Reclamação trabalhista por rescisão indireta'],
        ['Reclamação Trabalhista - Hora Extra', 'Reclamação trabalhista por horas extras'],
        ['Reclamação Trabalhista - Adicional Noturno', 'Reclamação trabalhista por adicional noturno'],
        ['Reclamação Trabalhista - FGTS', 'Reclamação trabalhista por FGTS'],
        ['Reclamação Trabalhista - 13º Salário', 'Reclamação trabalhista por décimo terceiro'],
        ['Reclamação Trabalhista - Férias Proporcionais', 'Reclamação trabalhista por férias proporcionais'],
        ['Reclamação Trabalhista - Diferenças Salariais', 'Reclamação trabalhista por diferenças salariais'],
        ['Reclamação Trabalhista - Danos Morais', 'Reclamação trabalhista por danos morais'],
        ['Reclamação Trabalhista - Assédio Moral', 'Reclamação trabalhista por assédio moral'],
        ['Reclamação Trabalhista - Assédio Sexual', 'Reclamação trabalhista por assédio sexual'],
        ['Reclamação Trabalhista - Acidente de Trabalho', 'Reclamação trabalhista por acidente de trabalho'],
        ['Reclamação Trabalhista - Equiparação Salarial', 'Reclamação trabalhista por equiparação salarial'],
        ['Reclamação Trabalhista - Insalubridade', 'Reclamação trabalhista por adicional de insalubridade'],
        ['Reclamação Trabalhista - Periculosidade', 'Reclamação trabalhista por adicional de periculosidade'],
        ['Reclamação Trabalhista - Vale Transporte', 'Reclamação trabalhista por vale transporte'],
        ['Reclamação Trabalhista - Vale Refeição', 'Reclamação trabalhista por vale refeição'],
        ['Reclamação Trabalhista - Comissões', 'Reclamação trabalhista por comissões não pagas'],
        ['Reclamação Trabalhista - Gratificação', 'Reclamação trabalhista por gratificação'],
        ['Reclamação Trabalhista - Descanso Semanal', 'Reclamação trabalhista por descanso semanal remunerado'],
        ['Reclamação Trabalhista - Estabilidade', 'Reclamação trabalhista por estabilidade provisória'],
        ['Reclamação Trabalhista - Anotação CTPS', 'Reclamação trabalhista por anotação na CTPS'],
        ['Reclamação Trabalhista - Salário Família', 'Reclamação trabalhista por salário família'],
        ['Reclamação Trabalhista - PIS/PASEP', 'Reclamação trabalhista por PIS/PASEP'],
        ['Reclamação Trabalhista - Aviso Prévio', 'Reclamação trabalhista por aviso prévio'],
        ['Reclamação Trabalhista - Multa do art. 477', 'Reclamação trabalhista por multa do artigo 477 da CLT'],
    ];
    
    foreach ($modelos_trabalhista as $modelo) {
        $modelos[] = [
            'categoria_id' => $trabalhista,
            'nome' => $modelo[0],
            'descricao' => $modelo[1],
            'tipo_plano' => 'basico',
            'template' => 'VARA DO TRABALHO DE {comarca}

RECLAMAÇÃO TRABALHISTA

Reclamante: {reclamante_nome}
Reclamado: {reclamado_nome}

{advogado_nome}, OAB/{oab_estado} nº {oab_numero}, vem, respeitosamente, à presença de Vossa Excelência, propor RECLAMAÇÃO TRABALHISTA em face de {reclamado_nome}, pelas razões que passa a expor:

I - DOS FATOS

{fatos}

II - DO DIREITO

{direito}

III - DOS PEDIDOS

Requer:

a) o recebimento da presente reclamação;
b) a citação do reclamado;
c) {pedidos};
d) a condenação em honorários e custas.

{local}, {data}

{advogado_nome}
OAB/{oab_estado} nº {oab_numero}',
            'variaveis' => null
        ];
    }
}

// ========== CATEGORIA TRIBUTÁRIO ==========
$tributario = $categorias['Tributário'] ?? null;
if ($tributario) {
    $modelos_tributario = [
        ['Mandado de Segurança - Imposto', 'Mandado de segurança contra cobrança de imposto'],
        ['Ação Anulatória de Débito Fiscal', 'Ação para anular débito fiscal'],
        ['Recurso Administrativo Fiscal', 'Recurso administrativo contra lançamento fiscal'],
        ['Pedido de Restituição de Imposto', 'Pedido de restituição de imposto pago indevidamente'],
        ['Ação Declaratória de Inexistência de Débito Fiscal', 'Ação declaratória de inexistência de crédito tributário'],
        ['Ação de Repetição de Indébito Tributário', 'Ação para repetir indébito tributário'],
        ['Embargos à Execução Fiscal', 'Embargos contra execução fiscal'],
        ['Defesa Prévia em Auto de Infração', 'Defesa prévia em processo administrativo fiscal'],
        ['Impugnação ao Valor da Causa Fiscal', 'Impugnação ao valor da causa em processo fiscal'],
        ['Ação de Consignação em Pagamento Fiscal', 'Ação de consignação em pagamento para dívida fiscal'],
        ['Mandado de Segurança - Contribuição Previdenciária', 'Mandado de segurança contra cobrança previdenciária'],
        ['Recurso contra Multa Fiscal', 'Recurso contra aplicação de multa fiscal'],
        ['Ação Anulatória de Lançamento', 'Ação para anular lançamento tributário'],
        ['Petição de Suspensão de Exigibilidade', 'Petição para suspender exigibilidade fiscal'],
        ['Impugnação ao Crédito Tributário', 'Impugnação ao crédito tributário'],
    ];
    
    foreach ($modelos_tributario as $modelo) {
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

// ========== CATEGORIA ADMINISTRATIVO ==========
$administrativo = $categorias['Administrativo'] ?? null;
if ($administrativo) {
    $modelos_administrativo = [
        ['Mandado de Segurança - Administrativo', 'Mandado de segurança contra ato administrativo'],
        ['Ação Popular', 'Ação popular contra ato lesivo ao patrimônio público'],
        ['Ação Civil Pública', 'Ação civil pública para defesa de interesses difusos'],
        ['Requerimento Administrativo', 'Requerimento administrativo genérico'],
        ['Recurso Administrativo', 'Recurso administrativo genérico'],
        ['Pedido de Reconsideração', 'Pedido de reconsideração de decisão administrativa'],
        ['Impugnação Administrativa', 'Impugnação a ato administrativo'],
        ['Ação de Obrigação de Fazer', 'Ação para obrigar administração pública a fazer'],
        ['Ação de Obrigação de Não Fazer', 'Ação para obrigar administração pública a não fazer'],
        ['Ação de Improbidade Administrativa', 'Ação de improbidade administrativa'],
        ['Mandado de Injunção', 'Mandado de injunção para suprir omissão legislativa'],
        ['Habeas Data', 'Habeas data para acesso a dados pessoais'],
        ['Petição de Licitação', 'Petição relacionada a processo licitatório'],
        ['Recurso em Licitação', 'Recurso contra decisão em processo licitatório'],
        ['Impugnação a Edital', 'Impugnação a edital de licitação'],
    ];
    
    foreach ($modelos_administrativo as $modelo) {
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

// ========== CATEGORIA FAMÍLIA ==========
$familia = $categorias['Família'] ?? null;
if ($familia) {
    $modelos_familia = [
        ['Petição Inicial - Divórcio Extrajudicial', 'Petição para divórcio extrajudicial em cartório'],
        ['Petição Inicial - Divórcio Litigioso com Partilha', 'Petição para divórcio litigioso com partilha de bens'],
        ['Petição Inicial - Separação Judicial', 'Petição para separação judicial'],
        ['Petição Inicial - União Estável', 'Petição para reconhecimento de união estável'],
        ['Petição Inicial - Dissolução de União Estável', 'Petição para dissolução de união estável'],
        ['Petição Inicial - Reconhecimento de Paternidade Socioafetiva', 'Petição para reconhecimento de paternidade socioafetiva'],
        ['Petição Inicial - Adoção', 'Petição para processo de adoção'],
        ['Petição Inicial - Alimentos Gravídicos', 'Petição para alimentos durante gravidez'],
        ['Petição Inicial - Investigação de Paternidade', 'Petição para investigação de paternidade'],
        ['Petição Inicial - Alimentos para Maior', 'Petição para fixar alimentos para maior de idade'],
        ['Petição Inicial - Exoneração de Alimentos', 'Petição para exonerar obrigação alimentar'],
        ['Petição Inicial - Revisão de Alimentos', 'Petição para revisar valor de alimentos'],
        ['Petição Inicial - Guarda Compartilhada', 'Petição para estabelecer guarda compartilhada'],
        ['Petição Inicial - Alteração de Guarda', 'Petição para alterar guarda de menor'],
        ['Petição Inicial - Regulamentação de Convivência', 'Petição para regulamentar convivência com filhos'],
        ['Petição Inicial - Interdição', 'Petição para interdição judicial'],
        ['Petição Inicial - Curatela', 'Petição para nomeação de curador'],
        ['Petição Inicial - Tutela', 'Petição para nomeação de tutor'],
        ['Petição Inicial - Inventário Extrajudicial', 'Petição para inventário em cartório'],
        ['Petição Inicial - Inventário Judicial', 'Petição para inventário judicial'],
        ['Petição Inicial - Arrolamento Sumário', 'Petição para arrolamento sumário'],
        ['Petição Inicial - Alvará Judicial', 'Petição para obter alvará judicial'],
        ['Petição Inicial - Anulação de Casamento', 'Petição para anular casamento'],
        ['Petição Inicial - Separação de Corpos', 'Petição para separação de corpos'],
        ['Petição Inicial - Partilha Amigável', 'Petição para partilha amigável de bens'],
    ];
    
    foreach ($modelos_familia as $modelo) {
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

echo "Iniciando inserção de modelos...\n\n";

foreach ($modelos as $modelo) {
    try {
        // Criar slug
        $slug = criarSlug($modelo['nome'], $db);
        
        // Extrair variáveis do template
        $variaveis = extrairVariaveis($modelo['template']);
        
        // Preparar SQL
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
        // Verificar se é erro de duplicidade
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

