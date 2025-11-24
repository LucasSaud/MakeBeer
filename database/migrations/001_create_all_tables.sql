-- ============================================================================
-- SISTEMA ATOMOS - CONTROLE DE ESTOQUE PARA CERVEJARIA
-- Script de criação completo de todas as tabelas do sistema
-- Data: 2025-01-19
-- ============================================================================

-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS atomos_cervejaria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE atomos_cervejaria;

-- ============================================================================
-- TABELAS PRINCIPAIS DO SISTEMA
-- ============================================================================

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('administrador', 'producao', 'consulta', 'comprador') NOT NULL DEFAULT 'consulta',
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_acesso DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_perfil (perfil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de fornecedores
CREATE TABLE IF NOT EXISTS fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(18) UNIQUE,
    email VARCHAR(100),
    telefone VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(50),
    estado VARCHAR(2),
    cep VARCHAR(10),
    contato_principal VARCHAR(100),
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de categorias de insumos
CREATE TABLE IF NOT EXISTS categorias_insumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABELAS DE INSUMOS E ESTOQUE
-- ============================================================================

-- Tabela de insumos/matérias-primas
CREATE TABLE IF NOT EXISTS insumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    categoria_id INT,
    tipo ENUM('malte', 'lupulo', 'levedura', 'adjunto', 'aditivo', 'embalagem', 'outros') NOT NULL,
    unidade_medida ENUM('kg', 'g', 'l', 'ml', 'un', 'm', 'cm') NOT NULL DEFAULT 'kg',
    estoque_atual DECIMAL(10,3) DEFAULT 0,
    estoque_minimo DECIMAL(10,3) DEFAULT 0,
    preco_medio DECIMAL(10,2) DEFAULT 0,
    fornecedor_principal_id INT,
    codigo_interno VARCHAR(50),
    ean VARCHAR(20),
    ativo BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_insumos(id),
    FOREIGN KEY (fornecedor_principal_id) REFERENCES fornecedores(id),
    INDEX idx_tipo (tipo),
    INDEX idx_estoque_minimo (estoque_minimo),
    INDEX idx_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de entradas de estoque
CREATE TABLE IF NOT EXISTS entradas_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insumo_id INT NOT NULL,
    fornecedor_id INT,
    quantidade DECIMAL(10,3) NOT NULL,
    preco_unitario DECIMAL(10,2),
    preco_total DECIMAL(10,2),
    lote_fornecedor VARCHAR(50),
    data_entrada DATE NOT NULL,
    data_validade DATE,
    numero_nota_fiscal VARCHAR(50),
    observacoes TEXT,
    usuario_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id),
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_data_entrada (data_entrada),
    INDEX idx_data_validade (data_validade),
    INDEX idx_insumo (insumo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABELAS DE RECEITAS E PRODUÇÃO
-- ============================================================================

-- Tabela de receitas
CREATE TABLE IF NOT EXISTS receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    estilo VARCHAR(50),
    descricao TEXT,
    volume_batch DECIMAL(8,2) NOT NULL,
    densidade_inicial DECIMAL(4,3),
    densidade_final DECIMAL(4,3),
    ibu DECIMAL(4,1),
    srm DECIMAL(4,1),
    abv DECIMAL(4,2),
    tempo_fermentacao INT,
    temperatura_fermentacao DECIMAL(4,1),
    instrucoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    criado_por INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    INDEX idx_nome (nome),
    INDEX idx_estilo (estilo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de ingredientes da receita
CREATE TABLE IF NOT EXISTS receita_ingredientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receita_id INT NOT NULL,
    insumo_id INT NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL,
    unidade ENUM('kg', 'g', 'l', 'ml', 'un') NOT NULL,
    fase ENUM('mostura', 'fervura', 'fermentacao', 'maturacao', 'envase') NOT NULL DEFAULT 'mostura',
    tempo_adicao INT DEFAULT 0,
    observacoes TEXT,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE CASCADE,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id),
    INDEX idx_receita_fase (receita_id, fase)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de lotes de produção
CREATE TABLE IF NOT EXISTS lotes_producao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    receita_id INT NOT NULL,
    volume_planejado DECIMAL(8,2) NOT NULL,
    volume_real DECIMAL(8,2),
    data_inicio DATE NOT NULL,
    data_fim DATE,
    status ENUM('planejado', 'em_producao', 'fermentando', 'maturando', 'finalizado', 'cancelado') DEFAULT 'planejado',
    densidade_inicial DECIMAL(4,3),
    densidade_final DECIMAL(4,3),
    ph_inicial DECIMAL(3,2),
    ph_final DECIMAL(3,2),
    temperatura_fermentacao DECIMAL(4,1),
    rendimento DECIMAL(5,2),
    observacoes TEXT,
    responsavel_id INT,
    envase_iniciado BOOLEAN DEFAULT FALSE,
    envase_finalizado BOOLEAN DEFAULT FALSE,
    data_envase DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (receita_id) REFERENCES receitas(id),
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
    INDEX idx_status (status),
    INDEX idx_data_inicio (data_inicio),
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de consumo de insumos por lote
CREATE TABLE IF NOT EXISTS lote_consumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lote_id INT NOT NULL,
    insumo_id INT NOT NULL,
    quantidade_planejada DECIMAL(10,3) NOT NULL,
    quantidade_real DECIMAL(10,3),
    entrada_estoque_id INT,
    fase ENUM('mostura', 'fervura', 'fermentacao', 'maturacao', 'envase') NOT NULL,
    data_consumo DATETIME,
    observacoes TEXT,
    FOREIGN KEY (lote_id) REFERENCES lotes_producao(id) ON DELETE CASCADE,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id),
    FOREIGN KEY (entrada_estoque_id) REFERENCES entradas_estoque(id),
    INDEX idx_lote_fase (lote_id, fase)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABELAS DE ENVASE E BARRIS
