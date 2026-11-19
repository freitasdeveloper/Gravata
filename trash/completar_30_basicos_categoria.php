<?php
/**
 * Script para completar 30 modelos BÁSICOS em cada categoria
 * Para que usuários do plano gratuito vejam 30 modelos em cada categoria
 */

require_once 'config.php';
require_once 'includes/database.php';

$db = Database::getInstance()->getConnection();

// Modelos básicos faltantes por categoria
$modelosBasicosFaltantes = [
    1 => [ // CIVIL - tem 15, faltam 15
        ['Contrato de Locação Residencial Básico', 'contrato-locacao-residencial-basico', 'Modelo básico de locação residencial', 'basico'],
        ['Contrato de Prestação de Serviços Básico', 'contrato-prestacao-servicos-basico', 'Modelo básico de prestação de serviços', 'basico'],
        ['Termo de Compromisso', 'termo-compromisso-basico', 'Termo de compromisso', 'basico'],
        ['Contrato de Parceria', 'contrato-parceria-basico', 'Contrato de parceria', 'basico'],
        ['Contrato de Cessão de Direitos', 'contrato-cessao-direitos-basico', 'Contrato de cessão de direitos', 'basico'],
        ['Contrato de Fiança Bancária', 'contrato-fianca-bancaria-basico', 'Contrato de fiança bancária', 'basico'],
        ['Contrato de Mútuo Simples', 'contrato-mutuo-simples', 'Contrato de mútuo simples', 'basico'],
        ['Contrato de Depósito', 'contrato-deposito-basico', 'Contrato de depósito', 'basico'],
        ['Ação de Cobrança Simples', 'acao-cobranca-simples', 'Ação de cobrança simples', 'basico'],
        ['Contrato de Comodato', 'contrato-comodato-basico', 'Contrato de comodato', 'basico'],
        ['Termo de Acordo', 'termo-acordo-basico', 'Termo de acordo', 'basico'],
        ['Contrato de Parceria Rural', 'contrato-parceria-rural-basico', 'Contrato de parceria rural', 'basico'],
        ['Contrato de Arrendamento', 'contrato-arrendamento-basico', 'Contrato de arrendamento', 'basico'],
        ['Recibo de Pagamento', 'recibo-pagamento', 'Recibo de pagamento', 'basico'],
        ['Contrato de Mandato', 'contrato-mandato-basico', 'Contrato de mandato', 'basico'],
    ],
    2 => [ // CRIMINAL - tem 0, faltam 30
        ['Queixa-Crime Simples', 'queixa-crime-simples', 'Queixa-crime simples', 'basico'],
        ['Pedido de Liberdade Provisória', 'pedido-liberdade-provisoria-basico', 'Pedido de liberdade provisória', 'basico'],
        ['Defesa Prévia Simplificada', 'defesa-previa-simplificada', 'Defesa prévia simplificada', 'basico'],
        ['Queixa-Crime por Injúria', 'queixa-crime-injuria-basico', 'Queixa-crime por injúria', 'basico'],
        ['Queixa-Crime por Difamação', 'queixa-crime-difamacao-basico', 'Queixa-crime por difamação', 'basico'],
        ['Queixa-Crime por Calúnia', 'queixa-crime-calunia-basico', 'Queixa-crime por calúnia', 'basico'],
        ['Queixa-Crime por Ameaça', 'queixa-crime-ameaca-basico', 'Queixa-crime por ameaça', 'basico'],
        ['Queixa-Crime por Perturbação', 'queixa-crime-perturbacao-basico', 'Queixa-crime por perturbação', 'basico'],
        ['Queixa-Crime por Desacato', 'queixa-crime-desacato-basico', 'Queixa-crime por desacato', 'basico'],
        ['Habeas Corpus Preventivo', 'habeas-corpus-preventivo-basico', 'Habeas corpus preventivo', 'basico'],
        ['Pedido de Revogação de Prisão', 'pedido-revogacao-prisao-basico', 'Pedido de revogação de prisão', 'basico'],
        ['Memorial de Defesa', 'memorial-defesa-basico', 'Memorial de defesa', 'basico'],
        ['Queixa-Crime por Estelionato', 'queixa-crime-estelionato-basico', 'Queixa-crime por estelionato', 'basico'],
        ['Queixa-Crime por Violência Doméstica', 'queixa-crime-violencia-domestica-basico', 'Queixa-crime por violência doméstica', 'basico'],
        ['Pedido de Progressão de Regime', 'pedido-progressao-regime-basico', 'Pedido de progressão de regime', 'basico'],
        ['Pedido de Remição de Pena', 'pedido-remicao-pena-basico', 'Pedido de remição de pena', 'basico'],
        ['Pedido de Pena Alternativa', 'pedido-pena-alternativa-basico', 'Pedido de pena alternativa', 'basico'],
        ['Queixa-Crime por Vias de Fato', 'queixa-crime-vias-fato-basico', 'Queixa-crime por vias de fato', 'basico'],
        ['Pedido de Livramento Condicional', 'pedido-livramento-condicional-basico', 'Pedido de livramento condicional', 'basico'],
        ['Queixa-Crime por Lesão Corporal', 'queixa-crime-lesao-corporal-basico', 'Queixa-crime por lesão corporal', 'basico'],
        ['Pedido de Relaxamento de Prisão', 'pedido-relaxamento-prisao-basico', 'Pedido de relaxamento de prisão', 'basico'],
        ['Defesa Preliminar Criminal', 'defesa-preliminar-criminal', 'Defesa preliminar criminal', 'basico'],
        ['Queixa-Crime por Tráfico de Drogas', 'queixa-crime-trafico-drogas-basico', 'Queixa-crime por tráfico de drogas', 'basico'],
        ['Pedido de Suspensão Condicional', 'pedido-suspensao-condicional-basico', 'Pedido de suspensão condicional', 'basico'],
        ['Recurso Criminal Simplificado', 'recurso-criminal-simplificado', 'Recurso criminal simplificado', 'basico'],
        ['Queixa-Crime por Roubo', 'queixa-crime-roubo', 'Queixa-crime por roubo', 'basico'],
        ['Queixa-Crime por Furto', 'queixa-crime-furto', 'Queixa-crime por furto', 'basico'],
        ['Pedido de Revisão Criminal', 'pedido-revisao-criminal-basico', 'Pedido de revisão criminal', 'basico'],
        ['Apelação Criminal Simples', 'apelacao-criminal-simples', 'Apelação criminal simples', 'basico'],
        ['Queixa-Crime por Homicídio', 'queixa-crime-homicidio', 'Queixa-crime por homicídio', 'basico'],
    ],
    3 => [ // TRABALHISTA - tem 6, faltam 24
        ['Certidão de Tempo de Serviço', 'certidao-tempo-servico-v2', 'Certidão de tempo de serviço', 'basico'],
        ['Pedido de 13º Salário', 'pedido-decimo-terceiro-v2', 'Pedido de 13º salário', 'basico'],
        ['Pedido de Antecipação de Férias', 'pedido-antecipacao-ferias-v2', 'Pedido de antecipação de férias', 'basico'],
        ['Pedido de Aviso Prévio', 'pedido-aviso-previo-v2', 'Pedido de aviso prévio', 'basico'],
        ['Pedido de Licença Maternidade', 'pedido-licenca-maternidade-v3', 'Pedido de licença maternidade', 'basico'],
        ['Pedido de Seguro Desemprego', 'pedido-seguro-desemprego-v3', 'Pedido de seguro desemprego', 'basico'],
        ['Carta de Demissão', 'carta-demissao-basico', 'Carta de demissão', 'basico'],
        ['Termo de Rescisão Contrato', 'termo-rescisao-contrato-basico', 'Termo de rescisão de contrato', 'basico'],
        ['Pedido de Férias Proporcionais', 'pedido-ferias-proporcionais', 'Pedido de férias proporcionais', 'basico'],
        ['Pedido de 13º Proporcional', 'pedido-decimo-terceiro-proporcional', 'Pedido de 13º salário proporcional', 'basico'],
        ['Reclamação Trabalhista Básica', 'reclamacao-trabalhista-basica', 'Reclamação trabalhista básica', 'basico'],
        ['Pedido de FGTS', 'pedido-fgts-basico', 'Pedido de FGTS', 'basico'],
        ['Pedido de Saldo de Salário', 'pedido-saldo-salario', 'Pedido de saldo de salário', 'basico'],
        ['Pedido de Multa do FGTS', 'pedido-multa-fgts', 'Pedido de multa do FGTS', 'basico'],
        ['Carta de Advertência', 'carta-advertencia', 'Carta de advertência', 'basico'],
        ['Pedido de Rescisão Indireta', 'pedido-rescisao-indireta-basico', 'Pedido de rescisão indireta', 'basico'],
        ['Termo de Acordo Trabalhista', 'termo-acordo-trabalhista', 'Termo de acordo trabalhista', 'basico'],
        ['Pedido de Aposentadoria', 'pedido-aposentadoria-trabalhista', 'Pedido de aposentadoria', 'basico'],
        ['Certidão de Trabalho', 'certidao-trabalho', 'Certidão de trabalho', 'basico'],
        ['Pedido de Auxílio Doença', 'pedido-auxilio-doenca', 'Pedido de auxílio doença', 'basico'],
        ['Pedido de Auxílio Acidente', 'pedido-auxilio-acidente', 'Pedido de auxílio acidente', 'basico'],
        ['Termo de Acordo Coletivo', 'termo-acordo-coletivo', 'Termo de acordo coletivo', 'basico'],
        ['Pedido de Admissão', 'pedido-admissao', 'Pedido de admissão', 'basico'],
        ['Certidão de CTPS', 'certidao-ctps', 'Certidão de CTPS', 'basico'],
    ],
    4 => [ // TRIBUTÁRIO - tem 2, faltam 28
        ['Pedido de Parcelamento', 'pedido-parcelamento-v2', 'Pedido de parcelamento', 'basico'],
        ['Pedido de Certidão Negativa', 'pedido-certidao-negativa-v2', 'Pedido de certidão negativa', 'basico'],
        ['Recurso Administrativo Fiscal', 'recurso-administrativo-fiscal-basico', 'Recurso administrativo fiscal', 'basico'],
        ['Pedido de Restituição Tributária', 'pedido-restituicao-tributaria-basico', 'Pedido de restituição tributária', 'basico'],
        ['Pedido de Compensação Tributária', 'pedido-compensacao-tributaria-basico', 'Pedido de compensação tributária', 'basico'],
        ['Recurso contra Auto de Infração', 'recurso-auto-infracao-basico', 'Recurso contra auto de infração', 'basico'],
        ['Pedido de Isenção Tributária', 'pedido-isencao-tributaria-basico', 'Pedido de isenção tributária', 'basico'],
        ['Pedido de Anistia Fiscal', 'pedido-anistia-fiscal-basico', 'Pedido de anistia fiscal', 'basico'],
        ['Recurso contra Multa Fiscal', 'recurso-contra-multa-fiscal-basico', 'Recurso contra multa fiscal', 'basico'],
        ['Pedido de Revisão de Lançamento', 'pedido-revisao-lancamento-basico', 'Pedido de revisão de lançamento', 'basico'],
        ['Recurso Administrativo ICMS', 'recurso-administrativo-icms-basico', 'Recurso administrativo ICMS', 'basico'],
        ['Recurso Administrativo IPI', 'recurso-administrativo-ipi-basico', 'Recurso administrativo IPI', 'basico'],
        ['Pedido de Certidão de Regularidade Fiscal', 'pedido-certidao-regularidade-fiscal', 'Pedido de certidão de regularidade fiscal', 'basico'],
        ['Pedido de Desconto Tributário', 'pedido-desconto-tributario-basico', 'Pedido de desconto tributário', 'basico'],
        ['Recurso contra Intimação', 'recurso-contra-intimacao-basico', 'Recurso contra intimação', 'basico'],
        ['Pedido de Compensação de Créditos', 'pedido-compensacao-creditos-basico', 'Pedido de compensação de créditos', 'basico'],
        ['Recurso contra Notificação de Débito', 'recurso-notificacao-debito-basico', 'Recurso contra notificação de débito', 'basico'],
        ['Pedido de Consulta Fiscal', 'pedido-consulta-fiscal', 'Pedido de consulta fiscal', 'basico'],
        ['Recurso Administrativo ISS', 'recurso-administrativo-iss', 'Recurso administrativo ISS', 'basico'],
        ['Pedido de Certidão de Dívida Ativa', 'pedido-certidao-divida-ativa', 'Pedido de certidão de dívida ativa', 'basico'],
        ['Recurso contra Lançamento', 'recurso-contra-lancamento-basico', 'Recurso contra lançamento', 'basico'],
        ['Pedido de Parcelamento Especial', 'pedido-parcelamento-especial', 'Pedido de parcelamento especial', 'basico'],
        ['Recurso Administrativo IRPF', 'recurso-administrativo-irpf', 'Recurso administrativo IRPF', 'basico'],
        ['Pedido de Certidão Negativa de Débitos', 'pedido-certidao-negativa-debitos', 'Pedido de certidão negativa de débitos', 'basico'],
        ['Recurso Administrativo IRPJ', 'recurso-administrativo-irpj', 'Recurso administrativo IRPJ', 'basico'],
        ['Pedido de Isenção de IPTU', 'pedido-isencao-iptu', 'Pedido de isenção de IPTU', 'basico'],
        ['Recurso Administrativo IPVA', 'recurso-administrativo-ipva', 'Recurso administrativo IPVA', 'basico'],
        ['Pedido de Certidão Positiva', 'pedido-certidao-positiva-tributaria', 'Pedido de certidão positiva tributária', 'basico'],
    ],
    5 => [ // ADMINISTRATIVO - tem 8, faltam 22
        ['Pedido de Certidão Administrativa', 'pedido-certidao-administrativa-v2', 'Pedido de certidão administrativa', 'basico'],
        ['Pedido de Licença Ambiental', 'pedido-licenca-ambiental-v2', 'Pedido de licença ambiental', 'basico'],
        ['Pedido de Registro Comercial', 'pedido-registro-comercial-v2', 'Pedido de registro comercial', 'basico'],
        ['Pedido de Certidão de Regularidade', 'pedido-certidao-regularidade-v2', 'Pedido de certidão de regularidade', 'basico'],
        ['Pedido de Habilitação Licitatória', 'pedido-habilitacao-licitatoria-v2', 'Pedido de habilitação licitatória', 'basico'],
        ['Pedido de Alvará de Funcionamento', 'pedido-alvara-funcionamento-v2', 'Pedido de alvará de funcionamento', 'basico'],
        ['Pedido de Aposentadoria', 'pedido-aposentadoria-v2', 'Pedido de aposentadoria', 'basico'],
        ['Recurso Administrativo Prefeitura', 'recurso-administrativo-prefeitura-basico', 'Recurso administrativo prefeitura', 'basico'],
        ['Pedido de Certidão Positiva', 'pedido-certidao-positiva-v2', 'Pedido de certidão positiva', 'basico'],
        ['Recurso Administrativo Estadual', 'recurso-administrativo-estadual-basico', 'Recurso administrativo estadual', 'basico'],
        ['Recurso Administrativo Federal', 'recurso-administrativo-federal-basico', 'Recurso administrativo federal', 'basico'],
        ['Pedido de Revisão de Alvará', 'pedido-revisao-alvara-basico', 'Pedido de revisão de alvará', 'basico'],
        ['Pedido de Licença Sanitária', 'pedido-licenca-sanitaria', 'Pedido de licença sanitária', 'basico'],
        ['Pedido de Alvará de Construção', 'pedido-alvara-construcao', 'Pedido de alvará de construção', 'basico'],
        ['Recurso contra Multa Administrativa', 'recurso-contra-multa-administrativa-basico', 'Recurso contra multa administrativa', 'basico'],
        ['Pedido de Certidão de Inscrição Municipal', 'pedido-certidao-inscricao-municipal', 'Pedido de certidão de inscrição municipal', 'basico'],
        ['Recurso contra Concurso Público', 'recurso-contra-concurso-publico-basico', 'Recurso contra concurso público', 'basico'],
        ['Pedido de Aprovação de Projeto', 'pedido-aprovacao-projeto', 'Pedido de aprovação de projeto', 'basico'],
        ['Recurso Administrativo Licitação', 'recurso-administrativo-licitacao-basico', 'Recurso administrativo licitação', 'basico'],
        ['Pedido de Certidão de Regularidade Ambiental', 'pedido-certidao-regularidade-ambiental', 'Pedido de certidão de regularidade ambiental', 'basico'],
        ['Recurso contra Decisão Administrativa', 'recurso-decisao-administrativa-basico', 'Recurso contra decisão administrativa', 'basico'],
        ['Pedido de Autorização de Funcionamento', 'pedido-autorizacao-funcionamento', 'Pedido de autorização de funcionamento', 'basico'],
    ],
    6 => [ // FAMÍLIA - tem 18, faltam 12
        ['Pedido de Guarda de Filhos', 'pedido-guarda-filhos-v2', 'Pedido de guarda de filhos', 'basico'],
        ['Ação de Alimentos', 'acao-alimentos-v2', 'Ação de alimentos', 'basico'],
        ['Pedido de Regulamentação de Visitas', 'pedido-regulamentacao-visitas-v2', 'Pedido de regulamentação de visitas', 'basico'],
        ['Ação de Investigação de Paternidade', 'acao-investigacao-paternidade-v2', 'Ação de investigação de paternidade', 'basico'],
        ['Pedido de Pensão Alimentícia Revisional', 'pedido-pensao-revisional-v2', 'Pedido de pensão alimentícia revisional', 'basico'],
        ['Ação de Desquite', 'acao-desquite-v2', 'Ação de desquite', 'basico'],
        ['Petição de Separação Judicial', 'peticao-separacao-judicial-v2', 'Petição de separação judicial', 'basico'],
        ['Ação de Exoneração de Alimentos', 'acao-exoneracao-alimentos-v2', 'Ação de exoneração de alimentos', 'basico'],
        ['Petição de Regime de Bens', 'peticao-regime-bens-v2', 'Petição de regime de bens', 'basico'],
        ['Ação de Alimentos Provisórios', 'acao-alimentos-provisorios-v2', 'Ação de alimentos provisórios', 'basico'],
        ['Pedido de Emancipação', 'pedido-emancipacao-v2', 'Pedido de emancipação', 'basico'],
        ['Ação de Separação de Corpos', 'acao-separacao-corpos-v2', 'Ação de separação de corpos', 'basico'],
    ]
];

