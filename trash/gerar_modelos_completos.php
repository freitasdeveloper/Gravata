<?php
/**
 * Script para gerar todos os modelos de documentos necessários
 * Garante pelo menos 30 modelos em cada categoria
 */

// Modelos adicionais necessários por categoria
$modelosAdicionais = [
    1 => 23, // Civil - já tem 7, precisa 23 para chegar a 30
    2 => 26, // Criminal - já tem 4, precisa 26 para chegar a 30
    3 => 24, // Trabalhista - já tem 6, precisa 24 para chegar a 30
    4 => 27, // Tributário - já tem 3, precisa 27 para chegar a 30
    5 => 27, // Administrativo - já tem 3, precisa 27 para chegar a 30
    6 => 24  // Família - já tem 6, precisa 24 para chegar a 30
];

$sql = "-- Modelos Adicionais de Documentos Jurídicos\n";
$sql .= "-- Gerado automaticamente para garantir 30 modelos por categoria\n\n";

// Modelos CIVIL (categoria 1)
$modelosCivil = [
    ['Contrato de Locação Residencial', 'contrato-locacao-residencial', 'basico'],
    ['Ação de Cobrança', 'acao-cobranca', 'basico'],
    ['Contrato de Prestação de Serviços', 'contrato-prestacao-servicos', 'basico'],
    ['Ação de Rescisão Contratual', 'acao-rescisao-contratual', 'pro1'],
    ['Contrato de Doação', 'contrato-doacao', 'basico'],
    ['Ação de Interdição', 'acao-interdicao', 'pro1'],
    ['Contrato de Comodato', 'contrato-comodato', 'basico'],
    ['Petição de Inventário', 'peticao-inventario', 'pro1'],
    ['Contrato de Mútuo', 'contrato-mutuo', 'basico'],
    ['Ação de Danos Morais', 'acao-danos-morais', 'pro1'],
    ['Contrato de Depósito', 'contrato-deposito', 'basico'],
    ['Petição de Partilha', 'peticao-partilha', 'pro1'],
    ['Contrato de Fiança', 'contrato-fianca', 'basico'],
    ['Ação de Indenização', 'acao-indenizacao', 'pro1'],
    ['Contrato de Promessa de Compra e Venda', 'promessa-compra-venda', 'basico'],
    ['Petição de Separação de Corpos', 'peticao-separacao-corpos', 'pro1'],
    ['Contrato de Arrendamento Mercantil', 'contrato-arrendamento-mercantil', 'pro1'],
    ['Ação de Usucapião Especial Urbana', 'acao-usucapiao-urbana', 'pro1'],
    ['Contrato de Mandato', 'contrato-mandato', 'basico'],
    ['Petição de Retificação de Registro', 'peticao-retificacao-registro', 'pro1'],
    ['Contrato de Parceria Rural', 'contrato-parceria-rural', 'basico'],
    ['Ação de Despejo', 'acao-despejo', 'pro1'],
    ['Contrato de Parceria Agrícola', 'contrato-parceria-agricola', 'basico']
];

foreach ($modelosCivil as $modelo) {
    $sql .= gerarModeloSQL(1, $modelo[0], $modelo[1], $modelo[2]);
}

