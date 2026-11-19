# 📋 Guia de Instalação - Sistema Jurídico

Este guia passo a passo irá ajudá-lo a instalar o Sistema de Automação Documental Jurídica em seu servidor.

## 📋 Requisitos do Sistema

### Servidor
- **SO**: Windows Server, Linux ou macOS
- **Espaço em disco**: Mínimo 500MB livre
- **PHP**: Versão 7.4 ou superior
- **MySQL/MariaDB**: Versão 5.7 ou superior
- **Servidor Web**: Apache 2.4+ ou Nginx

### Extensões PHP Necessárias
- `pdo`
- `pdo_mysql`
- `mbstring`
- `json`
- `hash`
- `session`
- `openssl`

### Extensões PHP Recomendadas (para funcionalidades futuras)
- `gd` ou `imagick` (para processamento de imagens)
- `zip` (para exportação de arquivos)
- `curl` (para APIs externas)

## 🚀 Instalação Passo a Passo

### 1️⃣ Configurar o Ambiente

#### Windows (XAMPP/WAMP)
1. Baixe e instale o [XAMPP](https://www.apachefriends.org/) ou [WAMP](https://www.wampserver.com/)
2. Certifique-se de que Apache e MySQL estão rodando
3. Vá para a pasta `htdocs` (XAMPP) ou `www` (WAMP)

#### Linux (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-mbstring php-curl php-zip php-gd
sudo systemctl start apache2
sudo systemctl start mysql
```

#### macOS (Homebrew)
```bash
brew install apache mysql php
brew services start apache
brew services start mysql
```

### 2️⃣ Configurar o Banco de Dados

```bash
# Conectar ao MySQL
mysql -u root -p

# Criar o banco de dados
CREATE DATABASE sistema_juridico CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Criar usuário (opcional, mas recomendado)
CREATE USER 'juridico_user'@'localhost' IDENTIFIED BY 'sua_senha_segura';
GRANT ALL PRIVILEGES ON sistema_juridico.* TO 'juridico_user'@'localhost';
FLUSH PRIVILEGES;

# Sair do MySQL
exit;
```

### 3️⃣ Instalar os Arquivos

#### Opção A: Clone/Download do Projeto
```bash
# Se usar Git
git clone <url-do-repositorio> sistema-juridico
cd sistema-juridico

# OU baixe o .zip e extraia os arquivos
```

#### Opção B: Arquivos Finais
Coloque todos os arquivos na pasta do servidor web:
- **Windows**: `C:\xampp\htdocs\Gravata\`
- **Linux**: `/var/www/html/gravata/`
- **macOS**: `/Library/WebServer/Documents/gravata/`

### 4️⃣ Importar Banco de Dados

```bash
# Importar estrutura e dados
mysql -u root -p sistema_juridico < database.sql

# Importar modelos de exemplo (opcional)
mysql -u root -p sistema_juridico < modelos_exemplo.sql
```

### 5️⃣ Configurar o Sistema

Edite o arquivo `config.php`:

```php
// Configurações de Banco de Dados
define('DB_HOST', 'localhost');        // Host do banco
define('DB_NAME', 'sistema_juridico'); // Nome do banco
define('DB_USER', 'root');             // Usuário do banco
define('DB_PASS', '');                 // Senha do banco

// URLs e Caminhos
define('BASE_URL', 'http://localhost/Gravata/'); // Ajuste conforme sua URL

// Segurança (IMPORTANTE EM PRODUÇÃO!)
define('SECRET_KEY', 'gere-uma-chave-aleatoria-longa-e-segura-aqui');
```

**⚠️ IMPORTANTE**: Em produção, altere a `SECRET_KEY` para uma chave longa e aleatória!

### 6️⃣ Configurar Permissões

#### Linux/macOS
```bash
sudo chmod -R 755 /caminho/para/sistema
sudo chmod -R 777 uploads/
sudo chmod -R 777 logs/
```

#### Windows
Certifique-se de que as pastas têm permissões de leitura/escrita:
- `uploads/`
- `logs/`

### 7️⃣ Configurar o Servidor Web

#### Apache (.htaccess)

Crie um arquivo `.htaccess` na raiz do projeto:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]

# Proteção de arquivos sensíveis
<FilesMatch "\.(sql|ini|conf)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Headers de segurança
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

#### Nginx

Adicione ao seu `nginx.conf`:

```nginx
location /gravata/ {
    root /var/www/html;
    index index.php;
    try_files $uri $uri/ /gravata/index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

### 8️⃣ Testar a Instalação

1. Acesse no navegador: `http://localhost/Gravata/`
2. Você deve ver a página inicial
3. Clique em "Cadastrar" e crie uma conta
4. Faça login
5. Teste gerar um documento

### 9️⃣ Verificar Funcionalidades

- [ ] Página inicial carrega corretamente
- [ ] Registro de novo usuário funciona
- [ ] Login funciona
- [ ] Dashboard aparece após login
- [ ] Visualização de documentos funciona
- [ ] Geração de documentos funciona
- [ ] Não há erros no log do PHP

## 🔧 Troubleshooting

### Erro: "Connection refused"
**Solução**: Verifique se o MySQL está rodando:
```bash
sudo systemctl status mysql  # Linux
# Ou verifique no XAMPP/WAMP Control Panel
```

### Erro: "Access denied for user"
**Solução**: Verifique usuário/senha em `config.php` e permissões do banco.

### Erro: "Undefined variable" ou "Undefined index"
**Solução**: Certifique-se de que todos os arquivos PHP foram carregados corretamente.

### Página em branco
**Solução**: 
1. Ative logs de erro do PHP
2. Verifique permissões dos arquivos
3. Verifique sintaxe do PHP

### Erro 500 Internal Server Error
**Solução**:
1. Verifique arquivo `.htaccess`
2. Verifique logs do Apache/Nginx
3. Verifique logs do PHP

## 🌐 Configuração para Produção

### Checklist de Segurança

- [ ] Alterar `SECRET_KEY` para valor aleatório forte
- [ ] Alterar senha padrão do admin
- [ ] Configurar HTTPS (SSL/TLS)
- [ ] Desabilitar `DEBUG` em `config.php`
- [ ] Configurar backup automático do banco
- [ ] Configurar firewalls apropriados
- [ ] Atualizar senhas padrão do MySQL
- [ ] Restringir acesso ao phpMyAdmin
- [ ] Configurar logs de segurança
- [ ] Validar e sanitizar todas as entradas

### Otimizações

1. **Cache**: Configure cache de PHP (APCu, OPcache)
2. **Minificação**: Minifique CSS/JS
3. **CDN**: Use CDN para assets estáticos
4. **Backup**: Configure backups automáticos
5. **Monitoramento**: Configure ferramentas de monitoramento

### Backup

Configure backups regulares:

```bash
# Backup do banco de dados
mysqldump -u root -p sistema_juridico > backup_$(date +%Y%m%d).sql

# Backup dos uploads
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/
```

## 📞 Suporte

Se encontrar problemas na instalação:

1. Verifique os logs: `logs/error.log`
2. Consulte o README.md
3. Entre em contato com suporte técnico

## 🎉 Conclusão

Após completar todos os passos, seu sistema estará pronto para uso!

**Próximos passos recomendados**:
1. Personalizar o design
2. Adicionar mais modelos de documentos
3. Configurar integração com gateway de pagamento
4. Implementar funcionalidades avançadas

