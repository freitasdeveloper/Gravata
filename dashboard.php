<?php
/**
 * Dashboard do Usuário
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

$db = Database::getInstance()->getConnection();
$usuario = $auth->getUsuario();

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Estatísticas
$stmt = $db->prepare("SELECT COUNT(*) as total FROM documentos WHERE usuario_id = ?");
$stmt->execute([$usuario['id']]);
$totalDocumentos = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM documentos WHERE usuario_id = ? AND MONTH(created_at) = MONTH(CURRENT_DATE())");
$stmt->execute([$usuario['id']]);
$documentosMes = $stmt->fetch()['total'];

// Sistema totalmente gratuito - sem limitações

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início - Legatus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-10">
                <!-- Top Navbar -->
                <nav class="navbar mb-4">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</h5>
                        </div>
                        <div>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Sistema Gratuito
                            </span>
                        </div>
                    </div>
                </nav>
                
                <div class="container-fluid">
                    
                    <!-- Estatísticas -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted mb-2">Total de Documentos</div>
                                        <div class="stat-number"><?php echo $totalDocumentos; ?></div>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted mb-2">Documentos este Mês</div>
                                        <div class="stat-number"><?php echo $documentosMes; ?></div>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-file-earmark-text-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted mb-2">Sistema</div>
                                        <div class="stat-number" style="font-size: 1.5rem;">
                                            Gratuito
                                        </div>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-check-circle text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ações Rápidas -->
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="bi bi-plus-circle" style="font-size: 3rem; color: var(--azul-claro);"></i>
                                    <h5 class="mt-3">Gerar Novo Documento</h5>
                                    <p class="text-muted">Crie documentos jurídicos profissionais em segundos</p>
                                    <a href="gerar.php" class="btn btn-primary">
                                        <i class="bi bi-plus-lg"></i> Começar
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="bi bi-folder" style="font-size: 3rem; color: var(--azul-claro);"></i>
                                    <h5 class="mt-3">Meus Documentos</h5>
                                    <p class="text-muted">Visualize e gerencie seus documentos criados</p>
                                    <a href="documentos.php" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> Ver Documentos
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conteúdo Jurídico em Tempo Real -->
                    <div class="row g-4 mt-4">
                        <div class="col-12">
                            <!-- Fique Atento -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="bi bi-eye"></i> Fique Atento
                                    </h6>
                                    <i class="bi bi-broadcast text-success" style="animation: pulse 2s infinite;"></i>
                                </div>
                                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                    <div id="jurisprudencias-container">
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Carregando...</span>
                                            </div>
                                            <p class="text-muted mt-2">Carregando informações...</p>
                                        </div>
                                    </div>
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
        // Função para formatar data
        function formatarData(dataString) {
            const data = new Date(dataString);
            const agora = new Date();
            const diffMs = agora - data;
            const diffMins = Math.floor(diffMs / 60000);
            
            if (diffMins < 1) return 'Agora mesmo';
            if (diffMins < 60) return `Há ${diffMins} min`;
            if (diffMins < 1440) return `Há ${Math.floor(diffMins / 60)} h`;
            
            return data.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }
        
        // Carregar jurisprudências reais
        fetch('ajax/buscar_juris_news.php?tipo=juris')
            .then(response => response.json())
            .then(data => {
                // Se não houver dados reais, mostrar mensagem educativa
                if (!data.dados || data.dados.length === 0) {
                    document.getElementById('jurisprudencias-container').innerHTML = `
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Informativo</h6>
                            <p class="mb-0 small">
                                Para exibir jurisprudências reais, é necessário configurar feeds RSS oficiais dos tribunais ou 
                                integrar com APIs como DataJud ou Jusbrasil. Veja o arquivo <code>api_fontes_oficiais.md</code> 
                                para instruções de integração.
                            </p>
                        </div>
                        <p class="text-muted small text-center mt-3">
                            <i class="bi bi-shield-check"></i> 
                            Trabalhando com fontes 100% oficiais para garantir veracidade das informações
                        </p>
                    `;
                    return;
                }
                
                // Processar dados reais
                let html = '';
                data.dados.forEach(j => {
                    const tribunal = j.tribunal || j.fonte || 'OAB';
                    const titulo = j.titulo || j.assunto || 'Sem título';
                    const link = j.link || '#';
                    
                    html += `
                        <a href="${link}" target="_blank" class="text-decoration-none text-reset">
                            <div class="border-bottom pb-3 mb-3 hover-effect">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary">${tribunal}</span>
                                    </div>
                                    <small class="text-muted">${formatarData(j.data)}</small>
                                </div>
                                <h6 class="mb-1">${titulo}</h6>
                                ${j.resumo ? `<p class="text-muted small mb-2">${j.resumo}</p>` : ''}
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="bi bi-clock-history"></i> 
                                    <span>Fonte: ${j.fonte || 'Tribunal Oficial'}</span>
                                </small>
                            </div>
                        </a>
                    `;
                });
                document.getElementById('jurisprudencias-container').innerHTML = html;
            })
            .catch(error => {
                console.error('Erro ao carregar jurisprudências:', error);
                document.getElementById('jurisprudencias-container').innerHTML = `
                    <div class="alert alert-warning">
                        <p class="mb-0 small">Erro ao carregar jurisprudências. Tente recarregar a página.</p>
                    </div>
                `;
            });
        
        // Animações CSS para pulsação
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            .hover-effect {
                transition: all 0.3s ease;
                cursor: pointer;
            }
            .hover-effect:hover {
                background: rgba(212, 196, 176, 0.05);
                border-radius: 8px;
                padding: 10px;
                margin-left: -10px;
                margin-right: -10px;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>

