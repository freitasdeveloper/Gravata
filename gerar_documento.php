<?php
/**
 * Página de Preenchimento de Documento
 * Extrai campos do modelo e permite preenchimento com visualização em tempo real
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

$usuario = $auth->getUsuario();
$db = Database::getInstance()->getConnection();
$documentoGerador = new DocumentoGerador();

// Obter ID do modelo
$modeloId = $_GET['id'] ?? 0;

if (!$modeloId) {
    header('Location: gerar.php');
    exit;
}

// Obter modelo
$modelo = $documentoGerador->getModelo($modeloId);

if (!$modelo) {
    $_SESSION['erro'] = 'Modelo não encontrado';
    header('Location: gerar.php');
    exit;
}

// Incrementar visualizações
$stmt = $db->prepare("UPDATE modelos_documentos SET visualizacoes = visualizacoes + 1 WHERE id = ?");
$stmt->execute([$modeloId]);

// Extrair variáveis do modelo
$variaveisModelo = json_decode($modelo['variaveis'], true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($variaveisModelo['variaveis'])) {
    $variaveisModelo = ['variaveis' => []];
}

// Variáveis globais para documento gerado
$documentoGeradoId = null;
$documentoGeradoHash = null;

// Processar POST - Gerar documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar_documento'])) {
    $variaveis = $_POST['variaveis'] ?? [];
    $acao = $_POST['acao'] ?? '';
    
    $resultado = $documentoGerador->gerarDocumento($usuario['id'], $modeloId, $variaveis);
    
    if ($resultado['sucesso']) {
        $documentoGeradoId = $resultado['documento_id'];
        $documentoGeradoHash = $resultado['hash'];
        
        // Se foi solicitado download direto
        if ($acao === 'pdf') {
            header('Location: download_pdf.php?id=' . $documentoGeradoId . '&hash=' . $documentoGeradoHash);
            exit;
        } elseif ($acao === 'docx') {
            header('Location: download_docx.php?id=' . $documentoGeradoId . '&hash=' . $documentoGeradoHash);
            exit;
        }
        
        // Senão, redirecionar para visualização
        header('Location: ver_documento.php?id=' . $resultado['documento_id'] . '&hash=' . $resultado['hash']);
        exit;
    } else {
        $erro = $resultado['mensagem'] ?? 'Erro ao gerar documento';
    }
}

// Obter categoria
$stmt = $db->prepare("SELECT nome FROM categorias_documentos WHERE id = ?");
$stmt->execute([$modelo['categoria_id']]);
$categoria = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Documento - <?php echo htmlspecialchars($modelo['nome']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php 
            // Determinar a página ativa para o sidebar
            $current_page = 'gerar.php'; // Forçar active no Gerar Documento
            include 'includes/sidebar.php'; 
            ?>
            
            <!-- Main Content -->
            <main class="col-md-10">
                <!-- Top Navbar -->
                <nav class="navbar mb-4">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-file-earmark-text"></i> 
                                <?php echo htmlspecialchars($modelo['nome']); ?>
                                <small class="text-muted ms-2">
                                    <i class="bi bi-folder"></i> <?php echo htmlspecialchars($categoria['nome']); ?>
                                </small>
                            </h5>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="btn-group" id="btnGroupAcoes" style="display: none;">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="baixarPDF()" title="Baixar PDF">
                                    <i class="bi bi-file-pdf"></i> PDF
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="baixarDOCX()" title="Baixar Editável">
                                    <i class="bi bi-file-word"></i> DOCX
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="compartilharDocumento()" title="Compartilhar">
                                    <i class="bi bi-share"></i> Compartilhar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="copiarLink()" title="Copiar Link">
                                    <i class="bi bi-link-45deg"></i> Copiar Link
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()" title="Imprimir">
                                    <i class="bi bi-printer"></i> Imprimir
                                </button>
                            </div>
                            <a href="gerar.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </div>
                </nav>
                
                <div class="container-fluid">
                    <?php if (isset($erro)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Interface Dual: Preenchimento + Visualização -->
                    <form method="POST" id="formGerar">
                        <input type="hidden" name="gerar_documento" value="1">
                        <div class="row g-3">
                            <!-- Painel de Preenchimento - Esquerda -->
                            <div class="col-lg-6">
                                <div class="card h-100" id="formVariaveis">
                                    <div class="card-header bg-primary text-white">
                                        <i class="bi bi-pencil-square"></i> Preencher Documento
                                    </div>
                                    <div class="card-body" id="camposVariaveis" style="overflow-y: auto; max-height: calc(100vh - 300px);">
                                        <?php if (empty($variaveisModelo['variaveis'])): ?>
                                            <div class="alert alert-warning">
                                                <i class="bi bi-info-circle"></i> Este modelo não possui campos configurados.
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($variaveisModelo['variaveis'] as $index => $variavel): ?>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">
                                                        <?php echo htmlspecialchars($variavel['nome'] ?? 'Campo ' . ($index + 1)); ?>
                                                        <?php if (isset($variavel['obrigatorio']) && $variavel['obrigatorio']): ?>
                                                            <span class="text-danger">*</span>
                                                        <?php endif; ?>
                                                    </label>
                                                    
                                                    <?php if (isset($variavel['tipo'])): ?>
                                                        <?php if ($variavel['tipo'] === 'textarea'): ?>
                                                            <textarea class="form-control campo-variavel" 
                                                                      name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                                      data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                                      placeholder="<?php echo htmlspecialchars($variavel['exemplo'] ?? $variavel['descricao'] ?? ''); ?>" 
                                                                      rows="<?php echo $variavel['linhas'] ?? 4; ?>"
                                                                      <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>><?php echo htmlspecialchars($variavel['valor_padrao'] ?? ''); ?></textarea>
                                                        <?php elseif ($variavel['tipo'] === 'numero' || $variavel['tipo'] === 'number'): ?>
                                                            <input type="number" 
                                                                   class="form-control campo-variavel" 
                                                                   name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                                   data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                                   placeholder="<?php echo htmlspecialchars($variavel['exemplo'] ?? ''); ?>"
                                                                   value="<?php echo htmlspecialchars($variavel['valor_padrao'] ?? ''); ?>"
                                                                   <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>>
                                                        <?php elseif ($variavel['tipo'] === 'data' || $variavel['tipo'] === 'date'): ?>
                                                            <input type="date" 
                                                                   class="form-control campo-variavel" 
                                                                   name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                                   data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                                   value="<?php echo htmlspecialchars($variavel['valor_padrao'] ?? date('Y-m-d')); ?>"
                                                                   onchange="atualizarPreview()"
                                                                   oninput="atualizarPreview()"
                                                                   <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>>
                                                        <?php elseif ($variavel['tipo'] === 'cpf'): ?>
                                                            <input type="text" 
                                                                   class="form-control campo-variavel" 
                                                                   name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                                   data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                                   placeholder="000.000.000-00"
                                                                   maxlength="14"
                                                                   pattern="[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}"
                                                                   value="<?php echo htmlspecialchars($variavel['valor_padrao'] ?? ''); ?>"
                                                                   oninput="formatarCPF(this)"
                                                                   <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>>
                                                        <?php elseif ($variavel['tipo'] === 'cnpj'): ?>
                                                            <input type="text" 
                                                                   class="form-control campo-variavel" 
                                                                   name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                                   data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                                   placeholder="00.000.000/0000-00"
                                                                   maxlength="18"
                                                                   pattern="[0-9]{2}\.[0-9]{3}\.[0-9]{3}/[0-9]{4}-[0-9]{2}"
                                                                   value="<?php echo htmlspecialchars($variavel['valor_padrao'] ?? ''); ?>"
                                                                   oninput="formatarCNPJ(this)"
                                                                   <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>>
                                                        <?php elseif ($variavel['tipo'] === 'cep'): ?>
                                                            <input type="text" 
                                                                   class="form-control campo-variavel" 
                                                                   name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                                   data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                                   placeholder="00000-000"
                                                                   maxlength="9"
                                                                   pattern="[0-9]{5}-[0-9]{3}"
                                                                   value="<?php echo htmlspecialchars($variavel['valor_padrao'] ?? ''); ?>"
                                                                   oninput="formatarCEP(this)"
                                                                   <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>>
                                                        <?php elseif ($variavel['tipo'] === 'telefone'): ?>
                                                            <input type="text" 
                                                                   class="form-control campo-variavel" 
                                                                   name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                                   data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                                   placeholder="(00) 00000-0000"
                                                                   maxlength="15"
                                                                   value="<?php echo htmlspecialchars($variavel['valor_padrao'] ?? ''); ?>"
                                                                   oninput="formatarTelefone(this)"
                                                                   <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>>
                                                        <?php elseif ($variavel['tipo'] === 'rg' || strpos(strtolower($variavel['codigo']), 'rg') !== false): ?>
                                                            <input type="text" 
                                                                   class="form-control campo-variavel" 
                                                                   name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                                   data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                                   placeholder="00.000.000-0"
                                                                   maxlength="12"
                                                                   value="<?php echo htmlspecialchars($variavel['valor_padrao'] ?? ''); ?>"
                                                                   oninput="formatarRG(this)"
                                                                   <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>>
                                                        <?php elseif ($variavel['tipo'] === 'email'): ?>
                                                            <input type="email" 
                                                                   class="form-control campo-variavel" 
                                                                   name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                                   data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                                   placeholder="exemplo@email.com"
                                                                   value="<?php echo htmlspecialchars($variavel['valor_padrao'] ?? ''); ?>"
                                                                   oninput="atualizarPreview()"
                                                                   <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>>
                                                        <?php elseif ($variavel['tipo'] === 'texto' || $variavel['tipo'] === 'text' || !isset($variavel['tipo'])): ?>
                                                            <input type="text" 
                                                                   class="form-control campo-variavel" 
                                                                   name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                                   data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                                   placeholder="<?php echo htmlspecialchars($variavel['exemplo'] ?? $variavel['descricao'] ?? ''); ?>"
                                                                   value="<?php echo htmlspecialchars($variavel['valor_padrao'] ?? ''); ?>"
                                                                   <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <input type="text" 
                                                               class="form-control campo-variavel" 
                                                               name="variaveis[<?php echo htmlspecialchars($variavel['codigo']); ?>]" 
                                                               data-codigo="<?php echo htmlspecialchars($variavel['codigo']); ?>"
                                                               placeholder="<?php echo htmlspecialchars($variavel['exemplo'] ?? ''); ?>"
                                                               value="<?php echo htmlspecialchars($variavel['valor_padrao'] ?? ''); ?>"
                                                               <?php echo (isset($variavel['obrigatorio']) && $variavel['obrigatorio']) ? 'required' : ''; ?>>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (isset($variavel['descricao']) && $variavel['descricao']): ?>
                                                        <small class="text-muted d-block mt-1">
                                                            <?php echo htmlspecialchars($variavel['descricao']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" name="gerar_documento" class="btn btn-success w-100">
                                            <i class="bi bi-check-circle"></i> Gerar Documento Final
                                        </button>
                                        <div class="btn-group w-100 mt-2" role="group">
                                            <button type="submit" name="acao" value="pdf" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-file-pdf"></i> Gerar e Baixar PDF
                                            </button>
                                            <button type="submit" name="acao" value="docx" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-file-word"></i> Gerar e Baixar DOCX
                                            </button>
                                        </div>
                                        <a href="gerar.php" class="btn btn-secondary w-100 mt-2">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Painel de Visualização - Direita -->
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-eye"></i> Visualização em Tempo Real (A4 - ABNT)</span>
                                        <button type="button" class="btn btn-sm btn-light" onclick="window.print()" title="Imprimir">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="previewDocumento" class="preview-abnt-container">
                                            <div class="abnt-documento">
                                                <?php 
                                                // Mostrar template inicial
                                                $templateInicial = htmlspecialchars($modelo['conteudo_template']);
                                                echo '<p class="text-muted text-center p-4">Preencha os campos à esquerda para visualizar o documento aqui</p>';
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Template do modelo
        const currentTemplate = <?php echo json_encode($modelo['conteudo_template'], JSON_UNESCAPED_UNICODE); ?>;
        let currentVariaveis = {};
        
        // Funções de formatação
        function formatarCPF(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length <= 11) {
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                input.value = valor;
            }
            atualizarPreview();
        }
        
        function formatarCNPJ(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length <= 14) {
                valor = valor.replace(/(\d{2})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d)/, '$1/$2');
                valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
                input.value = valor;
            }
            atualizarPreview();
        }
        
        function formatarCEP(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length <= 8) {
                valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
                input.value = valor;
            }
            atualizarPreview();
        }
        
        function formatarTelefone(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length <= 11) {
                if (valor.length <= 10) {
                    valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
                    valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
                    valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
                }
                input.value = valor;
            }
            atualizarPreview();
        }
        
        function formatarRG(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length <= 9) {
                valor = valor.replace(/(\d{2})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d{1})$/, '$1-$2');
                input.value = valor;
            }
            atualizarPreview();
        }
        
        // Adicionar event listeners para atualização em tempo real
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar listeners a todos os campos de variável
            function adicionarListeners() {
                document.querySelectorAll('.campo-variavel').forEach(campo => {
                    // Remover listeners antigos para evitar duplicação
                    campo.removeEventListener('input', atualizarPreview);
                    campo.removeEventListener('change', atualizarPreview);
                    campo.removeEventListener('keyup', atualizarPreview);
                    
                    // Adicionar novos listeners
                    campo.addEventListener('input', atualizarPreview);
                    campo.addEventListener('change', atualizarPreview);
                    campo.addEventListener('keyup', atualizarPreview);
                });
                
                // Também para textareas
                document.querySelectorAll('textarea.campo-variavel').forEach(campo => {
                    campo.removeEventListener('input', atualizarPreview);
                    campo.removeEventListener('change', atualizarPreview);
                    campo.removeEventListener('keyup', atualizarPreview);
                    campo.addEventListener('input', atualizarPreview);
                    campo.addEventListener('change', atualizarPreview);
                    campo.addEventListener('keyup', atualizarPreview);
                });
                
                // Para campos de data também
                document.querySelectorAll('input[type="date"].campo-variavel').forEach(campo => {
                    campo.removeEventListener('input', atualizarPreview);
                    campo.removeEventListener('change', atualizarPreview);
                    campo.addEventListener('input', atualizarPreview);
                    campo.addEventListener('change', atualizarPreview);
                });
            }
            
            // Adicionar listeners imediatamente
            adicionarListeners();
            
            // Re-adicionar listeners caso novos campos sejam adicionados dinamicamente
            const observer = new MutationObserver(function(mutations) {
                let precisaReaplicar = false;
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        precisaReaplicar = true;
                    }
                });
                if (precisaReaplicar) {
                    setTimeout(adicionarListeners, 100);
                }
            });
            
            const camposContainer = document.getElementById('camposVariaveis');
            if (camposContainer) {
                observer.observe(camposContainer, { childList: true, subtree: true });
            }
            
            // Primeira atualização
            setTimeout(atualizarPreview, 100);
        });
        
        function atualizarPreview() {
            const previewContainer = document.getElementById('previewDocumento');
            if (!previewContainer) return;
            
            let conteudoPreview = currentTemplate;
            
            // Coletar valores dos campos
            currentVariaveis = {};
            document.querySelectorAll('.campo-variavel').forEach(campo => {
                const codigo = campo.getAttribute('data-codigo');
                if (!codigo) return;
                
                // Obter valor dependendo do tipo de campo
                let valor = '';
                if (campo.tagName === 'TEXTAREA') {
                    valor = campo.value || '';
                } else if (campo.tagName === 'INPUT') {
                    if (campo.type === 'date') {
                        // Formatar data de YYYY-MM-DD para DD/MM/YYYY
                        if (campo.value) {
                            const partes = campo.value.split('-');
                            if (partes.length === 3) {
                                valor = `${partes[2]}/${partes[1]}/${partes[0]}`;
                            } else {
                                valor = campo.value;
                            }
                        }
                    } else {
                        valor = campo.value || '';
                    }
                } else {
                    valor = campo.textContent || campo.innerText || '';
                }
                
                currentVariaveis[codigo] = valor;
                
                // Escapar caracteres especiais do código para regex
                const codigoEscapado = codigo.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                
                // Substituir no template - suporta tanto {variavel} quanto {{variavel}}
                // Primeiro tenta o formato {{variavel}}
                const regex2 = new RegExp(`\\{\\{\\s*${codigoEscapado}\\s*\\}\\}`, 'g');
                conteudoPreview = conteudoPreview.replace(regex2, valor);
                
                // Depois tenta o formato {variavel}
                const regex1 = new RegExp(`\\{${codigoEscapado}\\}`, 'g');
                conteudoPreview = conteudoPreview.replace(regex1, valor);
            });
            
            // Processar funções especiais
            // Data atual
            conteudoPreview = conteudoPreview.replace(/\{\{data_atual\(([^)]+)\)\}\}/g, function(match, format) {
                format = format.replace(/['"]/g, '').trim() || 'd/m/Y';
                const date = new Date();
                return date.toLocaleDateString('pt-BR');
            });
            
            // Substituir campos restantes não preenchidos por string vazia (não por espaço)
            conteudoPreview = conteudoPreview.replace(/\{[^}]+\}/g, '');
            conteudoPreview = conteudoPreview.replace(/\{\{[^}]+\}\}/g, '');
            
            // Dividir em parágrafos baseado em duplas quebras de linha
            let paragrafos = conteudoPreview.split(/\n\s*\n/);
            
            // Se não houver parágrafos separados, tentar por quebras simples
            if (paragrafos.length === 1 && conteudoPreview.includes('\n')) {
                // Dividir por linhas e agrupar linhas consecutivas não vazias
                const linhas = conteudoPreview.split('\n');
                paragrafos = [];
                let paragrafoAtual = '';
                
                linhas.forEach(linha => {
                    linha = linha.trim();
                    if (linha) {
                        paragrafoAtual += (paragrafoAtual ? ' ' : '') + linha;
                    } else if (paragrafoAtual) {
                        paragrafos.push(paragrafoAtual);
                        paragrafoAtual = '';
                    }
                });
                if (paragrafoAtual) {
                    paragrafos.push(paragrafoAtual);
                }
            }
            
            let htmlFormatado = '';
            let primeiroParagrafo = true;
            
            paragrafos.forEach(paragrafo => {
                paragrafo = paragrafo.trim();
                if (!paragrafo) return;
                
                // Verificar se é título (tudo em maiúsculas ou começa com número seguido de pontos)
                const paragrafoUpper = paragrafo.toUpperCase().replace(/[^A-ZÁÀÂÃÉÊÍÓÔÕÚÇ\s\-]/g, '');
                const isTitulo = paragrafoUpper === paragrafo.toUpperCase().substring(0, paragrafoUpper.length) && 
                                 paragrafo.length < 80 && 
                                 paragrafo.length > 5 &&
                                 !paragrafo.match(/^\d/);
                const isSubtitulo = /^\d+[\.\)]?\s/.test(paragrafo);
                
                if (isTitulo) {
                    htmlFormatado += `<p class="abnt-titulo">${paragrafo}</p>`;
                    primeiroParagrafo = false;
                } else if (isSubtitulo) {
                    htmlFormatado += `<p class="abnt-subtitulo">${paragrafo.replace(/\n/g, ' ')}</p>`;
                    primeiroParagrafo = false;
                } else {
                    // Processar quebras de linha simples dentro do parágrafo
                    const texto = paragrafo.replace(/\n+/g, ' ').replace(/\s+/g, ' ');
                    const classe = primeiroParagrafo ? 'abnt-paragrafo abnt-paragrafo-primeiro' : 'abnt-paragrafo';
                    htmlFormatado += `<p class="${classe}">${texto}</p>`;
                    primeiroParagrafo = false;
                }
            });
            
            // Se não gerou nenhum parágrafo formatado, usar o texto original
            if (!htmlFormatado) {
                htmlFormatado = `<p class="abnt-paragrafo abnt-paragrafo-primeiro">${conteudoPreview.replace(/\n+/g, '<br>')}</p>`;
            }
            
            // Substituir espaços sublinhados por campos visuais
            htmlFormatado = htmlFormatado.replace(/_{3,}/g, '<span class="abnt-campo">_______</span>');
            
            // Renderizar com formatação ABNT completa
            previewContainer.innerHTML = `
                <div class="abnt-documento">
                    ${htmlFormatado}
                </div>
            `;
        }
        
        // Funções para ações com documento gerado
        <?php if ($documentoGeradoId && $documentoGeradoHash): ?>
        // Mostrar botões de ações quando documento foi gerado
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('btnGroupAcoes').style.display = 'flex';
            window.documentoId = <?php echo $documentoGeradoId; ?>;
            window.documentoHash = '<?php echo $documentoGeradoHash; ?>';
        });
        <?php endif; ?>
        
        function baixarPDF() {
            if (window.documentoId && window.documentoHash) {
                window.open('download_pdf.php?id=' + window.documentoId + '&hash=' + window.documentoHash, '_blank');
            } else {
                alert('Gere o documento primeiro antes de baixar.');
            }
        }
        
        function baixarDOCX() {
            if (window.documentoId && window.documentoHash) {
                window.open('download_docx.php?id=' + window.documentoId + '&hash=' + window.documentoHash, '_blank');
            } else {
                alert('Gere o documento primeiro antes de baixar.');
            }
        }
        
        function compartilharDocumento() {
            if (window.documentoId) {
                fetch('compartilhar_documento.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'documento_id=' + window.documentoId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        // Mostrar modal ou copiar link
                        const link = data.link;
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(link).then(() => {
                                alert('Link copiado para a área de transferência!\n\n' + link);
                            });
                        } else {
                            // Fallback: mostrar prompt
                            prompt('Link para compartilhar (copie este link):', link);
                        }
                        
                        // Opções de compartilhamento
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
            } else {
                alert('Gere o documento primeiro antes de compartilhar.');
            }
        }
        
        function copiarLink() {
            if (window.documentoId && window.documentoHash) {
                const link = window.location.origin + window.location.pathname.replace('gerar_documento.php', 'ver_documento.php') + '?id=' + window.documentoId + '&hash=' + window.documentoHash;
                
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(link).then(() => {
                        alert('Link copiado para a área de transferência!');
                    });
                } else {
                    prompt('Link do documento (copie este link):', link);
                }
            } else {
                alert('Gere o documento primeiro.');
            }
        }
    </script>
</body>
</html>