// Modelos CRIMINAL (categoria 2)
$modelosCriminal = [
    ['Defesa Prévia Criminal', 'defesa-previa-criminal', 'pro1'],
    ['Recurso em Liberdade Criminal', 'recurso-liberdade-criminal', 'pro2'],
    ['Queixa-Crime por Injúria', 'queixa-crime-injuria', 'pro1'],
    ['Pedido de Liberdade Provisória', 'pedido-liberdade-provisoria', 'pro1'],
    ['Queixa-Crime por Difamação', 'queixa-crime-difamacao', 'pro1'],
    ['Recurso em Sentido Estrito', 'recurso-sentido-estrito', 'pro2'],
    ['Queixa-Crime por Calúnia', 'queixa-crime-calunia', 'pro1'],
    ['Pedido de Revogação de Prisão', 'pedido-revogacao-prisao', 'pro1'],
    ['Defesa Preliminar', 'defesa-preliminar', 'pro1'],
    ['Apelação Criminal', 'apelacao-criminal', 'pro2'],
    ['Queixa-Crime por Estelionato', 'queixa-crime-estelionato', 'pro1'],
    ['Habeas Corpus Preventivo', 'habeas-corpus-preventivo', 'pro2'],
    ['Pedido de Absolvição Sumária', 'pedido-absolvicao-sumaria', 'pro1'],
    ['Recurso contra Pronúncia', 'recurso-contra-pronuncia', 'pro2'],
    ['Queixa-Crime por Ameaça', 'queixa-crime-ameaca', 'pro1'],
    ['Pedido de Revisão Criminal', 'pedido-revisao-criminal', 'pro2'],
    ['Memorial de Defesa', 'memorial-defesa', 'pro1'],
    ['Agravo em Execução Penal', 'agravo-execucao-penal', 'pro2'],
    ['Pedido de Progressão de Regime', 'pedido-progressao-regime', 'pro1'],
    ['Queixa-Crime por Violência Doméstica', 'queixa-crime-violencia-domestica', 'pro1'],
    ['Recurso Especial Criminal', 'recurso-especial-criminal', 'pro2'],
    ['Pedido de Remição de Pena', 'pedido-remicao-pena', 'pro1'],
    ['Queixa-Crime por Perturbação', 'queixa-crime-perturbacao', 'pro1'],
    ['Apelação em Sentença Penal', 'apelacao-sentenca-penal', 'pro2'],
    ['Pedido de Pena Alternativa', 'pedido-pena-alternativa', 'pro1'],
    ['Queixa-Crime por Desacato', 'queixa-crime-desacato', 'pro1']
];

foreach ($modelosCriminal as $modelo) {
    $sql .= gerarModeloSQL(2, $modelo[0], $modelo[1], $modelo[2]);
}

// Modelos TRABALHISTA (categoria 3)
$modelosTrabalhista = [
    ['Pedido de Reintegração', 'pedido-reintegracao', 'pro1'],
    ['Reclamação Trabalhista Danos Morais', 'reclamacao-danos-morais', 'pro1'],
    ['Certidão de Tempo de Serviço', 'certidao-tempo-servico', 'basico'],
    ['Reclamação por Horas Extras', 'reclamacao-horas-extras', 'pro1'],
    ['Pedido de Adicional Noturno', 'pedido-adicional-noturno', 'pro1'],
    ['Reclamação por FGTS', 'reclamacao-fgts', 'pro1'],
    ['Pedido de Equiparação Salarial', 'pedido-equiparacao-salarial', 'pro1'],
    ['Reclamação por Acidente de Trabalho', 'reclamacao-acidente-trabalho', 'pro1'],
    ['Pedido de 13º Salário', 'pedido-decimo-terceiro', 'basico'],
    ['Reclamação por Férias Vencidas', 'reclamacao-ferias-vencidas', 'pro1'],
    ['Pedido de Rescisão Indireta', 'pedido-rescisao-indireta', 'pro1'],
    ['Reclamação por Assédio Moral', 'reclamacao-assedio-moral', 'pro1'],
    ['Pedido de Estabilidade', 'pedido-estabilidade', 'pro1'],
    ['Reclamação por Insalubridade', 'reclamacao-insalubridade', 'pro1'],
    ['Pedido de Periculosidade', 'pedido-periculosidade', 'pro1'],
    ['Reclamação por Multa do FGTS', 'reclamacao-multa-fgts', 'pro1'],
    ['Pedido de Gratificação', 'pedido-gratificacao', 'pro1'],
    ['Reclamação por Intervalo', 'reclamacao-intervalo', 'pro1'],
    ['Pedido de Adicional de Insalubridade', 'pedido-adicional-insalubridade', 'pro1'],
    ['Reclamação por Comissões', 'reclamacao-comissoes', 'pro1'],
    ['Pedido de Aviso Prévio', 'pedido-aviso-previo', 'basico'],
    ['Reclamação por DSR', 'reclamacao-dsr', 'pro1'],
    ['Pedido de Antecipação de Férias', 'pedido-antecipacao-ferias', 'basico'],
    ['Reclamação por Desconto Indevido', 'reclamacao-desconto-indevido', 'pro1']
];

