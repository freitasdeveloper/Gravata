# 📋 Guia de Integração com Fontes Oficiais

## ⚠️ IMPORTANTE: Implementação de Dados Reais

Este documento descreve como integrar fontes **OFCIAIS** de dados jurídicos para garantir que todas as informações sejam **100% reais** e confiáveis.

---

## 🎯 Fontes Oficiais Recomendadas

### 1. **CNJ - API Pública do DataJud**
- **URL**: https://api-publica.datajud.cnj.jus.br/
- **Documentação**: https://datajud.cnj.jus.br/
- **Descrição**: API oficial do CNJ com metadados de processos de diversos tribunais
- **Autenticação**: Sim (recomendado)
- **Limitações**: Rate limiting aplicável

### 2. **Jusbrasil - API de Diários Oficiais**
- **URL**: https://api.jusbrasil.com.br/docs/
- **Documentação**: https://api.jusbrasil.com.br/docs/diarios_oficiais/index.html
- **Descrição**: API para acessar dados de diários judiciais oficiais
- **Autenticação**: Sim (API Key necessária)
- **Custo**: Planos gratuitos e pagos disponíveis

### 3. **Feeds RSS Oficiais dos Tribunais**

#### STF (Supremo Tribunal Federal)
- **URL Base**: https://portal.stf.jus.br/
- **Notícias**: /noticias/
- **Decisões**: Verificar se há RSS disponível

#### STJ (Superior Tribunal de Justiça)
- **URL Base**: https://www.stj.jus.br/
- **Notícias**: Verificar feed RSS oficial

#### TST (Tribunal Superior do Trabalho)
- **URL Base**: https://www.tst.jus.br/
- **Notícias**: Verificar feed RSS oficial

#### CNJ (Conselho Nacional de Justiça)
- **URL Base**: https://www.cnj.jus.br/
- **Notícias**: Verificar feed RSS oficial

#### OAB Nacional
- **URL Base**: https://www.oab.org.br/
- **Notícias**: Verificar feed RSS oficial

---

## 🔧 Como Implementar

### Opção 1: Feeds RSS (Mais Simples)

1. **Descobrir URLs dos Feeds RSS:**
   ```bash
   # Visitar cada site e procurar por:
   - Link no navegador com ícone RSS
   - Ou adicionar /feed ou /rss no final da URL
   - Exemplo: stf.jus.br/feed ou stf.jus.br/rss
   ```

2. **Parser RSS em PHP:**
   ```php
   // Usar SimpleXML ou biblioteca dedicada
   $rss = simplexml_load_file($urlFeed);
   foreach ($rss->channel->item as $item) {
       $titulo = (string)$item->title;
       $link = (string)$item->link;
       $data = (string)$item->pubDate;
   }
   ```

### Opção 2: APIs Oficiais (Recomendado)

#### Integração com DataJud
```php
// Exemplo de requisição
$url = 'https://api-publica.datajud.cnj.jus.br/api/v1/cnj/<endpoint>';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer <seu_token>',
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
```

#### Integração com Jusbrasil
```php
// Exemplo de requisição
$url = 'https://api.jusbrasil.com.br/v1/<endpoint>';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: <sua_api_key>',
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
```

### Opção 3: Scraping Responsável (Ultimo Recurso)

**⚠️ SOMENTE se feeds RSS/API não estiverem disponíveis**

- Sempre verificar robots.txt
- Respeitar rate limits
- Não sobrecarregar servidores
- Usar User-Agent apropriado
- Cachear resultados

---

## 📝 Passos para Integração Completa

### Passo 1: Identificar Fontes Reais

Visite cada tribunal e descubra:
- [ ] STF tem feed RSS? ___ 
- [ ] STJ tem feed RSS? ___ 
- [ ] TST tem feed RSS? ___ 
- [ ] CNJ tem feed RSS? ___ 
- [ ] OAB tem feed RSS? ___ 

### Passo 2: Testar Conectividade

```php
// Script de teste
$feeds = [
    'STF' => 'http://stf.jus.br/feed',
    'STJ' => 'http://stj.jus.br/feed',
    // etc...
];

foreach ($feeds as $nome => $url) {
    $xml = @simplexml_load_file($url);
    echo $nome . ': ' . ($xml ? 'OK' : 'FALHOU') . "\n";
}
```

### Passo 3: Implementar Cache

```php
// Cachear resultados por 30 minutos
function getNoticiasComCache($url, $cacheTime = 1800) {
    $cacheFile = 'cache/' . md5($url) . '.json';
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    // Buscar dados reais
    $dados = buscarFeedReal($url);
    
    // Salvar no cache
    file_put_contents($cacheFile, json_encode($dados));
    
    return $dados;
}
```

### Passo 4: Tratamento de Erros

```php
try {
    $dados = buscarFeedReal($url);
} catch (Exception $e) {
    // Log do erro
    error_log("Erro ao buscar feed: " . $e->getMessage());
    
    // Retornar dados em cache ou aviso
    return ['erro' => 'Feed temporariamente indisponível'];
}
```

---

## 🔐 Segurança e Credenciais

### APIs que Requerem Autenticação:

1. **Jusbrasil**
   - Registrar em: https://api.jusbrasil.com.br/
   - Obter API Key
   - Armazenar em `config.php`:
   ```php
   define('JUSBRASIL_API_KEY', 'sua_key_aqui');
   ```

2. **DataJud**
   - Registrar em: https://datajud.cnj.jus.br/
   - Obter token de acesso
   - Armazenar com segurança

---

## 📊 Estrutura de Dados Retornados

```php
[
    'sucesso' => true,
    'fonte' => 'STF - Oficial',
    'ultima_atualizacao' => '2024-01-15 10:30:00',
    'itens' => [
        [
            'titulo' => 'Título real da notícia',
            'link' => 'https://stf.jus.br/url-real',
            'data' => '2024-01-15 10:00:00',
            'resumo' => 'Resumo oficial...',
            'tribunal' => 'STF'
        ]
    ]
]
```

---

## ✅ Checklist de Implementação

- [ ] Identificar feeds RSS oficiais disponíveis
- [ ] Testar conectividade com cada feed
- [ ] Implementar parser RSS
- [ ] Implementar sistema de cache
- [ ] Adicionar tratamento de erros
- [ ] Configurar API keys (se necessário)
- [ ] Testar atualização em tempo real
- [ ] Validar qualidade dos dados
- [ ] Atualizar frontend para mostrar fonte oficial
- [ ] Implementar logging de erros

---

## 🚀 Exemplo de Implementação Completa

Ver arquivo: `ajax/buscar_dados_reais.php` (a ser criado após identificar fontes)

---

## ⚠️ Aviso Legal

Ao implementar integração com fontes oficiais:
- Respeitar termos de uso de cada API
- Não redistribuir conteúdo protegido
- Atribuir créditos corretamente
- Manter dados atualizados
- Fornecer meios de contato em caso de problemas

---

## 📞 Suporte

Para implementação completa com fontes reais, recomenda-se:
1. Contatar suporte técnico de cada tribunal
2. Verificar documentação oficial de APIs
3. Testar em ambiente de desenvolvimento
4. Implementar gradualmente

---

**Status Atual**: Sistema configurado para receber dados reais.
**Próximo Passo**: Identificar e integrar feeds RSS/APIs oficiais.

