<?php
/**
 * Visualização pública de documento compartilhado
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/documento.php';

$documentoId = intval($_GET['id'] ?? 0);
$hash = $_GET['hash'] ?? '';

if (!$documentoId || !$hash) {
    die('Link inválido');
}

$documentoGerador = new DocumentoGerador();
$db = Database::getInstance()->getConnection();

// Buscar documento pelo hash
$stmt = $db->prepare("
    SELECT d.*, md.nome as modelo_nome, cd.nome as categoria_nome 
    FROM documentos d 
    JOIN modelos_documentos md ON d.modelo_id = md.id 
    JOIN categorias_documentos cd ON md.categoria_id = cd.id 
    WHERE d.id = ? AND d.hash_seguranca = ?
");
$stmt->execute([$documentoId, $hash]);
$documento = $stmt->fetch();

if (!$documento) {
    die('Documento não encontrado ou link inválido');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($documento['titulo']); ?> - Visualização Pública</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text"></i> <?php echo htmlspecialchars($documento['titulo']); ?>
                            <small class="d-block mt-1" style="font-size: 0.85rem; opacity: 0.9;">
                                <i class="bi bi-folder"></i> <?php echo htmlspecialchars($documento['categoria_nome']); ?>
                                <span class="ms-3">
                                    <i class="bi bi-tag"></i> <?php echo htmlspecialchars($documento['modelo_nome']); ?>
                                </span>
                            </small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Este documento está sendo visualizado através de um link compartilhado.
                        </div>
                        
                        <div class="preview-abnt-container">
                            <div class="abnt-documento">
                                <?php 
                                $conteudo = htmlspecialchars($documento['conteudo_final']);
                                $paragrafos = preg_split('/\n\s*\n/', $conteudo);
                                foreach ($paragrafos as $paragrafo) {
                                    $paragrafo = trim($paragrafo);
                                    if (empty($paragrafo)) continue;
                                    
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
                        
                        <div class="mt-4 text-center">
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                            <button onclick="copiarLink()" class="btn btn-secondary">
                                <i class="bi bi-link-45deg"></i> Copiar Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copiarLink() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                alert('Link copiado para a área de transferência!');
            });
        }
    </script>
</body>
</html>

