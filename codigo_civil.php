<?php
/**
 * Código Civil - Visualizador de Código Jurídico
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

// Informações do código
$codigo = [
    'nome' => 'Código Civil',
    'lei' => 'Lei nº 10.406, de 10 de janeiro de 2002',
    'site_oficial' => 'https://www.planalto.gov.br/ccivil_03/leis/2002/l10406.htm',
    'biblioteca_senado' => 'https://www2.senado.leg.br/bdsf/bitstream/handle/id/522243/0029005700.pdf'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $codigo['nome']; ?> - Legatus</title>
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
                <nav class="navbar mb-4">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-book"></i> <?php echo $codigo['nome']; ?>
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
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="mb-4">
                                        <i class="bi bi-book"></i> <?php echo $codigo['nome']; ?>
                                    </h4>
                                    <p class="lead"><?php echo $codigo['lei']; ?></p>
                                    
                                    <div class="alert alert-info">
                                        <h6><i class="bi bi-info-circle"></i> Consulta do Código Civil</h6>
                                        <p>Acesse a versão oficial e atualizada do Código Civil diretamente das fontes oficiais do governo brasileiro:</p>
                                    </div>
                                    
                                    <div class="row g-3 mt-3">
                                        <div class="col-md-6">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i class="bi bi-building"></i> Portal Planalto
                                                    </h5>
                                                    <p class="card-text">Consulte a lei na íntegra com atualizações em tempo real</p>
                                                    <a href="<?php echo $codigo['site_oficial']; ?>" target="_blank" class="btn btn-primary">
                                                        <i class="bi bi-box-arrow-up-right"></i> Abrir no Portal Planalto
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-success">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i class="bi bi-download"></i> Biblioteca Digital do Senado
                                                    </h5>
                                                    <p class="card-text">Faça download da versão PDF atualizada</p>
                                                    <a href="<?php echo $codigo['biblioteca_senado']; ?>" target="_blank" class="btn btn-success">
                                                        <i class="bi bi-download"></i> Baixar PDF
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card mt-4">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="bi bi-search"></i> Buscar no Código
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <p>Para pesquisar artigos específicos, utilize o portal oficial:</p>
                                            <div class="input-group">
                                                <input type="text" class="form-control" placeholder="Digite o número do artigo (ex: 215)" id="buscarArtigo">
                                                <button class="btn btn-primary" onclick="buscarArtigo()">
                                                    <i class="bi bi-search"></i> Buscar
                                                </button>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                A pesquisa será realizada no site oficial do Planalto
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-warning mt-4">
                                        <i class="bi bi-shield-check"></i> 
                                        <strong>Fonte Oficial:</strong> Todas as informações são obtidas diretamente dos sites oficiais do governo brasileiro.
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
        function buscarArtigo() {
            const artigo = document.getElementById('buscarArtigo').value;
            if (artigo) {
                const url = '<?php echo $codigo['site_oficial']; ?>#art' + artigo;
                window.open(url, '_blank');
            }
        }
        
        // Permitir buscar com Enter
        document.getElementById('buscarArtigo').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarArtigo();
            }
        });
    </script>
</body>
</html>

