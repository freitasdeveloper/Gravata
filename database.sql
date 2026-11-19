-- Sistema de Automação Documental Jurídica
-- Banco de dados MySQL/MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Estrutura da tabela de planos
CREATE TABLE IF NOT EXISTS `planos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `codigo` varchar(20) NOT NULL UNIQUE,
  `limite_documentos` int(11) DEFAULT -1 COMMENT '-1 = ilimitado',
  `modelos_basicos` tinyint(1) DEFAULT 1,
  `modelos_tributarios` tinyint(1) DEFAULT 0,
  `modelos_trabalhistas` tinyint(1) DEFAULT 0,
  `modelos_avancados` tinyint(1) DEFAULT 0,
  `templates_personalizados` tinyint(1) DEFAULT 0,
  `geracao_em_massa` tinyint(1) DEFAULT 0,
  `interface_customizavel` tinyint(1) DEFAULT 0,
  `suporte` varchar(20) DEFAULT 'nenhum' COMMENT 'nenhum, básico, premium',
  `preco_mensal` decimal(10,2) DEFAULT 0.00,
  `preco_anual` decimal(10,2) DEFAULT 0.00,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados iniciais dos planos
INSERT INTO `planos` (`nome`, `codigo`, `limite_documentos`, `modelos_basicos`, `modelos_tributarios`, `modelos_trabalhistas`, `modelos_avancados`, `templates_personalizados`, `geracao_em_massa`, `interface_customizavel`, `suporte`, `preco_mensal`, `preco_anual`) VALUES
('Grátis', 'GRATIS', 5, 1, 0, 0, 0, 0, 0, 0, 'nenhum', 0.00, 0.00),
('Pro 1', 'PRO1', -1, 1, 1, 1, 0, 0, 0, 0, 'básico', 49.90, 499.00),
('Pro 2', 'PRO2', -1, 1, 1, 1, 1, 1, 0, 0, 'básico', 99.90, 999.00),
('Pro X', 'PROX', -1, 1, 1, 1, 1, 1, 1, 1, 'premium', 199.90, 1999.00);

-- Estrutura da tabela de usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `tipo` enum('usuario','admin') DEFAULT 'usuario',
  `plano_id` int(11) DEFAULT 1 COMMENT 'ID do plano atual',
  `assinatura_ativa` tinyint(1) DEFAULT 1,
  `data_vencimento` date DEFAULT NULL,
  `documentos_gerados_mes` int(11) DEFAULT 0,
  `ultimo_reset_mes` date DEFAULT NULL,
  `email_verificado` tinyint(1) DEFAULT 0,
  `token_verificacao` varchar(100) DEFAULT NULL,
  `token_recuperacao` varchar(100) DEFAULT NULL,
  `token_expiracao` datetime DEFAULT NULL,
  `api_key` varchar(100) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_plano` (`plano_id`),
  CONSTRAINT `fk_plano` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuário administrador padrão (senha: Admin@123)
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `tipo`, `plano_id`, `email_verificado`, `ativo`) VALUES
('Administrador', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 4, 1, 1);

-- Estrutura da tabela de categorias de documentos
CREATE TABLE IF NOT EXISTS `categorias_documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `descricao` text DEFAULT NULL,
  `icone` varchar(50) DEFAULT 'file-text',
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados iniciais de categorias
INSERT INTO `categorias_documentos` (`nome`, `slug`, `descricao`, `icone`, `ordem`) VALUES
('Civil', 'civil', 'Documentos relacionados ao Direito Civil', 'gavel', 1),
('Criminal', 'criminal', 'Documentos relacionados ao Direito Criminal', 'shield', 2),
('Trabalhista', 'trabalhista', 'Documentos relacionados ao Direito do Trabalho', 'briefcase', 3),
('Tributário', 'tributario', 'Documentos relacionados ao Direito Tributário', 'calculator', 4),
('Administrativo', 'administrativo', 'Documentos relacionados ao Direito Administrativo', 'file-alt', 5),
('Família', 'familia', 'Documentos relacionados ao Direito de Família', 'heart', 6);

-- Estrutura da tabela de modelos de documentos
CREATE TABLE IF NOT EXISTS `modelos_documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `descricao` text DEFAULT NULL,
  `tipo_plano_necessario` enum('basico','pro1','pro2','prox') DEFAULT 'basico',
  `conteudo_template` longtext NOT NULL COMMENT 'Template com placeholders',
  `variaveis` text DEFAULT NULL COMMENT 'JSON com variáveis disponíveis',
  `tags` varchar(255) DEFAULT NULL,
  `versao` decimal(5,2) DEFAULT 1.00,
  `autor` varchar(100) DEFAULT NULL,
  `visualizacoes` int(11) DEFAULT 0,
  `downloads` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_categoria` (`categoria_id`),
  CONSTRAINT `fk_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_documentos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura da tabela de documentos gerados
