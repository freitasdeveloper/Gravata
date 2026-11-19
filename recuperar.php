<?php
/**
 * Recuperação de Senha
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$erro = '';
$sucesso = '';
$mostrarFormulario = true;
$token = $_GET['token'] ?? '';

// Se tiver token, mostrar formulário de redefinição
if ($token) {
    // Verificar token
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE token_recuperacao = ? AND token_expiracao > NOW()");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        $erro = 'Token inválido ou expirado';
        $mostrarFormulario = false;
    }
    
    // Processar redefinição
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_senha'])) {
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        
        if ($novaSenha !== $confirmarSenha) {
            $erro = 'As senhas não coincidem';
        } elseif (strlen($novaSenha) < 6) {
            $erro = 'A senha deve ter no mínimo 6 caracteres';
        } else {
            $resultado = $auth->redefinirSenha($token, $novaSenha);
            if ($resultado['sucesso']) {
                $sucesso = $resultado['mensagem'];
                $mostrarFormulario = false;
            } else {
                $erro = $resultado['mensagem'];
            }
        }
    }
} else {
    // Processar solicitação de recuperação
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $erro = 'Por favor, informe seu e-mail';
        } else {
            $resultado = $auth->solicitarRecuperacao($email);
            $sucesso = $resultado['mensagem'];
            $mostrarFormulario = false;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $token ? 'Redefinir Senha' : 'Recuperar Senha'; ?> - Legatus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --azul-escuro: #1a3a5c;
            --azul-medio: #2c5aa0;
            --azul-claro: #4a90e2;
        }
        
        body {
            background: linear-gradient(135deg, var(--azul-escuro) 0%, var(--azul-medio) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .recover-container {
            max-width: 450px;
            margin: 0 auto;
        }
        
        .recover-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .recover-header {
            background: linear-gradient(135deg, var(--azul-escuro) 0%, var(--azul-medio) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .recover-body {
            padding: 40px 30px;
        }
        
        .btn-primary {
            background: var(--azul-claro);
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: var(--azul-escuro);
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
        }
        
        .form-control:focus {
            border-color: var(--azul-claro);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="recover-container">
            <div class="recover-card">
                <div class="recover-header">
                    <h2 class="mb-3">
                        <i class="bi bi-key"></i> <?php echo $token ? 'Redefinir Senha' : 'Recuperar Senha'; ?>
                    </h2>
                    <p class="mb-0"><?php echo $token ? 'Digite sua nova senha' : 'Informe seu e-mail para recuperar'; ?></p>
                </div>
                <div class="recover-body">
                    <?php if ($erro): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($sucesso && !$mostrarFormulario): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="login.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Voltar ao Login
                            </a>
                        </div>
                    <?php elseif ($mostrarFormulario): ?>
                        <form method="POST" action="">
                            <?php if ($token): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nova Senha</label>
                                    <input type="password" class="form-control" name="nova_senha" required minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Confirmar Nova Senha</label>
                                    <input type="password" class="form-control" name="confirmar_senha" required minlength="6">
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">E-mail</label>
                                    <input type="email" class="form-control" name="email" required 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    <small class="text-muted">Você receberá um link para redefinir sua senha</small>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-check-lg"></i> <?php echo $token ? 'Redefinir Senha' : 'Enviar'; ?>
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Voltar ao Login
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

