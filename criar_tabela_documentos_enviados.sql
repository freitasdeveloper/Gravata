-- Tabela para armazenar documentos enviados pelo usuário
CREATE TABLE IF NOT EXISTS `documentos_enviados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `caminho_arquivo` varchar(500) NOT NULL,
  `tipo_arquivo` varchar(50) NOT NULL,
  `tamanho` int(11) NOT NULL COMMENT 'Tamanho em bytes',
  `categoria` varchar(100) DEFAULT NULL COMMENT 'Categoria/Pasta personalizada',
  `descricao` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `compartilhar` tinyint(1) DEFAULT 0,
  `link_publico` varchar(100) DEFAULT NULL UNIQUE,
  `hash_seguranca` varchar(64) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_usuario_enviado` (`usuario_id`),
  KEY `idx_categoria` (`categoria`),
  CONSTRAINT `fk_usuario_enviado` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

