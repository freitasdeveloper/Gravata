<?php
/**
 * Upload de Documentos
 */

// Desabilitar exibição de erros na saída
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir header JSON desde o início
header('Content-Type: application/json');

require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autorizado']);
    exit;
}

// Capturar qualquer output inesperado
ob_start();

$db = Database::getInstance()->getConnection();
$usuario_id = $_SESSION['usuario_id'];
$resposta = ['sucesso' => false, 'mensagem' => ''];

// Verificar se a tabela existe
try {
    $db->query("SELECT 1 FROM documentos_enviados LIMIT 1");
} catch (PDOException $e) {
    ob_end_clean(); // Limpar qualquer output
    $resposta['mensagem'] = 'Sistema de upload não configurado. Execute o script de instalação.';
    echo json_encode($resposta);
    exit;
}

// Verificar se é upload de arquivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {
    $arquivo = $_FILES['arquivo'];
    $categoria = $_POST['categoria'] ?? 'Sem Categoria';
    $descricao = $_POST['descricao'] ?? '';
    $tags = $_POST['tags'] ?? '';
    
    // Validar arquivo
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        ob_end_clean(); // Limpar qualquer output
        $mensagens_erro = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede o tamanho máximo permitido pelo servidor',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho máximo permitido pelo formulário',
            UPLOAD_ERR_PARTIAL => 'Arquivo foi enviado parcialmente',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta pasta temporária',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar arquivo no disco',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
        ];
        $resposta['mensagem'] = $mensagens_erro[$arquivo['error']] ?? 'Erro no upload do arquivo (código: ' . $arquivo['error'] . ')';
        echo json_encode($resposta);
        exit;
    }
    
    // Validar tamanho (max 50MB)
    $tamanho_maximo = 50 * 1024 * 1024; // 50MB
    if ($arquivo['size'] > $tamanho_maximo) {
        ob_end_clean(); // Limpar qualquer output
        $resposta['mensagem'] = 'Arquivo muito grande. Tamanho máximo: 50MB';
        echo json_encode($resposta);
        exit;
    }
    
    // Validar tipo de arquivo
    $extensoes_permitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extensao, $extensoes_permitidas)) {
        ob_end_clean(); // Limpar qualquer output
        $resposta['mensagem'] = 'Tipo de arquivo não permitido. Extensões permitidas: ' . implode(', ', $extensoes_permitidas);
        echo json_encode($resposta);
        exit;
    }
    
    // Detectar tipo MIME
    $tipo_arquivo = mime_content_type($arquivo['tmp_name']);
    // Se mime_content_type falhar, tentar pelo tipo do navegador
    if (!$tipo_arquivo && isset($arquivo['type'])) {
        $tipo_arquivo = $arquivo['type'];
    }
    // Fallback baseado na extensão
    if (!$tipo_arquivo) {
        $tipos_mime = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        $tipo_arquivo = $tipos_mime[$extensao] ?? 'application/octet-stream';
    }
    
    // Criar diretório do usuário se não existir
    $diretorio_base = __DIR__ . '/../uploads/documentos/';
    
    // Criar diretório base se não existir
    if (!file_exists($diretorio_base)) {
        if (!mkdir($diretorio_base, 0755, true)) {
            ob_end_clean(); // Limpar qualquer output
            $resposta['mensagem'] = 'Erro ao criar diretório de uploads. Verifique as permissões.';
            echo json_encode($resposta);
            exit;
        }
    }
    
    $diretorio_usuario = $diretorio_base . $usuario_id . '/';
    if (!file_exists($diretorio_usuario)) {
        if (!mkdir($diretorio_usuario, 0755, true)) {
            ob_end_clean(); // Limpar qualquer output
            $resposta['mensagem'] = 'Erro ao criar diretório do usuário. Verifique as permissões.';
            echo json_encode($resposta);
            exit;
        }
    }
    
    // Gerar nome único para o arquivo (extensao já foi definida acima)
    $nome_arquivo = uniqid('doc_') . '_' . time() . '.' . $extensao;
    $caminho_completo = $diretorio_usuario . $nome_arquivo;
    
    // Caminho relativo para salvar no banco
    $caminho_relativo = 'uploads/documentos/' . $usuario_id . '/' . $nome_arquivo;
    
    // Mover arquivo
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
        // Salvar no banco de dados
        try {
            $stmt = $db->prepare("
                INSERT INTO documentos_enviados 
                (usuario_id, nome_arquivo, nome_original, caminho_arquivo, tipo_arquivo, tamanho, categoria, descricao, tags) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $usuario_id,
                $nome_arquivo,
                $arquivo['name'],
                $caminho_relativo,
                $tipo_arquivo,
                $arquivo['size'],
                $categoria,
                $descricao,
                $tags
            ]);
            
            $resposta['sucesso'] = true;
            $resposta['mensagem'] = 'Documento enviado com sucesso!';
            $resposta['id'] = $db->lastInsertId();
        } catch (PDOException $e) {
            // Remover arquivo se falhar
            @unlink($caminho_completo);
            $resposta['mensagem'] = 'Erro ao salvar no banco de dados: ' . $e->getMessage();
            // Em produção, não mostrar detalhes do erro
            if (defined('ENVIRONMENT') && ENVIRONMENT !== 'development') {
                $resposta['mensagem'] = 'Erro ao salvar no banco de dados. Contate o suporte.';
            }
            error_log('Erro ao salvar documento: ' . $e->getMessage());
        }
    } else {
        $resposta['mensagem'] = 'Erro ao mover arquivo. Verifique as permissões do diretório.';
        // Verificar permissões do diretório
        if (!is_writable($diretorio_usuario)) {
            $resposta['mensagem'] = 'Diretório sem permissão de escrita. Contate o administrador.';
        }
        error_log('Erro ao mover arquivo para: ' . $diretorio_usuario);
    }
} else {
    $resposta['mensagem'] = 'Nenhum arquivo foi enviado';
}

// Limpar qualquer output inesperado antes de enviar JSON
ob_end_clean();

echo json_encode($resposta);