foreach ($modelosTrabalhista as $modelo) {
    $sql .= gerarModeloSQL(3, $modelo[0], $modelo[1], $modelo[2]);
}

// Modelos TRIBUTÁRIO (categoria 4)
$modelosTributario = [
    ['Ação Anulatória de Débito Fiscal', 'acao-anulatoria-debito-fiscal', 'pro2'],
    ['Recurso Administrativo Fiscal', 'recurso-administrativo-fiscal', 'pro1'],
    ['Pedido de Restituição Tributária', 'pedido-restituicao-tributaria', 'pro1'],
    ['Ação de Repetição de Indébito', 'acao-repeticao-indebito', 'pro1'],
    ['Recurso contra Auto de Infração', 'recurso-auto-infracao', 'pro1'],
    ['Pedido de Compensação Tributária', 'pedido-compensacao-tributaria', 'pro1'],
    ['Ação Declaratória de Inexigibilidade', 'acao-declaratoria-inexigibilidade', 'pro2'],
    ['Recurso Especial Tributário', 'recurso-especial-tributario', 'pro2'],
    ['Pedido de Parcelamento', 'pedido-parcelamento', 'basico'],
    ['Ação de Exoneração Tributária', 'acao-exoneracao-tributaria', 'pro2'],
    ['Recurso contra Lançamento', 'recurso-contra-lancamento', 'pro1'],
    ['Pedido de Certidão Negativa', 'pedido-certidao-negativa', 'basico'],
    ['Ação de Consignação em Pagamento', 'acao-consignacao-pagamento', 'pro1'],
    ['Recurso Administrativo ICMS', 'recurso-administrativo-icms', 'pro1'],
    ['Pedido de Recuperação Fiscal', 'pedido-recuperacao-fiscal', 'pro2'],
    ['Ação de Anulação de Dívida Ativa', 'acao-anulacao-divida-ativa', 'pro2'],
    ['Recurso contra Multa Fiscal', 'recurso-contra-multa-fiscal', 'pro1'],
    ['Pedido de Isenção Tributária', 'pedido-isencao-tributaria', 'pro1'],
    ['Ação de Constitucionalidade', 'acao-constitucionalidade', 'pro2'],
    ['Recurso Administrativo IPI', 'recurso-administrativo-ipi', 'pro1'],
    ['Pedido de Anistia Fiscal', 'pedido-anistia-fiscal', 'pro1'],
    ['Ação de Nulidade de Lançamento', 'acao-nulidade-lancamento', 'pro2'],
    ['Recurso contra Intimação', 'recurso-contra-intimacao', 'pro1'],
    ['Pedido de Desconto Tributário', 'pedido-desconto-tributario', 'pro1'],
    ['Ação de Exclusão de ICMS', 'acao-exclusao-icms', 'pro2'],
    ['Recurso Especial Fazenda Pública', 'recurso-especial-fazenda', 'pro2'],
    ['Pedido de Revisão de Lançamento', 'pedido-revisao-lancamento', 'pro1']
];

foreach ($modelosTributario as $modelo) {
    $sql .= gerarModeloSQL(4, $modelo[0], $modelo[1], $modelo[2]);
}

