<?php
/**
 * Componente Sidebar reutilizável
 * Inclui menu principal, códigos jurídicos e configurações
 */

$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="col-md-2 sidebar">
    <div class="sidebar-brand">
        <h4><i class="bi bi-shield-check"></i> Legatus</h4>
    </div>
    <ul class="sidebar-menu">
        <!-- Menu Principal -->
        <li class="menu-section-item">
            <div class="menu-section-header" data-section="menu-principal">
                <span class="menu-label">Menu Principal</span>
                <i class="bi bi-chevron-down menu-toggle-icon"></i>
            </div>
            <ul class="menu-subitems" id="menu-principal-items">
                <li><a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-house-door"></i> <span>Início</span>
                </a></li>
                <li><a href="gerar.php" class="<?php echo $current_page == 'gerar.php' ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-plus"></i> <span>Gerar Documento</span>
                </a></li>
                <li><a href="documentos.php" class="<?php echo $current_page == 'documentos.php' ? 'active' : ''; ?>">
                    <i class="bi bi-files"></i> <span>Meus Documentos</span>
                </a></li>
                <li><a href="agenda.php" class="<?php echo $current_page == 'agenda.php' ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-event"></i> <span>Agenda</span>
                </a></li>
                <li><a href="https://teams.live.com/free" target="_blank" class="<?php echo $current_page == 'audiencia_virtual.php' ? 'active' : ''; ?>">
                    <i class="bi bi-camera-video"></i> <span>Audiência Virtual</span>
                </a></li>
                <li><a href="perfil.php" class="<?php echo $current_page == 'perfil.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person-circle"></i> <span>Meu Perfil</span>
                </a></li>
            </ul>
        </li>
        
        <!-- Códigos Jurídicos -->
        <li class="menu-section-item">
            <div class="menu-section-header" data-section="codigos-juridicos">
                <span class="menu-label">Códigos Jurídicos</span>
                <i class="bi bi-chevron-down menu-toggle-icon"></i>
            </div>
            <ul class="menu-subitems" id="codigos-juridicos-items">
                <li><a href="codigo_civil.php" class="<?php echo $current_page == 'codigo_civil.php' ? 'active' : ''; ?>">
                    <i class="bi bi-book"></i> <span>Código Civil</span>
                </a></li>
                <li><a href="codigo_penal.php" class="<?php echo $current_page == 'codigo_penal.php' ? 'active' : ''; ?>">
                    <i class="bi bi-shield-slash"></i> <span>Código Penal</span>
                </a></li>
                <li><a href="codigo_processo_civil.php" class="<?php echo $current_page == 'codigo_processo_civil.php' ? 'active' : ''; ?>">
                    <i class="bi bi-layout-text-window"></i> <span>Código de Processo Civil</span>
                </a></li>
                <li><a href="codigo_processo_penal.php" class="<?php echo $current_page == 'codigo_processo_penal.php' ? 'active' : ''; ?>">
                    <i class="bi bi-layout-text-window-reverse"></i> <span>Código de Processo Penal</span>
                </a></li>
                <li><a href="codigo_trabalhista.php" class="<?php echo $current_page == 'codigo_trabalhista.php' ? 'active' : ''; ?>">
                    <i class="bi bi-briefcase"></i> <span>CLT</span>
                </a></li>
                <li><a href="codigo_consumidor.php" class="<?php echo $current_page == 'codigo_consumidor.php' ? 'active' : ''; ?>">
                    <i class="bi bi-cart-check"></i> <span>Código do Consumidor</span>
                </a></li>
                <li><a href="constituicao.php" class="<?php echo $current_page == 'constituicao.php' ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-medical"></i> <span>Constituição Federal</span>
                </a></li>
            </ul>
        </li>
        
        <!-- Literatura -->
        <li class="menu-section-item">
            <div class="menu-section-header" data-section="literatura">
                <span class="menu-label">Literatura</span>
                <i class="bi bi-chevron-down menu-toggle-icon"></i>
            </div>
            <ul class="menu-subitems" id="literatura-items">
                <li><a href="literatura.php" class="<?php echo $current_page == 'literatura.php' ? 'active' : ''; ?>">
                    <i class="bi bi-book-half"></i> <span>Biblioteca</span>
                </a></li>
                <li><a href="processos.php" class="<?php echo $current_page == 'processos.php' ? 'active' : ''; ?>">
                    <i class="bi bi-folder-check"></i> <span>Processos</span>
                </a></li>
            </ul>
        </li>
        
        <?php if ($auth->isAdmin()): ?>
        <li class="menu-section menu-section-static">
            <span class="menu-label">Administração</span>
        </li>
        <li><a href="admin/">
            <i class="bi bi-gear-fill"></i> <span>Painel Admin</span>
        </a></li>
        <?php endif; ?>
        
        <li class="menu-section menu-section-bottom menu-section-static">
            <span class="menu-label">Conta</span>
        </li>
        <li><a href="logout.php" class="menu-logout">
            <i class="bi bi-box-arrow-right"></i> <span>Sair</span>
        </a></li>
    </ul>
    
    <script>
        // Função para inicializar menu retrátil
        (function() {
            // Salvar/recuperar estado do localStorage
            function salvarEstado(section, isOpen) {
                localStorage.setItem('menu_' + section, isOpen ? 'open' : 'closed');
            }
            
            function recuperarEstado(section) {
                const estado = localStorage.getItem('menu_' + section);
                // Por padrão, todas as seções começam abertas
                return estado !== 'closed';
            }
            
            // Função para animar altura
            function toggleSection(items, icon, isOpen) {
                if (isOpen) {
                    // Fechar
                    items.style.maxHeight = items.scrollHeight + 'px';
                    setTimeout(() => {
                        items.style.maxHeight = '0';
                        items.style.opacity = '0';
                    }, 10);
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                } else {
                    // Abrir
                    items.style.maxHeight = '0';
                    items.style.opacity = '0';
                    items.style.display = 'block';
                    setTimeout(() => {
                        items.style.maxHeight = items.scrollHeight + 'px';
                        items.style.opacity = '1';
                    }, 10);
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                }
            }
            
            // Inicializar seções
            const secoes = ['menu-principal', 'codigos-juridicos', 'literatura'];
            
            secoes.forEach(function(sectionId) {
                const header = document.querySelector('[data-section="' + sectionId + '"]');
                const items = document.getElementById(sectionId + '-items');
                const icon = header.querySelector('.menu-toggle-icon');
                
                if (!header || !items) return;
                
                // Preparar para animação
                items.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
                
                // Recuperar estado salvo
                const isOpen = recuperarEstado(sectionId);
                
                // Configurar estado inicial
                if (!isOpen) {
                    items.style.maxHeight = '0';
                    items.style.opacity = '0';
                    items.style.display = 'none';
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                } else {
                    items.style.maxHeight = items.scrollHeight + 'px';
                    items.style.opacity = '1';
                    items.style.display = 'block';
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                }
                
                // Adicionar evento de clique
                header.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isCurrentlyOpen = items.style.opacity !== '0' && items.style.maxHeight !== '0';
                    
                    toggleSection(items, icon, isCurrentlyOpen);
                    salvarEstado(sectionId, !isCurrentlyOpen);
                    
                    // Após animação, ajustar para display none se fechado
                    if (isCurrentlyOpen) {
                        setTimeout(() => {
                            if (items.style.maxHeight === '0') {
                                items.style.display = 'none';
                            }
                        }, 300);
                    }
                });
                
                // Tornar header clicável visualmente
                header.style.cursor = 'pointer';
            });
        })();
    </script>
</nav>

