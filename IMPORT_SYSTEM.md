# Sistema de Importação de Receitas BeerXML para Atomos

## Visão Geral

Este documento descreve o sistema de importação de receitas BeerXML desenvolvido para o sistema de gestão cervejeira Atomos. O sistema permite importar receitas no formato BeerXML, amplamente utilizado por softwares de elaboração de cerveja, diretamente para o banco de dados do Atomos.

## Estrutura do Sistema

### Componentes Principais

1. **ImportController.php** - Controlador responsável pela lógica de importação
2. **Views de Importação** - Interface web para importação de receitas
3. **Migrações de Banco de Dados** - Scripts SQL para preparar o banco de dados
4. **Rotas** - Configuração de URLs para acessar a funcionalidade

### Arquivos Criados

```
/app/controllers/ImportController.php          # Controlador de importação
/app/views/import/                             # Views da interface de importação
  ├── index.php                                # Página principal de importação
  ├── results.php                              # Página de resultados
  └── README.md                                # Documentação das views
/public/css/import.css                         # Estilos específicos para importação
/public/js/import.js                           # JavaScript para funcionalidades avançadas
/database/migrations/002_add_import_columns.sql # Migração para colunas adicionais
/app/views/receitas/index.php                  # Atualizado com botão de importação
/app/views/layouts/header.php                  # Atualizado com item de menu
/index.php                                     # Atualizado com rotas de importação
/test_import.php                               # Script de teste
/IMPORT_SYSTEM.md                              # Esta documentação
```

## Funcionalidades

### Importação por Arquivos

1. Interface web com área de drag and drop
2. Suporte a múltiplos arquivos simultaneamente
3. Validação de tipo de arquivo (.xml)
4. Feedback visual durante o processo

### Importação por Diretório

1. Processamento em lote de todos os arquivos XML em um diretório
2. Verificação automática de arquivos BeerXML válidos
3. Tratamento de erros por arquivo

### Processamento de Receitas

1. Mapeamento completo de elementos BeerXML para o banco de dados
2. Criação ou atualização de receitas existentes
3. Processamento de ingredientes (maltes, lúpulos, leveduras, diversos)
4. Preservação de dados existentes com atualização inteligente

### Interface do Usuário

1. Design responsivo e intuitivo
2. Barra de progresso durante importação
3. Resumo detalhado dos resultados
4. Navegação fácil entre etapas

## Mapeamento BeerXML → Banco de Dados

### Receitas
- `<RECIPE><NAME>` → `receitas.nome`
- `<RECIPE><STYLE><NAME>` → `receitas.estilo`
- `<RECIPE><NOTES>` → `receitas.descricao`
- `<RECIPE><BATCH_SIZE>` → `receitas.volume_batch`
- `<RECIPE><OG>` → `receitas.densidade_inicial`
- `<RECIPE><FG>` → `receitas.densidade_final`
- `<RECIPE><IBU>` → `receitas.ibu`
- `<RECIPE><EST_COLOR>` → `receitas.srm`
- `<RECIPE><ABV>` → `receitas.abv`
- `<RECIPE><PRIMARY_AGE>` → `receitas.tempo_fermentacao`
- `<RECIPE><PRIMARY_TEMP>` → `receitas.temperatura_fermentacao`

### Ingredientes (Insumos)
- `<FERMENTABLE><NAME>` → `insumos.nome` (tipo='malte')
- `<HOP><NAME>` → `insumos.nome` (tipo='lupulo')
- `<YEAST><NAME>` → `insumos.nome` (tipo='levedura')
- `<MISC><NAME>` → `insumos.nome` (tipo conforme `<TYPE>`)

### Propriedades Adicionais de Insumos

#### Maltes e Fermentáveis
- `origem` - Origem do malte
- `fornecedor_principal_id` - Fornecedor do insumo
- `cor` - Cor em SRM
- `rendimento` - Rendimento em porcentagem

#### Lúpulos
- `alfa_acidos` - Teor de alfa-ácidos
- `forma` - Forma do lúpulo (pellet, plug, leaf, extract)

#### Leveduras
- `laboratorio` - Laboratório fabricante
- `produto_id` - Código do produto
- `atenuacao` - Atenuação em porcentagem
- `temperatura_minima` - Temperatura mínima de fermentação
- `temperatura_maxima` - Temperatura máxima de fermentação

## Segurança

### Permissões
- Apenas usuários com perfil **administrador** podem realizar importações
- Validação de tipo de arquivo para prevenir uploads maliciosos
- Tratamento seguro de caminhos de diretório

### Validação
- Verificação de estrutura BeerXML
- Validação de dados antes da inserção no banco
- Tratamento de erros sem comprometer o sistema

## Uso

### Acesso à Funcionalidade

1. Faça login como administrador
2. Acesse "Receitas" no menu lateral
3. Clique no botão "📥 Importar Receitas"
4. Ou acesse diretamente via `/import`

### Importação de Arquivos

1. Arraste arquivos XML para a área de upload ou clique para selecionar
2. Selecione um ou mais arquivos BeerXML
3. Clique em "Iniciar Importação"
4. Aguarde o processamento
5. Revise os resultados

### Importação de Diretório

1. Informe o caminho completo para um diretório contendo arquivos XML
2. O sistema processará todos os arquivos .xml encontrados
3. Clique em "Iniciar Importação"
4. Aguarde o processamento
5. Revise os resultados

## Personalização

### Adicionando Novos Elementos BeerXML

1. Modifique o `ImportController.php` para processar novos elementos
2. Atualize o mapeamento de colunas conforme necessário
3. Adicione novas colunas ao banco de dados via migrações

### Modificando Regras de Importação

1. Ajuste as funções de mapeamento de fases
2. Modifique as regras de tratamento de dados duplicados
3. Personalize as mensagens de erro e sucesso

## Testes

### Teste de Funcionalidade

Execute o script de teste:
```bash
php test_import.php
```

### Teste Manual

1. Acesse a interface web de importação
2. Tente importar um arquivo BeerXML de exemplo
3. Verifique se os dados aparecem corretamente no sistema
4. Confirme que não há erros no processo

## Manutenção

### Atualização do Sistema

1. Execute as migrações de banco de dados
2. Verifique a compatibilidade com novas versões do BeerXML
3. Atualize a documentação conforme necessário

### Resolução de Problemas

#### Problemas Comuns

1. **Arquivos não são reconhecidos**
   - Verifique a extensão (.xml)
   - Confirme que o conteúdo é BeerXML válido

2. **Permissões insuficientes**
   - Confirme que o usuário é administrador
   - Verifique as permissões do banco de dados

3. **Erros de importação**
   - Revise os logs de erro
   - Verifique a estrutura dos arquivos XML

## Limitações Conhecidas

1. O sistema assume que os arquivos BeerXML seguem o padrão v1
2. Alguns elementos específicos de softwares podem não ser totalmente compatíveis
3. A importação não processa perfis de brassagem complexos
4. Não há suporte para importação de equipamentos ou perfis de água

## Melhorias Futuras

1. Adicionar suporte a versões mais recentes do BeerXML
2. Implementar importação de perfis de brassagem
3. Adicionar validação mais robusta de dados
4. Criar interface para mapeamento personalizado de campos
5. Adicionar suporte a exportação de receitas no formato BeerXML

## Suporte

Para problemas com o sistema de importação:
1. Verifique esta documentação
2. Revise os logs de erro do sistema
3. Confirme que os arquivos BeerXML são válidos
4. Contate o suporte técnico se necessário