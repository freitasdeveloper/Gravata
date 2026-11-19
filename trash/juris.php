<?php
/**
 * OAB - Conselho Federal da OAB
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAB - Conselho Federal da OAB - Legatus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        #juris-iframe {
            width: 100%;
            height: calc(100vh - 150px);
            border: none;
            border-radius: 8px;
        }
        .iframe-header {
            background: linear-gradient(135deg, #1a3a5c 0%, #2d5a8a 100%);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
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
                                <i class="bi bi-bank"></i> OAB - Conselho Federal da OAB
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
                    <div class="card">
                        <div class="iframe-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">
                                        <i class="bi bi-broadcast"></i> Conselho Federal da OAB
                                    </h6>
                                    <small class="opacity-75">Portal oficial da Ordem dos Advogados do Brasil - Acesso a serviços, normas e informações</small>
                                </div>
                                <a href="https://www.oab.org.br/" target="_blank" class="btn btn-sm btn-light">
                                    <i class="bi bi-box-arrow-up-right"></i> Abrir em nova aba
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0 position-relative">
                            <div id="iframe-loading" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                                <p class="text-muted mt-3">Carregando portal da OAB...</p>
                            </div>
                            <iframe id="juris-iframe" 
                                    src="https://www.oab.org.br/" 
                                    allowfullscreen
                                    style="display: none;"
                                    onload="iframeCarregado()"
                                    onerror="iframeErro()">
                            </iframe>
                            <div id="iframe-erro" class="alert alert-warning m-4" style="display: none;">
                                <h6><i class="bi bi-exclamation-triangle"></i> Não foi possível carregar o sistema dentro desta página</h6>
                                <p class="mb-3">O site da OAB pode ter restrições que impedem a visualização em iframes. Por favor, acesse diretamente:</p>
                                <a href="https://www.oab.org.br/" target="_blank" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-up-right"></i> Abrir OAB em nova aba
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações -->
                    <div class="alert alert-info mt-3">
                        <h6><i class="bi bi-info-circle"></i> Informações sobre a OAB</h6>
                        <ul class="mb-0 small">
                            <li>A <strong>Ordem dos Advogados do Brasil (OAB)</strong> é a instituição que representa e regula o exercício da advocacia no país</li>
                            <li>No portal você encontra cadastro nacional, jurisprudência, normas, eventos, serviços e muito mais</li>
                            <li>A navegação acontece diretamente nesta página, sem precisar sair do Legatus</li>
                            <li>Para uma melhor experiência, você também pode abrir em nova aba usando o botão acima</li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function iframeCarregado() {
            const loading = document.getElementById('iframe-loading');
            const iframe = document.getElementById('juris-iframe');
            const erro = document.getElementById('iframe-erro');
            
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
            const iframe = document.getElementById('juris-iframe');
            const erro = document.getElementById('iframe-erro');
            
            if (loading) loading.style.display = 'none';
            if (iframe) iframe.style.display = 'none';
            if (erro) erro.style.display = 'block';
        }
        
        // Fallback: se após 5 segundos não detectar carregamento, mostrar erro
        setTimeout(() => {
            const iframe = document.getElementById('juris-iframe');
            const loading = document.getElementById('iframe-loading');
            
            if (loading && loading.style.display !== 'none') {
                // Ainda está carregando, verificar se o iframe está visível
                try {
                    if (!iframe.contentWindow || iframe.contentWindow.location.href === 'about:blank') {
                        mostrarErro();
                    } else {
                        // Tenta verificar se tem conteúdo
                        iframeCarregado();
                    }
                } catch (e) {
                    mostrarErro();
                }
            }
        }, 5000);
    </script>
</body>
</html>

