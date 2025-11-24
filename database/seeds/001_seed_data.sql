-- ============================================================================
-- SISTEMA ATOMOS - DADOS DE EXEMPLO
-- Script de inserção de dados para demonstração
-- Data: 2025-01-19
-- ============================================================================

USE atomos_cervejaria;

-- ============================================================================
-- USUÁRIOS
-- ============================================================================

-- Usuário administrador padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, perfil, ativo) VALUES
('Administrador', 'admin@atomos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 1),
('João Silva', 'joao@atomos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'producao', 1),
('Maria Santos', 'maria@atomos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'comprador', 1),
('Pedro Costa', 'pedro@atomos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consulta', 1);

-- ============================================================================
-- FORNECEDORES
-- ============================================================================

INSERT INTO fornecedores (nome, cnpj, email, telefone, cidade, estado, contato_principal, ativo) VALUES
('Maltaria do Brasil', '12.345.678/0001-90', 'vendas@maltariabrasil.com.br', '(11) 3456-7890', 'São Paulo', 'SP', 'Carlos Fernandes', 1),
('Lúpulos Premium', '98.765.432/0001-10', 'contato@lupulospremium.com.br', '(41) 9876-5432', 'Curitiba', 'PR', 'Ana Paula', 1),
('Leveduras Especiais', '11.222.333/0001-44', 'vendas@levedurasespeciais.com.br', '(48) 3333-4444', 'Florianópolis', 'SC', 'Roberto Lima', 1),
('Embalagens Cervejeiras', '55.666.777/0001-88', 'comercial@embcerv.com.br', '(51) 5555-6666', 'Porto Alegre', 'RS', 'Juliana Souza', 1);

-- ============================================================================
-- CATEGORIAS E INSUMOS
-- ============================================================================

-- Categorias de insumos
INSERT INTO categorias_insumos (nome, descricao) VALUES
('Maltes', 'Maltes base e especiais'),
('Lúpulos', 'Lúpulos de amargor e aroma'),
('Leveduras', 'Leveduras ale e lager'),
('Adjuntos', 'Açúcares e adjuntos'),
('Aditivos', 'Clarificantes, nutrientes, etc'),
('Embalagens', 'Garrafas, tampas, rótulos, barris');

-- Insumos
INSERT INTO insumos (nome, tipo, categoria_id, unidade_medida, estoque_minimo, estoque_atual, preco_medio, fornecedor_principal_id, ativo) VALUES
-- Maltes
('Malte Pilsen', 'malte', 1, 'kg', 50, 150, 5.50, 1, 1),
('Malte Munich', 'malte', 1, 'kg', 25, 80, 6.20, 1, 1),
('Malte Caramelo 60L', 'malte', 1, 'kg', 10, 30, 7.80, 1, 1),
('Malte Chocolate', 'malte', 1, 'kg', 5, 15, 8.50, 1, 1),
('Malte Vienna', 'malte', 1, 'kg', 15, 45, 6.00, 1, 1),
-- Lúpulos
('Lúpulo Cascade', 'lupulo', 2, 'g', 500, 1500, 0.15, 2, 1),
('Lúpulo Centennial', 'lupulo', 2, 'g', 500, 1200, 0.18, 2, 1),
('Lúpulo Chinook', 'lupulo', 2, 'g', 300, 800, 0.16, 2, 1),
('Lúpulo Citra', 'lupulo', 2, 'g', 400, 1000, 0.20, 2, 1),
('Lúpulo Mosaic', 'lupulo', 2, 'g', 400, 900, 0.22, 2, 1),
-- Leveduras
('Levedura Safale US-05', 'levedura', 3, 'un', 10, 25, 12.00, 3, 1),
('Levedura Safale S-04', 'levedura', 3, 'un', 10, 20, 12.00, 3, 1),
('Levedura SafLager W-34/70', 'levedura', 3, 'un', 8, 15, 13.50, 3, 1),
('Levedura Safale BE-256', 'levedura', 3, 'un', 5, 10, 14.00, 3, 1),
-- Adjuntos
('Açúcar Cristal', 'adjunto', 4, 'kg', 5, 20, 3.50, 1, 1),
('Dextrose', 'adjunto', 4, 'kg', 3, 10, 8.00, 1, 1),
('Mel', 'adjunto', 4, 'kg', 2, 5, 25.00, 1, 1),
-- Aditivos
('Irish Moss', 'aditivo', 5, 'g', 100, 300, 0.08, 2, 1),
('Whirlfloc', 'aditivo', 5, 'un', 50, 150, 1.50, 2, 1),
-- Embalagens
('Garrafa 600ml Âmbar', 'embalagem', 6, 'un', 1000, 3000, 1.20, 4, 1),
('Tampa Pry-off', 'embalagem', 6, 'un', 1000, 3500, 0.08, 4, 1),
('Rótulo Adesivo', 'embalagem', 6, 'un', 500, 2000, 0.15, 4, 1),
('Barril 20L', 'embalagem', 6, 'un', 10, 30, 250.00, 4, 1),
('Barril 30L', 'embalagem', 6, 'un', 8, 20, 320.00, 4, 1);

-- ============================================================================
-- ENTRADAS DE ESTOQUE (Exemplos)
-- ============================================================================

INSERT INTO entradas_estoque (insumo_id, fornecedor_id, quantidade, preco_unitario, preco_total, data_entrada, data_validade, numero_nota_fiscal, usuario_id) VALUES
(1, 1, 100, 5.50, 550.00, '2025-01-10', '2026-01-10', 'NF-001234', 3),
(2, 1, 50, 6.20, 310.00, '2025-01-10', '2026-01-10', 'NF-001234', 3),
(6, 2, 1000, 0.15, 150.00, '2025-01-12', '2026-01-12', 'NF-005678', 3),
(7, 2, 800, 0.18, 144.00, '2025-01-12', '2026-01-12', 'NF-005678', 3),
(11, 3, 15, 12.00, 180.00, '2025-01-15', '2026-06-15', 'NF-009876', 3),
(20, 4, 2000, 1.20, 2400.00, '2025-01-08', NULL, 'NF-111222', 3);

-- ============================================================================
-- RECEITAS
-- ============================================================================

INSERT INTO receitas (nome, estilo, descricao, volume_batch, densidade_inicial, densidade_final, ibu, abv, tempo_fermentacao, temperatura_fermentacao, ativo, criado_por) VALUES
('American IPA Classic', 'IPA', 'IPA americana clássica com lúpulos cítricos', 100, 1.060, 1.012, 65, 6.3, 14, 18, 1, 1),
('Pilsen Premium', 'Pilsen', 'Pilsen alemã tradicional', 100, 1.048, 1.010, 35, 5.0, 21, 12, 1, 1),
('Weiss Tradicional', 'Weissbier', 'Cerveja de trigo alemã', 100, 1.052, 1.012, 15, 5.2, 14, 20, 1, 1),
('Porter Robusto', 'Porter', 'Porter inglesa com notas de café', 100, 1.055, 1.014, 30, 5.4, 14, 18, 1, 1);

-- Ingredientes das receitas

-- American IPA Classic
INSERT INTO receita_ingredientes (receita_id, insumo_id, quantidade, unidade, fase, tempo_adicao) VALUES
(1, 1, 18, 'kg', 'mostura', 0),
(1, 3, 1.5, 'kg', 'mostura', 0),
(1, 6, 60, 'g', 'fervura', 60),
(1, 7, 50, 'g', 'fervura', 15),
(1, 9, 80, 'g', 'fervura', 0),
(1, 11, 2, 'un', 'fermentacao', 0);

-- Pilsen Premium
INSERT INTO receita_ingredientes (receita_id, insumo_id, quantidade, unidade, fase, tempo_adicao) VALUES
(2, 1, 20, 'kg', 'mostura', 0),
(2, 6, 45, 'g', 'fervura', 60),
(2, 6, 30, 'g', 'fervura', 15),
(2, 13, 2, 'un', 'fermentacao', 0);

-- ============================================================================
-- LOTES DE PRODUÇÃO
-- ============================================================================

INSERT INTO lotes_producao (codigo, receita_id, volume_planejado, volume_real, data_inicio, data_fim, status, densidade_inicial, densidade_final, temperatura_fermentacao, responsavel_id) VALUES
('LOTE-2025-001', 1, 100, 98, '2025-01-05', '2025-01-19', 'finalizado', 1.060, 1.012, 18.5, 2),
('LOTE-2025-002', 2, 100, NULL, '2025-01-15', NULL, 'fermentando', 1.048, NULL, 12.0, 2),
('LOTE-2025-003', 1, 100, NULL, '2025-01-18', NULL, 'em_producao', 1.060, NULL, NULL, 2);

-- ============================================================================
-- SETORES DA CÂMARA FRIA
-- ============================================================================

INSERT INTO camarafria_setores (nome, descricao, capacidade_maxima, temperatura_ideal, ativo) VALUES
('Setor A - Principal', 'Setor principal de armazenamento', 100, 4.0, 1),
('Setor B - Secundário', 'Setor secundário de armazenamento', 80, 4.0, 1),
('Setor C - Maturação', 'Área de maturação de cervejas especiais', 50, 2.0, 1),
('Quarentena', 'Área de produtos em quarentena', 30, 4.0, 1);

-- ============================================================================
-- PRODUTOS FINAIS
-- ============================================================================

INSERT INTO produtos_finais (nome, estilo, descricao, abv, ibu, tipo_embalagem, preco_venda, estoque_atual, estoque_minimo, ativo) VALUES
('IPA Classic 600ml', 'IPA', 'IPA americana clássica', 6.3, 65, 'garrafa_600ml', 15.90, 120, 50, 1),
('Pilsen Premium 600ml', 'Pilsen', 'Pilsen alemã tradicional', 5.0, 35, 'garrafa_600ml', 12.90, 200, 100, 1),
('Weiss 600ml', 'Weissbier', 'Cerveja de trigo alemã', 5.2, 15, 'garrafa_600ml', 14.50, 80, 40, 1);

-- ============================================================================
-- ENVASES E BARRIS (Exemplos)
-- ============================================================================

-- Envase do lote finalizado
INSERT INTO envases (lote_id, codigo, data_envase, total_barris, total_litros, status, responsavel_id) VALUES
(1, 'ENV-2025-001', '2025-01-20', 5, 100, 'finalizado', 2);

-- Barris do envase
INSERT INTO estoque_barris (envase_id, numero_barril, codigo_barril, quantidade_litros, status, data_entrada) VALUES
(1, 1, 'LOTE-2025-001-B01', 20, 'disponivel', '2025-01-20'),
(1, 2, 'LOTE-2025-001-B02', 20, 'disponivel', '2025-01-20'),
(1, 3, 'LOTE-2025-001-B03', 20, 'disponivel', '2025-01-20'),
(1, 4, 'LOTE-2025-001-B04', 20, 'disponivel', '2025-01-20'),
(1, 5, 'LOTE-2025-001-B05', 20, 'disponivel', '2025-01-20');

-- ============================================================================
-- MENSAGEM FINAL
-- ============================================================================

SELECT '✅ Dados de exemplo inseridos com sucesso!' AS status;
SELECT CONCAT('Total de usuários: ', COUNT(*)) AS info FROM usuarios
UNION ALL
SELECT CONCAT('Total de fornecedores: ', COUNT(*)) FROM fornecedores
UNION ALL
SELECT CONCAT('Total de insumos: ', COUNT(*)) FROM insumos
UNION ALL
SELECT CONCAT('Total de receitas: ', COUNT(*)) FROM receitas
UNION ALL
SELECT CONCAT('Total de lotes: ', COUNT(*)) FROM lotes_producao
UNION ALL
SELECT CONCAT('Total de barris: ', COUNT(*)) FROM barris;
