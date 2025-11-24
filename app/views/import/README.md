# Funcionalidade de Importação de Receitas BeerXML

Este diretório contém os arquivos necessários para a funcionalidade de importação de receitas no formato BeerXML para o sistema Atomos.

## Estrutura de Arquivos

- `index.php` - Página principal de importação
- `results.php` - Página de resultados da importação
- `../../controllers/ImportController.php` - Controlador responsável pela lógica de importação

## Como Usar

### Acesso à Funcionalidade

1. Acesse o sistema e faça login como administrador
2. Na página de receitas, clique no botão "📥 Importar Receitas"
3. Você será redirecionado para a página de importação

### Métodos de Importação

#### 1. Importação de Arquivos Individuais
- Clique na área de upload ou arraste e solte os arquivos BeerXML
- Selecione um ou mais arquivos XML
- Clique em "Iniciar Importação"

#### 2. Importação de Diretório
- Informe o caminho completo para um diretório contendo arquivos XML
- O sistema irá processar todos os arquivos .xml encontrados no diretório
- Clique em "Iniciar Importação"

### Permissões

Apenas usuários com perfil de **administrador** podem realizar importações de receitas.

## Funcionalidades

### Processamento de Receitas
- Importa todas as receitas contidas nos arquivos BeerXML
- Mapeia corretamente os elementos BeerXML para as tabelas do banco de dados:
  - Receitas → tabela `receitas`
  - Ingredientes → tabelas `insumos` e `receita_ingredientes`

### Tratamento de Dados Duplicados
- Receitas com o mesmo nome são atualizadas em vez de criadas novamente
- Ingredientes já existentes são atualizados com as novas informações

### Feedback Visual
- Barra de progresso durante o processamento
- Resumo detalhado dos resultados da importação
- Indicação visual de sucesso/erro para cada arquivo processado

## Mapeamento BeerXML → Banco de Dados

| Elemento BeerXML | Tabela no Banco de Dados | Campo(s) Correspondente(s) |
|------------------|--------------------------|-----------------------------|
| `<RECIPE>` | `receitas` | nome, estilo, descricao, volume_batch, densidade_inicial, densidade_final, ibu, srm, abv, tempo_fermentacao, temperatura_fermentacao |
| `<FERMENTABLE>` | `insumos` + `receita_ingredientes` | nome, tipo='malte', quantidade, fase='mostura' |
| `<HOP>` | `insumos` + `receita_ingredientes` | nome, tipo='lupulo', quantidade, fase (baseado em USE), tempo_adicao |
| `<YEAST>` | `insumos` + `receita_ingredientes` | nome, tipo='levedura', quantidade, fase='fermentacao' |
| `<MISC>` | `insumos` + `receita_ingredientes` | nome, tipo (baseado em TYPE), quantidade, fase (baseado em USE), tempo_adicao |

## Segurança

- Validação de tipo de arquivo (apenas .xml são aceitos)
- Verificação de permissões de usuário
- Tratamento de erros para arquivos inválidos
- Proteção contra uploads maliciosos

## Personalização

O código pode ser facilmente adaptado para:
- Adicionar suporte a novos elementos do BeerXML
- Modificar o mapeamento entre elementos BeerXML e campos do banco de dados
- Alterar as regras de tratamento de dados duplicados
- Adicionar novas fases de adição de ingredientes

## Suporte

Para problemas com a importação:
1. Verifique se os arquivos estão no formato BeerXML válido
2. Confirme se você tem permissões de administrador
3. Verifique o tamanho dos arquivos (limites do servidor)