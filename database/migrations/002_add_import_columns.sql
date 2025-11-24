-- ============================================================================
-- MIGRAÇÃO PARA ADICIONAR COLUNAS NECESSÁRIAS PARA IMPORTAÇÃO BEERXML
-- Data: 2025-01-19
-- ============================================================================

USE atomos_cervejaria;

-- Adicionar colunas que podem estar faltando nas tabelas existentes

-- Verificar e adicionar coluna de descrição mais detalhada na tabela insumos
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'descricao_detalhada'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN descricao_detalhada TEXT NULL AFTER descricao',
    'SELECT "Coluna descricao_detalhada já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de fornecedor alternativo na tabela insumos
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'fornecedor_alternativo_id'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN fornecedor_alternativo_id INT NULL AFTER fornecedor_principal_id,
     ADD CONSTRAINT fk_insumos_fornecedor_alternativo FOREIGN KEY (fornecedor_alternativo_id) REFERENCES fornecedores(id) ON DELETE SET NULL',
    'SELECT "Coluna fornecedor_alternativo_id já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de origem na tabela insumos
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'origem'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN origem VARCHAR(100) NULL AFTER fornecedor_principal_id',
    'SELECT "Coluna origem já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de laboratório na tabela insumos (para leveduras)
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'laboratorio'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN laboratorio VARCHAR(100) NULL AFTER origem',
    'SELECT "Coluna laboratorio já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de produto_id na tabela insumos (para leveduras)
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'produto_id'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN produto_id VARCHAR(50) NULL AFTER laboratorio',
    'SELECT "Coluna produto_id já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de atenuação na tabela insumos (para leveduras)
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'atenuacao'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN atenuacao DECIMAL(5,2) NULL AFTER produto_id',
    'SELECT "Coluna atenuacao já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de temperatura mínima na tabela insumos (para leveduras)
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'temperatura_minima'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN temperatura_minima DECIMAL(4,1) NULL AFTER atenuacao',
    'SELECT "Coluna temperatura_minima já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de temperatura máxima na tabela insumos (para leveduras)
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'temperatura_maxima'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN temperatura_maxima DECIMAL(4,1) NULL AFTER temperatura_minima',
    'SELECT "Coluna temperatura_maxima já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de alfa-ácidos na tabela insumos (para lúpulos)
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'alfa_acidos'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN alfa_acidos DECIMAL(4,2) NULL AFTER temperatura_maxima',
    'SELECT "Coluna alfa_acidos já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de forma na tabela insumos (para lúpulos)
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'forma'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN forma ENUM("pellet", "plug", "leaf", "extract") NULL AFTER alfa_acidos',
    'SELECT "Coluna forma já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de cor na tabela insumos (para maltes)
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'cor'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN cor DECIMAL(6,2) NULL AFTER forma',
    'SELECT "Coluna cor já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar coluna de rendimento na tabela insumos (para maltes)
SET @columnExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND COLUMN_NAME = 'rendimento'
);

SET @sql = IF(
    @columnExists = 0,
    'ALTER TABLE insumos ADD COLUMN rendimento DECIMAL(5,2) NULL AFTER cor',
    'SELECT "Coluna rendimento já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar índices para melhorar performance
SET @indexExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'insumos' 
    AND INDEX_NAME = 'idx_nome_tipo'
);

SET @sql = IF(
    @indexExists = 0,
    'CREATE INDEX idx_nome_tipo ON insumos (nome, tipo)',
    'SELECT "Índice idx_nome_tipo já existe em insumos" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar índice para receitas
SET @indexExists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'atomos_cervejaria' 
    AND TABLE_NAME = 'receitas' 
    AND INDEX_NAME = 'idx_nome_estilo'
);

SET @sql = IF(
    @indexExists = 0,
    'CREATE INDEX idx_nome_estilo ON receitas (nome, estilo)',
    'SELECT "Índice idx_nome_estilo já existe em receitas" as mensagem'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mensagem de conclusão
SELECT 'Migração 002 concluída com sucesso' as mensagem;