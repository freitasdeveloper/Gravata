<?php
/**
 * Visualizar Documento
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/documento.php';

$auth = new Auth();

// Verificar se está logado
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
$documentoObj = new DocumentoGerador();
$documento = $documentoObj->getDocumento($id, $_SESSION['usuario_id']);

if (!$documento) {
    header('Location: documentos.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Documento - Legatus</title>
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
                        <a href="documentos.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <div class="btn-group">
                            <a href="download_pdf.php?id=<?php echo $documento['id']; ?>&hash=<?php echo $documento['hash_seguranca']; ?>" 
                               class="btn btn-sm btn-outline-danger" target="_blank">
                                <i class="bi bi-file-pdf"></i> Baixar PDF
                            </a>
                            <a href="download_docx.php?id=<?php echo $documento['id']; ?>&hash=<?php echo $documento['hash_seguranca']; ?>" 
                               class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-file-word"></i> Baixar DOCX
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="compartilharDocumento(<?php echo $documento['id']; ?>)">
                                <i class="bi bi-share"></i> Compartilhar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="copiarLink(<?php echo $documento['id']; ?>, '<?php echo $documento['hash_seguranca']; ?>')">
                                <i class="bi bi-link-45deg"></i> Copiar Link
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </nav>
                
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-file-earmark-text"></i> <?php echo htmlspecialchars($documento['titulo']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="badge bg-secondary">
                                    <i class="bi bi-folder"></i> <?php echo htmlspecialchars($documento['categoria_nome']); ?>
                                </span>
                                <span class="badge bg-info">
                                    <i class="bi bi-tag"></i> <?php echo htmlspecialchars($documento['modelo_nome']); ?>
                                </span>
                                <small class="text-muted ms-3">
                                    <i class="bi bi-calendar"></i> 
                                    Criado em <?php echo date('d/m/Y H:i', strtotime($documento['created_at'])); ?>
                                </small>
                            </div>
                            
                            <div class="preview-abnt-container">
                                <div class="abnt-documento">
                                    <?php 
                                    // Processar o conteúdo para formatação ABNT
                                    $conteudo = htmlspecialchars($documento['conteudo_final']);
                                    // Dividir em parágrafos
                                    $paragrafos = preg_split('/\n\s*\n/', $conteudo);
                                    foreach ($paragrafos as $paragrafo) {
                                        $paragrafo = trim($paragrafo);
                                        if (empty($paragrafo)) continue;
                                        
                                        // Verificar se é título
                                        $isTitulo = preg_match('/^[A-ZÁÀÂÃÉÊÍÓÔÕÚÇ\s\-]+$/', $paragrafo) && strlen($paragrafo) < 80;
                                        $isSubtitulo = preg_match('/^\d+[\.\)]?\s/', $paragrafo);
                                        
                                        if ($isTitulo) {
                                            echo '<p class="abnt-titulo">' . $paragrafo . '</p>';
                                        } elseif ($isSubtitulo) {
                                            echo '<p class="abnt-subtitulo">' . nl2br($paragrafo) . '</p>';
                                        } else {
                                            echo '<p class="abnt-paragrafo">' . nl2br($paragrafo) . '</p>';
                                        }
                                    }
                                    ?>
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
        function compartilharDocumento(documentoId) {
            fetch('compartilhar_documento.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'documento_id=' + documentoId
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    const link = data.link;
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(link).then(() => {
                            alert('Link copiado para a área de transferência!\n\n' + link);
                        });
                    } else {
                        prompt('Link para compartilhar (copie este link):', link);
                    }
                    
                    // Opções de compartilhamento nativo
                    if (navigator.share) {
                        navigator.share({
                            title: 'Documento Jurídico',
                            text: 'Confira este documento',
                            url: link
                        });
                    }
                } else {
                    alert('Erro ao gerar link: ' + (data.mensagem || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao compartilhar documento');
            });
        }
        
        function copiarLink(documentoId, hash) {
            const link = window.location.origin + window.location.pathname + '?id=' + documentoId + '&hash=' + hash;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(link).then(() => {
                    alert('Link copiado para a área de transferência!');
                });
            } else {
                prompt('Link do documento (copie este link):', link);
            }
        }
    </script>
</body>
</html>

