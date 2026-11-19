<?php
/**
 * Consulta Processual por Estado
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

// Mapeamento de estados para sistemas de consulta processual (Tribunais de Justiça Estaduais)
// Links atualizados usando portais principais dos tribunais (mais estáveis) e links diretos apenas quando confirmados
// Bandeiras via Wikimedia Commons SVG direto (mais confiável que PNG thumb)
$estados = [
    'AC' => ['nome' => 'Acre', 'link' => 'https://www.tjac.jus.br', 'sistema' => 'TJAC', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/4/4c/Bandeira_do_Acre.svg'],
    'AL' => ['nome' => 'Alagoas', 'link' => 'https://www.tjal.jus.br', 'sistema' => 'TJAL', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/8/88/Bandeira_de_Alagoas.svg'],
    'AP' => ['nome' => 'Amapá', 'link' => 'https://www.tjap.jus.br', 'sistema' => 'TJAP', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/0/0a/Bandeira_do_Amap%C3%A1.svg'],
    'AM' => ['nome' => 'Amazonas', 'link' => 'https://www.tjam.jus.br', 'sistema' => 'TJAM', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/6/6b/Bandeira_do_Amazonas.svg'],
    'BA' => ['nome' => 'Bahia', 'link' => 'https://www.tjba.jus.br', 'sistema' => 'TJBA', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/2/28/Bandeira_da_Bahia.svg'],
    'CE' => ['nome' => 'Ceará', 'link' => 'https://www.tjce.jus.br', 'sistema' => 'TJCE', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/2/2e/Bandeira_do_Cear%C3%A1.svg'],
    'DF' => ['nome' => 'Distrito Federal', 'link' => 'https://www.tjdft.jus.br', 'sistema' => 'TJDFT', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/3/3c/Bandeira_do_Distrito_Federal_%28Brasil%29.svg'],
    'ES' => ['nome' => 'Espírito Santo', 'link' => 'https://www.tjes.jus.br', 'sistema' => 'TJES', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/a/aa/Bandeira_do_Esp%C3%ADrito_Santo.svg'],
    'GO' => ['nome' => 'Goiás', 'link' => 'https://www.tjgo.jus.br', 'sistema' => 'TJGO', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/b/be/Bandeira_de_Goi%C3%A1s.svg'],
    'MA' => ['nome' => 'Maranhão', 'link' => 'https://www.tjma.jus.br', 'sistema' => 'TJMA', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/4/45/Bandeira_do_Maranh%C3%A3o.svg'],
    'MT' => ['nome' => 'Mato Grosso', 'link' => 'https://www.tjmt.jus.br', 'sistema' => 'TJMT', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/0/0b/Bandeira_de_Mato_Grosso.svg'],
    'MS' => ['nome' => 'Mato Grosso do Sul', 'link' => 'https://www.tjms.jus.br', 'sistema' => 'TJMS', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/8/8e/Bandeira_de_Mato_Grosso_do_Sul.svg'],
    'MG' => ['nome' => 'Minas Gerais', 'link' => 'https://www.tjmg.jus.br', 'sistema' => 'TJMG', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/f/f4/Bandeira_de_Minas_Gerais.svg'],
    'PA' => ['nome' => 'Pará', 'link' => 'https://www.tjpa.jus.br', 'sistema' => 'TJPA', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/0/02/Bandeira_do_Par%C3%A1.svg'],
    'PB' => ['nome' => 'Paraíba', 'link' => 'https://www.tjpb.jus.br', 'sistema' => 'TJPB', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/b/bb/Bandeira_da_Para%C3%ADba.svg'],
    'PR' => ['nome' => 'Paraná', 'link' => 'https://www.tjpr.jus.br', 'sistema' => 'TJPR', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/9/93/Bandeira_do_Paran%C3%A1.svg'],
    'PE' => ['nome' => 'Pernambuco', 'link' => 'https://www.tjpe.jus.br', 'sistema' => 'TJPE', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/5/59/Bandeira_de_Pernambuco.svg'],
    'PI' => ['nome' => 'Piauí', 'link' => 'https://www.tjpi.jus.br', 'sistema' => 'TJPI', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/3/33/Bandeira_do_Piau%C3%AD.svg'],
    'RJ' => ['nome' => 'Rio de Janeiro', 'link' => 'https://www.tjrj.jus.br', 'sistema' => 'TJRJ', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/7/7b/Bandeira_do_Rio_de_Janeiro.svg'],
    'RN' => ['nome' => 'Rio Grande do Norte', 'link' => 'https://www.tjrn.jus.br', 'sistema' => 'TJRN', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/3/3e/Bandeira_do_Rio_Grande_do_Norte.svg'],
    'RS' => ['nome' => 'Rio Grande do Sul', 'link' => 'https://www.tjrs.jus.br', 'sistema' => 'TJRS', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/6/63/Bandeira_do_Rio_Grande_do_Sul.svg'],
    'RO' => ['nome' => 'Rondônia', 'link' => 'https://www.tjro.jus.br', 'sistema' => 'TJRO', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/d/de/Bandeira_de_Rond%C3%B4nia.svg'],
    'RR' => ['nome' => 'Roraima', 'link' => 'https://www.tjrr.jus.br', 'sistema' => 'TJRR', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/9/98/Bandeira_de_Roraima.svg'],
    'SC' => ['nome' => 'Santa Catarina', 'link' => 'https://www.tjsc.jus.br', 'sistema' => 'TJSC', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/1/1a/Bandeira_de_Santa_Catarina.svg'],
    'SP' => ['nome' => 'São Paulo', 'link' => 'https://esaj.tjsp.jus.br/cpopg/open.do', 'sistema' => 'e-SAJ - TJSP', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/2/2b/Bandeira_de_S%C3%A3o_Paulo.svg'],
    'SE' => ['nome' => 'Sergipe', 'link' => 'https://www.tjse.jus.br', 'sistema' => 'TJSE', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/b/bc/Bandeira_de_Sergipe.svg'],
    'TO' => ['nome' => 'Tocantins', 'link' => 'https://www.tjto.jus.br', 'sistema' => 'TJTO', 'bandeira' => 'https://upload.wikimedia.org/wikipedia/commons/f/ff/Bandeira_do_Tocantins.svg']
];

// Processar seleção de estado
$estadoSelecionado = $_GET['estado'] ?? null;
$urlProcesso = null;
$sistemaProcesso = null;

if ($estadoSelecionado && isset($estados[$estadoSelecionado])) {
    $urlProcesso = $estados[$estadoSelecionado]['link'];
    $sistemaProcesso = $estados[$estadoSelecionado]['sistema'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Processual - Legatus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        #processo-iframe {
            width: 100%;
            height: calc(100vh - 200px);
            border: none;
            border-radius: 8px;
        }
        .iframe-header {
            background: linear-gradient(135deg, #1a3a5c 0%, #2d5a8a 100%);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        .estado-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            background: linear-gradient(135deg, #2d4a5c 0%, #1a3a5c 100%);
            border-color: rgba(212,196,176,0.2) !important;
        }
        .estado-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            background: linear-gradient(135deg, #3d5a6c 0%, #2a4a6c 100%);
        }
        .bandeira-estado {
            width: 100%;
            max-width: 120px;
            height: 80px;
            object-fit: contain;
            border: 2px solid rgba(212,196,176,0.3);
            border-radius: 4px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            background: white;
        }
        .bandeira-estado:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            border-color: var(--azul-claro);
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
                                <i class="bi bi-folder-check"></i> Consulta Processual por Estado
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
                    <?php if (!$estadoSelecionado): ?>
                        <!-- Seleção de Estado -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-map"></i> Selecione seu Estado
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-4">
                                    Clique na bandeira do estado onde deseja consultar processos. Você será redirecionado diretamente para o sistema oficial de consulta processual do respectivo tribunal.
                                </p>
                                
                                <div class="row g-3">
                                    <?php foreach ($estados as $sigla => $info): ?>
                                    <div class="col-md-3 col-sm-4 col-6">
                                        <div class="card estado-card border-secondary h-100">
                                            <div class="card-body text-center p-3">
                                                <a href="<?php echo htmlspecialchars($info['link']); ?>" target="_blank" class="text-decoration-none">
                                                    <div class="mb-2">
                                                        <img src="<?php echo htmlspecialchars($info['bandeira']); ?>" 
                                                             alt="Bandeira de <?php echo htmlspecialchars($info['nome']); ?>" 
                                                             class="bandeira-estado">
                                                    </div>
                                                    <h6 class="mb-1 text-white fw-bold"><?php echo htmlspecialchars($info['nome']); ?></h6>
                                                    <small class="text-light opacity-75"><?php echo $sigla; ?></small>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Informações sobre Consulta Processual</h6>
                                <ul class="mb-0 small">
                                    <li>Cada estado possui seu próprio sistema de consulta processual oficial</li>
                                    <li>Os sistemas podem variar entre PJe (Processo Judicial Eletrônico), e-SAJ e sistemas próprios dos tribunais</li>
                                    <li>Ao selecionar um estado, você será direcionado para o portal oficial do tribunal</li>
                                    <li>Alguns links direcionam diretamente para a consulta processual, outros para o portal principal onde você pode acessar as consultas</li>
                                    <li>Alguns sistemas podem exigir cadastro ou certificado digital para acesso completo</li>
                                </ul>
                        </div>
                    
                    <?php else: ?>
                        <!-- Sistema de Consulta do Estado Selecionado -->
                        <div class="card">
                            <div class="iframe-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <i class="bi bi-broadcast"></i> <?php echo htmlspecialchars($estados[$estadoSelecionado]['nome']); ?> - <?php echo htmlspecialchars($sistemaProcesso); ?>
                                        </h6>
                                        <small class="opacity-75">Sistema oficial de consulta processual</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="processos.php" class="btn btn-sm btn-outline-light">
                                            <i class="bi bi-arrow-left"></i> Escolher outro estado
                                        </a>
                                        <a href="<?php echo htmlspecialchars($urlProcesso); ?>" target="_blank" class="btn btn-sm btn-light">
                                            <i class="bi bi-box-arrow-up-right"></i> Abrir em nova aba
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0 position-relative">
                                <div id="iframe-loading" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <p class="text-muted mt-3">Carregando sistema de consulta processual...</p>
                                </div>
                                <iframe id="processo-iframe" 
                                        src="<?php echo htmlspecialchars($urlProcesso); ?>" 
                                        allowfullscreen
                                        style="display: none;"
                                        onload="iframeCarregado()"
                                        onerror="iframeErro()">
                                </iframe>
                                <div id="iframe-erro" class="alert alert-warning m-4" style="display: none;">
                                    <h6><i class="bi bi-exclamation-triangle"></i> Não foi possível carregar o sistema dentro desta página</h6>
                                    <p class="mb-3">O sistema de consulta processual pode ter restrições que impedem a visualização em iframes. Por favor, acesse diretamente:</p>
                                    <a href="<?php echo htmlspecialchars($urlProcesso); ?>" target="_blank" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-up-right"></i> Abrir sistema em nova aba
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações -->
                        <div class="alert alert-info mt-3">
                            <h6><i class="bi bi-info-circle"></i> Consulta Processual - <?php echo htmlspecialchars($estados[$estadoSelecionado]['nome']); ?></h6>
                            <ul class="mb-0 small">
                                <li>Sistema: <strong><?php echo htmlspecialchars($sistemaProcesso); ?></strong></li>
                                <li>No sistema você pode consultar processos, acompanhar andamentos e acessar documentos</li>
                                <li>A navegação acontece diretamente nesta página, sem precisar sair do Legatus</li>
                                <li>Para uma melhor experiência, você também pode abrir em nova aba usando o botão acima</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function iframeCarregado() {
            const loading = document.getElementById('iframe-loading');
            const iframe = document.getElementById('processo-iframe');
            const erro = document.getElementById('iframe-erro');
            
            if (!loading || !iframe || !erro) return;
            
            // Aguardar um pouco para verificar se realmente carregou
            setTimeout(() => {
                try {
                    // Tentar acessar o conteúdo do iframe (pode gerar erro se bloqueado)
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    
                    // Se chegou aqui, o iframe carregou
                    if (loading) loading.style.display = 'none';
                    if (iframe) iframe.style.display = 'block';
                    if (erro) erro.style.display = 'none';
                } catch (e) {
                    // Erro ao acessar iframe (provavelmente bloqueado)
                    mostrarErro();
                }
            }, 3000);
        }
        
        function iframeErro() {
            mostrarErro();
        }
        
        function mostrarErro() {
            const loading = document.getElementById('iframe-loading');
            const iframe = document.getElementById('processo-iframe');
            const erro = document.getElementById('iframe-erro');
            
            if (loading) loading.style.display = 'none';
            if (iframe) iframe.style.display = 'none';
            if (erro) erro.style.display = 'block';
        }
        
        // Fallback: se após 5 segundos não detectar carregamento, mostrar erro
        <?php if ($estadoSelecionado): ?>
        setTimeout(() => {
            const iframe = document.getElementById('processo-iframe');
            const loading = document.getElementById('iframe-loading');
            
            if (loading && loading.style.display !== 'none') {
                try {
                    if (!iframe.contentWindow || iframe.contentWindow.location.href === 'about:blank') {
                        mostrarErro();
                    } else {
                        iframeCarregado();
                    }
                } catch (e) {
                    mostrarErro();
                }
            }
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>

