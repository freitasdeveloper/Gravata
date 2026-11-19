<?php
/**
 * Página intermediária para buscar livro no Domínio Público
 * Solução simplificada: mostra dados e fornece bookmarklet para preenchimento
 */

require_once 'config.php';

$titulo = $_GET['titulo'] ?? '';
$autor = $_GET['autor'] ?? '';

if (empty($titulo)) {
    header('Location: literatura.php');
    exit;
}

// Preparar dados
$buscaTitulo = htmlspecialchars(trim($titulo), ENT_QUOTES, 'UTF-8');
$buscaAutor = !empty($autor) ? htmlspecialchars(trim($autor), ENT_QUOTES, 'UTF-8') : '';

// URL do formulário
$urlFormulario = "https://www.dominiopublico.gov.br/pesquisa/PesquisaObraForm.jsp";

// Criar código JavaScript minificado para bookmarklet
$jsCode = sprintf(
    'javascript:(function(){var t=%s;var a=%s;var ct=document.querySelector(\'input[name="texto_titulo"]\')||document.querySelector(\'input[id*="titulo"i]\');var ca=document.querySelector(\'input[name="co_autor"]\')||document.querySelector(\'input[id*="autor"i]\');var ci=document.querySelector(\'select[name="select_idioma"]\');if(ct){ct.value=t;ct.dispatchEvent(new Event(\'input\',{bubbles:true}));}if(ca&&a){ca.value=a;ca.dispatchEvent(new Event(\'input\',{bubbles:true}));}if(ci){for(var i=0;i<ci.options.length;i++){if(ci.options[i].value==\'PT\'){ci.selectedIndex=i;break;}}}var f=document.querySelector(\'form\');if(f){f.action=\'https://www.dominiopublico.gov.br/pesquisa/ResultadoPesquisaObraForm.do\';var af=document.querySelector(\'input[name="select_action"]\');if(!af){af=document.createElement(\'input\');af.type=\'hidden\';af.name=\'select_action\';af.value=\'Ok\';f.appendChild(af);}else{af.value=\'Ok\';}}})();',
    json_encode($buscaTitulo),
    json_encode($buscaAutor)
);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="2;url=<?php echo htmlspecialchars($urlFormulario); ?>">
    <title>Redirecionando para Domínio Público...</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #e8e0d6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            width: 100%;
            background: rgba(45, 45, 45, 0.95);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(212, 165, 116, 0.3);
        }
        h1 {
            color: #d4a574;
            margin-bottom: 10px;
            font-size: 28px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #aaa;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .info-card {
            background: rgba(212, 165, 116, 0.1);
            border: 2px solid rgba(212, 165, 116, 0.3);
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
        }
        .info-item {
            margin: 15px 0;
            font-size: 16px;
        }
        .info-label {
            color: #d4a574;
            font-weight: bold;
            display: inline-block;
            min-width: 80px;
        }
        .info-value {
            color: #fff;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .btn {
            flex: 1;
            min-width: 200px;
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #d4a574;
            color: #1a1a1a;
        }
        .btn-primary:hover {
            background: #c0955d;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 165, 116, 0.4);
        }
        .btn-secondary {
            background: rgba(212, 165, 116, 0.2);
            color: #d4a574;
            border: 2px solid rgba(212, 165, 116, 0.5);
        }
        .btn-secondary:hover {
            background: rgba(212, 165, 116, 0.3);
        }
        .instrucoes {
            background: rgba(0, 0, 0, 0.3);
            border-left: 4px solid #d4a574;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .instrucoes h3 {
            color: #d4a574;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .instrucoes ol {
            margin-left: 20px;
            line-height: 2;
        }
        .instrucoes li {
            margin: 8px 0;
        }
        .bookmarklet-box {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(212, 165, 116, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            position: relative;
        }
        .bookmarklet-code {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #d4a574;
            word-break: break-all;
            user-select: all;
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 5px;
        }
        .copy-btn {
            margin-top: 10px;
            padding: 8px 15px;
            background: rgba(212, 165, 116, 0.2);
            color: #d4a574;
            border: 1px solid rgba(212, 165, 116, 0.5);
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .copy-btn:hover {
            background: rgba(212, 165, 116, 0.3);
        }
        .success-msg {
            display: none;
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.5);
            color: #28a745;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            text-align: center;
        }
        .loading {
            text-align: center;
            margin: 20px 0;
        }
        .spinner {
            border: 3px solid rgba(212, 165, 116, 0.3);
            border-top: 3px solid #d4a574;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Busca no Domínio Público</h1>
        <p class="subtitle">Preenchendo formulário automaticamente...</p>

        <div class="info-card">
            <div class="info-item">
                <span class="info-label">Título:</span>
                <span class="info-value"><?php echo htmlspecialchars($buscaTitulo); ?></span>
            </div>
            <?php if ($buscaAutor): ?>
            <div class="info-item">
                <span class="info-label">Autor:</span>
                <span class="info-value"><?php echo htmlspecialchars($buscaAutor); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="loading">
            <div class="spinner"></div>
            <p style="margin-top: 15px; color: #aaa;">Redirecionando em alguns segundos...</p>
        </div>

        <div class="btn-group">
            <a href="<?php echo htmlspecialchars($urlFormulario); ?>" target="_blank" class="btn btn-primary">
                ✨ Abrir Formulário Agora
            </a>
            <button onclick="executarPreenchimento()" class="btn btn-secondary">
                📋 Executar Preenchimento
            </button>
        </div>

        <div class="instrucoes">
            <h3>📖 Como usar o preenchimento automático:</h3>
            <ol>
                <li>Clique em "Abrir Formulário Agora" acima</li>
                <li>Após o formulário carregar, volte para esta página</li>
                <li>Clique em "Executar Preenchimento" ou cole o código abaixo na barra de endereços do navegador</li>
                <li>Os campos serão preenchidos automaticamente!</li>
            </ol>

            <div class="bookmarklet-box">
                <p style="margin-bottom: 10px; color: #aaa; font-size: 14px;">
                    <strong>Código para preenchimento automático:</strong>
                </p>
                <div class="bookmarklet-code" id="codigoBookmarklet"><?php echo htmlspecialchars($jsCode); ?></div>
                <button class="copy-btn" onclick="copiarCodigo()">📋 Copiar Código</button>
                <div class="success-msg" id="msgSucesso">✓ Código copiado! Cole na barra de endereços do navegador.</div>
            </div>
        </div>
    </div>

    <script>
        var codigoBookmarklet = <?php echo json_encode($jsCode); ?>;
        var urlFormulario = <?php echo json_encode($urlFormulario); ?>;

        function copiarCodigo() {
            var codigo = document.getElementById('codigoBookmarklet').textContent;
            navigator.clipboard.writeText(codigo).then(function() {
                var msg = document.getElementById('msgSucesso');
                msg.style.display = 'block';
                setTimeout(function() {
                    msg.style.display = 'none';
                }, 3000);
            }).catch(function() {
                // Fallback
                var textarea = document.createElement('textarea');
                textarea.value = codigo;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Código copiado! Cole na barra de endereços.');
            });
        }

        function executarPreenchimento() {
            // Tentar abrir o formulário e executar o código
            var novaJanela = window.open(urlFormulario, '_blank');
            
            if (novaJanela) {
                alert('Após o formulário carregar completamente, cole o código copiado na barra de endereços e pressione Enter.');
                copiarCodigo();
                
                // Tentar injetar script após alguns segundos
                setTimeout(function() {
                    try {
                        if (novaJanela && !novaJanela.closed) {
                            var script = novaJanela.document.createElement('script');
                            script.textContent = codigoBookmarklet.replace('javascript:', '');
                            novaJanela.document.body.appendChild(script);
                        }
                    } catch (e) {
                        console.log('Não foi possível injetar automaticamente. Use o código manualmente.');
                    }
                }, 3000);
            } else {
                alert('Popup bloqueado. Por favor, abra o formulário manualmente e use o código fornecido.');
                copiarCodigo();
            }
        }

        // Auto-abrir formulário após 2 segundos
        setTimeout(function() {
            var btn = document.querySelector('.btn-primary');
            if (btn) {
                btn.click();
            }
        }, 2000);
    </script>
</body>
</html>