// Modelos ADMINISTRATIVO (categoria 5)
$modelosAdministrativo = [
    ['Pedido de Revisão Administrativa', 'pedido-revisao-administrativa', 'pro1'],
    ['Recurso Administrativo Licitação', 'recurso-administrativo-licitacao', 'pro1'],
    ['Pedido de Anulação de Licitação', 'pedido-anulacao-licitacao', 'pro1'],
    ['Ação de Obrigação de Fazer', 'acao-obrigacao-fazer', 'pro1'],
    ['Recurso contra Decisão Administrativa', 'recurso-decisao-administrativa', 'pro1'],
    ['Pedido de Certidão Administrativa', 'pedido-certidao-administrativa', 'basico'],
    ['Ação Popular', 'acao-popular', 'pro1'],
    ['Recurso Administrativo Prefeitura', 'recurso-administrativo-prefeitura', 'pro1'],
    ['Pedido de Revisão de Alvará', 'pedido-revisao-alvara', 'pro1'],
    ['Ação Civil Pública', 'acao-civil-publica', 'pro2'],
    ['Recurso contra Multa Administrativa', 'recurso-contra-multa-administrativa', 'pro1'],
    ['Pedido de Licença Ambiental', 'pedido-licenca-ambiental', 'basico'],
    ['Ação de Improbidade Administrativa', 'acao-improbidade-administrativa', 'pro2'],
    ['Recurso Administrativo Estadual', 'recurso-administrativo-estadual', 'pro1'],
    ['Pedido de Registro Comercial', 'pedido-registro-comercial', 'basico'],
    ['Ação de Anulação de Ato', 'acao-anulacao-ato', 'pro1'],
    ['Recurso contra Ato Normativo', 'recurso-contra-ato-normativo', 'pro2'],
    ['Pedido de Certidão de Regularidade', 'pedido-certidao-regularidade', 'basico'],
    ['Ação de Conselho de Classe', 'acao-conselho-classe', 'pro1'],
    ['Recurso Administrativo Federal', 'recurso-administrativo-federal', 'pro1'],
    ['Pedido de Habilitação Licitatória', 'pedido-habilitacao-licitatoria', 'basico'],
    ['Ação de Responsabilidade Civil', 'acao-responsabilidade-civil-adm', 'pro2'],
    ['Recurso Especial Administrativo', 'recurso-especial-administrativo', 'pro2'],
    ['Pedido de Alvará de Funcionamento', 'pedido-alvara-funcionamento', 'basico'],
    ['Ação de Desapropriação', 'acao-desapropriacao', 'pro2'],
    ['Recurso contra Concurso Público', 'recurso-contra-concurso-publico', 'pro1'],
    ['Pedido de Aposentadoria', 'pedido-aposentadoria', 'basico']
];

foreach ($modelosAdministrativo as $modelo) {
    $sql .= gerarModeloSQL(5, $modelo[0], $modelo[1], $modelo[2]);
}

