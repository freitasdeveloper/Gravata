<?php
/**
 * Script de Debug para Agenda
 * Acesse: http://localhost/Gravata/debug_agenda.php
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    die("Usuário não está logado. Faça login primeiro.");
}

$usuario = $auth->getUsuario();
$db = Database::getInstance()->getConnection();

echo "<h2>Debug da Agenda</h2>";
echo "<hr>";

// 1. Verificar se usuário está autenticado
echo "<h3>1. Informações do Usuário</h3>";
echo "ID: " . ($usuario['id'] ?? 'N/A') . "<br>";
echo "Nome: " . ($usuario['nome'] ?? 'N/A') . "<br>";
echo "Email: " . ($usuario['email'] ?? 'N/A') . "<br>";
echo "<hr>";

// 2. Verificar se tabelas existem
echo "<h3>2. Verificação das Tabelas</h3>";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'eventos_agenda'");
    $tabela_existe = $stmt->fetch();
    echo "Tabela eventos_agenda: " . ($tabela_existe ? "✓ Existe" : "✗ NÃO EXISTE") . "<br>";
    
    $stmt = $db->query("SHOW TABLES LIKE 'tipos_eventos_agenda'");
    $tabela_tipos = $stmt->fetch();
    echo "Tabela tipos_eventos_agenda: " . ($tabela_tipos ? "✓ Existe" : "✗ NÃO EXISTE") . "<br>";
} catch (Exception $e) {
    echo "Erro ao verificar tabelas: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// 3. Verificar tipos de eventos
echo "<h3>3. Tipos de Eventos</h3>";
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM tipos_eventos_agenda");
    $total_tipos = $stmt->fetch()['total'];
    echo "Total de tipos: $total_tipos<br>";
    
    if ($total_tipos > 0) {
        $stmt = $db->query("SELECT * FROM tipos_eventos_agenda LIMIT 5");
        $tipos = $stmt->fetchAll();
        echo "<pre>";
        print_r($tipos);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// 4. Verificar eventos no banco
echo "<h3>4. Eventos no Banco de Dados</h3>";
try {
    // Total geral
    $stmt = $db->query("SELECT COUNT(*) as total FROM eventos_agenda");
    $total_geral = $stmt->fetch()['total'];
    echo "Total de eventos (todos usuários): $total_geral<br>";
    
    // Total do usuário atual
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM eventos_agenda WHERE usuario_id = ?");
    $stmt->execute([$usuario['id']]);
    $total_usuario = $stmt->fetch()['total'];
    echo "Total de eventos do usuário atual: $total_usuario<br>";
    
    // Listar eventos do usuário
    if ($total_usuario > 0) {
        $stmt = $db->prepare("SELECT * FROM eventos_agenda WHERE usuario_id = ? ORDER BY data_evento DESC LIMIT 10");
        $stmt->execute([$usuario['id']]);
        $eventos = $stmt->fetchAll();
        
        echo "<h4>Últimos 10 eventos do usuário:</h4>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Título</th><th>Data Evento</th><th>Tipo ID</th><th>Status</th></tr>";
        foreach ($eventos as $evento) {
            echo "<tr>";
            echo "<td>" . $evento['id'] . "</td>";
            echo "<td>" . htmlspecialchars($evento['titulo']) . "</td>";
            echo "<td>" . $evento['data_evento'] . "</td>";
            echo "<td>" . $evento['tipo_evento_id'] . "</td>";
            echo "<td>" . $evento['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<strong style='color: red;'>Nenhum evento encontrado para este usuário!</strong><br>";
        echo "Isso pode significar:<br>";
        echo "- Os eventos não estão sendo salvos<br>";
        echo "- O usuario_id está incorreto<br>";
        echo "- Há erro na inserção<br>";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// 5. Testar query completa
echo "<h3>5. Teste da Query Completa (como em agenda.php)</h3>";
try {
    $sql = "SELECT e.*, t.nome as tipo_nome, t.icone as tipo_icone, t.cor as tipo_cor 
            FROM eventos_agenda e 
            LEFT JOIN tipos_eventos_agenda t ON e.tipo_evento_id = t.id 
            WHERE e.usuario_id = ?
            ORDER BY e.data_evento ASC, e.prioridade DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$usuario['id']]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Eventos retornados pela query: " . count($eventos) . "<br>";
    if (count($eventos) > 0) {
        echo "<pre>";
        print_r(array_slice($eventos, 0, 3));
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "Erro na query: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// 6. Testar inserção manual
echo "<h3>6. Teste de Inserção Manual</h3>";
echo "<form method='POST'>";
echo "<input type='hidden' name='teste_inserir' value='1'>";
echo "<button type='submit'>Inserir Evento de Teste</button>";
echo "</form>";

if (isset($_POST['teste_inserir'])) {
    try {
        // Buscar primeiro tipo
        $stmt = $db->query("SELECT id FROM tipos_eventos_agenda LIMIT 1");
        $tipo = $stmt->fetch();
        
        if ($tipo) {
            $stmt = $db->prepare("INSERT INTO eventos_agenda (usuario_id, tipo_evento_id, titulo, descricao, data_evento, prioridade, status) 
                                  VALUES (?, ?, 'Evento de Teste', 'Criado pelo debug', NOW(), 'media', 'agendado')");
            $resultado = $stmt->execute([$usuario['id'], $tipo['id']]);
            
            if ($resultado) {
                echo "<strong style='color: green;'>Evento de teste inserido com sucesso!</strong><br>";
                echo "<a href='agenda.php'>Ir para Agenda</a>";
            } else {
                echo "<strong style='color: red;'>Erro ao inserir evento de teste</strong><br>";
            }
        } else {
            echo "<strong style='color: red;'>Nenhum tipo de evento encontrado. Execute instalar_agenda.php primeiro.</strong><br>";
        }
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";
echo "<a href='agenda.php'>← Voltar para Agenda</a> | <a href='instalar_agenda.php'>Instalar Tabelas</a>";
?>

