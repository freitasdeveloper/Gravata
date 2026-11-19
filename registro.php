<?php
/**
 * Página de Registro
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$erro = '';
$sucesso = '';

// Se já estiver logado, redirecionar
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter no mínimo 6 caracteres';
    } else {
        $resultado = $auth->registrar($nome, $email, $senha, $telefone);
        if ($resultado['sucesso']) {
            $sucesso = $resultado['mensagem'];
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
    <title>Cadastro - Legatus</title>
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
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <h2 class="mb-3">
                        <i class="bi bi-person-plus"></i> Criar Conta
                    </h2>
                    <p class="mb-0">Comece grátis hoje mesmo</p>
                </div>
                <div class="register-body">
                    <?php if ($erro): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($sucesso): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome Completo <span class="text-danger">*</span></label>
                            <div class="input-group-icon">
                                <i class="bi bi-person"></i>
                                <input type="text" class="form-control" name="nome" required 
                                       value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">E-mail <span class="text-danger">*</span></label>
                            <div class="input-group-icon">
                                <i class="bi bi-envelope"></i>
                                <input type="email" class="form-control" name="email" required 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Telefone</label>
                            <div class="input-group-icon">
                                <i class="bi bi-telephone"></i>
                                <input type="tel" class="form-control" name="telefone" 
                                       value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Senha <span class="text-danger">*</span></label>
                            <div class="input-group-icon">
                                <i class="bi bi-lock"></i>
                                <input type="password" class="form-control" name="senha" required minlength="6">
                            </div>
                            <small class="text-muted">Mínimo de 6 caracteres</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Confirmar Senha <span class="text-danger">*</span></label>
                            <div class="input-group-icon">
                                <i class="bi bi-lock-fill"></i>
                                <input type="password" class="form-control" name="confirmar_senha" required minlength="6">
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" required id="termos">
                            <label class="form-check-label" for="termos">
                                Eu concordo com os <a href="#">termos de uso</a> e <a href="#">política de privacidade</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-person-plus"></i> Criar Conta Grátis
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Já tem uma conta?</p>
                        <a href="login.php" class="btn btn-outline-primary mt-2">
                            <i class="bi bi-box-arrow-in-right"></i> Fazer login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

