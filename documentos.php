<?php
/**
 * Meus Documentos - Organizador com Upload
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

$db = Database::getInstance()->getConnection();
$usuario_id = $_SESSION['usuario_id'];
$documento = new DocumentoGerador();

// Processar exclusão
if (isset($_POST['excluir_documento']) && isset($_POST['id'])) {
    $doc_id = (int)$_POST['id'];
    $tipo = $_POST['tipo'] ?? 'gerado';
    
    if ($tipo === 'enviado') {
        $stmt = $db->prepare("SELECT caminho_arquivo FROM documentos_enviados WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$doc_id, $usuario_id]);
        $doc = $stmt->fetch();
        
        if ($doc) {
            $caminho_completo = __DIR__ . '/' . $doc['caminho_arquivo'];
            if (file_exists($caminho_completo)) {
                @unlink($caminho_completo);
            }
        }
        
        $stmt = $db->prepare("DELETE FROM documentos_enviados WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$doc_id, $usuario_id]);
    } elseif ($tipo === 'gerado') {
        // Verificar se o documento pertence ao usuário
        $stmt = $db->prepare("SELECT id FROM documentos WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$doc_id, $usuario_id]);
        $doc = $stmt->fetch();
        
        if ($doc) {
            // Excluir histórico do documento
            $stmt = $db->prepare("DELETE FROM historico_documentos WHERE documento_id = ?");
            $stmt->execute([$doc_id]);
            
            // Excluir o documento
            $stmt = $db->prepare("DELETE FROM documentos WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$doc_id, $usuario_id]);
        }
    }
    
    header('Location: documentos.php');
    exit;
}

// Buscar documentos gerados
$documentos_gerados = $documento->listarDocumentosUsuario($usuario_id, 100, 0);

// Buscar documentos enviados
try {
    $stmt = $db->prepare("SELECT * FROM documentos_enviados WHERE usuario_id = ? AND ativo = 1 ORDER BY created_at DESC");
    $stmt->execute([$usuario_id]);
    $documentos_enviados = $stmt->fetchAll();
} catch (PDOException $e) {
    $documentos_enviados = [];
}

// Buscar categorias dos documentos enviados
$categorias = [];
foreach ($documentos_enviados as $doc) {
    if (!empty($doc['categoria']) && !in_array($doc['categoria'], $categorias)) {
        $categorias[] = $doc['categoria'];
    }
}
sort($categorias);

// Filtrar por categoria se solicitado
$filtro_categoria = $_GET['categoria'] ?? null;
$tipo_visualizacao = $_GET['tipo'] ?? 'todos'; // todos, gerados, enviados

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Documentos - Legatus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .organizer-view {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .document-card {
            background: linear-gradient(135deg, rgba(42,42,42,0.95), rgba(58,58,58,0.95));
            border: 1px solid rgba(212,196,176,0.25);
            border-radius: 12px;
            padding: 24px;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
            min-height: 280px;
            display: flex;
            flex-direction: column;
        }
        
        .document-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.6);
            border-color: rgba(212,196,176,0.4);
        }
        
        .document-icon {
            font-size: 4rem;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            height: 80px;
        }
        
        .document-icon i {
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        
        .document-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--prata-brilho);
            margin-bottom: 16px;
            word-break: break-word;
            line-height: 1.4;
            min-height: 48px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .document-meta {
            font-size: 0.8rem;
            color: var(--prata-medio);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .document-meta i {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .document-actions {
            margin-top: auto;
            padding-top: 18px;
            border-top: 1px solid rgba(212,196,176,0.15);
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .document-actions .btn {
            border-width: 1.5px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .document-actions .btn:hover {
            transform: scale(1.05);
        }
        
        .btn-download-main {
            flex: 1;
            background: linear-gradient(135deg, var(--bege-medio), var(--bege-escuro));
            border: 1.5px solid var(--bege-medio);
            color: white;
            padding: 11px 18px;
            font-weight: 600;
            border-radius: 8px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-download-main:hover {
            background: linear-gradient(135deg, var(--bege-claro), var(--bege-medio));
            border-color: var(--bege-claro);
            color: white;
            transform: scale(1.02);
        }
        
        .btn-view-small {
            min-width: 44px;
            width: 44px;
            height: 44px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid #0dcaf0;
            background: transparent;
            color: #0dcaf0;
            border-radius: 8px;
        }
        
        .btn-view-small:hover {
            background: #0dcaf0;
            color: white;
            border-color: #0dcaf0;
            transform: scale(1.05);
        }
        
        .btn-view-small i {
            font-size: 1.1rem;
        }
        
        .btn-delete-small {
            min-width: 44px;
            width: 44px;
            height: 44px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid #dc3545;
            background: transparent;
            color: #dc3545;
            border-radius: 8px;
        }
        
        .btn-delete-small:hover {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
            transform: scale(1.05);
        }
        
        .btn-delete-small i {
            font-size: 1.1rem;
        }
        
        .folder-card {
            background: linear-gradient(135deg, rgba(61,47,31,0.9), rgba(139,115,85,0.9));
            border: 2px dashed rgba(212,196,176,0.4);
        }
        
        .upload-area {
            border: 2px dashed rgba(212,196,176,0.4);
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: rgba(42,42,42,0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            border-color: var(--bege-medio);
            background: rgba(42,42,42,0.7);
        }
        
        .upload-area.dragover {
            border-color: var(--bege-claro);
            background: rgba(212,196,176,0.1);
        }
        
        .view-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .category-filter {
            margin-bottom: 20px;
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
                        <h5 class="mb-0">Meus Documentos</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="bi bi-cloud-upload"></i> Enviar Documento
                            </button>
                            <a href="gerar.php" class="btn btn-outline-primary">
                                <i class="bi bi-plus-lg"></i> Novo Documento
                            </a>
                        </div>
                    </div>
                </nav>
                
                <div class="container-fluid">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="view-toggle">
                                <a href="?tipo=todos" class="btn btn-sm <?php echo $tipo_visualizacao === 'todos' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                                    <i class="bi bi-grid"></i> Todos
                                </a>
                                <a href="?tipo=gerados" class="btn btn-sm <?php echo $tipo_visualizacao === 'gerados' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                                    <i class="bi bi-file-earmark-text"></i> Gerados
                                </a>
                                <a href="?tipo=enviados" class="btn btn-sm <?php echo $tipo_visualizacao === 'enviados' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                                    <i class="bi bi-upload"></i> Enviados
                                </a>
                            </div>
                        </div>
                        <?php if (!empty($categorias) && $tipo_visualizacao !== 'gerados'): ?>
                        <div class="col-md-6">
                            <select class="form-select" id="filtroCategoria" onchange="filtrarPorCategoria(this.value)">
                                <option value="">Todas as Categorias</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filtro_categoria === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Organizador -->
                    <div class="organizer-view">
                        <?php if ($tipo_visualizacao === 'todos' || $tipo_visualizacao === 'gerados'): ?>
                            <?php foreach ($documentos_gerados as $doc): ?>
                            <?php
                            $hash = $doc['hash_seguranca'] ?? '';
                            ?>
                            <div class="document-card">
                                <div class="document-icon" style="color: #0d6efd;">
                                    <i class="bi bi-file-earmark-text-fill"></i>
                                </div>
                                <div class="document-title"><?php echo htmlspecialchars($doc['titulo']); ?></div>
                                <div class="document-meta">
                                    <i class="bi bi-folder"></i> <span><?php echo htmlspecialchars($doc['categoria_nome']); ?></span>
                                </div>
                                <div class="document-meta">
                                    <i class="bi bi-calendar"></i> <span><?php echo date('d/m/Y', strtotime($doc['created_at'])); ?></span>
                                </div>
                                <div class="document-actions" onclick="event.stopPropagation()">
                                    <a href="download_pdf.php?id=<?php echo $doc['id']; ?><?php echo $hash ? '&hash=' . urlencode($hash) : ''; ?>" class="btn btn-download-main">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    <a href="ver_documento.php?id=<?php echo $doc['id']; ?>" class="btn btn-view-small" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-delete-small" onclick="excluirDocumento(<?php echo $doc['id']; ?>, 'gerado')" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if ($tipo_visualizacao === 'todos' || $tipo_visualizacao === 'enviados'): ?>
                            <?php 
                            $docs_filtrados = $documentos_enviados;
                            if ($filtro_categoria) {
                                $docs_filtrados = array_filter($documentos_enviados, function($doc) use ($filtro_categoria) {
                                    return $doc['categoria'] === $filtro_categoria;
                                });
                            }
                            
                            if (empty($docs_filtrados) && $tipo_visualizacao === 'enviados'): ?>
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="bi bi-cloud-upload" style="font-size: 4rem; color: #ccc;"></i>
                                        <h4 class="mt-3 text-muted">Nenhum documento enviado</h4>
                                        <p class="text-muted">Envie seus documentos para organizá-los aqui</p>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                            <i class="bi bi-cloud-upload"></i> Enviar Primeiro Documento
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($docs_filtrados as $doc): ?>
                                <?php
                                $extensao = pathinfo($doc['nome_arquivo'], PATHINFO_EXTENSION);
                                $icone = 'file-earmark';
                                $cor = 'var(--prata-claro)';
                                
                                switch (strtolower($extensao)) {
                                    case 'pdf':
                                        $icone = 'file-earmark-pdf';
                                        $cor = '#dc3545';
                                        break;
                                    case 'doc':
                                    case 'docx':
                                        $icone = 'file-earmark-word';
                                        $cor = '#0d6efd';
                                        break;
                                    case 'xls':
                                    case 'xlsx':
                                        $icone = 'file-earmark-excel';
                                        $cor = '#198754';
                                        break;
                                    case 'ppt':
                                    case 'pptx':
                                        $icone = 'file-earmark-ppt';
                                        $cor = '#fd7e14';
                                        break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'gif':
                                        $icone = 'file-earmark-image';
                                        $cor = '#6f42c1';
                                        break;
                                }
                                
                                $tamanho_formatado = formatarTamanho($doc['tamanho']);
                                ?>
                                <div class="document-card">
                                    <div class="document-icon" style="color: <?php echo $cor; ?>">
                                        <i class="bi bi-<?php echo $icone; ?>-fill"></i>
                                    </div>
                                    <div class="document-title"><?php echo htmlspecialchars($doc['nome_original']); ?></div>
                                    <?php if ($doc['categoria']): ?>
                                    <div class="document-meta">
                                        <i class="bi bi-folder"></i> <span><?php echo htmlspecialchars($doc['categoria']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="document-meta">
                                        <i class="bi bi-cloud-download"></i> <span><?php echo $tamanho_formatado; ?></span>
                                    </div>
                                    <div class="document-meta">
                                        <i class="bi bi-calendar"></i> <span><?php echo date('d/m/Y', strtotime($doc['created_at'])); ?></span>
                                    </div>
                                    <div class="document-actions">
                                        <a href="<?php echo htmlspecialchars($doc['caminho_arquivo']); ?>" download class="btn btn-download-main">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <a href="<?php echo htmlspecialchars($doc['caminho_arquivo']); ?>" target="_blank" class="btn btn-view-small" title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn btn-delete-small" onclick="excluirDocumento(<?php echo $doc['id']; ?>, 'enviado')" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if (empty($documentos_gerados) && empty($documentos_enviados) && $tipo_visualizacao === 'todos'): ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                                    <h4 class="mt-3 text-muted">Nenhum documento</h4>
                                    <p class="text-muted">Comece gerando ou enviando seus documentos</p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                            <i class="bi bi-cloud-upload"></i> Enviar Documento
                                        </button>
                                        <a href="gerar.php" class="btn btn-outline-primary">
                                            <i class="bi bi-plus-circle"></i> Gerar Documento
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de Upload -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: rgba(42,42,42,0.95); border: 1px solid rgba(212,196,176,0.3);">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-cloud-upload"></i> Enviar Documento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="upload-area" id="uploadArea">
                            <i class="bi bi-cloud-upload" style="font-size: 3rem; color: var(--bege-claro);"></i>
                            <p class="mt-3">Arraste o arquivo aqui ou clique para selecionar</p>
                            <input type="file" name="arquivo" id="arquivoInput" class="d-none" required>
                        </div>
                        <div id="arquivoInfo" class="mt-3 d-none">
                            <div class="alert alert-info">
                                <i class="bi bi-file"></i> <span id="arquivoNome"></span>
                                <button type="button" class="btn btn-sm btn-outline-secondary float-end" onclick="removerArquivo()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Categoria/Pasta</label>
                            <input type="text" name="categoria" class="form-control" placeholder="Ex: Contratos, Petições, Processos..." list="categoriasExistentes">
                            <datalist id="categoriasExistentes">
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição (opcional)</label>
                            <textarea name="descricao" class="form-control" rows="2" placeholder="Adicione uma descrição..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tags (opcional)</label>
                            <input type="text" name="tags" class="form-control" placeholder="Ex: importante, urgente, revisar">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="enviarDocumento()">
                        <i class="bi bi-cloud-upload"></i> Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="excluir_documento" value="1">
        <input type="hidden" name="id" id="deleteId">
        <input type="hidden" name="tipo" id="deleteTipo">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Upload area
        const uploadArea = document.getElementById('uploadArea');
        const arquivoInput = document.getElementById('arquivoInput');
        const arquivoInfo = document.getElementById('arquivoInfo');
        const arquivoNome = document.getElementById('arquivoNome');
        
        uploadArea.addEventListener('click', () => arquivoInput.click());
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                arquivoInput.files = files;
                mostrarArquivoInfo(files[0]);
            }
        });
        
        arquivoInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                mostrarArquivoInfo(e.target.files[0]);
            }
        });
        
        function mostrarArquivoInfo(file) {
            arquivoNome.textContent = file.name + ' (' + formatarTamanho(file.size) + ')';
            arquivoInfo.classList.remove('d-none');
        }
        
        function removerArquivo() {
            arquivoInput.value = '';
            arquivoInfo.classList.add('d-none');
        }
        
        function enviarDocumento() {
            const form = document.getElementById('uploadForm');
            const formData = new FormData(form);
            
            if (!arquivoInput.files[0]) {
                alert('Selecione um arquivo para enviar');
                return;
            }
            
            // Validar tamanho do arquivo antes de enviar
            const arquivo = arquivoInput.files[0];
            const tamanhoMaximo = 50 * 1024 * 1024; // 50MB
            if (arquivo.size > tamanhoMaximo) {
                alert('Arquivo muito grande! Tamanho máximo permitido: 50MB');
                return;
            }
            
            const btnEnviar = event.target;
            const btnOriginal = btnEnviar.innerHTML;
            btnEnviar.disabled = true;
            btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';
            
            fetch('ajax/upload_documento.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.sucesso) {
                    // Fechar modal e recarregar
                    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
                    if (modal) {
                        modal.hide();
                    }
                    // Mostrar mensagem de sucesso
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    alert(data.mensagem || 'Erro ao enviar arquivo. Tente novamente.');
                    btnEnviar.disabled = false;
                    btnEnviar.innerHTML = btnOriginal;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao comunicar com o servidor: ' + error.message + '\n\nVerifique sua conexão e tente novamente.');
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = btnOriginal;
            });
        }
        
        function filtrarPorCategoria(categoria) {
            const url = new URL(window.location);
            if (categoria) {
                url.searchParams.set('categoria', categoria);
            } else {
                url.searchParams.delete('categoria');
            }
            window.location.href = url.toString();
        }
        
        function excluirDocumento(id, tipo) {
            if (confirm('Tem certeza que deseja excluir este documento?')) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteTipo').value = tipo;
                document.getElementById('deleteForm').submit();
            }
        }
        
        function formatarTamanho(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    </script>
</body>
</html>

<?php
function formatarTamanho($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>
