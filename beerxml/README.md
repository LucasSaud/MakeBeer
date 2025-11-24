# Importação de Receitas BeerXML

Este diretório contém scripts e recursos para importar receitas no formato BeerXML para o sistema de gestão cervejeira Atomos.

## Estrutura de Arquivos

- `*.xml` - Arquivos de receitas BeerXML exportados do Brewfather
- `import_beerxml_recipe.sql` - Script SQL para importação manual de uma receita
- `../import_beerxml.php` - Script PHP para importação automática de receitas
- `../run_import.php` - Script de linha de comando para executar a importação

## Como Importar Receitas

### Método 1: Usando SQL (Manual)

1. Abra o script `import_beerxml_recipe.sql`
2. Execute o script em seu cliente de banco de dados MySQL/MariaDB
3. O script criará a receita "Atomos Session" e todos os seus ingredientes

### Método 2: Usando PHP (Automático)

1. Execute o script de importação via linha de comando:

```bash
php run_import.php beerxml/f_mIP1gcsGfXWmu9RU4Mp2BTmzlsI3_Brewfather_BeerXML_AtomosSession_20251123.xml
```

2. O script fará o parsing do arquivo BeerXML e importará automaticamente todos os dados para o banco de dados

## Mapeamento BeerXML → Banco de Dados

| Elemento BeerXML | Tabela no Banco de Dados | Campo(s) Correspondente(s) |
|------------------|--------------------------|-----------------------------|
| `<RECIPE>` | `receitas` | nome, estilo, descricao, volume_batch, densidade_inicial, densidade_final, ibu, srm, abv, tempo_fermentacao, temperatura_fermentacao |
| `<FERMENTABLE>` | `insumos` + `receita_ingredientes` | nome, tipo='malte', quantidade, fase='mostura' |
| `<HOP>` | `insumos` + `receita_ingredientes` | nome, tipo='lupulo', quantidade, fase (baseado em USE), tempo_adicao |
| `<YEAST>` | `insumos` + `receita_ingredientes` | nome, tipo='levedura', quantidade, fase='fermentacao' |
| `<MISC>` | `insumos` + `receita_ingredientes` | nome, tipo (baseado em TYPE), quantidade, fase (baseado em USE), tempo_adicao |

## Detalhes da Importação

### Receita
- Nome da receita: Extraído de `<NAME>`
- Estilo: Extraído de `<STYLE><NAME>`
- Volume do lote: Extraído de `<BATCH_SIZE>` (em litros)
- Densidades: OG de `<OG>`, FG de `<FG>`
- IBU: Extraído de `<IBU>`
- Cor: Extraído de `<EST_COLOR>` (convertido para SRM)
- ABV: Extraído de `<ABV>` (convertido para porcentagem)
- Tempo de fermentação: Extraído de `<PRIMARY_AGE>` (em dias)
- Temperatura de fermentação: Extraída de `<PRIMARY_TEMP>` (em °C)

### Ingredientes
- **Maltes/Fermentáveis**: Cada `<FERMENTABLE>` se torna um insumo do tipo 'malte'
- **Lúpulos**: Cada `<HOP>` se torna um insumo do tipo 'lupulo'
- **Leveduras**: Cada `<YEAST>` se torna um insumo do tipo 'levedura'
- **Diversos**: Cada `<MISC>` se torna um insumo do tipo 'aditivo', 'adjunto' ou 'outros'

### Fases de Adição
- **Mostura**: Ingredientes adicionados durante o processo de mosturação
- **Fervura**: Ingredientes adicionados durante a fervura (incluindo lúpulos de aroma)
- **Fermentação**: Ingredientes adicionados durante a fermentação (como dry hop e levedura)
- **Envase**: Ingredientes adicionados durante o envase (como clarificantes)

## Suporte a Dados Adicionais

O sistema também suporta informações adicionais que podem ser extraídas dos arquivos BeerXML:

- Perfil de brassagem (`<MASH>`)
- Equipamentos (`<EQUIPMENT>`)
- Perfis de fermentação (`<FERMENTATION_STAGES>`)
- Informações de carbonatação (`<CARBONATION>`)

## Problemas Conhecidos

1. Alguns arquivos BeerXML podem conter dados em formatos não padrão
2. A unidade de medida para alguns ingredientes pode precisar de ajuste manual
3. Algumas informações específicas do Brewfather podem não ser totalmente compatíveis

## Contribuindo

Para adicionar suporte a novos elementos do BeerXML:
1. Modifique o script `import_beerxml.php`
2. Atualize este documento com as informações relevantes
3. Teste com diferentes arquivos BeerXML

## Licença

Este projeto faz parte do sistema Atomos para gestão cervejeira.