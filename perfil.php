<?php
/**
 * Perfil do Usuário
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$auth = new Auth();

// Verificar se está logado
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$usuario = $auth->getUsuario();
$erro = '';
$sucesso = '';

// Processar alterações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    
    if (empty($nome)) {
        $erro = 'Nome é obrigatório';
    } else {
        $stmt = $db->prepare("UPDATE usuarios SET nome = ?, telefone = ? WHERE id = ?");
        if ($stmt->execute([$nome, $telefone, $usuario['id']])) {
            $sucesso = 'Perfil atualizado com sucesso!';
            $usuario['nome'] = $nome;
            $usuario['telefone'] = $telefone;
            $_SESSION['usuario_nome'] = $nome;
        } else {
            $erro = 'Erro ao atualizar perfil';
        }
    }
}

// Alterar senha
if (isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if (!password_verify($senha_atual, $usuario['senha'])) {
        $erro = 'Senha atual incorreta';
    } elseif ($nova_senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem';
    } elseif (strlen($nova_senha) < 6) {
        $erro = 'A senha deve ter no mínimo 6 caracteres';
    } else {
        $senhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        if ($stmt->execute([$senhaHash, $usuario['id']])) {
            $sucesso = 'Senha alterada com sucesso!';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Legatus</title>
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
                        <h5 class="mb-0">Meu Perfil</h5>
                    </div>
                </nav>
                
                <div class="container-fluid">
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
                    
                    <div class="row">
                        <!-- Informações Pessoais -->
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <i class="bi bi-person"></i> Informações Pessoais
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nome Completo</label>
                                            <input type="text" class="form-control" name="nome" required 
                                                   value="<?php echo htmlspecialchars($usuario['nome']); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">E-mail</label>
                                            <input type="email" class="form-control" disabled 
                                                   value="<?php echo htmlspecialchars($usuario['email'] ?? $_SESSION['usuario_email'] ?? ''); ?>">
                                            <small class="text-muted">O e-mail não pode ser alterado</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Telefone</label>
                                            <input type="tel" class="form-control" name="telefone" 
                                                   value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>">
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg"></i> Salvar Alterações
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Alterar Senha -->
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <i class="bi bi-lock"></i> Alterar Senha
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Senha Atual</label>
                                            <input type="password" class="form-control" name="senha_atual" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nova Senha</label>
                                            <input type="password" class="form-control" name="nova_senha" required minlength="6">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Confirmar Nova Senha</label>
                                            <input type="password" class="form-control" name="confirmar_senha" required minlength="6">
                                        </div>
                                        <button type="submit" name="alterar_senha" class="btn btn-warning">
                                            <i class="bi bi-key"></i> Alterar Senha
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Conta -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <i class="bi bi-person-circle"></i> Minha Conta
                                </div>
                                <div class="card-body">
                                    <div class="plano-card mb-3">
                                        <h3 class="mb-3">Sistema Gratuito</h3>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Acesso Completo
                                        </span>
                                    </div>
                                    <div class="text-center">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> 
                                            Membros desde <?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

