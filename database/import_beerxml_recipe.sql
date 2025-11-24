-- ============================================================================
-- SCRIPT DE IMPORTAÇÃO DE RECEITA BEERXML PARA SISTEMA ATOMOS
-- Importação da receita "Atomos Session" do arquivo BeerXML
-- ============================================================================

-- Iniciar transação
START TRANSACTION;

-- Variável para armazenar o ID da receita
SET @receita_id = NULL;

-- 1. Criar ou atualizar informações da receita principal
INSERT INTO receitas (
    nome, 
    estilo, 
    descricao, 
    volume_batch, 
    densidade_inicial, 
    densidade_final, 
    ibu, 
    srm, 
    abv, 
    tempo_fermentacao, 
    temperatura_fermentacao, 
    instrucoes, 
    ativo, 
    criado_por
) VALUES (
    'Atomos Session',  -- nome
    'Session IPA',     -- estilo
    'p/ IPA: 34,3 IBU p/ session IPA: 71,2 IBU 1° mosto: 15,2° bx 2° mosto: 6,34°bx',  -- descricao
    250.00,            -- volume_batch (em litros)
    1.047,             -- densidade_inicial (OG)
    1.007,             -- densidade_final (FG)
    45.4,              -- ibu
    5.9,               -- srm (cor)
    5.25,              -- abv (%)
    14,                -- tempo_fermentacao (dias)
    16.0,              -- temperatura_fermentacao (°C)
    'Ver procedimento padrão de brassagem e fermentação',  -- instrucoes
    1,                 -- ativo
    1                  -- criado_por (usuário admin)
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    estilo = VALUES(estilo),
    descricao = VALUES(descricao),
    volume_batch = VALUES(volume_batch),
    densidade_inicial = VALUES(densidade_inicial),
    densidade_final = VALUES(densidade_final),
    ibu = VALUES(ibu),
    srm = VALUES(srm),
    abv = VALUES(abv),
    tempo_fermentacao = VALUES(tempo_fermentacao),
    temperatura_fermentacao = VALUES(temperatura_fermentacao),
    instrucoes = VALUES(instrucoes);

-- Obter o ID da receita inserida/atualizada
SET @receita_id = LAST_INSERT_ID();

-- Se a receita já existia, LAST_INSERT_ID() retornará 0, então precisamos buscá-lo
SELECT id INTO @receita_id FROM receitas WHERE nome = 'Atomos Session' LIMIT 1;

-- 2. Criar ou atualizar ingredientes e adicioná-los à receita
-- 2.1 Malte Pilsen (FERMENTABLE)
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'El Jaguar Genuino Pilsen Malt Uma',  -- nome
    'Malte pilsen argentino da Uma Malta', -- descricao
    'malte',                              -- tipo
    'kg',                                 -- unidade_medida
    100.000,                              -- estoque_atual
    20.000,                               -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'El Jaguar Genuino Pilsen Malt Uma' LIMIT 1;

-- Adicionar ingrediente à receita
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    50.000,     -- quantidade (em kg)
    'kg',       -- unidade
    'mostura',  -- fase
    0           -- tempo_adicao (minutos)
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- 2.2 Chateau Black (FERMENTABLE)
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'CHATEAU BLACK',                      -- nome
    'Malte especial belga Castle Malting', -- descricao
    'malte',                              -- tipo
    'kg',                                 -- unidade_medida
    10.000,                               -- estoque_atual
    2.000,                                -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'CHATEAU BLACK' LIMIT 1;

-- Adicionar ingrediente à receita
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    0.200,      -- quantidade (em kg)
    'kg',       -- unidade
    'mostura',  -- fase
    0           -- tempo_adicao (minutos)
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- 2.3 Columbus/Tomahawk/Zeus (CTZ) (HOP)
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'Columbus/Tomahawk/Zeus (CTZ)',       -- nome
    'Lúpulo americano tipo CTZ',          -- descricao
    'lupulo',                             -- tipo
    'kg',                                 -- unidade_medida
    5.000,                                -- estoque_atual
    1.000,                                -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'Columbus/Tomahawk/Zeus (CTZ)' LIMIT 1;

-- Adicionar ingrediente à receita (First Wort hopping)
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    0.320,      -- quantidade (em kg)
    'kg',       -- unidade
    'fervura',  -- fase
    60          -- tempo_adicao (minutos - First Wort)
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- 2.4 Amarillo (HOP)
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'Amarillo',                           -- nome
    'Lúpulo americano tipo Amarillo',     -- descricao
    'lupulo',                             -- tipo
    'kg',                                 -- unidade_medida
    5.000,                                -- estoque_atual
    1.000,                                -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'Amarillo' LIMIT 1;

-- Adicionar ingrediente à receita (Aroma hopping)
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    0.300,      -- quantidade (em kg)
    'kg',       -- unidade
    'fervura',  -- fase
    10          -- tempo_adicao (minutos - Aroma)
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- 2.5 Idaho #7 (HOP) - Dry Hop
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'Idaho #7',                           -- nome
    'Lúpulo americano tipo Idaho #7',     -- descricao
    'lupulo',                             -- tipo
    'kg',                                 -- unidade_medida
    5.000,                                -- estoque_atual
    1.000,                                -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'Idaho #7' LIMIT 1;

-- Adicionar ingrediente à receita (Dry Hop)
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    0.500,         -- quantidade (em kg)
    'kg',          -- unidade
    'fermentacao', -- fase
    14400          -- tempo_adicao (minutos - 10 dias)
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- 2.6 Simcoe (HOP) - Dry Hop
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'Simcoe',                             -- nome
    'Lúpulo americano tipo Simcoe',       -- descricao
    'lupulo',                             -- tipo
    'kg',                                 -- unidade_medida
    5.000,                                -- estoque_atual
    1.000,                                -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'Simcoe' LIMIT 1;

-- Adicionar ingrediente à receita (Dry Hop)
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    0.500,         -- quantidade (em kg)
    'kg',          -- unidade
    'fermentacao', -- fase
    14400          -- tempo_adicao (minutos - 10 dias)
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- 2.7 Safale American (YEAST)
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'Safale American',                    -- nome
    'Levedura seca americana Fermentis US-05', -- descricao
    'levedura',                           -- tipo
    'un',                                 -- unidade_medida
    100,                                  -- estoque_atual
    20,                                   -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'Safale American' LIMIT 1;

-- Adicionar ingrediente à receita
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    50.28,         -- quantidade (em ml)
    'ml',          -- unidade
    'fermentacao', -- fase
    0              -- tempo_adicao (no início da fermentação)
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- 2.8 Ascorbic Acid (MISC)
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'Ascorbic Acid',                      -- nome
    'Ácido ascórbico para estabilização', -- descricao
    'aditivo',                            -- tipo
    'kg',                                 -- unidade_medida
    1.000,                                -- estoque_atual
    0.100,                                -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'Ascorbic Acid' LIMIT 1;

-- Adicionar ingrediente à receita
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    0.002,      -- quantidade (em kg/2g)
    'kg',       -- unidade
    'envase',   -- fase
    0           -- tempo_adicao
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- 2.9 Gypsum (MISC)
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'Gypsum (Calcium Sulfate)',           -- nome
    'Sulfato de cálcio para ajuste de água', -- descricao
    'aditivo',                            -- tipo
    'kg',                                 -- unidade_medida
    1.000,                                -- estoque_atual
    0.100,                                -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'Gypsum (Calcium Sulfate)' LIMIT 1;

-- Adicionar ingrediente à receita
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    0.010,      -- quantidade (em kg/10g)
    'kg',       -- unidade
    'fervura',  -- fase
    15          -- tempo_adicao (minutos)
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- 2.10 Sodium Metabisulfite (MISC)
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'Sodium Metabisulfite',               -- nome
    'Metabissulfito de sódio para sanitização', -- descricao
    'aditivo',                            -- tipo
    'kg',                                 -- unidade_medida
    1.000,                                -- estoque_atual
    0.100,                                -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'Sodium Metabisulfite' LIMIT 1;

-- Adicionar ingrediente à receita
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    0.001,      -- quantidade (em kg/1g)
    'kg',       -- unidade
    'fervura',  -- fase
    15          -- tempo_adicao (minutos)
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- 2.11 Whirlfloc (MISC)
SET @insumo_id = NULL;
INSERT INTO insumos (
    nome, 
    descricao, 
    tipo, 
    unidade_medida, 
    estoque_atual, 
    estoque_minimo, 
    preco_medio, 
    ativo
) VALUES (
    'Whirlfloc',                          -- nome
    'Agente clarificante para whirlpool', -- descricao
    'aditivo',                            -- tipo
    'kg',                                 -- unidade_medida
    1.000,                                -- estoque_atual
    0.100,                                -- estoque_minimo
    0.00,                                 -- preco_medio
    1                                     -- ativo
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

-- Obter ID do insumo
SELECT id INTO @insumo_id FROM insumos WHERE nome = 'Whirlfloc' LIMIT 1;

-- Adicionar ingrediente à receita
INSERT INTO receita_ingredientes (
    receita_id, 
    insumo_id, 
    quantidade, 
    unidade, 
    fase, 
    tempo_adicao
) VALUES (
    @receita_id,
    @insumo_id,
    0.010,      -- quantidade (em kg/10g)
    'kg',       -- unidade
    'fervura',  -- fase
    15          -- tempo_adicao (minutos)
) ON DUPLICATE KEY UPDATE
    quantidade = VALUES(quantidade);

-- Confirmar transação
COMMIT;

-- Exibir mensagem de sucesso
SELECT CONCAT('Receita "Atomos Session" importada com sucesso com ID: ', @receita_id) AS resultado;