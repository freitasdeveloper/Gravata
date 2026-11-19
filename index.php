<?php
/**
 * Página inicial do Legatus
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$logado = $auth->isLoggedIn();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legatus - Sistema de Automação Documental Jurídica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shield-check"></i> Legatus
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($logado): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="documentos.php">
                                <i class="bi bi-file-earmark-text"></i> Documentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="perfil.php">
                                <i class="bi bi-person-circle"></i> Perfil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Entrar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="registro.php">
                                <i class="bi bi-person-plus"></i> Cadastrar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Automação Documental Jurídica Inteligente
                    </h1>
                    <p class="lead mb-4">
                        Simplifique a criação de documentos jurídicos profissionais. 
                        Petições, contratos, ofícios e muito mais em segundos.
                    </p>
                    <?php if (!$logado): ?>
                        <a href="registro.php" class="btn btn-light btn-lg">
                            Começar Grátis
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    <?php else: ?>
                        <a href="documentos.php" class="btn btn-light btn-lg">
                            Gerar Documento
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="bi bi-file-earmark-text-fill" style="font-size: 200px; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Por que escolher nosso sistema?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-lightning-charge feature-icon"></i>
                            <h4>Rápido e Eficiente</h4>
                            <p class="text-muted">
                                Gere documentos profissionais em segundos, 
                                economizando horas de trabalho manual.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-check-circle feature-icon"></i>
                            <h4>Modelos Atualizados</h4>
                            <p class="text-muted">
                                Templates revisados e atualizados conforme 
                                a legislação vigente.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-lock feature-icon"></i>
                            <h4>Seguro e Confiável</h4>
                            <p class="text-muted">
                                Seus dados protegidos com criptografia 
                                de ponta a ponta.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Legatus</h5>
                    <p class="text-muted mb-0">
                        © <?php echo date('Y'); ?> Todos os direitos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="contato.php" class="text-light text-decoration-none me-3">
                        <i class="bi bi-envelope"></i> Contato
                    </a>
                    <a href="suporte.php" class="text-light text-decoration-none">
                        <i class="bi bi-question-circle"></i> Suporte
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

