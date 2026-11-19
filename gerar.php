<?php
/**
 * Geração de Documentos
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

$documento = new DocumentoGerador();
$erro = '';
$sucesso = '';
$categoriaId = $_GET['categoria'] ?? null;

// Obter categorias e modelos
$categorias = $documento->listarCategorias();
$modelos = $documento->listarModelos($categoriaId, $_SESSION['usuario_id']);

// Processar geração de documento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modeloId = intval($_POST['modelo_id'] ?? 0);
    $variaveis = $_POST['variaveis'] ?? [];
    
    // Validar modelo
    $modelo = $documento->getModelo($modeloId);
    if ($modelo) {
        $resultado = $documento->gerarDocumento($_SESSION['usuario_id'], $modeloId, $variaveis);
        
        if ($resultado['sucesso']) {
            header('Location: ver_documento.php?id=' . $resultado['documento_id']);
            exit;
        } else {
            $erro = $resultado['mensagem'];
        }
    } else {
        $erro = 'Modelo não encontrado';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Documento - Legatus</title>
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
                        <h5 class="mb-0">Gerar Novo Documento</h5>
                    </div>
                </nav>
                
                <div class="container-fluid">
                    <?php if ($erro): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Seleção de Categoria e Modelo -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <i class="bi bi-folder"></i> Categorias
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" role="group" style="flex-wrap: wrap;">
                                        <a href="gerar.php" class="btn <?php echo !$categoriaId ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                                            <i class="bi bi-list-ul"></i> Todos
                                        </a>
                                        <?php foreach ($categorias as $cat): ?>
                                        <a href="?categoria=<?php echo $cat['id']; ?>" 
                                           class="btn <?php echo $categoriaId == $cat['id'] ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                                            <i class="bi bi-<?php echo htmlspecialchars($cat['icone']); ?>"></i> 
                                            <?php echo htmlspecialchars($cat['nome']); ?>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($modelos)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> <strong>Nenhum modelo disponível nesta categoria.</strong>
                            <hr>
                            <p class="mb-2">Se você acabou de instalar o sistema, é necessário importar os modelos de documentos.</p>
                            <div class="d-flex gap-2">
                                <a href="verificar_modelos.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i> Verificar Modelos no Banco
                                </a>
                                <a href="importar_modelos.php" class="btn btn-success btn-sm">
                                    <i class="bi bi-download"></i> Importar Modelos Automaticamente
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Barra de Pesquisa -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-primary text-white">
                                                <i class="bi bi-search"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="pesquisaModelo" 
                                                   placeholder="Pesquisar modelo de documento (digite o nome ou descrição)..."
                                                   autocomplete="off">
                                            <button class="btn btn-outline-secondary" type="button" id="limparPesquisa" style="display: none;">
                                                <i class="bi bi-x-circle"></i> Limpar
                                            </button>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            <i class="bi bi-info-circle"></i> 
                                            <span id="contadorModelos"><?php echo count($modelos); ?></span> modelo(s) disponível(is)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modelos para Seleção -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <i class="bi bi-file-earmark-text"></i> Selecione um Modelo
                                    </div>
                                    <div class="card-body">
                                        <div class="row" id="listaModelos">
                                            <?php foreach ($modelos as $modelo): ?>
                                            <div class="col-md-4 mb-3 modelo-item" 
                                                 data-nome="<?php echo htmlspecialchars(strtolower($modelo['nome'])); ?>"
                                                 data-descricao="<?php echo htmlspecialchars(strtolower($modelo['descricao'])); ?>"
                                                 data-categoria="<?php echo htmlspecialchars(strtolower($modelo['categoria_nome'])); ?>">
                                                <div class="card h-100 modelo-card" data-modelo-id="<?php echo $modelo['id']; ?>">
                                                    <div class="card-body">
                                                        <h6 class="mb-2 modelo-nome">
                                                            <?php echo htmlspecialchars($modelo['nome']); ?>
                                                        </h6>
                                                        <p class="text-muted small mb-2">
                                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($modelo['categoria_nome']); ?></span>
                                                        </p>
                                                        <p class="text-muted small mb-3 modelo-descricao">
                                                            <?php echo htmlspecialchars($modelo['descricao']); ?>
                                                        </p>
                                                        <button type="button" class="btn btn-primary btn-sm w-100" 
                                                                onclick="usarModelo(<?php echo $modelo['id']; ?>)">
                                                            <i class="bi bi-check-circle"></i> Usar Este Modelo
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function usarModelo(modeloId) {
            // Redirecionar para página de geração com o modelo selecionado
            window.location.href = 'gerar_documento.php?id=' + modeloId;
        }

        // Funcionalidade de pesquisa
        document.addEventListener('DOMContentLoaded', function() {
            const campoPesquisa = document.getElementById('pesquisaModelo');
            const botaoLimpar = document.getElementById('limparPesquisa');
            const contador = document.getElementById('contadorModelos');
            const modelos = document.querySelectorAll('.modelo-item');
            const totalModelos = modelos.length;

            function filtrarModelos() {
                const termoPesquisa = campoPesquisa.value.toLowerCase().trim();
                let modelosVisiveis = 0;

                modelos.forEach(function(modelo) {
                    const nome = modelo.getAttribute('data-nome') || '';
                    const descricao = modelo.getAttribute('data-descricao') || '';
                    const categoria = modelo.getAttribute('data-categoria') || '';
                    
                    const corresponde = nome.includes(termoPesquisa) || 
                                       descricao.includes(termoPesquisa) || 
                                       categoria.includes(termoPesquisa);

                    if (corresponde || termoPesquisa === '') {
                        modelo.style.display = '';
                        modelosVisiveis++;
                    } else {
                        modelo.style.display = 'none';
                    }
                });

                // Atualizar contador
                contador.textContent = modelosVisiveis;
                
                // Mostrar/ocultar botão limpar
                if (termoPesquisa !== '') {
                    botaoLimpar.style.display = '';
                } else {
                    botaoLimpar.style.display = 'none';
                }

                // Mostrar mensagem se nenhum resultado
                if (modelosVisiveis === 0 && termoPesquisa !== '') {
                    const listaModelos = document.getElementById('listaModelos');
                    let mensagemVazia = listaModelos.querySelector('.mensagem-sem-resultado');
                    if (!mensagemVazia) {
                        mensagemVazia = document.createElement('div');
                        mensagemVazia.className = 'col-12 mensagem-sem-resultado';
                        mensagemVazia.innerHTML = `
                            <div class="alert alert-warning text-center">
                                <i class="bi bi-search"></i>
                                <strong>Nenhum modelo encontrado</strong><br>
                                <small>Tente pesquisar com outros termos ou verifique a ortografia.</small>
                            </div>
                        `;
                        listaModelos.appendChild(mensagemVazia);
                    }
                } else {
                    const mensagemVazia = document.querySelector('.mensagem-sem-resultado');
                    if (mensagemVazia) {
                        mensagemVazia.remove();
                    }
                }
            }

            // Event listeners
            campoPesquisa.addEventListener('input', filtrarModelos);
            campoPesquisa.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    campoPesquisa.value = '';
                    filtrarModelos();
                    campoPesquisa.focus();
                }
            });

            botaoLimpar.addEventListener('click', function() {
                campoPesquisa.value = '';
                filtrarModelos();
                campoPesquisa.focus();
            });

            // Destacar termos pesquisados (opcional)
            campoPesquisa.addEventListener('input', function() {
                const termo = this.value.toLowerCase().trim();
                if (termo !== '') {
                    modelos.forEach(function(modelo) {
                        const nomeElement = modelo.querySelector('.modelo-nome');
                        const descricaoElement = modelo.querySelector('.modelo-descricao');
                        
                        if (nomeElement && descricaoElement) {
                            const nomeOriginal = nomeElement.getAttribute('data-original') || nomeElement.textContent;
                            const descricaoOriginal = descricaoElement.getAttribute('data-original') || descricaoElement.textContent;
                            
                            nomeElement.setAttribute('data-original', nomeOriginal);
                            descricaoElement.setAttribute('data-original', descricaoOriginal);
                            
                            // Destacar termo (simples)
                            const regex = new RegExp(`(${termo.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                            nomeElement.innerHTML = nomeOriginal.replace(regex, '<mark>$1</mark>');
                            descricaoElement.innerHTML = descricaoOriginal.replace(regex, '<mark>$1</mark>');
                        }
                    });
                } else {
                    modelos.forEach(function(modelo) {
                        const nomeElement = modelo.querySelector('.modelo-nome');
                        const descricaoElement = modelo.querySelector('.modelo-descricao');
                        
                        if (nomeElement && descricaoElement) {
                            const nomeOriginal = nomeElement.getAttribute('data-original');
                            const descricaoOriginal = descricaoElement.getAttribute('data-original');
                            
                            if (nomeOriginal) {
                                nomeElement.textContent = nomeOriginal;
                                nomeElement.removeAttribute('data-original');
                            }
                            if (descricaoOriginal) {
                                descricaoElement.textContent = descricaoOriginal;
                                descricaoElement.removeAttribute('data-original');
                            }
                        }
                    });
                }
            });
        });
    </script>
    <style>
        #pesquisaModelo {
            border-left: none;
        }
        
        #pesquisaModelo:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .input-group-text {
            border-right: none;
        }
        
        .modelo-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .modelo-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        mark {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .mensagem-sem-resultado {
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</body>
</html>

