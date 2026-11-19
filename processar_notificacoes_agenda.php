<?php
/**
 * Script para processar notificações da agenda
 * Deve ser executado diariamente (via cron job)
 * Envia lembretes por email 1 dia antes dos eventos
 * 
 * Para configurar como cron job (Linux):
 * 0 8 * * * /usr/bin/php /caminho/para/processar_notificacoes_agenda.php
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/email.php';

$db = Database::getInstance()->getConnection();
$email = new Email();

// Buscar eventos que precisam de lembrete (1 dia antes e email ainda não foi enviado)
$data_amanha = date('Y-m-d', strtotime('+1 day'));

try {
    $stmt = $db->prepare("
        SELECT e.*, u.nome as usuario_nome, u.email as usuario_email 
        FROM eventos_agenda e
        INNER JOIN usuarios u ON e.usuario_id = u.id
        WHERE DATE(e.data_evento) = ?
        AND e.lembrete_email = 1
        AND e.email_enviado = 0
        AND e.status NOT IN ('concluido', 'cancelado')
    ");
    $stmt->execute([$data_amanha]);
    $eventos = $stmt->fetchAll();
    
    $enviados = 0;
    $erros = 0;
    
    foreach ($eventos as $evento) {
        $usuario = [
            'nome' => $evento['usuario_nome'],
            'email' => $evento['usuario_email']
        ];
        
        if ($email->enviarLembreteEvento($evento, $usuario)) {
            $enviados++;
            echo "Email enviado para evento: " . $evento['titulo'] . " (Usuário: " . $usuario['email'] . ")\n";
        } else {
            $erros++;
            echo "ERRO ao enviar email para evento: " . $evento['titulo'] . "\n";
        }
    }
    
    echo "\n=== Resumo ===\n";
    echo "Total de eventos processados: " . count($eventos) . "\n";
    echo "Emails enviados com sucesso: " . $enviados . "\n";
    echo "Erros: " . $erros . "\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    error_log("Erro ao processar notificações da agenda: " . $e->getMessage());
}

