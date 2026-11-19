<?php
/**
 * Classe para gerenciamento de planos e controle de acesso
 */

class Plano {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obter informações do plano
     */
    public function getPlano($planoId) {
        $stmt = $this->db->prepare("SELECT * FROM planos WHERE id = ? AND ativo = 1");
        $stmt->execute([$planoId]);
        return $stmt->fetch();
    }
    
    /**
     * Obter plano por código
     */
    public function getPlanoPorCodigo($codigo) {
        $stmt = $this->db->prepare("SELECT * FROM planos WHERE codigo = ? AND ativo = 1");
        $stmt->execute([$codigo]);
        return $stmt->fetch();
    }
    
    /**
     * Verificar se usuário pode gerar documento
     * Sistema totalmente gratuito - sempre permite
     */
    public function podeGerarDocumento($usuarioId) {
        return true; // Sempre permite - sistema gratuito
    }
    
    /**
     * Incrementar contador de documentos gerados
     */
    public function incrementarDocumentosGerados($usuarioId) {
        $stmt = $this->db->prepare("
            UPDATE usuarios 
            SET documentos_gerados_mes = documentos_gerados_mes + 1 
            WHERE id = ?
        ");
        $stmt->execute([$usuarioId]);
    }
    
    /**
     * Verificar acesso a tipo de modelo
     * Sistema totalmente gratuito - sempre permite
     */
    public function temAcessoModelo($usuarioId, $tipoModelo) {
        return true; // Sempre permite - sistema gratuito
    }
    
    /**
     * Verificar se plano tem feature específica
     */
    public function temFeature($usuarioId, $feature) {
        $stmt = $this->db->prepare("
            SELECT p.templates_personalizados, p.geracao_em_massa, 
                   p.interface_customizavel 
            FROM usuarios u 
            JOIN planos p ON u.plano_id = p.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$usuarioId]);
        $plano = $stmt->fetch();
        
        if (!$plano) {
            return false;
        }
        
        $features = [
            'templates_personalizados' => $plano['templates_personalizados'],
            'geracao_em_massa' => $plano['geracao_em_massa'],
            'interface_customizavel' => $plano['interface_customizavel']
        ];
        
        return isset($features[$feature]) && $features[$feature] == 1;
    }
    
    /**
     * Obter todos os planos ativos
     */
    public function listarPlanos() {
        $stmt = $this->db->query("SELECT * FROM planos WHERE ativo = 1 ORDER BY preco_mensal ASC");
        return $stmt->fetchAll();
    }
    
    /**
     * Upgradar usuário para um plano
     */
    public function upgradeUsuario($usuarioId, $planoId) {
        $stmt = $this->db->prepare("UPDATE usuarios SET plano_id = ? WHERE id = ?");
        $stmt->execute([$planoId, $usuarioId]);
        
        // Atualizar sessão
        $_SESSION['usuario_plano'] = $planoId;
        
        // Log
        $this->registrarLog($usuarioId, 'upgrade_plano', "Upgrade para plano ID: $planoId");
    }
    
    /**
     * Registrar log
     */
    private function registrarLog($usuarioId, $tipo, $acao) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO logs (usuario_id, tipo, acao, ip_address) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$usuarioId, $tipo, $acao, $_SERVER['REMOTE_ADDR'] ?? null]);
        } catch (PDOException $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }
}

