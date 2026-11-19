# 🎯 Implementação de Dados Reais - GUIA RÁPIDO

## ⚠️ IMPORTANTE
Atualmente o dashboard está configurado para mostrar **apenas dados reais**. 

Como nenhuma API/feed está configurado ainda, o sistema exibe uma mensagem informativa ao usuário sobre como configurar fontes oficiais.

---

## 🚀 Como Implementar Dados Reais

### Opção 1: Feeds RSS (Mais Simples e Gratuito)

#### Passo 1: Descobrir Feeds Oficiais

Visite cada site e procure pelo ícone RSS ou tente URLs comuns:

```
STF: https://portal.stf.jus.br/noticias/rss.xml
STJ: https://www.stj.jus.br/portal_stj/publicacao/engine.wsp?tmp.area=398
TST: https://www.tst.jus.br/noticias/rss.xml
CNJ: https://www.cnj.jus.br/imprensa/noticias/rss
OAB: https://www.oab.org.br/noticias/rss
```

#### Passo 2: Atualizar ajax/buscar_juris_news.php

Descomente a seção "SEÇÃO 1" e adicione as URLs dos feeds:

```php
$feedsOficiais = [
    'stf' => 'https://portal.stf.jus.br/noticias/rss.xml',
    'stj' => 'https://www.stj.jus.br/feed',
    // Adicione mais conforme disponível
];
```

#### Passo 3: Testar

Recarregue o dashboard e verifique se os dados aparecem.

---

### Opção 2: API DataJud (Recomendado para Jurisprudências)

#### Passo 1: Registrar no DataJud

1. Acesse: https://datajud.cnj.jus.br/
2. Crie uma conta
3. Obtenha seu token de API

#### Passo 2: Configurar Credenciais

No arquivo `config.php`, adicione:

```php
define('DATJUD_TOKEN', 'seu_token_aqui');
```

#### Passo 3: Atualizar ajax/buscar_juris_news.php

Descomente a seção "SEÇÃO 2" e adicione seu token:

```php
$dataJudToken = DATJUD_TOKEN;
```

#### Passo 4: Testar

Recarregue o dashboard e verifique se os dados aparecem.

---

### Opção 3: API Jusbrasil (Recomendado para Diários)

#### Passo 1: Registrar no Jusbrasil

1. Acesse: https://api.jusbrasil.com.br/
2. Crie uma conta
3. Obtenha sua API Key

#### Passo 2: Configurar Credenciais

No arquivo `config.php`, adicione:

```php
define('JUSBRASIL_API_KEY', 'sua_key_aqui');
```

#### Passo 3: Atualizar ajax/buscar_juris_news.php

Descomente a seção "SEÇÃO 3" e adicione sua key:

```php
$jusbrasilKey = JUSBRASIL_API_KEY;
```

---

## 📝 Checklist de Implementação

- [ ] Identifiquei feeds RSS oficiais funcionais
- [ ] OU me registrei no DataJud/Jusbrasil
- [ ] Adicionei credenciais em config.php
- [ ] Descomentei as seções no ajax/buscar_juris_news.php
- [ ] Testei em ambiente de desenvolvimento
- [ ] Validei que os dados são reais e atualizados
- [ ] Implementei cache para reduzir requisições
- [ ] Configurei tratamento de erros
- [ ] Documentei mudanças para a equipe

---

## 🔒 Segurança

**NUNCA** commite credenciais de API no Git!

Use arquivo `.env` ou `config.php` que já está no `.gitignore`.

---

## 🎯 Resultado Esperado

Após implementação, o dashboard deve mostrar:
- ✅ Jurisprudências reais de tribunais
- ✅ Notícias oficiais do STF, STJ, TST, etc.
- ✅ Novidades do mundo jurídico
- ✅ Informações atualizadas dos tribunais
- ✅ Links diretos para documentos oficiais
- ✅ Timestamps precisos de publicação

---

## 📞 Problemas?

Consulte a documentação completa em: `api_fontes_oficiais.md`

