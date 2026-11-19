<?php
/**
 * Página de Login
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$erro = '';

// Se já estiver logado, redirecionar
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $lembrar = isset($_POST['lembrar']);
    
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos';
    } else {
        $resultado = $auth->login($email, $senha, $lembrar);
        if ($resultado['sucesso']) {
            header('Location: dashboard.php');
            exit;
        } else {
            $erro = $resultado['mensagem'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Legatus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h2 class="mb-3">
                        <i class="bi bi-shield-check"></i> Legatus
                    </h2>
                    <p class="mb-0">Acesse sua conta</p>
                </div>
                <div class="login-body">
                    <?php if ($erro): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">E-mail</label>
                            <div class="input-group-icon">
                                <i class="bi bi-envelope"></i>
                                <input type="email" class="form-control" name="email" required 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Senha</label>
                            <div class="input-group-icon">
                                <i class="bi bi-lock"></i>
                                <input type="password" class="form-control" name="senha" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="lembrar" id="lembrar">
                            <label class="form-check-label" for="lembrar">
                                Lembrar-me
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right"></i> Entrar
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <a href="recuperar.php" class="text-decoration-none">
                            <i class="bi bi-key"></i> Esqueceu sua senha?
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Não tem uma conta?</p>
                        <a href="registro.php" class="btn btn-outline-primary mt-2">
                            <i class="bi bi-person-plus"></i> Criar conta grátis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

