<?php
/**
 * Sistema de Autenticação
 */

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Registrar novo usuário
     */
    public function registrar($nome, $email, $senha, $telefone = null) {
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['sucesso' => false, 'mensagem' => 'E-mail inválido'];
        }
        
        // Verificar se email já existe
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['sucesso' => false, 'mensagem' => 'E-mail já cadastrado'];
        }
        
        // Criptografar senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        try {
            // Inserir usuário com email já verificado
            $stmt = $this->db->prepare("
                INSERT INTO usuarios (nome, email, senha, telefone, plano_id, email_verificado) 
                VALUES (?, ?, ?, ?, 1, 1)
            ");
            $stmt->execute([$nome, $email, $senhaHash, $telefone]);
            
            return ['sucesso' => true, 'mensagem' => 'Cadastro realizado com sucesso!'];
        } catch (PDOException $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro ao cadastrar usuário'];
        }
    }
    
    /**
     * Realizar login
     */
    public function login($email, $senha, $lembrar = false) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            return ['sucesso' => false, 'mensagem' => 'E-mail ou senha incorretos'];
        }
        
        // Criar sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        $_SESSION['usuario_plano'] = $usuario['plano_id'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Lembrar-me
        if ($lembrar) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
            $stmt = $this->db->prepare("UPDATE usuarios SET token_recuperacao = ? WHERE id = ?");
            $stmt->execute([$token, $usuario['id']]);
        }
        
        // Log de login
        $this->registrarLog($usuario['id'], 'login', 'Login realizado');
        
        // Reset mensal se necessário
        $this->verificarResetMensal($usuario);
        
        return ['sucesso' => true, 'mensagem' => 'Login realizado com sucesso'];
    }
    
    /**
     * Verificar se usuário está logado
     */
    public function isLoggedIn() {
        return isset($_SESSION['usuario_id']);
    }
    
    /**
     * Verificar se é admin
     */
    public function isAdmin() {
        return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin';
    }
    
    /**
     * Obter usuário logado
     */
    public function getUsuario() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Garantir que o email esteja presente (usar da sessão se não estiver no banco)
        if ($usuario && empty($usuario['email']) && isset($_SESSION['usuario_email'])) {
            $usuario['email'] = $_SESSION['usuario_email'];
        }
        
        return $usuario;
    }
    
    /**
     * Fazer logout
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->registrarLog($_SESSION['usuario_id'], 'logout', 'Logout realizado');
        }
        
        session_unset();
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    /**
     * Solicitar recuperação de senha
     */
    public function solicitarRecuperacao($email) {
        $stmt = $this->db->prepare("SELECT id, nome FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            // Por segurança, retornar sucesso mesmo se não encontrar
            return ['sucesso' => true, 'mensagem' => 'Se o e-mail existir, você receberá instruções'];
        }
        
        $token = bin2hex(random_bytes(32));
        $expiracao = date('Y-m-d H:i:s', time() + TOKEN_EXPIRATION);
        
        $stmt = $this->db->prepare("UPDATE usuarios SET token_recuperacao = ?, token_expiracao = ? WHERE id = ?");
        $stmt->execute([$token, $expiracao, $usuario['id']]);
        
        $this->enviarEmailRecuperacao($email, $usuario['nome'], $token);
        
        return ['sucesso' => true, 'mensagem' => 'E-mail de recuperação enviado'];
    }
    
    /**
     * Redefinir senha
     */
    public function redefinirSenha($token, $novaSenha) {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE token_recuperacao = ? AND token_expiracao > NOW()");
        $stmt->execute([$token]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return ['sucesso' => false, 'mensagem' => 'Token inválido ou expirado'];
        }
        
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("UPDATE usuarios SET senha = ?, token_recuperacao = NULL, token_expiracao = NULL WHERE id = ?");
        $stmt->execute([$senhaHash, $usuario['id']]);
        
        $this->registrarLog($usuario['id'], 'senha_redefinida', 'Senha redefinida com sucesso');
        
        return ['sucesso' => true, 'mensagem' => 'Senha redefinida com sucesso'];
    }
    
    /**
     * Verificar CSRF Token
     */
    public function verificarCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Registrar log de atividade
     */
    public function registrarLog($usuarioId, $tipo, $acao, $descricao = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO logs (usuario_id, tipo, acao, descricao, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $usuarioId,
                $tipo,
                $acao,
                $descricao,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (PDOException $e) {
            // Log em arquivo se falhar
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar e resetar contador mensal
     */
    private function verificarResetMensal($usuario) {
        $hoje = date('Y-m-d');
        $dataReset = $usuario['ultimo_reset_mes'];
        
        // Verificar se é um novo mês
        if (!$dataReset || date('Y-m', strtotime($dataReset)) < date('Y-m')) {
            $stmt = $this->db->prepare("UPDATE usuarios SET documentos_gerados_mes = 0, ultimo_reset_mes = ? WHERE id = ?");
            $stmt->execute([$hoje, $usuario['id']]);
        }
    }
    
    /**
     * Enviar email de verificação
     */
    private function enviarEmailVerificacao($email, $nome, $token) {
        // Implementação simplificada - em produção usar PHPMailer ou similar
        if (ENVIRONMENT === 'development') {
            // Em desenvolvimento, apenas loga sem enviar email
            error_log("Email de verificação para $email: " . BASE_URL . "verificar.php?token=" . $token);
            return true;
        }
        $link = BASE_URL . "verificar.php?token=" . $token;
        $mensagem = "Olá $nome,\n\nClique no link abaixo para verificar seu e-mail:\n$link";
        @mail($email, "Verificação de E-mail", $mensagem);
        return true;
    }
    
    /**
     * Enviar email de recuperação
     */
    private function enviarEmailRecuperacao($email, $nome, $token) {
        if (ENVIRONMENT === 'development') {
            // Em desenvolvimento, apenas loga sem enviar email
            error_log("Email de recuperação para $email: " . BASE_URL . "recuperar.php?token=" . $token);
            return true;
        }
        $link = BASE_URL . "recuperar.php?token=" . $token;
        $mensagem = "Olá $nome,\n\nClique no link abaixo para redefinir sua senha:\n$link\n\nO link expira em 24 horas.";
        @mail($email, "Recuperação de Senha", $mensagem);
        return true;
    }
}