-- ============================================================================

-- Tabela de Envases
CREATE TABLE IF NOT EXISTS envases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lote_id INT NOT NULL,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    data_envase DATE NOT NULL,
    total_barris INT NOT NULL DEFAULT 0,
    total_litros DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('em_processo', 'finalizado', 'cancelado') NOT NULL DEFAULT 'em_processo',
    responsavel_id INT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lote_id) REFERENCES lotes_producao(id) ON DELETE CASCADE,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_lote (lote_id),
    INDEX idx_data (data_envase),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTA: Tabela 'barris' removida pois pode conflitar com catálogo existente de barris físicos
-- Os dados de envase de barris são armazenados diretamente na tabela estoque_barris

-- Tabela de Estoque de Barris
-- NOTA: barril_fisico_id é opcional e referencia o catálogo de barris físicos, se existir
CREATE TABLE IF NOT EXISTS estoque_barris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barril_fisico_id INT NULL COMMENT 'Referência opcional ao catálogo de barris físicos',
    envase_id INT NOT NULL,
    lote_codigo VARCHAR(50) NOT NULL,
    numero_barril INT NOT NULL,
    codigo_barril VARCHAR(50) NOT NULL,
    estilo VARCHAR(100),
    quantidade_litros DECIMAL(10,2) NOT NULL,
    data_entrada DATE NOT NULL,
    status ENUM('disponivel', 'reservado', 'em_processo', 'baixado') NOT NULL DEFAULT 'disponivel',
    localizacao VARCHAR(255),
    temperatura_armazenamento DECIMAL(5,2),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (envase_id) REFERENCES envases(id) ON DELETE CASCADE,
    INDEX idx_barril_fisico (barril_fisico_id),
    INDEX idx_envase (envase_id),
    INDEX idx_lote (lote_codigo),
    INDEX idx_status (status),
    INDEX idx_codigo_barril (codigo_barril)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Saída de Barril
