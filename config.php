<?php
/**
 * Configurações Principais do Sistema
 * Sistema de Automação Documental Jurídica
 */

// Configurações de Ambiente
define('ENVIRONMENT', 'development'); // development, production
define('DEBUG', true);

// UTF-8 Configuration
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

// URLs e Caminhos
define('BASE_URL', 'http://localhost/Gravata/');
define('BASE_PATH', __DIR__ . '/');

// Configurações de Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_juridico');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Segurança
define('SECRET_KEY', 'your-secret-key-change-in-production-make-it-long-and-random');
define('TOKEN_EXPIRATION', 3600 * 24); // 24 horas
define('HASH_ALGO', 'sha256');

// Sessões
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // 1 em produção com HTTPS
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de Upload
define('UPLOAD_DIR', BASE_PATH . 'uploads/');
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['png', 'jpg', 'jpeg', 'gif', 'pdf', 'doc', 'docx']);

// Configurações de Email
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_USER', 'noreply@sistema.com');
define('SMTP_PASS', 'your-email-password');
define('SMTP_PORT', 587);
define('EMAIL_FROM', 'noreply@sistema.com');
define('EMAIL_FROM_NAME', 'Legatus');

// Pagamento
define('PAGSEGURO_EMAIL', 'seu-email@pagseguro.com');
define('PAGSEGURO_TOKEN', 'seu-token');

// Limites por Plano
define('LIMITE_DOCUMENTOS_GRATIS', 5);
define('DIAS_EXPIRACAO_LINK', 7);

// Período de Reset Mensal
define('DATA_RESET_MENSAL', 1); // Dia 1 de cada mês

// Configurações de Exibição
define('ITENS_POR_PAGINA', 20);

// Logs
define('LOG_ERROR', true);
define('LOG_PATH', BASE_PATH . 'logs/');

// Verificar e criar diretórios necessários
$dirs = ['uploads', 'uploads/documentos', 'uploads/logos', 'uploads/anexos', 'logs'];
foreach ($dirs as $dir) {
    if (!is_dir(BASE_PATH . $dir)) {
        mkdir(BASE_PATH . $dir, 0755, true);
    }
}