// Modelos FAMÍLIA (categoria 6)
$modelosFamilia = [
    ['Pedido de Guarda de Filhos', 'pedido-guarda-filhos', 'basico'],
    ['Petição de Adoção', 'peticao-adocao', 'pro1'],
    ['Ação de Reconhecimento Paternidade', 'acao-reconhecimento-paternidade', 'basico'],
    ['Pedido de Suspensão de Poder Familiar', 'pedido-suspensao-poder-familiar', 'pro1'],
    ['Ação de Alteração de Guarda', 'acao-alteracao-guarda', 'basico'],
    ['Petição de Tutela', 'peticao-tutela', 'pro1'],
    ['Ação de Alimentos', 'acao-alimentos', 'basico'],
    ['Pedido de Regulamentação de Visitas', 'pedido-regulamentacao-visitas', 'basico'],
    ['Ação de Investigação de Paternidade', 'acao-investigacao-paternidade', 'basico'],
    ['Petição de Curatela', 'peticao-curatela', 'pro1'],
    ['Ação de Destituição de Poder Familiar', 'acao-destituicao-poder-familiar', 'pro2'],
    ['Pedido de Pensão Alimentícia Revisional', 'pedido-pensao-revisional', 'basico'],
    ['Ação de Desquite', 'acao-desquite', 'basico'],
    ['Petição de Separação Judicial', 'peticao-separacao-judicial', 'basico'],
    ['Ação de Exoneração de Alimentos', 'acao-exoneracao-alimentos', 'basico'],
    ['Pedido de Testamento', 'pedido-testamento', 'pro1'],
    ['Ação de Anulação de Casamento', 'acao-anulacao-casamento', 'pro1'],
    ['Petição de Regime de Bens', 'peticao-regime-bens', 'basico'],
    ['Ação de Sucessão', 'acao-sucessao', 'pro1'],
    ['Pedido de Adoção Internacional', 'pedido-adocao-internacional', 'pro2'],
    ['Ação de Partilha de Bens', 'acao-partilha-bens', 'pro1'],
    ['Petição de Interdição Família', 'peticao-interdicao-familia', 'pro1'],
    ['Ação de Alimentos Provisórios', 'acao-alimentos-provisorios', 'basico'],
    ['Pedido de Emancipação', 'pedido-emancipacao', 'basico']
];

foreach ($modelosFamilia as $modelo) {
    $sql .= gerarModeloSQL(6, $modelo[0], $modelo[1], $modelo[2]);
}

$sql .= "\nUPDATE `modelos_documentos` SET `autor` = 'Sistema Jurídico', `versao` = 1.00 WHERE `autor` IS NULL;\n";

file_put_contents('modelos_adicionais_completos.sql', $sql);
echo "Arquivo SQL gerado com sucesso! Total de modelos: " . (23 + 26 + 24 + 27 + 27 + 24) . "\n";

function gerarModeloSQL($categoriaId, $nome, $slug, $plano) {
    $descricao = "Modelo de " . strtolower($nome);
    $template = gerarTemplate($nome);
    $variaveis = gerarVariaveis($nome);
    
    $sql = "-- {$nome}\n";
    $sql .= "INSERT INTO `modelos_documentos` \n";
    $sql .= "(`categoria_id`, `nome`, `slug`, `descricao`, `tipo_plano_necessario`, `conteudo_template`, `variaveis`) \n";
    $sql .= "VALUES \n";
    $sql .= "({$categoriaId}, '{$nome}', '{$slug}', '{$descricao}', '{$plano}',\n";
    $sql .= "'{$template}',\n";
    $sql .= "'{$variaveis}');\n\n";
    
    return $sql;
}

function gerarTemplate($nome) {
    $base = "DOCUMENTO: {$nome}\n\n";
    $base .= "EXCELENTÍSSIMO SENHOR DOUTOR JUIZ DE DIREITO DA __ VARA DA COMARCA DE __\n\n";
    $base .= "{{requerente_nome}}, brasileiro(a), __, portador(a) do RG nº __, inscrito(a) no CPF sob nº __, residente e domiciliado(a) à __, vem respeitosamente propor a presente ação, pelos fatos e fundamentos que passa a expor.\n\n";
    $base .= "DOS FATOS\n\n";
    $base .= "{{relato_fatos}}\n\n";
    $base .= "DO DIREITO\n\n";
    $base .= "{{fundamentos_juridicos}}\n\n";
    $base .= "DO PEDIDO\n\n";
    $base .= "Ante o exposto, requer V.Sa.:\n\n";
    $base .= "a) {{pedido1}};\n";
    $base .= "b) {{pedido2}};\n";
    $base .= "c) Os ônus da sucumbência.\n\n";
    $base .= "{{local}}, __/__/____\n\n";
    $base .= "{{advogado_nome}}\n";
    $base .= "OAB/{{estado_oab}} nº {{oab_numero}}";
    
    return addslashes($base);
}

function gerarVariaveis($nome) {
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
    
    return addslashes(json_encode($variaveis));
}

