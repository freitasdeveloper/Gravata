# Sistema de Automação Documental Jurídica

Sistema web completo em PHP para geração automatizada de documentos jurídicos profissionais, com controle de acesso baseado em planos de assinatura.

## 🎯 Características Principais

### Módulos Implementados

1. **Módulo de Autenticação**
   - Cadastro e login seguro
   - Recuperação de senha
   - Verificação de e-mail
   - Sessões JWT-like com proteção CSRF

2. **Sistema de Planos**
   - **Grátis**: 5 documentos/mês, modelos básicos
   - **Pro 1**: Documentos ilimitados, modelos tributários e trabalhistas
   - **Pro 2**: Todos do Pro 1 + modelos avançados e templates personalizados
   - **Pro X**: Acesso total + geração em massa e interface customizável

3. **Geração de Documentos**
   - Templates dinâmicos com processamento de variáveis
   - Sistema de categorias (Civil, Criminal, Trabalhista, Tributário, etc.)
   - Histórico e versionamento de documentos
   - Controle de limites por plano

4. **Dashboard e Interface**
   - Design profissional e responsivo
   - Menu lateral fixo
   - Estatísticas em tempo real
   - Cores sóbrias (azul escuro, cinza, branco)

## 📁 Estrutura do Projeto

```
Gravata/
├── config.php                 # Configurações principais
├── database.sql               # Estrutura do banco de dados
├── index.php                  # Página inicial
├── login.php                  # Login
├── registro.php               # Cadastro
├── recuperar.php              # Recuperação de senha
├── dashboard.php              # Painel do usuário
├── logout.php                 # Logout
├── includes/
│   ├── database.php          # Classe de conexão com BD
│   ├── auth.php              # Sistema de autenticação
│   ├── plano.php             # Gerenciamento de planos
│   └── documento.php         # Geração de documentos
├── uploads/                   # Arquivos enviados
└── logs/                      # Logs do sistema
```

## 🚀 Instalação

### Requisitos
- PHP 7.4 ou superior
- MySQL/MariaDB 5.7 ou superior
- Apache/Nginx com mod_rewrite
- Extensões PHP: PDO, pdo_mysql

### Passo a Passo

1. **Clone ou baixe o projeto**
```bash
cd C:\Users\user\Desktop\Gravata
```

2. **Configure o banco de dados**
```bash
# Importe o arquivo database.sql
mysql -u root -p < database.sql
```

3. **Configure as credenciais**
Edite `config.php` e ajuste:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_juridico');
define('DB_USER', 'root');
define('DB_PASS', 'sua_senha');
define('BASE_URL', 'http://localhost/Gravata/');
```

4. **Configure permissões**
```bash
chmod 755 uploads/
chmod 755 logs/
```

5. **Acesse o sistema**
```
http://localhost/Gravata/
```

### Usuário Administrador Padrão
- **E-mail**: admin@sistema.com
- **Senha**: Admin@123

⚠️ **IMPORTANTE**: Altere a senha do admin logo após o primeiro acesso!

## 🔐 Segurança

- Senhas criptografadas com `password_hash()`
- Proteção CSRF em formulários
- Prepared statements (prevenção SQL Injection)
- Validação de dados de entrada
- Headers de segurança configurados
- Proteção contra XSS (htmlspecialchars)

## 📊 Estrutura de Banco de Dados

Principais tabelas:

- `planos` - Planos de assinatura disponíveis
- `usuarios` - Usuários do sistema
- `categorias_documentos` - Categorias de documentos
- `modelos_documentos` - Templates de documentos
- `documentos` - Documentos gerados
- `logs` - Registro de atividades
- `tickets` - Sistema de suporte

## 🎨 Tecnologias Utilizadas

- **Backend**: PHP 7.4+ (PDO)
- **Banco de Dados**: MySQL/MariaDB
- **Frontend**: Bootstrap 5.3
- **Ícones**: Bootstrap Icons
- **Estilo**: CSS customizado

## 📝 Funcionalidades Futuras

- [ ] Exportação para PDF/DOCX
- [ ] Sistema de tickets de suporte
- [ ] API REST para integrações
- [ ] Geração em massa
- [ ] Upload de logos personalizados
- [ ] Integração com gateways de pagamento
- [ ] Dashboard administrativo completo
- [ ] Notificações por e-mail
- [ ] Auditoria avançada

## 🤝 Contribuindo

Este é um projeto inicial em desenvolvimento. Sugestões e melhorias são bem-vindas!

## 📄 Licença

Sistema desenvolvido para fins educacionais e comerciais.

## 📞 Suporte

Para dúvidas ou problemas:
- Crie uma issue no repositório
- Entre em contato via e-mail de suporte configurado em `config.php`

## 🔄 Atualizações

- **v1.0.0** - Release inicial com módulos básicos
  - Autenticação completa
  - Sistema de planos
  - Geração de documentos
  - Dashboard funcional