// Funções auxiliares
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

// Importar modelos
try {
    $db->beginTransaction();
    
    $sucessos = 0;
    $erros = 0;
    
    foreach ($modelosBasicosFaltantes as $categoriaId => $modelos) {
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
                    'basico',
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
    echo "\nVerificando modelos BÁSICOS por categoria...\n\n";
    
    // Verificar novamente
    $stmt = $db->query("
        SELECT 
            cd.nome as categoria_nome,
            COUNT(md.id) as total_basicos
        FROM categorias_documentos cd
        LEFT JOIN modelos_documentos md ON cd.id = md.categoria_id AND md.ativo = 1 AND md.tipo_plano_necessario = 'basico'
        WHERE cd.ativo = 1
        GROUP BY cd.id, cd.nome
        ORDER BY cd.ordem
    ");
    
    $resultados = $stmt->fetchAll();
    
    echo "Categoria | Modelos BÁSICOS | Status\n";
    echo "----------|-----------------|--------\n";
    
    foreach ($resultados as $row) {
        $total = (int)$row['total_basicos'];
        $status = $total >= 30 ? '✅ OK' : '⚠️ Faltam ' . (30 - $total);
        printf("%-9s | %15d | %s\n", $row['categoria_nome'], $total, $status);
    }
    
} catch (Exception $e) {
    $db->rollBack();
    echo "ERRO GERAL: " . $e->getMessage() . "\n";
}

?>