CREATE TABLE IF NOT EXISTS `documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `modelo_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `numero_processo` varchar(50) DEFAULT NULL,
  `conteudo_final` longtext NOT NULL,
  `variaveis_usadas` text DEFAULT NULL COMMENT 'JSON com variáveis utilizadas',
  `formato` enum('docx','pdf','html') DEFAULT 'docx',
  `tamanho_arquivo` int(11) DEFAULT 0,
  `link_publico` varchar(100) DEFAULT NULL UNIQUE,
  `hash_seguranca` varchar(64) DEFAULT NULL,
  `data_vencimento_link` datetime DEFAULT NULL,
  `versao` int(11) DEFAULT 1,
  `editar` tinyint(1) DEFAULT 1,
  `compartilhar` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_usuario_doc` (`usuario_id`),
  KEY `fk_modelo_doc` (`modelo_id`),
  CONSTRAINT `fk_usuario_doc` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_modelo_doc` FOREIGN KEY (`modelo_id`) REFERENCES `modelos_documentos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura da tabela de histórico de versões
CREATE TABLE IF NOT EXISTS `historico_documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `versao` int(11) NOT NULL,
  `conteudo_anterior` longtext NOT NULL,
  `alteracoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_doc_historico` (`documento_id`),
  KEY `fk_user_historico` (`usuario_id`),
  CONSTRAINT `fk_doc_historico` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`),
  CONSTRAINT `fk_user_historico` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura da tabela de tickets de suporte
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `categoria` varchar(50) DEFAULT 'geral',
  `prioridade` enum('baixa','media','alta','urgente') DEFAULT 'media',
  `status` enum('aberto','em_andamento','respondido','resolvido','fechado') DEFAULT 'aberto',
  `atribuido_para` int(11) DEFAULT NULL COMMENT 'Admin responsável',
  `ultima_resposta` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_usuario_ticket` (`usuario_id`),
  KEY `fk_atribuido` (`atribuido_para`),
  CONSTRAINT `fk_usuario_ticket` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_atribuido` FOREIGN KEY (`atribuido_para`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura da tabela de mensagens de tickets
CREATE TABLE IF NOT EXISTS `mensagens_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `anexo` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ticket_msg` (`ticket_id`),
  KEY `fk_user_msg` (`usuario_id`),
  CONSTRAINT `fk_ticket_msg` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`),
  CONSTRAINT `fk_user_msg` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura da tabela de logs do sistema
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL COMMENT 'login, documento_gerado, erro, etc',
  `acao` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_usuario_log` (`usuario_id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_data` (`created_at`),
  CONSTRAINT `fk_usuario_log` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura da tabela de notícias/atualizações
CREATE TABLE IF NOT EXISTS `noticias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `conteudo` text NOT NULL,
  `tipo` enum('noticia','atualizacao','manutencao','novidade') DEFAULT 'noticia',
  `destaque` tinyint(1) DEFAULT 0,
  `autor_id` int(11) DEFAULT NULL,
  `visualizacoes` int(11) DEFAULT 0,
  `publicado` tinyint(1) DEFAULT 0,
  `data_publicacao` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_autor_noticia` (`autor_id`),
  CONSTRAINT `fk_autor_noticia` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura da tabela de configurações do sistema
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL UNIQUE,
  `valor` text DEFAULT NULL,
  `tipo` varchar(20) DEFAULT 'string',
  `descricao` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configurações iniciais
INSERT INTO `configuracoes` (`chave`, `valor`, `tipo`, `descricao`) VALUES
('nome_sistema', 'Sistema Jurídico', 'string', 'Nome do sistema'),
('manutencao_ativa', '0', 'boolean', 'Modo de manutenção ativado'),
('email_suporte', 'suporte@sistema.com', 'string', 'E-mail de suporte'),
('telefone_suporte', '(00) 0000-0000', 'string', 'Telefone de suporte'),
('pgto_gateway', 'pagseguro', 'string', 'Gateway de pagamento padrão');

-- Estrutura da tabela de planos de fundo personalizados (Pro 2/Pro X)
CREATE TABLE IF NOT EXISTS `logos_usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `caminho` varchar(500) NOT NULL,
  `tipo` enum('logo','assinatura','timbre') DEFAULT 'logo',
  `principal` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user_logo` (`usuario_id`),
  CONSTRAINT `fk_user_logo` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para performance
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_documentos_usuario ON documentos(usuario_id, created_at);
CREATE INDEX idx_tickets_status ON tickets(status, prioridade);
CREATE INDEX idx_logs_usuario_tipo ON logs(usuario_id, tipo);

