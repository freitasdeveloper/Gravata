# 📚 Instruções para Literatura Jurídica

## ✅ O QUE FOI IMPLEMENTADO

### Menu Lateral
- ✅ Seção "Literatura" adicionada ao menu
- ✅ Link para "Biblioteca de Livros"
- ✅ Integrado em todos os arquivos via `includes/sidebar.php`

### Página de Literatura
- ✅ `literatura.php` criada com layout profissional
- ✅ Grid responsivo de livros
- ✅ Filtros por categoria
- ✅ Estatísticas de livros
- ✅ Sistema de copiar links
- ✅ Links para fontes oficiais

---

## 📝 COMO ADICIONAR LIVROS REAIS

### Passo 1: Identificar Fontes Reais

**Fontes Oficiais Recomendadas:**
1. **Domínio Público**: https://www.dominiopublico.gov.br/
2. **Biblioteca Digital do Senado**: https://www2.senado.leg.br/bdsf/
3. **Internet Archive**: https://archive.org/
4. **Brasiliana Digital USP**: https://www.brasiliana.usp.br/
5. **Biblioteca Nacional Digital**: http://bndigital.bn.gov.br/

### Passo 2: Editar literatura.php

Abra `literatura.php` e localize o array `$livros` (linha ~21):

```php
$livros = [
    [
        'titulo' => 'Título do Livro',
        'autor' => 'Nome do Autor',
        'categoria' => 'Direito Civil', // ou outra categoria
        'descricao' => 'Descrição curta do livro',
        'fonte' => 'Nome da Fonte Oficial',
        'link' => 'URL_COMPLETA_DO_LIVRO',
        'tipo' => 'PDF'
    ],
    // Adicione mais livros aqui
];
```

### Passo 3: Substituir Links de Exemplo

Os links atuais são **exemplos**. Substitua pelos links reais que você encontrar.

**Exemplo de substituição:**

```php
// ANTES (exemplo)
'link' => 'https://www.dominiopublico.gov.br/pesquisa/PesquisaObraDetalhe.jsp?id=12345',

// DEPOIS (real - exemplo)
'link' => 'https://www.dominiopublico.gov.br/download/texto/bv000186.pdf',
```

---

## 🔍 COMO BUSCAR LIVROS REAIS

### Método 1: Domínio Público
1. Acesse: https://www.dominiopublico.gov.br/
2. Pesquise por "direito civil", "direito penal", etc.
3. Encontre o livro desejado
4. Copie o link de download PDF
5. Adicione ao array `$livros`

### Método 2: Senado Federal
1. Acesse: https://www2.senado.leg.br/bdsf/
2. Pesquise por livros jurídicos
3. Verifique se está em domínio público
4. Copie o link direto para o PDF

### Método 3: Internet Archive
1. Acesse: https://archive.org/
2. Busque por "brazil law", "direito brasileiro"
3. Filtre por livros em português
4. Copie o link de visualização/Download

---

## 📖 LIVROS SUGERIDOS PARA ADICIONAR

### Direito Civil
- Maria Helena Diniz - Curso de Direito Civil (vários volumes)
- Sílvio de Salvo Venosa - Direito Civil
- Caio Mario da Silva Pereira - Instituições de Direito Civil

### Direito Penal
- Damásio E. de Jesus - Direito Penal
- Fernando Capez - Curso de Direito Penal
- Luiz Regis Prado - Curso de Direito Penal Brasileiro

### Direito Processual
- Cândido Rangel Dinamarco - Instituições de Direito Processual Civil
- Alexandre Câmara - Lições de Direito Processual Civil
- Antonio Magalhães Gomes Filho - Direito Processual Penal

### Direito Constitucional
- José Afonso da Silva - Curso de Direito Constitucional Positivo
- Luís Roberto Barroso - Curso de Direito Constitucional
- Uadi Lammêgo Bulos - Curso de Direito Constitucional

### Direito Administrativo
- Celso Antônio Bandeira de Mello - Curso de Direito Administrativo
- Diogo de Figueiredo Moreira Neto - Curso de Direito Administrativo
- Maria Sylvia Zanella Di Pietro - Direito Administrativo

### Direito do Trabalho
- Amauri Mascaro Nascimento - Curso de Direito do Trabalho
- Alice Monteiro de Barros - Curso de Direito do Trabalho
- Amador Paes de Almeida - CLT Comentada

### Outras Áreas
- Roque Carrazza - Curso de Direito Tributário
- Paulo Affonso Leme Machado - Direito Ambiental Brasileiro
- Fábio Ulhoa Coelho - Manual de Direito Comercial
- Pablo Stolze Gagliano - Novo Curso de Direito Civil

---

## ⚖️ IMPORTANTE - CONSIDERAÇÕES LEGAIS

### ✅ PERMITIDO
- Livros em **domínio público** (direitos autorais expirados)
- Obras de **bibliotecas oficiais** que autorizam acesso
- Links para **websites oficiais** que disponibilizam conteúdo

### ❌ PROIBIDO
- Livros sob direitos autorais ainda protegidos
- Scraping/Download de livros pagos sem autorização
- Distribuição não autorizada de conteúdo protegido

### 🔒 BOAS PRÁTICAS
- Sempre verificar se o livro está em domínio público
- Priorizar links para bibliotecas oficiais
- Manter atribuição correta aos autores
- Adicionar aviso sobre direitos autorais
- Links para download devem ser de fontes oficiais

---

## 📊 ESTRUTURA DA PÁGINA

```
literatura.php
├── Header/Navbar
├── Filtros por Categoria
├── Estatísticas (Total, Categorias, Gratuitos)
├── Grid de Livros
│   ├── Card por Livro
│   │   ├── Badges (Categoria, Tipo)
│   │   ├── Título
│   │   ├── Autor
│   │   ├── Descrição
│   │   └── Botões (Ler, Copiar Link)
│   └── Responsivo (col-md-6 col-lg-4)
├── Avisos Legais
└── Links Úteis (Bibliotecas Digitais)
```

---

## 🚀 PRÓXIMOS PASSOS

1. **Pesquise livros reais** nas fontes oficiais
2. **Substitua os links de exemplo** em `literatura.php`
3. **Teste cada link** para garantir funcionamento
4. **Adicione mais livros** conforme encontrar
5. **Atualize periodicamente** com novos títulos

---

## 📞 SUPORTE

Se precisar de ajuda para:
- Encontrar livros específicos
- Verificar direitos autorais
- Configurar links corretos
- Adicionar funcionalidades extras

Consulte: Documentação oficial das bibliotecas digitais

---

**Status**: Sistema configurado e pronto para receber livros reais!
**Próxima Ação**: Substituir links de exemplo por links reais de bibliotecas oficiais.

