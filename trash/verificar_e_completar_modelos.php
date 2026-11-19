<?php
/**
 * Script para verificar e completar modelos até 30 por categoria
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Completar Modelos - Sistema Jurídico</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='card'>
            <div class='card-header bg-primary text-white'>
                <h4><i class='bi bi-list-check'></i> Verificação e Completação de Modelos</h4>
            </div>
            <div class='card-body'>";

try {
    // Verificar modelos existentes por categoria
    $stmt = $db->query("
        SELECT 
            cd.id,
            cd.nome as categoria_nome,
            COUNT(md.id) as total_modelos
        FROM categorias_documentos cd
        LEFT JOIN modelos_documentos md ON cd.id = md.categoria_id AND md.ativo = 1
        WHERE cd.ativo = 1
        GROUP BY cd.id, cd.nome
        ORDER BY cd.ordem
    ");
    
    $categorias = $stmt->fetchAll();
    $categoriasFaltando = [];
    
    echo "<h5 class='mb-3'>Status Atual:</h5>";
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>Categoria</th><th>Modelos Existentes</th><th>Meta</th><th>Faltam</th><th>Status</th></tr></thead>";
    echo "<tbody>";
    
    foreach ($categorias as $cat) {
        $total = (int)$cat['total_modelos'];
        $faltam = max(0, 30 - $total);
        $status = $total >= 30 ? '✅ OK' : '⚠️ Incompleto';
        $classe = $total >= 30 ? 'success' : 'warning';
        
        if ($faltam > 0) {
            $categoriasFaltando[] = [
                'id' => $cat['id'],
                'nome' => $cat['categoria_nome'],
                'total' => $total,
                'faltam' => $faltam
            ];
        }
        
        echo "<tr>";
        echo "<td><strong>{$cat['categoria_nome']}</strong></td>";
        echo "<td><span class='badge bg-info'>{$total}</span></td>";
        echo "<td>30</td>";
        echo "<td><span class='badge bg-{$classe}'>{$faltam}</span></td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
    
    if (count($categoriasFaltando) > 0) {
        echo "<div class='alert alert-warning mt-4'>";
        echo "<h6><i class='bi bi-exclamation-triangle'></i> Categorias que precisam de mais modelos:</h6>";
        echo "<ul>";
        foreach ($categoriasFaltando as $cat) {
            echo "<li><strong>{$cat['nome']}</strong>: Tem {$cat['total']}, faltam {$cat['faltam']} para chegar a 30</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='alert alert-info'>";
        echo "<h6><i class='bi bi-info-circle'></i> Gerando modelos adicionais...</h6>";
        echo "<p>Um arquivo SQL será gerado com os modelos necessários para completar todas as categorias.</p>";
        echo "</div>";
        
        // Gerar modelos faltantes
        gerarModelosFaltantes($categoriasFaltando);
        
        echo "<div class='alert alert-success mt-3'>";
        echo "<h6><i class='bi bi-check-circle'></i> Arquivo gerado com sucesso!</h6>";
        echo "<p>Arquivo: <code>modelos_faltantes.sql</code></p>";
        echo "<p>Este arquivo contém apenas os modelos que faltam para completar as categorias até 30.</p>";
        echo "<a href='importar_modelos_faltantes.php' class='btn btn-success mt-2'><i class='bi bi-download'></i> Importar Modelos Faltantes</a>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success mt-4'>";
        echo "<h5><i class='bi bi-check-circle'></i> Perfeito!</h5>";
        echo "<p>Todas as categorias têm pelo menos 30 modelos!</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>Erro:</h5>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "            </div>
            <div class='card-footer'>
                <a href='gerar.php' class='btn btn-primary'><i class='bi bi-file-earmark-plus'></i> Gerar Documento</a>
                <a href='verificar_modelos.php' class='btn btn-secondary'><i class='bi bi-search'></i> Verificar Novamente</a>
            </div>
        </div>
    </div>
</body>
</html>";

function gerarModelosFaltantes($categoriasFaltando) {
    $sql = "-- Modelos Faltantes para Completar 30 por Categoria\n";
    $sql .= "-- Gerado automaticamente em " . date('d/m/Y H:i:s') . "\n\n";
    
    // Lista de modelos por categoria
    $modelosPorCategoria = [
        1 => [ // CIVIL
            ['Contrato de Locação Comercial', 'contrato-locacao-comercial', 'basico'],
            ['Contrato de Prestação de Serviços Profissionais', 'contrato-prestacao-profissionais', 'basico'],
            ['Termo de Compromisso', 'termo-compromisso', 'basico'],
            ['Contrato de Parceria', 'contrato-parceria', 'basico'],
            ['Ação de Indenização por Danos Materiais', 'acao-indenizacao-materiais', 'pro1'],
            ['Ação de Indenização por Danos Morais', 'acao-indenizacao-morais', 'pro1'],
            ['Ação de Indenização por Lucros Cessantes', 'acao-lucros-cessantes', 'pro1'],
            ['Petição de Inventário Extrajudicial', 'peticao-inventario-extrajudicial', 'pro1'],
            ['Petição de Arrolamento', 'peticao-arrolamento', 'pro1'],
            ['Ação de Adjudicação Compulsória', 'acao-adjudicacao-compulsoria', 'pro2'],
            ['Contrato de Cessão de Direitos', 'contrato-cessao-direitos', 'basico'],
            ['Contrato de Fiança Bancária', 'contrato-fianca-bancaria', 'basico'],
            ['Ação de Prestação de Contas', 'acao-prestacao-contas', 'pro1'],
            ['Ação de Cobrança de Aluguéis', 'acao-cobranca-alugueis', 'pro1'],
            ['Contrato de Mútuo com Garantia', 'contrato-mutuo-garantia', 'basico'],
            ['Ação de Reivindicação', 'acao-reivindicacao', 'pro1'],
            ['Ação de Nulidade de Ato Jurídico', 'acao-nulidade-ato', 'pro1'],
            ['Petição de Separação de Corpos', 'peticao-separacao-corpos', 'pro1'],
            ['Contrato de Prestação de Serviços Técnicos', 'contrato-servicos-tecnicos', 'basico'],
            ['Ação de Despejo por Falta de Pagamento', 'acao-despejo-falta-pagamento', 'pro1'],
            ['Contrato de Representação Comercial', 'contrato-representacao-comercial', 'basico'],
            ['Ação de Perdas e Danos', 'acao-perdas-danos', 'pro1'],
            ['Contrato de Locação de Equipamentos', 'contrato-locacao-equipamentos', 'basico'],
        ],
        2 => [ // CRIMINAL
            ['Queixa-Crime por Estelionato', 'queixa-crime-estelionato', 'pro1'],
            ['Pedido de Liberdade Provisória com Fiança', 'pedido-liberdade-provisoria-fianca', 'pro1'],
            ['Recurso em Sentido Estrito', 'recurso-sentido-estrito', 'pro2'],
            ['Apelação Criminal', 'apelacao-criminal', 'pro2'],
            ['Queixa-Crime por Ameaça', 'queixa-crime-ameaca', 'pro1'],
            ['Pedido de Absolvição Sumária', 'pedido-absolvicao-sumaria', 'pro1'],
            ['Recurso contra Pronúncia', 'recurso-contra-pronuncia', 'pro2'],
            ['Pedido de Revisão Criminal', 'pedido-revisao-criminal', 'pro2'],
            ['Memorial de Defesa Criminal', 'memorial-defesa-criminal', 'pro1'],
            ['Agravo em Execução Penal', 'agravo-execucao-penal', 'pro2'],
            ['Pedido de Progressão de Regime', 'pedido-progressao-regime', 'pro1'],
            ['Queixa-Crime por Violência Doméstica', 'queixa-crime-violencia-domestica', 'pro1'],
            ['Recurso Especial Criminal', 'recurso-especial-criminal', 'pro2'],
            ['Pedido de Remição de Pena', 'pedido-remicao-pena', 'pro1'],
            ['Queixa-Crime por Perturbação', 'queixa-crime-perturbacao', 'pro1'],
            ['Apelação em Sentença Penal', 'apelacao-sentenca-penal', 'pro2'],
            ['Pedido de Pena Alternativa', 'pedido-pena-alternativa', 'pro1'],
            ['Queixa-Crime por Desacato', 'queixa-crime-desacato', 'pro1'],
            ['Recurso contra Decisão Interlocutória', 'recurso-decisao-interlocutoria', 'pro2'],
            ['Pedido de Suspensão Condicional', 'pedido-suspensao-condicional', 'pro1'],
            ['Queixa-Crime por Vias de Fato', 'queixa-crime-vias-fato', 'pro1'],
            ['Pedido de Sursis', 'pedido-sursis', 'pro1'],
            ['Recurso de Embargos Infringentes', 'recurso-embargos-infringentes', 'pro2'],
            ['Queixa-Crime por Tráfico de Drogas', 'queixa-crime-trafico-drogas', 'pro1'],
            ['Pedido de Livramento Condicional', 'pedido-livramento-condicional', 'pro1'],
            ['Apelação em Súmula', 'apelacao-sumula', 'pro2'],
        ],
        3 => [ // TRABALHISTA
            ['Reclamação por Horas Extras Não Pagas', 'reclamacao-horas-extras', 'pro1'],
            ['Pedido de Adicional Noturno', 'pedido-adicional-noturno', 'pro1'],
            ['Reclamação por FGTS', 'reclamacao-fgts', 'pro1'],
            ['Pedido de Equiparação Salarial', 'pedido-equiparacao-salarial', 'pro1'],
            ['Reclamação por Acidente de Trabalho', 'reclamacao-acidente-trabalho', 'pro1'],
            ['Pedido de 13º Salário', 'pedido-decimo-terceiro', 'basico'],
            ['Reclamação por Férias Vencidas', 'reclamacao-ferias-vencidas', 'pro1'],
            ['Pedido de Rescisão Indireta', 'pedido-rescisao-indireta', 'pro1'],
            ['Reclamação por Assédio Moral', 'reclamacao-assedio-moral', 'pro1'],
            ['Pedido de Estabilidade Gestante', 'pedido-estabilidade-gestante', 'pro1'],
            ['Reclamação por Insalubridade', 'reclamacao-insalubridade', 'pro1'],
            ['Pedido de Periculosidade', 'pedido-periculosidade', 'pro1'],
            ['Reclamação por Multa do FGTS', 'reclamacao-multa-fgts', 'pro1'],
            ['Pedido de Gratificação', 'pedido-gratificacao', 'pro1'],
            ['Reclamação por Intervalo Interjornada', 'reclamacao-intervalo', 'pro1'],
            ['Pedido de Adicional de Insalubridade', 'pedido-adicional-insalubridade', 'pro1'],
            ['Reclamação por Comissões', 'reclamacao-comissoes', 'pro1'],
            ['Pedido de Aviso Prévio', 'pedido-aviso-previo', 'basico'],
            ['Reclamação por DSR', 'reclamacao-dsr', 'pro1'],
            ['Pedido de Antecipação de Férias', 'pedido-antecipacao-ferias', 'basico'],
            ['Reclamação por Desconto Indevido', 'reclamacao-desconto-indevido', 'pro1'],
            ['Pedido de Seguro Desemprego', 'pedido-seguro-desemprego', 'basico'],
            ['Reclamação por Salário Família', 'reclamacao-salario-familia', 'pro1'],
            ['Pedido de Licença Maternidade', 'pedido-licenca-maternidade', 'basico'],
        ],
        4 => [ // TRIBUTÁRIO
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
            ['Pedido de Revisão de Lançamento', 'pedido-revisao-lancamento', 'pro1'],
            ['Ação de Consignação Tributária', 'acao-consignacao-tributaria', 'pro1'],
        ],
        5 => [ // ADMINISTRATIVO
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
            ['Pedido de Aposentadoria', 'pedido-aposentadoria', 'basico'],
            ['Ação de Revisão de Ato Administrativo', 'acao-revisao-ato-administrativo', 'pro1'],
        ],
        6 => [ // FAMÍLIA
            ['Pedido de Alteração de Guarda', 'pedido-alteracao-guarda', 'basico'],
            ['Petição de Tutela', 'peticao-tutela', 'pro1'],
            ['Ação de Alimentos Provisórios', 'acao-alimentos-provisorios', 'basico'],
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
            ['Ação de Alimentos Definitivos', 'acao-alimentos-definitivos', 'basico'],
            ['Pedido de Emancipação', 'pedido-emancipacao', 'basico'],
            ['Ação de Separação de Corpos', 'acao-separacao-corpos', 'basico'],
            ['Pedido de Regime de Visitas Ampliado', 'pedido-visitas-ampliado', 'pro1'],
            ['Ação de Reconhecimento de União Estável', 'acao-reconhecimento-uniao-estavel', 'basico'],
            ['Pedido de Autorização para Viagem', 'pedido-autorizacao-viagem', 'basico'],
        ]
    ];
    
    foreach ($categoriasFaltando as $cat) {
        $categoriaId = $cat['id'];
        $faltam = $cat['faltam'];
        
        if (!isset($modelosPorCategoria[$categoriaId])) {
            continue;
        }
        
        $modelos = $modelosPorCategoria[$categoriaId];
        $modelosParaAdicionar = array_slice($modelos, 0, $faltam);
        
        foreach ($modelosParaAdicionar as $modelo) {
            $sql .= gerarModeloSQL($categoriaId, $modelo[0], $modelo[1], $modelo[2]);
        }
    }
    
    $sql .= "\nUPDATE `modelos_documentos` SET `autor` = 'Sistema Jurídico', `versao` = 1.00 WHERE `autor` IS NULL;\n";
    
    file_put_contents(__DIR__ . '/modelos_faltantes.sql', $sql);
}

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
    
    // Escapar aspas simples
    return str_replace("'", "''", $base);
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
    
    $json = json_encode($variaveis, JSON_UNESCAPED_UNICODE);
    // Escapar aspas simples para SQL
    return str_replace("'", "''", $json);
}

?>

