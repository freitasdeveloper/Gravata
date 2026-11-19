-- Tabela de tipos de eventos da agenda
CREATE TABLE IF NOT EXISTS `tipos_eventos_agenda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `icone` varchar(50) DEFAULT 'calendar-event',
  `cor` varchar(7) DEFAULT '#007bff',
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados iniciais dos tipos de eventos
INSERT INTO `tipos_eventos_agenda` (`nome`, `icone`, `cor`, `ordem`) VALUES
('Reunião', 'people', '#007bff', 1),
('Consultoria', 'briefcase', '#28a745', 2),
('Audiência', 'gavel', '#dc3545', 3),
('Visita', 'house-door', '#17a2b8', 4),
('Compromisso', 'calendar-check', '#ffc107', 5),
('Ligação', 'telephone', '#6f42c1', 6),
('Tarefa', 'list-check', '#fd7e14', 7),
('Outros', 'three-dots', '#6c757d', 8);

-- Tabela de eventos da agenda
CREATE TABLE IF NOT EXISTS `eventos_agenda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tipo_evento_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_evento` datetime NOT NULL,
  `data_fim` datetime DEFAULT NULL,
  `local` varchar(255) DEFAULT NULL,
  `participantes` text DEFAULT NULL COMMENT 'JSON com participantes',
  `lembrete_email` tinyint(1) DEFAULT 1 COMMENT 'Enviar email 1 dia antes',
  `lembrete_whatsapp` tinyint(1) DEFAULT 0 COMMENT 'Enviar WhatsApp 1 dia antes (futuro)',
  `email_enviado` tinyint(1) DEFAULT 0 COMMENT 'Email de lembrete já foi enviado',
  `whatsapp_enviado` tinyint(1) DEFAULT 0 COMMENT 'WhatsApp de lembrete já foi enviado',
  `status` enum('agendado','em_andamento','concluido','cancelado') DEFAULT 'agendado',
  `prioridade` enum('baixa','media','alta') DEFAULT 'media',
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_usuario_evento` (`usuario_id`),
  KEY `fk_tipo_evento` (`tipo_evento_id`),
  KEY `idx_data_evento` (`data_evento`),
  CONSTRAINT `fk_usuario_evento` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tipo_evento` FOREIGN KEY (`tipo_evento_id`) REFERENCES `tipos_eventos_agenda` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de histórico de notificações enviadas
CREATE TABLE IF NOT EXISTS `notificacoes_agenda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `tipo` enum('email','whatsapp') NOT NULL,
  `data_envio` datetime NOT NULL,
  `status` enum('enviado','falhou','pendente') DEFAULT 'pendente',
  `erro` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_evento_notificacao` (`evento_id`),
  CONSTRAINT `fk_evento_notificacao` FOREIGN KEY (`evento_id`) REFERENCES `eventos_agenda` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

