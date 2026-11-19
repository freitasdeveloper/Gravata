<?php
/**
 * Biblioteca de Literatura Jurídica
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$auth = new Auth();

// Verificar se está logado
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$usuario = $auth->getUsuario();

// Função para gerar link direto do livro
function gerarLinkLivro($titulo, $autor, $fonte, $link = null) {
    // Se já tem link direto, usar ele
    if (!empty($link) && $link !== 'https://www.dominiopublico.gov.br') {
        return $link;
    }
    
    // Fontes oficiais
    if ($fonte === 'STJ') {
        return 'https://www.stj.jus.br';
    } elseif ($fonte === 'Planalto') {
        // Link já vem direto do array - usar o link fornecido
        return $link ?? 'https://www.planalto.gov.br/ccivil_03/';
    } elseif ($fonte === 'Senado') {
        return 'https://www.senado.leg.br';
    } elseif ($fonte === 'Archive.org') {
        return $link ?? 'https://archive.org';
    } elseif ($fonte === 'Google Books') {
        return $link ?? 'https://books.google.com';
    }
    
    // Fallback: se não tem link, tentar buscar no Archive.org
    $tituloEncoded = urlencode($titulo);
    $autorEncoded = urlencode($autor);
    return "https://archive.org/search.php?query=" . urlencode($titulo . ' ' . $autor);
}

// Livros jurídicos disponíveis (Códigos e Legislações Oficiais - Links para Portal Planalto)
$livros = [
    // Códigos Fundamentais (Portal Planalto - Links Diretos Funcionais)
    ['titulo' => 'Constituição Federal de 1988', 'autor' => 'Brasil', 'categoria' => 'Direito Constitucional', 'descricao' => 'Constituição Federal completa e atualizada', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/constituicao/constituicao.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código Civil - Lei 10.406/2002', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Código Civil Brasileiro completo e atualizado', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2002/l10406.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código de Processo Civil - Lei 13.105/2015', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Código de Processo Civil atualizado', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2015/lei/l13105.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código Penal - Decreto-Lei 2.848/1940', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Código Penal Brasileiro completo', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/decreto-lei/del2848compilado.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código de Processo Penal - Decreto-Lei 3.689/1941', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Código de Processo Penal completo', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/decreto-lei/del3689.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Consolidação das Leis do Trabalho - CLT', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'CLT completa e atualizada', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/decreto-lei/del5452.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código de Defesa do Consumidor - Lei 8.078/1990', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Código de Defesa do Consumidor completo', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8078.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Estatuto da Criança e do Adolescente - Lei 8.069/1990', 'autor' => 'Brasil', 'categoria' => 'Direito da Criança e Adolescente', 'descricao' => 'ECA completo e atualizado', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8069.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Execução Penal - Lei 7.210/1984', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei de Execução Penal completa', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7210.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Falências - Lei 11.101/2005', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei de Recuperação de Empresas e Falências', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2005/lei/l11101.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei Maria da Penha - Lei 11.340/2006', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei de combate à violência doméstica', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2006/lei/l11340.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei Geral de Proteção de Dados - Lei 13.709/2018', 'autor' => 'Brasil', 'categoria' => 'Direito Digital', 'descricao' => 'LGPD completa e atualizada', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código Florestal - Lei 12.651/2012', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Código Florestal Brasileiro', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2012/lei/l12651.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Licitações - Lei 14.133/2021', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Nova Lei de Licitações e Contratos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2021/lei/l14133.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Improbidade Administrativa - Lei 8.429/1992', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei de Improbidade Administrativa', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8429.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Estatuto do Idoso - Lei 10.741/2003', 'autor' => 'Brasil', 'categoria' => 'Direito da Pessoa Idosa', 'descricao' => 'Estatuto do Idoso completo', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2003/l10.741.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes Hediondos - Lei 8.072/1990', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei dos Crimes Hediondos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8072.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Estatuto da OAB - Lei 8.906/1994', 'autor' => 'Brasil', 'categoria' => 'Direito Profissional', 'descricao' => 'Estatuto da Advocacia e da OAB', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8906.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Diretrizes e Bases da Educação - LDB', 'autor' => 'Brasil', 'categoria' => 'Direito Educacional', 'descricao' => 'Lei 9.394/1996 - LDB completa', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9394.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Estatuto da Cidade - Lei 10.257/2001', 'autor' => 'Brasil', 'categoria' => 'Direito Urbanístico', 'descricao' => 'Estatuto da Cidade - Política Urbana', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/leis_2001/l10257.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei Anticorrupção - Lei 12.846/2013', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei Anticorrupção - Responsabilização de Pessoas Jurídicas', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2013/lei/l12846.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Tortura - Lei 9.455/1997', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei que define crimes de tortura', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9455.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Organização e Divisão Judiciária - LOJ', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei Complementar 35/1979', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp35.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Segurança Nacional - Lei 7.170/1983', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei de Segurança Nacional', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7170.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Acesso à Informação - Lei 12.527/2011', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei de Acesso à Informação Pública', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2011/lei/l12527.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código Eleitoral - Lei 4.737/1965', 'autor' => 'Brasil', 'categoria' => 'Direito Eleitoral', 'descricao' => 'Código Eleitoral Brasileiro', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l4737.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei da Anistia - Lei 6.683/1979', 'autor' => 'Brasil', 'categoria' => 'Direito Constitucional', 'descricao' => 'Lei da Anistia', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6683.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Registros Públicos - Lei 6.015/1973', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei dos Registros Públicos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6015.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Locações - Lei 8.245/1991', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei de Locações Urbanas', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8245.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei do Inquilinato - Lei 7.783/1989', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei do Inquilinato', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7783.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Propriedade Industrial - Lei 9.279/1996', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei da Propriedade Industrial', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9279.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Marcas - Lei 9.279/1996', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei de Propriedade Industrial - Marcas e Patentes', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9279.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Falências e Recuperação - Lei 11.101/2005', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei de Recuperação de Empresas e Falências', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2005/lei/l11101.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Previdência Social - Lei 8.213/1991', 'autor' => 'Brasil', 'categoria' => 'Direito Previdenciário', 'descricao' => 'Lei de Previdência Social', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8213cons.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Benefícios Previdenciários - Lei 8.212/1991', 'autor' => 'Brasil', 'categoria' => 'Direito Previdenciário', 'descricao' => 'Lei de Organização da Seguridade Social', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8212cons.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Assistência Social - LOAS', 'autor' => 'Brasil', 'categoria' => 'Direito Previdenciário', 'descricao' => 'Lei Orgânica da Assistência Social', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8742.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes Ambientais - Lei 9.605/1998', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei de Crimes Ambientais', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9605.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei da Política Nacional do Meio Ambiente', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 6.938/1981 - Política Nacional do Meio Ambiente', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6938.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Parcelamento do Solo - Lei 6.766/1979', 'autor' => 'Brasil', 'categoria' => 'Direito Urbanístico', 'descricao' => 'Lei de Parcelamento do Solo Urbano', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6766.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Desapropriação - Decreto-Lei 3.365/1941', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei de Desapropriação por Utilidade Pública', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/decreto-lei/del3365.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Concessões - Lei 8.987/1995', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei de Concessões de Serviços Públicos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8987cons.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Parcerias Público-Privadas - Lei 11.079/2004', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei de Parcerias Público-Privadas', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2004/lei/l11079.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Responsabilidade Fiscal - LRF', 'autor' => 'Brasil', 'categoria' => 'Direito Financeiro', 'descricao' => 'Lei Complementar 101/2000 - LRF', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp101.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Direitos Autorais - Lei 9.610/1998', 'autor' => 'Brasil', 'categoria' => 'Direito Autoral', 'descricao' => 'Lei de Direitos Autorais', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9610.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Software - Lei 9.609/1998', 'autor' => 'Brasil', 'categoria' => 'Direito Digital', 'descricao' => 'Lei de Proteção à Propriedade Intelectual de Software', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9609.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Telecomunicações - Lei 9.472/1997', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei Geral de Telecomunicações', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9472.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes contra Ordem Tributária', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei 8.137/1990 - Crimes contra a Ordem Tributária', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8137.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código Tributário Nacional - CTN', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei 5.172/1966 - Código Tributário Nacional', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l5172.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Organização Judiciária do Estado', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei Complementar 35/1979 - Organização Judiciária', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp35.htm', 'tipo' => 'PDF'],
    
    // Leis Especiais Adicionais
    ['titulo' => 'Estatuto do Desarmamento - Lei 10.826/2003', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Estatuto do Desarmamento - Controle de Armas', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2003/l10.826.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Lavagem de Dinheiro - Lei 9.613/1998', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei de Lavagem ou Ocultação de Bens', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9613.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes contra o Sistema Financeiro', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 7.492/1986 - Crimes contra o Sistema Financeiro Nacional', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7492.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes Eleitorais - Lei 9.504/1997', 'autor' => 'Brasil', 'categoria' => 'Direito Eleitoral', 'descricao' => 'Lei das Eleições e Crimes Eleitorais', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9504.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Inclusão da Pessoa com Deficiência - Lei 13.146/2015', 'autor' => 'Brasil', 'categoria' => 'Direito da Pessoa com Deficiência', 'descricao' => 'Estatuto da Pessoa com Deficiência', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2015/lei/l13146.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei da Primeira Infância - Lei 13.257/2016', 'autor' => 'Brasil', 'categoria' => 'Direito da Criança e Adolescente', 'descricao' => 'Marco Legal da Primeira Infância', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2016/lei/l13257.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Mobilidade Urbana - Lei 12.587/2012', 'autor' => 'Brasil', 'categoria' => 'Direito Urbanístico', 'descricao' => 'Política Nacional de Mobilidade Urbana', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2012/lei/l12587.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Migração - Lei 13.445/2017', 'autor' => 'Brasil', 'categoria' => 'Direito Internacional', 'descricao' => 'Lei de Migração - Estatuto do Estrangeiro', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2017/lei/l13445.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei Geral do Cooperativismo - Lei 5.764/1971', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei Geral do Cooperativismo', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l5764.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei do Estágio - Lei 11.788/2008', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei do Estágio de Estudantes', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2008/lei/l11788.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Servidores Públicos - Lei 8.112/1990', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Regime Jurídico dos Servidores Públicos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8112cons.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Responsabilização Terceirizada - Lei 13.429/2017', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Terceirização e Responsabilidade Subsidiária', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2017/lei/l13429.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Recuperação Fiscal - REFIS', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Programa de Recuperação Fiscal', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2013/lei/l12865.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços Públicos', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 9.507/1997 - Consumidor de Serviços Públicos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9507.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção de Dados Pessoais', 'autor' => 'Brasil', 'categoria' => 'Direito Digital', 'descricao' => 'Lei 14.010/2020 - Proteção de Dados em Emergência', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2020/lei/l14010.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Trânsito - CTB - Lei 9.503/1997', 'autor' => 'Brasil', 'categoria' => 'Direito de Trânsito', 'descricao' => 'Código de Trânsito Brasileiro', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9503.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Controle de Substâncias Entorpecentes', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 11.343/2006 - Drogas e Entorpecentes', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2006/lei/l11343.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Organização e Fiscalização do Mercado de Valores', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 6.385/1976 - CVM e Mercado de Valores', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6385.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Falência de Instituições Financeiras', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 6.024/1974 - Intervenção e Liquidação', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6024.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor Bancário', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 10.820/2003 - Proteção ao Consumidor Bancário', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2003/l10.820.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Seguro Privado', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei Complementar 126/2007 - SUSEP', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp126.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Trabalho em Domicílio', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 12.551/2011 - Trabalho Remoto', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2011/lei/l12551.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Emprego Doméstico', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei Complementar 150/2015 - Trabalho Doméstico', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp150.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Endividado', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 14.181/2021 - Superendividamento', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2021/lei/l14181.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Crédito', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 10.820/2003 - Crédito ao Consumidor', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2003/l10.820.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços de Saúde', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 9.656/1998 - Planos de Saúde', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9656.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Energia Elétrica', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 10.438/2002 - Consumidor de Energia', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2002/l10438.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Telecomunicações', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 9.472/1997 - Consumidor de Telecomunicações', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9472.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços Postais', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 6.538/1978 - Serviços Postais', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6538.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços de Transporte', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 7.565/1986 - Transporte Aéreo', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7565.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços de Educação', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 9.870/1999 - Mensalidades Escolares', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9870.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços de Turismo', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 11.771/2008 - Turismo', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2008/lei/l11771.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços de Lazer', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 8.078/1990 - Consumidor de Lazer', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8078.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços Financeiros', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 10.820/2003 - Serviços Financeiros', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2003/l10.820.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços de Segurança', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 7.102/1983 - Segurança Privada', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7102.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços de Limpeza', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 8.078/1990 - Serviços de Limpeza', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8078.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços de Alimentação', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 8.078/1990 - Serviços de Alimentação', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8078.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços de Hotelaria', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 8.078/1990 - Serviços de Hotelaria', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8078.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Consumidor de Serviços de Entretenimento', 'autor' => 'Brasil', 'categoria' => 'Direito do Consumidor', 'descricao' => 'Lei 8.078/1990 - Serviços de Entretenimento', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8078.htm', 'tipo' => 'PDF'],
    
    // Códigos Processuais e Especializados
    ['titulo' => 'Código de Defesa do Consumidor - Processo Coletivo', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 7.347/1985 - Ação Civil Pública', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7347orig.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código de Defesa do Consumidor - Mandado de Segurança Coletivo', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 12.016/2009 - Mandado de Segurança', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2009/lei/l12016.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código de Defesa do Consumidor - Ação Popular', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 4.717/1965 - Ação Popular', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l4717.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Código de Defesa do Consumidor - Ação de Improbidade', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 8.429/1992 - Ação de Improbidade', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8429.htm', 'tipo' => 'PDF'],
    
    // Leis Trabalhistas Especiais
    ['titulo' => 'Lei do Trabalho Doméstico - PEC das Domésticas', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei Complementar 150/2015 - Direitos Trabalhistas', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp150.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei do Trabalho Portuário', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 12.815/2013 - Trabalho Portuário', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2013/lei/l12815.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei do Trabalho Rural', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 5.889/1973 - Trabalho Rural', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l5889.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei do Trabalho Temporário', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 6.019/1974 - Trabalho Temporário', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6019.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei do Trabalho em Altura', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'NR 35 - Trabalho em Altura', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2012/decreto/d7735.htm', 'tipo' => 'PDF'],
    
    // Leis Ambientais Especiais
    ['titulo' => 'Lei de Recursos Hídricos', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 9.433/1997 - Política Nacional de Recursos Hídricos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9433.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Saneamento Básico', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 11.445/2007 - Saneamento Básico', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2007/lei/l11445.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Resíduos Sólidos', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 12.305/2010 - Política Nacional de Resíduos Sólidos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2010/lei/l12305.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção da Vegetação Nativa', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 12.651/2012 - Código Florestal', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2012/lei/l12651.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Áreas Protegidas', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 9.985/2000 - Sistema Nacional de Unidades de Conservação', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9985.htm', 'tipo' => 'PDF'],
    
    // Leis Tributárias Especiais
    ['titulo' => 'Lei de Imposto de Renda', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei 8.981/1995 - Imposto de Renda', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8981.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de ICMS', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei Complementar 87/1996 - ICMS', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp87.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de ISS', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei Complementar 116/2003 - ISS', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp116.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de IPI', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Decreto-Lei 1.455/1976 - IPI', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/decreto-lei/del1455.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de IPTU', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei Complementar 63/1990 - IPTU', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp63.htm', 'tipo' => 'PDF'],
    
    // Leis Administrativas Especiais
    ['titulo' => 'Lei de Regime Diferenciado de Contratações - RDC', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 12.462/2011 - RDC', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2011/lei/l12462.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Pregão', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 10.520/2002 - Pregão', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2002/l10520.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Consórcios Públicos', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 11.107/2005 - Consórcios Públicos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2005/lei/l11107.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Agentes Públicos', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 8.112/1990 - Agentes Públicos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8112cons.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Processo Administrativo', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 9.784/1999 - Processo Administrativo', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9784.htm', 'tipo' => 'PDF'],
    
    // Leis Penais Especiais
    ['titulo' => 'Lei de Abuso de Autoridade', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 13.869/2019 - Abuso de Autoridade', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2019/lei/l13869.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes de Responsabilidade', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 1.079/1950 - Crimes de Responsabilidade', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/1950-1969/l1079.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes de Trânsito', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 9.503/1997 - Crimes de Trânsito', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9503.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes Ambientais - Penal', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 9.605/1998 - Crimes Ambientais', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9605.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes contra a Ordem Tributária - Penal', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 8.137/1990 - Crimes Tributários', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8137.htm', 'tipo' => 'PDF'],
    
    // Leis Processuais Especiais
    ['titulo' => 'Lei de Execução de Títulos Extrajudiciais', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 11.382/2006 - Execução de Títulos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2006/lei/l11382.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Juizados Especiais', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 9.099/1995 - Juizados Especiais', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9099.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Juizados Especiais Federais', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 10.259/2001 - JEF', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/leis_2001/l10259.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Arbitragem', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 9.307/1996 - Arbitragem', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9307.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Mediação', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 13.140/2015 - Mediação', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2015/lei/l13140.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Conciliação', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 11.441/2007 - Conciliação Extrajudicial', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2007/lei/l11441.htm', 'tipo' => 'PDF'],
    
    // Leis Civis Especiais
    ['titulo' => 'Lei de União Estável', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei 9.278/1996 - União Estável', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9278.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Divórcio', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei 6.515/1977 - Divórcio', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6515.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Adoção', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei 12.010/2009 - Adoção', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2009/lei/l12010.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Alimentos', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei 5.478/1968 - Alimentos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l5478.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Usucapião', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei 13.465/2017 - Usucapião', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2017/lei/l13465.htm', 'tipo' => 'PDF'],
    
    // Leis Empresariais Especiais
    ['titulo' => 'Lei de Sociedades Anônimas', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 6.404/1976 - Lei das S.A.', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6404consol.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Sociedades Limitadas', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 10.406/2002 - Sociedades Limitadas', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2002/l10406.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Microempresa e EPP', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei Complementar 123/2006 - Simples Nacional', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp123.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Franquias', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 8.955/1994 - Franquias', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8955.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Consórcios', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 11.795/2008 - Consórcios', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2008/lei/l11795.htm', 'tipo' => 'PDF'],
    
    // Leis Previdenciárias Especiais
    ['titulo' => 'Lei de Benefício de Prestação Continuada', 'autor' => 'Brasil', 'categoria' => 'Direito Previdenciário', 'descricao' => 'Lei 8.742/1993 - BPC/LOAS', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8742.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Aposentadoria Especial', 'autor' => 'Brasil', 'categoria' => 'Direito Previdenciário', 'descricao' => 'Lei 8.213/1991 - Aposentadoria', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8213cons.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Seguro Desemprego', 'autor' => 'Brasil', 'categoria' => 'Direito Previdenciário', 'descricao' => 'Lei 7.998/1990 - Seguro Desemprego', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7998.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de FGTS', 'autor' => 'Brasil', 'categoria' => 'Direito Previdenciário', 'descricao' => 'Lei 8.036/1990 - FGTS', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8036cons.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de PIS/PASEP', 'autor' => 'Brasil', 'categoria' => 'Direito Previdenciário', 'descricao' => 'Lei Complementar 7/1970 - PIS/PASEP', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp07.htm', 'tipo' => 'PDF'],
    
    // Leis Digitais e Tecnológicas
    ['titulo' => 'Lei de Comércio Eletrônico', 'autor' => 'Brasil', 'categoria' => 'Direito Digital', 'descricao' => 'Decreto 7.962/2013 - Comércio Eletrônico', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2013/decreto/d7962.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Marco Civil da Internet', 'autor' => 'Brasil', 'categoria' => 'Direito Digital', 'descricao' => 'Lei 12.965/2014 - Marco Civil da Internet', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2014/lei/l12965.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes Cibernéticos', 'autor' => 'Brasil', 'categoria' => 'Direito Digital', 'descricao' => 'Lei 12.737/2012 - Crimes Informáticos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2012/lei/l12737.htm', 'tipo' => 'PDF'],
    
    // Leis Urbanísticas Especiais
    ['titulo' => 'Lei de Regularização Fundiária', 'autor' => 'Brasil', 'categoria' => 'Direito Urbanístico', 'descricao' => 'Lei 13.465/2017 - Regularização Fundiária', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2017/lei/l13465.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Zona de Especial Interesse Social - ZEIS', 'autor' => 'Brasil', 'categoria' => 'Direito Urbanístico', 'descricao' => 'Lei 10.257/2001 - ZEIS', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/leis_2001/l10257.htm', 'tipo' => 'PDF'],
    
    // Leis Constitucionais Especiais
    ['titulo' => 'Lei Orgânica da Magistratura', 'autor' => 'Brasil', 'categoria' => 'Direito Constitucional', 'descricao' => 'Lei Complementar 35/1979 - LOMAN', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp35.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei Orgânica do Ministério Público', 'autor' => 'Brasil', 'categoria' => 'Direito Constitucional', 'descricao' => 'Lei Complementar 75/1993 - LOMPU', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp75.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei Orgânica da Defensoria Pública', 'autor' => 'Brasil', 'categoria' => 'Direito Constitucional', 'descricao' => 'Lei Complementar 80/1994 - LODP', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp80.htm', 'tipo' => 'PDF'],
    
    // Leis Eleitorais Especiais
    ['titulo' => 'Lei de Partidos Políticos', 'autor' => 'Brasil', 'categoria' => 'Direito Eleitoral', 'descricao' => 'Lei 9.096/1995 - Partidos Políticos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9096.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Financiamento de Campanhas', 'autor' => 'Brasil', 'categoria' => 'Direito Eleitoral', 'descricao' => 'Lei 9.504/1997 - Financiamento Eleitoral', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9504.htm', 'tipo' => 'PDF'],
    
    // Leis Penais Especiais Adicionais
    ['titulo' => 'Lei de Extorsão mediante Sequestro', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 9.503/1997 - Sequestro e Cárcere Privado', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9503.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes contra Vida Silvestre', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 9.605/1998 - Crimes contra Fauna', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9605.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes de Genocídio', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Decreto-Lei 2.848/1940 - Genocídio', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/decreto-lei/del2848compilado.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes contra Dignidade Sexual', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 12.015/2009 - Crimes Sexuais', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2009/lei/l12015.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Estatuto da Criança e do Adolescente - Crimes', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 8.069/1990 - Crimes contra Crianças', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8069.htm', 'tipo' => 'PDF'],
    
    // Leis Processuais Penais Especiais
    ['titulo' => 'Lei de Prisão Temporária', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 7.960/1989 - Prisão Temporária', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7960.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Prisão Preventiva', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Decreto-Lei 3.689/1941 - Prisão Preventiva', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/decreto-lei/del3689.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Habeas Corpus', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 13.979/2020 - Habeas Corpus', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2020/lei/l13979.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Mandado de Segurança', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 12.016/2009 - Mandado de Segurança', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2009/lei/l12016.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Habeas Data', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 9.507/1997 - Habeas Data', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9507.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Mandado de Injunção', 'autor' => 'Brasil', 'categoria' => 'Direito Processual', 'descricao' => 'Lei 13.300/2016 - Mandado de Injunção', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2016/lei/l13300.htm', 'tipo' => 'PDF'],
    
    // Leis Administrativas Adicionais
    ['titulo' => 'Lei de Intervenção Federal', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei Complementar 97/1999 - Intervenção Federal', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp97.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Controle Externo', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei Complementar 101/2000 - LRF', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp101.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Regime Jurídico Único', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 8.112/1990 - RJU', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8112cons.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Controle Interno', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei Complementar 131/2009 - Controle Interno', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp131.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Transparência Pública', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei Complementar 131/2009 - Transparência', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp131.htm', 'tipo' => 'PDF'],
    
    // Leis Trabalhistas Adicionais
    ['titulo' => 'Lei de Segurança e Medicina do Trabalho', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 6.514/1977 - Segurança do Trabalho', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6514.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Previdência Complementar', 'autor' => 'Brasil', 'categoria' => 'Direito Previdenciário', 'descricao' => 'Lei Complementar 109/2001 - Previdência Complementar', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp109.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Vale Transporte', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 7.418/1985 - Vale Transporte', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7418.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Vale Alimentação', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 8.284/1991 - Vale Alimentação', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8284.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Jornada de Trabalho', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Decreto-Lei 5.452/1943 - Jornada de Trabalho', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/decreto-lei/del5452.htm', 'tipo' => 'PDF'],
    
    // Leis Tributárias Adicionais
    ['titulo' => 'Lei de COFINS', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei 10.833/2003 - COFINS', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2003/l10.833.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de PIS', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei 10.833/2003 - PIS', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2003/l10.833.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Contribuição Social sobre Lucro', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei 7.689/1988 - CSLL', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7689.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Imposto sobre Importação', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei 4.502/1964 - II', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/1950-1969/l4502.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Imposto sobre Exportação', 'autor' => 'Brasil', 'categoria' => 'Direito Tributário', 'descricao' => 'Lei 4.502/1964 - IE', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/1950-1969/l4502.htm', 'tipo' => 'PDF'],
    
    // Leis Ambientais Adicionais
    ['titulo' => 'Lei de Recursos Hídricos - Outorga', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 9.433/1997 - Outorga de Águas', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9433.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Fauna Silvestre', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 5.197/1967 - Fauna Silvestre', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l5197.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Pesca', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 11.959/2009 - Pesca', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2009/lei/l11959.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Biossegurança', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 11.105/2005 - Biossegurança', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2005/lei/l11105.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Política Nacional de Resíduos Sólidos', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 12.305/2010 - Resíduos Sólidos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2010/lei/l12305.htm', 'tipo' => 'PDF'],
    
    // Leis Civis Adicionais
    ['titulo' => 'Lei de Registro de Imóveis', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei 6.015/1973 - Registro Imobiliário', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6015.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Condomínio', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei 4.591/1964 - Condomínio', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/1950-1969/l4591.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Incorporação Imobiliária', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei 4.591/1964 - Incorporação', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/1950-1969/l4591.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Doação', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei 10.406/2002 - Doação', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2002/l10406.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Testamento', 'autor' => 'Brasil', 'categoria' => 'Direito Civil', 'descricao' => 'Lei 10.406/2002 - Sucessão Testamentária', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2002/l10406.htm', 'tipo' => 'PDF'],
    
    // Leis Empresariais Adicionais
    ['titulo' => 'Lei de Sociedades por Cotas', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 10.406/2002 - Sociedades Limitadas', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2002/l10406.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Cooperativas', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 5.764/1971 - Cooperativas', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l5764.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Sociedades de Economia Mista', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 6.404/1976 - S.A. Públicas', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6404consol.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Títulos de Crédito', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei Uniforme - Títulos de Crédito', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/decreto-lei/del5957.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Duplicata', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 5.474/1968 - Duplicata', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l5474.htm', 'tipo' => 'PDF'],
    
    // Leis de Proteção Específica
    ['titulo' => 'Lei de Proteção ao Trabalhador Rural', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 5.889/1973 - Trabalhador Rural', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l5889.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Trabalhador Portuário', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 12.815/2013 - Trabalhador Portuário', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2013/lei/l12815.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Trabalhador Temporário', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 6.019/1974 - Trabalho Temporário', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l6019.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção ao Menor Aprendiz', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 10.097/2000 - Aprendizagem', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/leis_2001/l10097.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção à Maternidade', 'autor' => 'Brasil', 'categoria' => 'Direito do Trabalho', 'descricao' => 'Lei 11.770/2008 - Empresa Cidadã', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2008/lei/l11770.htm', 'tipo' => 'PDF'],
    
    // Leis de Direitos Humanos
    ['titulo' => 'Lei de Proteção à Pessoa com Deficiência', 'autor' => 'Brasil', 'categoria' => 'Direito da Pessoa com Deficiência', 'descricao' => 'Lei 13.146/2015 - Estatuto da Pessoa com Deficiência', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2015/lei/l13146.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Igualdade Racial', 'autor' => 'Brasil', 'categoria' => 'Direito Constitucional', 'descricao' => 'Lei 12.288/2010 - Estatuto da Igualdade Racial', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2010/lei/l12288.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Combate à Intolerância Religiosa', 'autor' => 'Brasil', 'categoria' => 'Direito Constitucional', 'descricao' => 'Lei 9.459/1997 - Intolerância Religiosa', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9459.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Combate ao Racismo', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 7.716/1989 - Crimes de Racismo', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7716.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Combate à Discriminação', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 7.716/1989 - Discriminação', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7716.htm', 'tipo' => 'PDF'],
    
    // Leis de Saúde Pública
    ['titulo' => 'Lei Orgânica da Saúde', 'autor' => 'Brasil', 'categoria' => 'Direito da Saúde', 'descricao' => 'Lei 8.080/1990 - SUS', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8080.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Participação Social na Saúde', 'autor' => 'Brasil', 'categoria' => 'Direito da Saúde', 'descricao' => 'Lei 8.142/1990 - Participação Social', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8142.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Agrotóxicos', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 7.802/1989 - Agrotóxicos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7802.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Biossegurança - Transgênicos', 'autor' => 'Brasil', 'categoria' => 'Direito Ambiental', 'descricao' => 'Lei 11.105/2005 - Transgênicos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2005/lei/l11105.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Vigilância Sanitária', 'autor' => 'Brasil', 'categoria' => 'Direito da Saúde', 'descricao' => 'Lei 9.782/1999 - ANVISA', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9782.htm', 'tipo' => 'PDF'],
    
    // Leis de Ciência e Tecnologia
    ['titulo' => 'Lei de Inovação Tecnológica', 'autor' => 'Brasil', 'categoria' => 'Direito Digital', 'descricao' => 'Lei 10.973/2004 - Inovação Tecnológica', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2004/lei/l10973.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Propriedade Industrial - Patentes', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 9.279/1996 - Patentes', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9279.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Proteção de Cultivares', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 9.456/1997 - Proteção de Cultivares', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9456.htm', 'tipo' => 'PDF'],
    
    // Leis de Educação
    ['titulo' => 'Lei de Diretrizes e Bases da Educação Nacional', 'autor' => 'Brasil', 'categoria' => 'Direito Educacional', 'descricao' => 'Lei 9.394/1996 - LDBEN', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9394.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Fundo de Manutenção da Educação Básica', 'autor' => 'Brasil', 'categoria' => 'Direito Educacional', 'descricao' => 'Lei Complementar 62/2009 - FUNDEB', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp62.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Sistema Nacional de Avaliação da Educação', 'autor' => 'Brasil', 'categoria' => 'Direito Educacional', 'descricao' => 'Lei 10.861/2004 - SINAES', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2004/lei/l10861.htm', 'tipo' => 'PDF'],
    
    // Leis de Segurança Pública
    ['titulo' => 'Lei de Segurança Pública', 'autor' => 'Brasil', 'categoria' => 'Direito Penal', 'descricao' => 'Lei 13.675/2018 - Sistema Único de Segurança Pública', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13675.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Segurança Privada', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 7.102/1983 - Segurança Privada', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7102.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Polícia Civil', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 12.830/2013 - Polícia Civil', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2013/lei/l12830.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Polícia Federal', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 10.446/2002 - Polícia Federal', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/2002/l10446.htm', 'tipo' => 'PDF'],
    
    // Leis de Desenvolvimento Econômico
    ['titulo' => 'Lei de Incentivo à Inovação', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 11.196/2005 - Lei do Bem', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2005/lei/l11196.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Micro e Pequena Empresa', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei Complementar 123/2006 - ME/EPP', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp123.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Financiamento à Exportação', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 8.578/1993 - Finamex', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l8578.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Comércio Exterior', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 9.019/1995 - Comércio Exterior', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9019.htm', 'tipo' => 'PDF'],
    
    // Leis de Infraestrutura
    ['titulo' => 'Lei de Energia Elétrica', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 9.074/1995 - Energia Elétrica', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9074.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Petróleo e Gás', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 9.478/1997 - Petróleo', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9478.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Mineração', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 14.128/2021 - Mineração', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2021/lei/l14128.htm', 'tipo' => 'PDF'],
    
    // Leis de Proteção de Dados e Privacidade
    ['titulo' => 'Lei de Proteção de Dados Pessoais - LGPD', 'autor' => 'Brasil', 'categoria' => 'Direito Digital', 'descricao' => 'Lei 13.709/2018 - LGPD', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Marco Civil da Internet', 'autor' => 'Brasil', 'categoria' => 'Direito Digital', 'descricao' => 'Lei 12.965/2014 - Neutralidade da Rede', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2014/lei/l12965.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Crimes Cibernéticos - Internet', 'autor' => 'Brasil', 'categoria' => 'Direito Digital', 'descricao' => 'Lei 12.737/2012 - Crimes Informáticos', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2012/lei/l12737.htm', 'tipo' => 'PDF'],
    
    // Leis de Regularização
    ['titulo' => 'Lei de Regularização de Terras', 'autor' => 'Brasil', 'categoria' => 'Direito Agrário', 'descricao' => 'Lei 11.952/2009 - Regularização Fundiária Rural', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2007-2010/2009/lei/l11952.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Reforma Agrária', 'autor' => 'Brasil', 'categoria' => 'Direito Agrário', 'descricao' => 'Lei 4.504/1964 - Estatuto da Terra', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/1950-1969/l4504.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Função Social da Propriedade', 'autor' => 'Brasil', 'categoria' => 'Direito Agrário', 'descricao' => 'Lei 4.504/1964 - Função Social', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/1950-1969/l4504.htm', 'tipo' => 'PDF'],
    
    // Leis de Transporte
    ['titulo' => 'Lei de Trânsito - Infrações', 'autor' => 'Brasil', 'categoria' => 'Direito de Trânsito', 'descricao' => 'Lei 9.503/1997 - Infrações de Trânsito', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l9503.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Transporte Rodoviário', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 10.233/2001 - ANTT', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/leis_2001/l10233.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Transporte Aquaviário', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 10.233/2001 - ANTAQ', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/leis_2001/l10233.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Transporte Aéreo', 'autor' => 'Brasil', 'categoria' => 'Direito Administrativo', 'descricao' => 'Lei 7.565/1986 - Código Brasileiro de Aeronáutica', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/l7565.htm', 'tipo' => 'PDF'],
    
    // Leis de Regulação Econômica
    ['titulo' => 'Lei de Defesa da Concorrência', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 12.529/2011 - CADE', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2011/lei/l12529.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Regulação de Mercados', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 10.233/2001 - Regulação', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/leis_2001/l10233.htm', 'tipo' => 'PDF'],
    ['titulo' => 'Lei de Sistema Financeiro Nacional', 'autor' => 'Brasil', 'categoria' => 'Direito Empresarial', 'descricao' => 'Lei 4.595/1964 - CMN', 'fonte' => 'Planalto', 'link' => 'https://www.planalto.gov.br/ccivil_03/leis/1950-1969/l4595.htm', 'tipo' => 'PDF'],
    
    // Tribunais
    ['titulo' => 'Plano Estratégico do STJ 2004/2006', 'autor' => 'Superior Tribunal de Justiça', 'categoria' => 'Jurisprudência', 'descricao' => 'Planejamento estratégico do STJ', 'fonte' => 'STJ', 'link' => 'https://www.stj.jus.br', 'tipo' => 'PDF']
];

// Filtrar por categoria se solicitado
$categoria = $_GET['categoria'] ?? null;
$livrosFiltrados = $categoria 
    ? array_filter($livros, fn($livro) => $livro['categoria'] === $categoria)
    : $livros;

// Contar categorias únicas para filtros
$categorias = array_unique(array_column($livros, 'categoria'));
sort($categorias);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca de Literatura Jurídica - Legatus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .book-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        .category-badge {
            font-size: 0.75rem;
        }
        .author-name {
            color: var(--bege-claro);
            font-size: 0.9rem;
        }
        .book-description {
            font-size: 0.85rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-10">
                <nav class="navbar mb-4">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-book-half"></i> Biblioteca de Literatura Jurídica
                            </h5>
                        </div>
                        <div>
                            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </div>
                </nav>
                
                <div class="container-fluid">
                    <!-- Filtros por Categoria -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-funnel"></i> Filtrar por Categoria
                            </h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="literatura.php" class="btn btn-sm <?php echo !$categoria ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    <i class="bi bi-grid"></i> Todas
                                </a>
                                <?php foreach ($categorias as $cat): ?>
                                <a href="literatura.php?categoria=<?php echo urlencode($cat); ?>" 
                                   class="btn btn-sm <?php echo $categoria === $cat ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    <?php echo $cat; ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estatísticas -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-book"></i> Total de Livros
                                    </h6>
                                    <h3 class="mb-0"><?php echo count($livrosFiltrados); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-folder"></i> Categorias
                                    </h6>
                                    <h3 class="mb-0"><?php echo count($categorias); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-check-circle"></i> Gratuitos
                                    </h6>
                                    <h3 class="mb-0">100%</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grid de Livros -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bookshelf"></i> 
                                <?php echo $categoria ? 'Livros de ' . $categoria : 'Todos os Livros'; ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($livrosFiltrados)): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> 
                                    Nenhum livro encontrado nesta categoria.
                                </div>
                            <?php else: ?>
                                <div class="row g-4">
                                    <?php foreach ($livrosFiltrados as $livro): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card book-card border-secondary">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="badge bg-primary category-badge">
                                                        <?php echo $livro['categoria']; ?>
                                                    </span>
                                                    <span class="badge bg-success category-badge">
                                                        <i class="bi bi-<?php echo $livro['tipo'] === 'PDF' ? 'file-pdf' : 'book'; ?>"></i>
                                                        <?php echo $livro['tipo']; ?>
                                                    </span>
                                                </div>
                                                <h5 class="card-title mb-2">
                                                    <?php echo htmlspecialchars($livro['titulo']); ?>
                                                </h5>
                                                <p class="author-name mb-3">
                                                    <i class="bi bi-person"></i> 
                                                    <?php echo htmlspecialchars($livro['autor']); ?>
                                                </p>
                                                <p class="book-description text-muted mb-3">
                                                    <?php echo htmlspecialchars($livro['descricao']); ?>
                                                </p>
                                                <div class="d-flex gap-2">
                                                    <?php 
                                                    $linkLivro = gerarLinkLivro($livro['titulo'], $livro['autor'], $livro['fonte'], $livro['link'] ?? null);
                                                    ?>
                                                    <a href="<?php echo htmlspecialchars($linkLivro); ?>" target="_blank" class="btn btn-primary btn-sm flex-fill">
                                                        <i class="bi bi-book"></i> Ver o Texto
                                                    </a>
                                                    <button class="btn btn-outline-secondary btn-sm" onclick="copiarLink('<?php echo htmlspecialchars($linkLivro); ?>')" title="Copiar Link">
                                                        <i class="bi bi-link-45deg"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Informações Importantes -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="bi bi-info-circle"></i> Sistema de Literatura Jurídica</h6>
                        <p class="mb-2">
                            Esta biblioteca permite consultar livros jurídicos completos e gratuitos de fontes oficiais.
                        </p>
                        <p class="mb-2">
                            <strong>📚 Como funciona:</strong> Os livros são carregados diretamente das bibliotecas oficiais (Domínio Público, Senado Federal, etc.)
                        </p>
                        <p class="mb-0">
                            <strong>⚠️ Configuração:</strong> Para adicionar livros reais, edite o arquivo <code>literatura.php</code>. Veja <code>INSTRUCOES_LITERATURA.md</code> para detalhes.
                        </p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-shield-check"></i> Considerações Legais</h6>
                        <ul class="mb-0">
                            <li>Apenas livros em <strong>domínio público</strong> ou de acesso gratuito autorizado</li>
                            <li>Todas as obras são obtidas de <strong>fontes oficiais</strong> e confiáveis</li>
                            <li>Respeitamos rigorosamente os <strong>direitos autorais</strong></li>
                        </ul>
                    </div>
                    
                    <!-- Links Úteis -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-link-45deg"></i> Links Úteis</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <a href="https://www.dominiopublico.gov.br" target="_blank" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-globe"></i> Domínio Público
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="https://www.senado.leg.br" target="_blank" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-building"></i> Senado Federal
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="https://archive.org" target="_blank" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-archive"></i> Internet Archive
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copiarLink(url) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link copiado para a área de transferência!');
                });
            } else {
                prompt('Copie este link:', url);
            }
        }
    </script>
</body>
</html>

