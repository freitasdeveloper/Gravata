<?php
/**
 * Classe para envio de emails
 */

class Email {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Envia email de lembrete de evento
     */
    public function enviarLembreteEvento($evento, $usuario) {
        try {
            $assunto = "Lembrete: " . $evento['titulo'];
            $mensagem = $this->gerarMensagemLembrete($evento, $usuario);
            
            // Usar função mail do PHP (pode ser substituído por PHPMailer ou similar)
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">\r\n";
            $headers .= "Reply-To: " . EMAIL_FROM . "\r\n";
            
            $enviado = @mail($usuario['email'], $assunto, $mensagem, $headers);
            
            // Registrar envio no banco
            if ($enviado) {
                $stmt = $this->db->prepare("UPDATE eventos_agenda SET email_enviado = 1 WHERE id = ?");
                $stmt->execute([$evento['id']]);
                
                $stmt = $this->db->prepare("INSERT INTO notificacoes_agenda (evento_id, tipo, data_envio, status) VALUES (?, 'email', NOW(), 'enviado')");
                $stmt->execute([$evento['id']]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO notificacoes_agenda (evento_id, tipo, data_envio, status, erro) VALUES (?, 'email', NOW(), 'falhou', ?)");
                $stmt->execute([$evento['id'], 'Erro ao enviar email']);
            }
            
            return $enviado;
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gera o HTML do email de lembrete
     */
    private function gerarMensagemLembrete($evento, $usuario) {
        $data_formatada = date('d/m/Y', strtotime($evento['data_evento']));
        $hora_formatada = date('H:i', strtotime($evento['data_evento']));
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1a3a5c 0%, #2d5a8a 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .evento-info { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #007bff; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Legatus - Lembrete de Evento</h2>
                </div>
                <div class='content'>
                    <p>Olá, <strong>" . htmlspecialchars($usuario['nome']) . "</strong>,</p>
                    <p>Este é um lembrete de que você tem um evento agendado <strong>amanhã</strong>:</p>
                    
                    <div class='evento-info'>
                        <h3 style='margin-top: 0; color: #1a3a5c;'>" . htmlspecialchars($evento['titulo']) . "</h3>
                        <p><strong>📅 Data:</strong> " . $data_formatada . " às " . $hora_formatada . "</p>";
        
        if ($evento['local']) {
            $html .= "<p><strong>📍 Local:</strong> " . htmlspecialchars($evento['local']) . "</p>";
        }
        
        if ($evento['descricao']) {
            $html .= "<p><strong>📝 Descrição:</strong><br>" . nl2br(htmlspecialchars($evento['descricao'])) . "</p>";
        }
        
        $html .= "
                    </div>
                    
                    <p style='margin-top: 30px;'>Não se esqueça de sua agenda!</p>
                </div>
                <div class='footer'>
                    <p>Este é um email automático do sistema Legatus.</p>
                    <p>Para mais informações, acesse sua agenda no sistema.</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }
}

