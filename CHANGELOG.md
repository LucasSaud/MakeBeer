# 📝 Changelog - Sistema Atomos

## [2.1.0] - 2025-11-12

### 🐛 Correções

#### Correções de Envase
- ✅ Corrigido erro "Field 'barril_id' doesn't have a default value" ao criar envase
- ✅ Corrigido erro "Field 'quantidade_litros' doesn't have a default value" ao criar envase
- ✅ Corrigido problema de chave indefinida "codigo" nas views de envase
- ✅ Corrigido arredondamento de volume do barril - agora preserva casas decimais
- ✅ Corrigido acesso a colunas inexistentes na tabela envases
- ✅ Corrigido JOINs com tabelas de barris para usar barris_cadastro

#### Correções de Produção
- ✅ Corrigido erro "acao nao encontrada" ao atualizar status de lotes em produção
- ✅ Corrigido formulário de atualização de status na view de detalhes do lote
- ✅ Adicionado campo hidden com ID do lote no formulário de atualização

#### Correções Gerais
- ✅ Corrigido problema de sessão duplicada na tela de login
- ✅ Adicionada verificação de token CSRF nos formulários de autenticação
- ✅ Corrigido warnings de acesso a índices inexistentes em arrays

### 🔧 Melhorias

#### Interface
- ✅ Melhorada a precisão na exibição de volumes (sem arredondamento)
- ✅ Adicionada seleção de barril no formulário de criação de envase
- ✅ Corrigida exibição de status nas views de envase

#### Sistema
- ✅ Melhorada a estrutura de dados para envase de barris
- ✅ Adicionada verificação de campos obrigatórios no modelo de Envase
- ✅ Otimizada a busca de informações relacionadas aos lotes

## [2.0.0] - 2025-01-19

### ✨ Novos Recursos

#### Módulo de Envase e Barris
- ✅ Sistema completo de envase de barris
- ✅ Registro individual de cada barril
- ✅ Controle de saída de barris
- ✅ Estoque de barris em câmara fria
- ✅ Rastreamento completo do lote até a saída

#### Interface
- ✅ Menu lateral com ícones
- ✅ Design moderno e responsivo
- ✅ Tooltips informativos
- ✅ Item ativo destacado
- ✅ Header fixo no topo

#### Scripts de Instalação
- ✅ `setup.php` - Instalador completo com interface gráfica
- ✅ `atualizar.php` - Atualizador inteligente que detecta tabelas faltantes
- ✅ Compatível com Windows e Linux

### 🔧 Melhorias

#### Banco de Dados
- ✅ Todas as migrações consolidadas em 2 arquivos:
  - `001_create_all_tables.sql` - Criação completa
  - `002_seed_data.sql` - Dados de exemplo
- ✅ Adicionadas colunas de controle de envase em `lotes_producao`
- ✅ Criadas tabelas: `envases`, `barris`, `saida_barril`, `estoque_barris`

#### Sistema
- ✅ Função `icon()` protegida contra redeclaração
- ✅ Removidos includes duplicados de `icons.php`
- ✅ CSS consolidado em arquivo único
- ✅ Estrutura de pastas organizada

### 🗑️ Arquivos Removidos

#### Documentação Antiga
- ❌ VIEWS_CRIADAS.md
- ❌ INSTRUCOES_CAMARAFRIA.md
- ❌ ANALISE_COMPATIBILIDADE_CROSS_PLATFORM.md
- ❌ INSTALACAO_WINDOWS.md
- ❌ COMPATIBILIDADE_RESUMO.md
- ❌ RELATORIO_CAMARAFRIA.md
- ❌ SETUP_README.md
- ❌ IMPLEMENTACAO_ENVASE.md
- ❌ GUIA_RAPIDO_INSTALACAO.md
- ❌ CORRECOES_REALIZADAS.md
- ❌ MENU_LATERAL_IMPLEMENTADO.md
- ❌ Todos os arquivos .txt

#### Migrações Antigas
- ❌ create_tables.sql
- ❌ create_camarafria_tables.sql
- ❌ seed_data.sql
- ❌ seed_data_complete.sql
- ❌ 004_create_envase_tables.sql
- ❌ 004_1_alter_lotes_producao.sql
- ❌ 004_2_create_envase_tables_simple.sql

#### Arquivos de Layout Antigos
- ❌ header-old.php
- ❌ footer-old.php
- ❌ style-old.css

### 🐛 Correções

#### Erros Corrigidos
- ✅ Erro: "Cannot redeclare icon()" - Protegido com `function_exists()`
- ✅ Erro: "Table saida_barril doesn't exist" - Criada no script de atualização
- ✅ Erro: CSS não carregando - Corrigido caminho em header.php
- ✅ Erro: Includes duplicados - Removidos de todas as views

#### Páginas Corrigidas
- ✅ /produtos/index.php
- ✅ /produtos/view.php
- ✅ /envase/view.php
- ✅ /envase/form.php
- ✅ /saidabarril/index.php
- ✅ /saidabarril/view.php
- ✅ /saidabarril/form.php
- ✅ /estoque/view.php
- ✅ /estoque/index.php

### 📦 Estrutura Final

```
/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   ├── helpers/
│   └── lang/
├── database/
│   └── migrations/
│       ├── 001_create_all_tables.sql
│       └── 002_seed_data.sql
├── public/
│   ├── css/style.css
│   ├── js/
│   └── images/
├── storage/
├── index.php
├── setup.php
├── atualizar.php
├── README.md
└── CHANGELOG.md
```

### 🎯 Tabelas do Sistema

#### Principais
- usuarios
- fornecedores
- categorias_insumos
- insumos
- entradas_estoque

#### Produção
- receitas
- receita_ingredientes
- lotes_producao
- lote_consumos

#### Envase e Barris (NOVO)
- **envases** - Registros de envase
- **barris** - Detalhamento de cada barril
- **saida_barril** - Baixas/saídas
- **estoque_barris** - Estoque atual

#### Produtos
- produtos_finais
- producao_produtos

#### Câmara Fria
- camarafria_setores
- estoque_localizacao
- camarafria_movimentacoes
- camarafria_temperatura

#### Sistema
- movimentacoes_estoque
- log_atividades

### 📊 Estatísticas

- **Total de Tabelas**: 22
- **Novas Tabelas**: 4 (envase e barris)
- **Arquivos Removidos**: 25+
- **Linhas de Código Limpas**: ~500+

### 🚀 Próximos Passos

Para atualizar um sistema em produção:

1. Faça backup do banco de dados
2. Copie os novos arquivos
3. Acesse `atualizar.php`
4. Execute a atualização
5. Delete `atualizar.php`

### ⚠️ Avisos Importantes

- **Backup obrigatório** antes de atualizar
- Scripts `setup.php` e `atualizar.php` devem ser removidos após uso
- Altere a senha padrão do admin
- Verifique permissões da pasta `storage/`

---

**Versão 2.0.0** - Sistema completo e otimizado 🎉