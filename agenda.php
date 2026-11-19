<?php
/**
 * Agenda - Sistema de Gerenciamento de Eventos
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$auth = new Auth();

// Verificar se está logado
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$usuario = $auth->getUsuario();
$db = Database::getInstance()->getConnection();

// Processar ações
$acao = $_POST['acao'] ?? $_GET['acao'] ?? null;
$mensagem = '';
$tipo_mensagem = '';

if ($acao == 'adicionar' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Verificar se usuário está logado
        if (empty($usuario['id'])) {
            throw new Exception('Usuário não autenticado');
        }
        
        // Validar campos obrigatórios
        if (empty($_POST['titulo'])) {
            throw new Exception('Título é obrigatório');
        }
        if (empty($_POST['tipo_evento_id'])) {
            throw new Exception('Tipo de evento é obrigatório');
        }
        if (empty($_POST['data_evento'])) {
            throw new Exception('Data do evento é obrigatória');
        }
        
        $stmt = $db->prepare("INSERT INTO eventos_agenda (usuario_id, tipo_evento_id, titulo, descricao, data_evento, data_fim, local, participantes, lembrete_email, lembrete_whatsapp, prioridade, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'agendado')");
        $participantes_json = !empty($_POST['participantes']) ? json_encode(explode(',', $_POST['participantes'])) : null;
        
        $resultado = $stmt->execute([
            $usuario['id'],
            $_POST['tipo_evento_id'],
            $_POST['titulo'],
            $_POST['descricao'] ?? null,
            $_POST['data_evento'],
            !empty($_POST['data_fim']) ? $_POST['data_fim'] : null,
            $_POST['local'] ?? null,
            $participantes_json,
            isset($_POST['lembrete_email']) ? 1 : 0,
            isset($_POST['lembrete_whatsapp']) ? 1 : 0,
            $_POST['prioridade'] ?? 'media'
        ]);
        
        if ($resultado) {
            $mensagem = 'Evento adicionado com sucesso!';
            $tipo_mensagem = 'success';
            // Redirecionar para limpar POST e recarregar eventos
            header('Location: agenda.php?msg=sucesso');
            exit;
        } else {
            throw new Exception('Falha ao inserir evento no banco de dados');
        }
    } catch (Exception $e) {
        $mensagem = 'Erro ao adicionar evento: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
        error_log("Erro ao adicionar evento: " . $e->getMessage());
    }
}

if ($acao == 'editar' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $db->prepare("UPDATE eventos_agenda SET tipo_evento_id=?, titulo=?, descricao=?, data_evento=?, data_fim=?, local=?, participantes=?, lembrete_email=?, lembrete_whatsapp=?, prioridade=?, status=? WHERE id=? AND usuario_id=?");
        $participantes_json = !empty($_POST['participantes']) ? json_encode(explode(',', $_POST['participantes'])) : null;
        $stmt->execute([
            $_POST['tipo_evento_id'],
            $_POST['titulo'],
            $_POST['descricao'] ?? null,
            $_POST['data_evento'],
            !empty($_POST['data_fim']) ? $_POST['data_fim'] : null,
            $_POST['local'] ?? null,
            $participantes_json,
            isset($_POST['lembrete_email']) ? 1 : 0,
            isset($_POST['lembrete_whatsapp']) ? 1 : 0,
            $_POST['prioridade'] ?? 'media',
            $_POST['status'] ?? 'agendado',
            $_POST['evento_id'],
            $usuario['id']
        ]);
        $mensagem = 'Evento atualizado com sucesso!';
        $tipo_mensagem = 'success';
    } catch (Exception $e) {
        $mensagem = 'Erro ao atualizar evento: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

if ($acao == 'excluir' && isset($_GET['id'])) {
    try {
        $stmt = $db->prepare("DELETE FROM eventos_agenda WHERE id=? AND usuario_id=?");
        $stmt->execute([$_GET['id'], $usuario['id']]);
        $mensagem = 'Evento excluído com sucesso!';
        $tipo_mensagem = 'success';
    } catch (Exception $e) {
        $mensagem = 'Erro ao excluir evento: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

// Buscar tipos de eventos
$tipos_eventos = [];
$erro_tabelas = false;

// Tipos padrão caso a tabela não exista ou esteja vazia
$tipos_padrao_array = [
    ['Reunião', 'people', '#007bff', 1],
    ['Consultoria', 'briefcase', '#28a745', 2],
    ['Audiência', 'gavel', '#dc3545', 3],
    ['Visita', 'house-door', '#17a2b8', 4],
    ['Compromisso', 'calendar-check', '#ffc107', 5],
    ['Ligação', 'telephone', '#6f42c1', 6],
    ['Tarefa', 'list-check', '#fd7e14', 7],
    ['Outros', 'three-dots', '#6c757d', 8]
];

try {
    // Tentar buscar tipos do banco
    $stmt = $db->query("SELECT * FROM tipos_eventos_agenda WHERE ativo = 1 ORDER BY ordem, nome");
    $tipos_eventos = $stmt->fetchAll();
    
    // Se não houver tipos, criar os padrão automaticamente
    if (empty($tipos_eventos)) {
        try {
            $stmt_insert = $db->prepare("INSERT INTO tipos_eventos_agenda (nome, icone, cor, ordem) VALUES (?, ?, ?, ?)");
            foreach ($tipos_padrao_array as $tipo) {
                $stmt_insert->execute($tipo);
            }
            
            // Buscar novamente
            $stmt = $db->query("SELECT * FROM tipos_eventos_agenda WHERE ativo = 1 ORDER BY ordem, nome");
            $tipos_eventos = $stmt->fetchAll();
        } catch (Exception $e) {
            // Se falhar ao inserir, criar array manual com os tipos padrão
            $tipos_eventos = [];
            foreach ($tipos_padrao_array as $idx => $tipo) {
                $tipos_eventos[] = [
                    'id' => $idx + 1,
                    'nome' => $tipo[0],
                    'icone' => $tipo[1],
                    'cor' => $tipo[2],
                    'ordem' => $tipo[3],
                    'ativo' => 1
                ];
            }
        }
    }
} catch (Exception $e) {
    // Se a tabela não existir, usar tipos padrão como array
    $tipos_eventos = [];
    foreach ($tipos_padrao_array as $idx => $tipo) {
        $tipos_eventos[] = [
            'id' => $idx + 1,
            'nome' => $tipo[0],
            'icone' => $tipo[1],
            'cor' => $tipo[2],
            'ordem' => $tipo[3],
            'ativo' => 1
        ];
    }
    $erro_tabelas = true;
    if (empty($mensagem)) {
        $mensagem = "As tabelas da agenda ainda não foram criadas. Execute o arquivo criar_tabela_agenda.sql ou instalar_agenda.php no banco de dados. Os tipos padrão foram carregados temporariamente.";
        $tipo_mensagem = 'warning';
    }
}

// Garantir que sempre haja pelo menos os tipos padrão
if (empty($tipos_eventos)) {
    $tipos_eventos = [];
    foreach ($tipos_padrao_array as $idx => $tipo) {
        $tipos_eventos[] = [
            'id' => $idx + 1,
            'nome' => $tipo[0],
            'icone' => $tipo[1],
            'cor' => $tipo[2],
            'ordem' => $tipo[3],
            'ativo' => 1
        ];
    }
}

// Buscar evento para edição
$evento_editar = null;
if (isset($_GET['editar'])) {
    try {
        $stmt = $db->prepare("SELECT * FROM eventos_agenda WHERE id=? AND usuario_id=?");
        $stmt->execute([$_GET['editar'], $usuario['id']]);
        $evento_editar = $stmt->fetch();
    } catch (Exception $e) {}
}

// Verificar mensagem de sucesso da URL
if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso') {
    $mensagem = 'Evento adicionado com sucesso!';
    $tipo_mensagem = 'success';
}

// Filtros
$filtro_data = $_GET['data'] ?? 'todos';
$filtro_tipo = $_GET['tipo'] ?? 'todos';
$filtro_status = $_GET['status'] ?? 'todos';

// Buscar eventos
$eventos = [];
if (!$erro_tabelas && !empty($usuario['id'])) {
try {
    $sql = "SELECT e.*, t.nome as tipo_nome, t.icone as tipo_icone, t.cor as tipo_cor 
            FROM eventos_agenda e 
            LEFT JOIN tipos_eventos_agenda t ON e.tipo_evento_id = t.id 
            WHERE e.usuario_id = ?";
    $params = [$usuario['id']];
    
    if ($filtro_data != 'todos') {
        $sql .= " AND DATE(e.data_evento) = ?";
        $params[] = $filtro_data;
    }
    
    if ($filtro_tipo != 'todos') {
        $sql .= " AND e.tipo_evento_id = ?";
        $params[] = $filtro_tipo;
    }
    
    if ($filtro_status != 'todos') {
        $sql .= " AND e.status = ?";
        $params[] = $filtro_status;
    }
    
    $sql .= " ORDER BY e.data_evento ASC, e.prioridade DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Processar participantes (JSON)
    foreach ($eventos as &$evento) {
        if (!empty($evento['participantes'])) {
            $evento['participantes_array'] = json_decode($evento['participantes'], true);
        } else {
            $evento['participantes_array'] = [];
        }
        // Garantir que tipo_cor sempre tenha valor
        if (empty($evento['tipo_cor'])) {
            $evento['tipo_cor'] = '#007bff';
        }
    }
    
    // Debug: log se não encontrar eventos mas deveria
    if (empty($eventos) && $filtro_data == 'todos' && $filtro_tipo == 'todos' && $filtro_status == 'todos') {
        // Verificar se há eventos no banco para este usuário
        try {
            $stmt_check = $db->prepare("SELECT COUNT(*) as total FROM eventos_agenda WHERE usuario_id = ?");
            $stmt_check->execute([$usuario['id']]);
            $total_eventos = $stmt_check->fetch()['total'];
            if ($total_eventos > 0) {
                error_log("Eventos encontrados no banco ($total_eventos) mas não retornados pela query principal");
            }
        } catch (Exception $e) {
            error_log("Erro ao verificar total de eventos: " . $e->getMessage());
        }
    }
} catch (Exception $e) {
    // Tabela pode não existir ainda
    if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "não existe") !== false) {
        $erro_tabelas = true;
        if (empty($mensagem)) {
            $mensagem = "As tabelas da agenda ainda não foram criadas. Execute o arquivo criar_tabela_agenda.sql no banco de dados.";
            $tipo_mensagem = 'warning';
        }
    } else {
        error_log("Erro ao buscar eventos: " . $e->getMessage());
        if (empty($mensagem)) {
            $mensagem = "Erro ao carregar eventos. Verifique os logs.";
            $tipo_mensagem = 'danger';
        }
    }
}
}

// Estatísticas
$stats = [
    'total' => 0,
    'hoje' => 0,
    'semana' => 0,
    'mes' => 0
];

if (!$erro_tabelas) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM eventos_agenda WHERE usuario_id = ?");
        $stmt->execute([$usuario['id']]);
        $stats['total'] = $stmt->fetch()['total'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM eventos_agenda WHERE usuario_id = ? AND DATE(data_evento) = CURDATE()");
        $stmt->execute([$usuario['id']]);
        $stats['hoje'] = $stmt->fetch()['total'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM eventos_agenda WHERE usuario_id = ? AND data_evento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute([$usuario['id']]);
        $stats['semana'] = $stmt->fetch()['total'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM eventos_agenda WHERE usuario_id = ? AND MONTH(data_evento) = MONTH(CURDATE()) AND YEAR(data_evento) = YEAR(CURDATE())");
        $stmt->execute([$usuario['id']]);
        $stats['mes'] = $stmt->fetch()['total'];
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - Legatus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css">
    <style>
        .evento-card {
            border-left: 4px solid;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .evento-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .prioridade-alta { border-left-color: #dc3545; }
        .prioridade-media { border-left-color: #ffc107; }
        .prioridade-baixa { border-left-color: #28a745; }
        .fc-event {
            cursor: pointer;
            margin: 1px 0 !important;
            display: block !important;
            position: relative !important;
        }
        
        /* Forçar espaçamento absoluto entre eventos */
        .fc-daygrid-day-events {
            position: relative !important;
            margin-top: 4px !important;
        }
        
        .fc-daygrid-event-harness {
            position: relative !important;
            margin-bottom: 3px !important;
            display: block !important;
        }
        
        /* Remover qualquer posicionamento absoluto que cause sobreposição */
        .fc-daygrid-event-harness-abs {
            position: relative !important;
            margin-bottom: 3px !important;
        }
        
        /* Melhorias no calendário - números maiores e melhor contraste */
        .fc-daygrid-day-number {
            font-size: 1.3rem !important;
            font-weight: 600 !important;
            color: #e8e0d6 !important; /* Bege claro para melhor contraste */
            padding: 8px 10px !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5) !important;
            position: relative !important;
            z-index: 1 !important;
            cursor: pointer !important;
        }
        
        /* Corrigir sobreposição - garantir que números fiquem acima */
        .fc-daygrid-day {
            position: relative;
        }
        
        .fc-daygrid-day-top {
            position: relative;
            z-index: 2;
        }
        
        .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            color: #f5f0e8 !important;
            font-weight: 700 !important;
            font-size: 1.4rem !important;
        }
        
        .fc-daygrid-day-top {
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
        }
        
        /* Cabeçalho dos dias da semana */
        .fc-col-header-cell-cushion {
            font-size: 0.95rem !important;
            font-weight: 600 !important;
            color: #e8e0d6 !important;
            padding: 10px !important;
        }
        
        /* Mes e ano no cabeçalho */
        .fc-toolbar-title {
            font-size: 1.5rem !important;
            font-weight: 600 !important;
            color: #e8e0d6 !important;
        }
        
        /* Botões de navegação */
        .fc-button {
            background: rgba(212,196,176,0.2) !important;
            border-color: rgba(212,196,176,0.3) !important;
            color: #e8e0d6 !important;
            font-weight: 500 !important;
        }
        
        .fc-button:hover {
            background: rgba(212,196,176,0.3) !important;
            border-color: rgba(212,196,176,0.5) !important;
            color: #f5f0e8 !important;
        }
        
        .fc-button-active {
            background: rgba(212,196,176,0.4) !important;
            border-color: var(--bege-medio) !important;
            color: #f5f0e8 !important;
        }
        
        /* Células do calendário */
        .fc-daygrid-day {
            background: rgba(42,42,42,0.5) !important;
            border-color: rgba(212,196,176,0.15) !important;
        }
        
        .fc-daygrid-day.fc-day-today {
            background: rgba(212,196,176,0.15) !important;
        }
        
        /* Números dos dias de outros meses (mais discretos) */
        .fc-day-other .fc-daygrid-day-number {
            color: #888888 !important;
            opacity: 0.7;
            font-size: 1.1rem !important;
        }
        
        /* Melhorar visibilidade dos eventos no calendário */
        .fc-event-title {
            font-weight: 500 !important;
            padding: 2px 4px !important;
        }
        
        /* Área do calendário */
        #calendar {
            background: rgba(42,42,42,0.3);
            border-radius: 8px;
            padding: 10px;
        }
        
        /* Corrigir sobreposição de eventos */
        .fc-daygrid-event {
            position: relative;
            z-index: 1;
            margin-bottom: 2px !important;
        }
        
        .fc-event-time,
        .fc-event-title {
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            line-height: 1.3 !important;
            padding: 2px 4px !important;
        }
        
        /* Evitar sobreposição de eventos na visualização de dia/semana */
        .fc-timeGrid-event,
        .fc-daygrid-event,
        .fc-event {
            margin-bottom: 2px !important;
            overflow: hidden !important;
        }
        
        /* Visualização de tempo (dia/semana) */
        .fc-timegrid-slot {
            height: 2.5em !important;
        }
        
        .fc-timegrid-event {
            position: absolute !important;
            margin: 1px 2px !important;
            padding: 2px 4px !important;
        }
        
        .fc-timegrid-event .fc-event-time {
            font-size: 0.75rem !important;
            white-space: nowrap !important;
        }
        
        .fc-timegrid-event .fc-event-title {
            font-size: 0.85rem !important;
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            line-height: 1.2 !important;
        }
        
        /* Visualização de grade do dia */
        .fc-timegrid-col-events {
            margin: 0 !important;
        }
        
        /* Evitar que eventos fiquem sobrepostos */
        .fc-event-main-frame {
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }
        
        /* Células de dia com altura mínima */
        .fc-daygrid-day-frame {
            min-height: 100px !important;
        }
        
        /* Espaçamento entre eventos no mesmo dia */
        .fc-daygrid-event-dot {
            margin-right: 4px !important;
        }
        
        /* Garantir que células sejam clicáveis */
        .fc-daygrid-day-frame {
            cursor: pointer;
        }
        
        /* Correção adicional para sobreposição em visualização de semana */
        .fc-timeGridWeek-view .fc-timegrid-event {
            margin: 1px !important;
            border-radius: 3px !important;
        }
        
        /* Correção para visualização de dia */
        .fc-timeGridDay-view .fc-timegrid-event {
            margin: 1px !important;
            min-height: 20px !important;
        }
        
        /* Garantir que o texto não ultrapasse os limites */
        .fc-event-main {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            max-height: 100% !important;
        }
        
        /* Espaçamento vertical entre eventos sobrepostos */
        .fc-timegrid-event-selected,
        .fc-timegrid-event-selected.fc-event-selected {
            z-index: 10 !important;
        }
        
        /* Scroll personalizado para visualização de dia */
        .fc-scroller {
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }
        
        /* Ajustar altura dos slots de tempo */
        .fc-timegrid-slot-label {
            font-size: 0.75rem !important;
            padding: 0 8px !important;
        }
        
        /* Melhorar legibilidade dos horários */
        .fc-timegrid-now-indicator-line {
            border-color: var(--bege-medio) !important;
        }
        
        /* Correção global para evitar sobreposição em TODAS as visualizações */
        .fc-view-harness {
            overflow: visible !important;
        }
        
        .fc-scrollgrid-section > td {
            vertical-align: top !important;
        }
        
        /* Garantir espaçamento adequado em todas as visualizações */
        .fc-daygrid-day-events {
            margin-top: 4px !important;
        }
        
        .fc-daygrid-event-harness {
            margin-bottom: 2px !important;
        }
        
        /* Prevenir sobreposição após mudança de visualização */
        .fc-event.fc-event-start.fc-event-end {
            margin-bottom: 3px !important;
        }
        
        /* Ajustar altura das células após renderização */
        .fc-daygrid-day {
            min-height: 80px !important;
        }
        
        .fc-col-header-cell,
        .fc-daygrid-day {
            border-right: 1px solid rgba(212,196,176,0.15) !important;
        }
        
        /* Eventos em linha para evitar sobreposição horizontal */
        .fc-daygrid-event-harness-abs {
            margin-bottom: 2px !important;
        }
        
        /* Forçar quebra de linha em títulos longos */
        .fc-event-title-container {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
        }
        
        /* Visual de hover ao passar sobre data */
        .fc-daygrid-day:hover .fc-daygrid-day-number {
            color: var(--bege-medio) !important;
            transform: scale(1.1);
            transition: all 0.2s ease;
        }
        
        /* Corrigir z-index de elementos do calendário */
        .fc-scrollgrid {
            position: relative;
        }
        
        .fc-daygrid-body {
            position: relative;
            z-index: 1;
        }
        .stat-card {
            background: linear-gradient(135deg, #1a3a5c 0%, #2d5a8a 100%);
            color: white;
            border-radius: 8px;
            padding: 20px;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        /* Melhorias de contraste no modal */
        .modal-content {
            background: linear-gradient(135deg, rgba(42,42,42,0.98), rgba(58,58,58,0.98));
            border: 1px solid rgba(212,196,176,0.3);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(212,196,176,0.3);
        }
        
        .modal-title {
            color: var(--bege-creme) !important;
            font-weight: 600;
        }
        
        .modal-body .form-label {
            color: #e8e0d6 !important; /* Bege claro para melhor contraste */
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .modal-body .form-control,
        .modal-body .form-select {
            background: rgba(30,30,30,0.95) !important;
            border: 1px solid rgba(212,196,176,0.4) !important;
            color: #f5f0e8 !important; /* Texto bem claro */
            padding: 10px 12px;
        }
        
        .modal-body .form-control::placeholder,
        .modal-body .form-select::placeholder {
            color: #b8b0a8 !important; /* Placeholder mais claro */
            opacity: 0.8;
        }
        
        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            background: rgba(35,35,35,0.95) !important;
            border-color: var(--bege-medio) !important;
            color: #f5f0e8 !important;
            box-shadow: 0 0 0 0.25rem rgba(212,196,176,0.35) !important;
        }
        
        .modal-body .form-check-label {
            color: #e8e0d6 !important; /* Labels dos checkboxes mais claros */
            font-weight: 400;
            margin-left: 8px;
        }
        
        .modal-body .form-check-label.text-muted {
            color: #b8b0a8 !important; /* Texto desabilitado um pouco mais claro */
            opacity: 0.85;
        }
        
        .modal-body .form-check-input:checked {
            background-color: var(--bege-medio);
            border-color: var(--bege-medio);
        }
        
        .modal-body .form-check-input:focus {
            border-color: var(--bege-medio);
            box-shadow: 0 0 0 0.25rem rgba(212,196,176,0.35);
        }
        
        .modal-footer {
            border-top: 1px solid rgba(212,196,176,0.3);
        }
        
        .modal-footer .btn-secondary {
            background: rgba(138,138,138,0.4) !important;
            border-color: rgba(212,196,176,0.4) !important;
            color: #e8e0d6 !important;
        }
        
        .modal-footer .btn-primary {
            background: linear-gradient(135deg, var(--bege-medio), var(--bege-escuro)) !important;
            border: none !important;
            color: #f5f0e8 !important;
            font-weight: 500;
        }
        
        .modal-footer .btn-primary:hover {
            background: linear-gradient(135deg, var(--bege-claro), var(--bege-medio)) !important;
            color: #1a1a1a !important;
            transform: translateY(-2px);
        }
        
        /* Melhorar visibilidade dos ícones de data */
        .modal-body input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: brightness(1.5);
            cursor: pointer;
        }
        
        /* Select dropdown mais legível */
        .modal-body .form-select {
            color: #f5f0e8 !important;
        }
        
        .modal-body .form-select option {
            background: rgba(30,30,30,0.98);
            color: #f5f0e8;
            padding: 8px;
        }
        
        .modal-body .form-select option:checked {
            background: rgba(212,196,176,0.3);
            color: #f5f0e8;
        }
        
        /* Textarea */
        .modal-body textarea.form-control {
            background: rgba(30,30,30,0.95) !important;
            color: #f5f0e8 !important;
            min-height: 80px;
        }
        
        .modal-body textarea.form-control::placeholder {
            color: #b8b0a8 !important;
        }
        
        /* Garantir que o texto digitado seja sempre visível */
        .modal-body input[type="text"]:not(:placeholder-shown),
        .modal-body input[type="datetime-local"]:not(:placeholder-shown),
        .modal-body textarea:not(:placeholder-shown) {
            color: #f5f0e8 !important;
        }
        
        /* Melhorar visibilidade do botão de fechar */
        .btn-close {
            filter: brightness(1.5);
        }
        
        /* Melhorar contraste dos ícones nos labels */
        .modal-body .form-check-label i {
            color: inherit;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-10">
                <nav class="navbar mb-4">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar-event"></i> Agenda
                            </h5>
                        </div>
                        <div>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEvento">
                                <i class="bi bi-plus-circle"></i> Novo Evento
                            </button>
                        </div>
                    </div>
                </nav>
                
                <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($mensagem); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stat-card text-center">
                            <i class="bi bi-calendar-check" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <h4><?php echo $stats['total']; ?></h4>
                            <small>Total de Eventos</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stat-card text-center">
                            <i class="bi bi-calendar-day" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <h4><?php echo $stats['hoje']; ?></h4>
                            <small>Eventos Hoje</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stat-card text-center">
                            <i class="bi bi-calendar-week" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <h4><?php echo $stats['semana']; ?></h4>
                            <small>Esta Semana</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stat-card text-center">
                            <i class="bi bi-calendar-month" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <h4><?php echo $stats['mes']; ?></h4>
                            <small>Este Mês</small>
                        </div>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Data</label>
                                <input type="date" name="data" class="form-control" value="<?php echo htmlspecialchars($filtro_data); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo</label>
                                <select name="tipo" class="form-select">
                                    <option value="todos">Todos os Tipos</option>
                                    <?php if (!empty($tipos_eventos)): ?>
                                        <?php foreach ($tipos_eventos as $tipo): ?>
                                        <option value="<?php echo isset($tipo['id']) ? $tipo['id'] : ''; ?>" <?php echo $filtro_tipo == $tipo['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tipo['nome'] ?? ''); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Nenhum tipo disponível</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="todos" <?php echo $filtro_status == 'todos' ? 'selected' : ''; ?>>Todos</option>
                                    <option value="agendado" <?php echo $filtro_status == 'agendado' ? 'selected' : ''; ?>>Agendado</option>
                                    <option value="em_andamento" <?php echo $filtro_status == 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                    <option value="concluido" <?php echo $filtro_status == 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                    <option value="cancelado" <?php echo $filtro_status == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="agenda.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Limpar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Calendário -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-calendar3"></i> Calendário</h6>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
                
                <!-- Debug Info (remover depois) -->
                <?php if (defined('DEBUG') && DEBUG): ?>
                <div class="alert alert-info">
                    <strong>Debug:</strong><br>
                    Usuário ID: <?php echo $usuario['id'] ?? 'N/A'; ?><br>
                    Total de eventos: <?php echo count($eventos); ?><br>
                    Erro tabelas: <?php echo $erro_tabelas ? 'Sim' : 'Não'; ?><br>
                    Filtros: Data=<?php echo $filtro_data; ?>, Tipo=<?php echo $filtro_tipo; ?>, Status=<?php echo $filtro_status; ?>
                </div>
                <?php endif; ?>
                
                <!-- Lista de Eventos -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-list-ul"></i> Eventos <?php echo !empty($eventos) ? '(' . count($eventos) . ')' : ''; ?></h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($eventos)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-calendar-x" style="font-size: 4rem; color: #ccc;"></i>
                                <p class="text-muted mt-3">Nenhum evento encontrado</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEvento">
                                    <i class="bi bi-plus-circle"></i> Criar Primeiro Evento
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($eventos as $evento): ?>
                                <div class="list-group-item evento-card prioridade-<?php echo $evento['prioridade']; ?>" style="border-left-color: <?php echo htmlspecialchars($evento['tipo_cor'] ?? '#007bff'); ?>;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-<?php echo htmlspecialchars($evento['tipo_icone'] ?? 'calendar-event'); ?>" style="color: <?php echo htmlspecialchars($evento['tipo_cor'] ?? '#007bff'); ?>; margin-right: 10px;"></i>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($evento['titulo']); ?></h6>
                                                <span class="badge bg-<?php 
                                                    echo $evento['status'] == 'concluido' ? 'success' : 
                                                        ($evento['status'] == 'cancelado' ? 'danger' : 
                                                        ($evento['status'] == 'em_andamento' ? 'warning' : 'primary')); 
                                                ?> ms-2">
                                                    <?php 
                                                        echo $evento['status'] == 'concluido' ? 'Concluído' : 
                                                            ($evento['status'] == 'cancelado' ? 'Cancelado' : 
                                                            ($evento['status'] == 'em_andamento' ? 'Em Andamento' : 'Agendado')); 
                                                    ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($evento['data_evento'])); ?>
                                                <?php if ($evento['data_fim']): ?>
                                                    até <?php echo date('H:i', strtotime($evento['data_fim'])); ?>
                                                <?php endif; ?>
                                                <?php if ($evento['local']): ?>
                                                    | <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($evento['local']); ?>
                                                <?php endif; ?>
                                            </small>
                                            <?php if ($evento['descricao']): ?>
                                                <p class="mb-1 mt-2"><?php echo nl2br(htmlspecialchars($evento['descricao'])); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($evento['participantes_array'])): ?>
                                                <small class="text-muted">
                                                    <i class="bi bi-people"></i> <?php echo implode(', ', $evento['participantes_array']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="btn-group ms-3">
                                            <a href="agenda.php?editar=<?php echo $evento['id']; ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEvento">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="agenda.php?acao=excluir&id=<?php echo $evento['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir este evento?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal Adicionar/Editar Evento -->
    <div class="modal fade" id="modalEvento" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-<?php echo $evento_editar ? 'pencil' : 'plus-circle'; ?>"></i>
                        <?php echo $evento_editar ? 'Editar Evento' : 'Novo Evento'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="agenda.php">
                    <input type="hidden" name="acao" value="<?php echo $evento_editar ? 'editar' : 'adicionar'; ?>">
                    <?php if ($evento_editar): ?>
                    <input type="hidden" name="evento_id" value="<?php echo $evento_editar['id']; ?>">
                    <?php endif; ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Título *</label>
                                <input type="text" name="titulo" class="form-control" required value="<?php echo htmlspecialchars($evento_editar['titulo'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Evento *</label>
                                <select name="tipo_evento_id" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    <?php if (!empty($tipos_eventos)): ?>
                                        <?php foreach ($tipos_eventos as $tipo): ?>
                                        <option value="<?php echo isset($tipo['id']) ? $tipo['id'] : ''; ?>" 
                                            <?php echo ($evento_editar && isset($evento_editar['tipo_evento_id']) && $evento_editar['tipo_evento_id'] == $tipo['id']) ? 'selected' : ''; ?>
                                            style="color: <?php echo htmlspecialchars($tipo['cor'] ?? '#007bff'); ?>;">
                                            <?php echo htmlspecialchars($tipo['nome'] ?? ''); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Erro: Nenhum tipo disponível</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prioridade</label>
                                <select name="prioridade" class="form-select">
                                    <option value="baixa" <?php echo ($evento_editar && $evento_editar['prioridade'] == 'baixa') ? 'selected' : ''; ?>>Baixa</option>
                                    <option value="media" <?php echo (!$evento_editar || $evento_editar['prioridade'] == 'media') ? 'selected' : ''; ?>>Média</option>
                                    <option value="alta" <?php echo ($evento_editar && $evento_editar['prioridade'] == 'alta') ? 'selected' : ''; ?>>Alta</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Data e Hora *</label>
                                <input type="datetime-local" name="data_evento" class="form-control" required 
                                       value="<?php echo $evento_editar ? date('Y-m-d\TH:i', strtotime($evento_editar['data_evento'])) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Data e Hora Fim</label>
                                <input type="datetime-local" name="data_fim" class="form-control"
                                       value="<?php echo ($evento_editar && $evento_editar['data_fim']) ? date('Y-m-d\TH:i', strtotime($evento_editar['data_fim'])) : ''; ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Local</label>
                                <input type="text" name="local" class="form-control" 
                                       value="<?php echo htmlspecialchars($evento_editar['local'] ?? ''); ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Participantes (separados por vírgula)</label>
                                <input type="text" name="participantes" class="form-control" 
                                       placeholder="Nome1, Nome2, Nome3"
                                       value="<?php echo ($evento_editar && !empty($evento_editar['participantes_array'])) ? implode(', ', $evento_editar['participantes_array']) : ''; ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Descrição</label>
                                <textarea name="descricao" class="form-control" rows="3"><?php echo htmlspecialchars($evento_editar['descricao'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Notas Adicionais</label>
                                <textarea name="notas" class="form-control" rows="2"><?php echo htmlspecialchars($evento_editar['notas'] ?? ''); ?></textarea>
                            </div>
                            <?php if ($evento_editar): ?>
                            <div class="col-md-12">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="agendado" <?php echo $evento_editar['status'] == 'agendado' ? 'selected' : ''; ?>>Agendado</option>
                                    <option value="em_andamento" <?php echo $evento_editar['status'] == 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                    <option value="concluido" <?php echo $evento_editar['status'] == 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                    <option value="cancelado" <?php echo $evento_editar['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="lembrete_email" id="lembrete_email" 
                                           <?php echo (!$evento_editar || $evento_editar['lembrete_email']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="lembrete_email">
                                        <i class="bi bi-envelope"></i> Enviar lembrete por email 1 dia antes
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="lembrete_whatsapp" id="lembrete_whatsapp" 
                                           <?php echo ($evento_editar && $evento_editar['lembrete_whatsapp']) ? 'checked' : ''; ?> disabled>
                                    <label class="form-check-label text-muted" for="lembrete_whatsapp">
                                        <i class="bi bi-whatsapp"></i> Enviar lembrete por WhatsApp 1 dia antes (em breve)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> <?php echo $evento_editar ? 'Atualizar' : 'Adicionar'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/pt-br.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'pt-br',
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: [
                        <?php 
                        if (!empty($eventos)): 
                            foreach ($eventos as $evento): 
                                // Garantir que todos os campos necessários existam
                                $evento_id = isset($evento['id']) ? $evento['id'] : 0;
                                $evento_titulo = isset($evento['titulo']) ? addslashes($evento['titulo']) : 'Sem título';
                                $evento_data = isset($evento['data_evento']) ? date('Y-m-d\TH:i:s', strtotime($evento['data_evento'])) : date('Y-m-d\TH:i:s');
                                $evento_data_fim = '';
                                if (!empty($evento['data_fim'])) {
                                    $evento_data_fim = date('Y-m-d\TH:i:s', strtotime($evento['data_fim']));
                                } else {
                                    $evento_data_fim = date('Y-m-d\TH:i:s', strtotime($evento['data_evento'] . ' +1 hour'));
                                }
                                $evento_cor = isset($evento['tipo_cor']) && !empty($evento['tipo_cor']) ? htmlspecialchars($evento['tipo_cor']) : '#007bff';
                        ?>
                        {
                            id: '<?php echo $evento_id; ?>',
                            title: '<?php echo $evento_titulo; ?>',
                            start: '<?php echo $evento_data; ?>',
                            end: '<?php echo $evento_data_fim; ?>',
                            color: '<?php echo $evento_cor; ?>',
                            url: 'agenda.php?editar=<?php echo $evento_id; ?>'
                        },
                        <?php 
                            endforeach;
                        endif; 
                        ?>
                    ],
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        window.location.href = info.event.url;
                        var modal = new bootstrap.Modal(document.getElementById('modalEvento'));
                        modal.show();
                    },
                    dateClick: function(info) {
                        // Ao clicar em uma data, abrir modal com data pré-preenchida
                        var dataSelecionada = info.dateStr;
                        var horaAtual = new Date().toTimeString().slice(0, 5);
                        var dataHoraCompleta = dataSelecionada + 'T' + horaAtual;
                        
                        // Limpar evento de edição
                        document.querySelector('input[name="evento_id"]')?.remove();
                        document.querySelector('input[name="acao"]').value = 'adicionar';
                        
                        // Preencher data no formulário
                        var dataEventoInput = document.querySelector('input[name="data_evento"]');
                        if (dataEventoInput) {
                            dataEventoInput.value = dataHoraCompleta;
                        }
                        
                        // Limpar campos do formulário
                        document.querySelector('input[name="titulo"]').value = '';
                        document.querySelector('textarea[name="descricao"]').value = '';
                        document.querySelector('input[name="local"]').value = '';
                        document.querySelector('input[name="participantes"]').value = '';
                        document.querySelector('textarea[name="notas"]').value = '';
                        
                        // Selecionar primeiro tipo de evento por padrão
                        var tipoSelect = document.querySelector('select[name="tipo_evento_id"]');
                        if (tipoSelect && tipoSelect.options.length > 1) {
                            tipoSelect.selectedIndex = 1;
                        }
                        
                        // Atualizar título do modal
                        var modalTitle = document.querySelector('#modalEvento .modal-title');
                        if (modalTitle) {
                            modalTitle.innerHTML = '<i class="bi bi-plus-circle"></i> Novo Evento';
                        }
                        
                        // Abrir modal
                        var modal = new bootstrap.Modal(document.getElementById('modalEvento'));
                        modal.show();
                    }
                });
                calendar.render();
                
                // Função robusta para corrigir sobreposição
                function corrigirSobreposicao() {
                    // Forçar espaçamento em TODOS os eventos
                    var todosEventos = document.querySelectorAll('.fc-event, .fc-daygrid-event, .fc-timegrid-event');
                    todosEventos.forEach(function(evento, index) {
                        evento.style.cssText += 'margin: 1px 0 !important; display: block !important; position: relative !important; overflow: hidden !important;';
                    });
                    
                    // Corrigir containers de eventos
                    var containers = document.querySelectorAll('.fc-daygrid-event-harness, .fc-event-harness, .fc-daygrid-event-harness-abs');
                    containers.forEach(function(container, index) {
                        container.style.cssText += 'position: relative !important; margin-bottom: 3px !important; display: block !important;';
                    });
                    
                    // Corrigir área de eventos do dia
                    var areasEventos = document.querySelectorAll('.fc-daygrid-day-events');
                    areasEventos.forEach(function(area) {
                        area.style.cssText += 'position: relative !important; margin-top: 4px !important;';
                    });
                    
                    // Ajustar células do dia
                    var celulas = document.querySelectorAll('.fc-daygrid-day');
                    celulas.forEach(function(celula) {
                        celula.style.cssText += 'min-height: 80px !important;';
                    });
                    
                    // Corrigir títulos
                    var titulos = document.querySelectorAll('.fc-event-title, .fc-event-title-container');
                    titulos.forEach(function(titulo) {
                        titulo.style.cssText += 'white-space: normal !important; word-wrap: break-word !important; overflow-wrap: break-word !important; overflow: hidden !important;';
                    });
                }
                
                // Corrigir imediatamente após renderização
                setTimeout(corrigirSobreposicao, 100);
                
                // Corrigir após mudança de visualização
                calendar.on('viewDidMount', function() {
                    setTimeout(corrigirSobreposicao, 200);
                    setTimeout(corrigirSobreposicao, 500);
                });
                
                calendar.on('datesSet', function() {
                    setTimeout(corrigirSobreposicao, 100);
                    setTimeout(corrigirSobreposicao, 300);
                });
                
                // Usar MutationObserver para detectar mudanças no DOM
                var observer = new MutationObserver(function(mutations) {
                    var precisaCorrigir = false;
                    mutations.forEach(function(mutation) {
                        if (mutation.addedNodes.length > 0 || mutation.type === 'attributes') {
                            precisaCorrigir = true;
                        }
                    });
                    if (precisaCorrigir) {
                        setTimeout(corrigirSobreposicao, 50);
                    }
                });
                
                // Observar mudanças no calendário
                var calendarContainer = document.getElementById('calendar');
                if (calendarContainer) {
                    observer.observe(calendarContainer, {
                        childList: true,
                        subtree: true,
                        attributes: true,
                        attributeFilter: ['style', 'class']
                    });
                }
                
                // Corrigir quando botões são clicados
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('fc-button') || 
                        e.target.closest('.fc-button') ||
                        e.target.closest('.fc-toolbar-chunk')) {
                        setTimeout(corrigirSobreposicao, 100);
                        setTimeout(corrigirSobreposicao, 300);
                        setTimeout(corrigirSobreposicao, 600);
                    }
                }, true);
            }
        });
        
        // Abrir modal ao editar
        <?php if ($evento_editar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('modalEvento'));
            modal.show();
        });
        <?php endif; ?>
    </script>
</body>
</html>