CREATE TABLE IF NOT EXISTS saida_barril (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estoque_barril_id INT,
    envase_id INT NOT NULL,
    lote_codigo VARCHAR(50) NOT NULL,
    numero_barril INT NOT NULL,
    quantidade_litros DECIMAL(10,2) NOT NULL,
    estilo VARCHAR(100),
    data_saida DATE NOT NULL,
    destino VARCHAR(255),
    responsavel_id INT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estoque_barril_id) REFERENCES estoque_barris(id) ON DELETE SET NULL,
    FOREIGN KEY (envase_id) REFERENCES envases(id) ON DELETE CASCADE,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_estoque_barril (estoque_barril_id),
    INDEX idx_data (data_saida),
    INDEX idx_lote (lote_codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABELAS DE PRODUTOS FINAIS
-- ============================================================================

-- Tabela de produtos finais
CREATE TABLE IF NOT EXISTS produtos_finais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    estilo VARCHAR(50),
    descricao TEXT,
    abv DECIMAL(4,2),
    ibu DECIMAL(4,1),
    tipo_embalagem ENUM('garrafa_600ml', 'garrafa_355ml', 'lata_350ml', 'lata_473ml', 'growler_1l', 'growler_2l', 'keg_20l', 'keg_30l', 'keg_50l') NOT NULL,
    preco_venda DECIMAL(8,2),
    estoque_atual INT DEFAULT 0,
    estoque_minimo INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo_embalagem (tipo_embalagem),
    INDEX idx_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de produção de produtos finais (envase em garrafas/latas)
CREATE TABLE IF NOT EXISTS producao_produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lote_producao_id INT NOT NULL,
    produto_final_id INT NOT NULL,
    quantidade_produzida INT NOT NULL,
    data_envase DATE NOT NULL,
    data_validade DATE,
    lote_produto VARCHAR(50),
    observacoes TEXT,
    responsavel_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lote_producao_id) REFERENCES lotes_producao(id),
    FOREIGN KEY (produto_final_id) REFERENCES produtos_finais(id),
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
    INDEX idx_data_envase (data_envase),
    INDEX idx_data_validade (data_validade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABELAS DE CÂMARA FRIA
-- ============================================================================

-- Tabela de Setores da Câmara Fria
CREATE TABLE IF NOT EXISTS camarafria_setores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    capacidade_maxima INT DEFAULT 0 COMMENT 'Capacidade máxima em unidades',
    temperatura_ideal DECIMAL(5,2) COMMENT 'Temperatura ideal em Celsius',
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo),
    INDEX idx_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Localização de Estoque
CREATE TABLE IF NOT EXISTS estoque_localizacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    lote_id INT DEFAULT NULL,
    setor_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('disponivel', 'quarentena', 'vencido', 'reservado') DEFAULT 'disponivel',
    data_entrada DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos_finais(id) ON DELETE CASCADE,
    FOREIGN KEY (lote_id) REFERENCES lotes_producao(id) ON DELETE SET NULL,
    FOREIGN KEY (setor_id) REFERENCES camarafria_setores(id) ON DELETE RESTRICT,
    INDEX idx_produto (produto_id),
    INDEX idx_lote (lote_id),
    INDEX idx_setor (setor_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Movimentações Internas da Câmara Fria
CREATE TABLE IF NOT EXISTS camarafria_movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    lote_id INT DEFAULT NULL,
    setor_origem_id INT,
    setor_destino_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    tipo_movimentacao ENUM('entrada', 'transferencia', 'saida', 'ajuste') DEFAULT 'transferencia',
    motivo VARCHAR(255),
    responsavel VARCHAR(100),
    usuario_id INT,
    data_movimentacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos_finais(id) ON DELETE CASCADE,
    FOREIGN KEY (lote_id) REFERENCES lotes_producao(id) ON DELETE SET NULL,
    FOREIGN KEY (setor_origem_id) REFERENCES camarafria_setores(id) ON DELETE SET NULL,
    FOREIGN KEY (setor_destino_id) REFERENCES camarafria_setores(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_produto (produto_id),
    INDEX idx_lote (lote_id),
    INDEX idx_data (data_movimentacao),
    INDEX idx_tipo (tipo_movimentacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Controle de Temperatura
CREATE TABLE IF NOT EXISTS camarafria_temperatura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setor_id INT NOT NULL,
    temperatura DECIMAL(5,2) NOT NULL,
    umidade DECIMAL(5,2) DEFAULT NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    equipamento VARCHAR(100),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (setor_id) REFERENCES camarafria_setores(id) ON DELETE CASCADE,
    INDEX idx_setor (setor_id),
    INDEX idx_data (data_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABELAS DE MOVIMENTAÇÕES E AUDITORIA
-- ============================================================================

-- Tabela de movimentações de estoque
CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insumo_id INT,
    produto_final_id INT,
    tipo ENUM('entrada', 'saida', 'ajuste', 'perda', 'transferencia') NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL,
    motivo VARCHAR(100),
    observacoes TEXT,
    lote_producao_id INT,
    usuario_id INT NOT NULL,
    data_movimentacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id),
    FOREIGN KEY (produto_final_id) REFERENCES produtos_finais(id),
    FOREIGN KEY (lote_producao_id) REFERENCES lotes_producao(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_tipo (tipo),
    INDEX idx_data (data_movimentacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de log de atividades
CREATE TABLE IF NOT EXISTS log_atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(100) NOT NULL,
    tabela_afetada VARCHAR(50),
    registro_id INT,
    dados_anteriores JSON,
    dados_novos JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_usuario_data (usuario_id, created_at),
    INDEX idx_acao (acao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;