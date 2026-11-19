<?php
/**
 * Classe para gerenciamento de documentos jurídicos
 */

class DocumentoGerador {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Listar categorias de documentos
     */
    public function listarCategorias() {
        $stmt = $this->db->query("SELECT * FROM categorias_documentos WHERE ativo = 1 ORDER BY ordem ASC");
        return $stmt->fetchAll();
    }
    
    /**
     * Listar modelos por categoria
     * Todos os modelos estão disponíveis gratuitamente para todos os usuários
     */
    public function listarModelos($categoriaId = null, $usuarioId = null) {
        $sql = "SELECT md.*, cd.nome as categoria_nome 
                FROM modelos_documentos md 
                JOIN categorias_documentos cd ON md.categoria_id = cd.id 
                WHERE md.ativo = 1";
        
        if ($categoriaId) {
            $sql .= " AND md.categoria_id = $categoriaId";
        }
        
        $sql .= " ORDER BY cd.ordem ASC, md.nome ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obter modelo específico
     */
    public function getModelo($modeloId) {
        $stmt = $this->db->prepare("
            SELECT md.*, cd.nome as categoria_nome 
            FROM modelos_documentos md 
            JOIN categorias_documentos cd ON md.categoria_id = cd.id 
            WHERE md.id = ? AND md.ativo = 1
        ");
        $stmt->execute([$modeloId]);
        return $stmt->fetch();
    }
    
    /**
     * Verificar acesso do usuário ao modelo
     * Todos os modelos estão disponíveis gratuitamente
     */
    public function verificarAcessoModelo($modeloId, $usuarioId) {
        $modelo = $this->getModelo($modeloId);
        return $modelo !== false;
    }
    
    /**
     * Gerar documento
     * Sistema totalmente gratuito - sem limitações
     */
    public function gerarDocumento($usuarioId, $modeloId, $variaveis) {
        // Verificar acesso ao modelo (sempre permitido)
        if (!$this->verificarAcessoModelo($modeloId, $usuarioId)) {
            return ['sucesso' => false, 'mensagem' => 'Modelo não encontrado'];
        }
        
        // Obter modelo
        $modelo = $this->getModelo($modeloId);
        if (!$modelo) {
            return ['sucesso' => false, 'mensagem' => 'Modelo não encontrado'];
        }
        
        // Processar template
        $conteudoFinal = $this->processarTemplate($modelo['conteudo_template'], $variaveis);
        
        // Salvar documento
        $hash = bin2hex(random_bytes(16));
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO documentos (usuario_id, modelo_id, titulo, numero_processo, conteudo_final, variaveis_usadas, hash_seguranca) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $titulo = $variaveis['titulo'] ?? $modelo['nome'];
            $numeroProcesso = $variaveis['numero_processo'] ?? null;
            $variaveisJson = json_encode($variaveis);
            
            $stmt->execute([
                $usuarioId,
                $modeloId,
                $titulo,
                $numeroProcesso,
                $conteudoFinal,
                $variaveisJson,
                $hash
            ]);
            
            $documentoId = $this->db->lastInsertId();
            
            // Contador de documentos (sem limitação)
            
            // Incrementar downloads do modelo
            $stmt = $this->db->prepare("UPDATE modelos_documentos SET downloads = downloads + 1 WHERE id = ?");
            $stmt->execute([$modeloId]);
            
            // Log
            $this->registrarLog($usuarioId, 'documento_gerado', "Documento gerado: $titulo");
            
            return ['sucesso' => true, 'documento_id' => $documentoId, 'hash' => $hash];
            
        } catch (PDOException $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro ao gerar documento'];
        }
    }
    
    /**
     * Processar template com variáveis
     */
    private function processarTemplate($template, $variaveis) {
        $conteudo = $template;
        
        // Substituir placeholders {{variavel}}
        foreach ($variaveis as $chave => $valor) {
            $conteudo = str_replace('{{' . $chave . '}}', $valor, $conteudo);
        }
        
        // Processar funções especiais
        $conteudo = $this->processarFuncoesEspeciais($conteudo, $variaveis);
        
        return $conteudo;
    }
    
    /**
     * Processar funções especiais no template
     */
    private function processarFuncoesEspeciais($conteudo, $variaveis) {
        // Data atual formatada
        $conteudo = preg_replace_callback('/{{data_atual\((.*?)\)}}/', function($matches) {
            $formato = $matches[1] ?: 'd/m/Y';
            return date($formato);
        }, $conteudo);
        
        // Data formatada
        if (isset($variaveis['data'])) {
            $conteudo = preg_replace_callback('/{{data_formatada\((.*?)\)}}/', function($matches) use ($variaveis) {
                $formato = $matches[1] ?: 'd/m/Y';
                return date($formato, strtotime($variaveis['data']));
            }, $conteudo);
        }
        
        // Número em extenso (simplificado)
        $conteudo = preg_replace_callback('/{{numero_extenso\((.*?)\)}}/', function($matches) {
            return $this->numeroPorExtenso($matches[1]);
        }, $conteudo);
        
        return $conteudo;
    }
    
    /**
     * Converter número para extenso (simplificado)
     */
    private function numeroPorExtenso($numero) {
        $numeros = ['zero', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove', 'dez'];
        return $numeros[intval($numero)] ?? $numero;
    }
    
    /**
     * Obter documento por ID
     */
    public function getDocumento($documentoId, $usuarioId = null) {
        $sql = "SELECT d.*, md.nome as modelo_nome, cd.nome as categoria_nome 
                FROM documentos d 
                JOIN modelos_documentos md ON d.modelo_id = md.id 
                JOIN categorias_documentos cd ON md.categoria_id = cd.id 
                WHERE d.id = ?";
        
        if ($usuarioId) {
            $sql .= " AND d.usuario_id = ?";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($usuarioId) {
            $stmt->execute([$documentoId, $usuarioId]);
        } else {
            $stmt->execute([$documentoId]);
        }
        
        return $stmt->fetch();
    }
    
    /**
     * Listar documentos do usuário
     */
    public function listarDocumentosUsuario($usuarioId, $limite = 20, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT d.*, md.nome as modelo_nome, cd.nome as categoria_nome 
            FROM documentos d 
            JOIN modelos_documentos md ON d.modelo_id = md.id 
            JOIN categorias_documentos cd ON md.categoria_id = cd.id 
            WHERE d.usuario_id = ? 
            ORDER BY d.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$usuarioId, $limite, $offset]);
        return $stmt->fetchAll();
    }
    
    
    /**
     * Exportar para PDF
     */
    public function exportarPDF($documentoId, $usuarioId) {
        // Implementação com TCPDF ou DomPDF
        $documento = $this->getDocumento($documentoId, $usuarioId);
        if (!$documento) {
            return false;
        }
        
        // TODO: Implementar conversão para PDF
        return true;
    }
    
    /**
     * Exportar para DOCX
     */
    public function exportarDOCX($documentoId, $usuarioId) {
        // Implementação com PhpOffice/PhpWord
        $documento = $this->getDocumento($documentoId, $usuarioId);
        if (!$documento) {
            return false;
        }
        
        // TODO: Implementar conversão para DOCX
        return true;
    }
    
    /**
     * Registrar log de atividade
     */
    private function registrarLog($usuarioId, $tipo, $acao, $descricao = null) {
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
}

