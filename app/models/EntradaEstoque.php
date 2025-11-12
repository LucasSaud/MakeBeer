<?php
require_once 'BaseModel.php';

class EntradaEstoque extends BaseModel {
    protected $table = 'entradas_estoque';
    protected $timestamps = false; // Tabela não possui created_at/updated_at
    protected $fillable = [
        'insumo_id', 'fornecedor_id', 'quantidade', 'preco_unitario',
        'preco_total', 'lote_fornecedor', 'data_entrada', 'data_validade',
        'numero_nota_fiscal', 'observacoes', 'usuario_id'
    ];

    /**
     * Registra entrada de estoque
     */
    public function registrarEntrada($data) {
        // Validar dados
        if (empty($data['insumo_id']) || empty($data['quantidade']) || empty($data['data_entrada'])) {
            throw new Exception('Dados obrigatórios faltando');
        }

        // Calcular preço total se não fornecido
        if (empty($data['preco_total']) && !empty($data['preco_unitario'])) {
            $data['preco_total'] = $data['quantidade'] * $data['preco_unitario'];
        }

        // Adicionar usuário logado
        $user = getCurrentUser();
        $data['usuario_id'] = $user['id'];

        // Criar entrada
        $entradaId = $this->create($data);

        if ($entradaId) {
            // Atualizar estoque do insumo
            $insumoModel = new Insumo();
            $insumoModel->atualizarEstoque($data['insumo_id'], $data['quantidade'], 'adicionar');

            // Atualizar preço médio
            if (!empty($data['preco_unitario'])) {
                $this->atualizarPrecoMedio($data['insumo_id']);
            }

            // Registrar movimentação
            $this->registrarMovimentacao($data['insumo_id'], $data['quantidade'], 'entrada',
                "Entrada de estoque - NF: " . ($data['numero_nota_fiscal'] ?? 'S/N'));
        }

        return $entradaId;
    }

    /**
     * Atualiza preço médio do insumo
     */
    private function atualizarPrecoMedio($insumoId) {
        $sql = "SELECT AVG(preco_unitario) as preco_medio
                FROM {$this->table}
                WHERE insumo_id = ? AND preco_unitario > 0";

        $result = $this->db->fetchOne($sql, [$insumoId]);

        if ($result && $result['preco_medio']) {
            $insumoModel = new Insumo();
            $insumoModel->update($insumoId, ['preco_medio' => $result['preco_medio']]);
        }
    }

    /**
     * Registra movimentação de estoque
     */
    private function registrarMovimentacao($insumoId, $quantidade, $tipo, $motivo) {
        $user = getCurrentUser();

        $sql = "INSERT INTO movimentacoes_estoque
                (insumo_id, tipo, quantidade, motivo, usuario_id, data_movimentacao)
                VALUES (?, ?, ?, ?, ?, NOW())";

        $this->db->query($sql, [$insumoId, $tipo, $quantidade, $motivo, $user['id']]);
    }

    /**
     * Busca entradas com detalhes
     */
    public function getAllWithDetails($limite = 100) {
        $sql = "SELECT e.*,
                i.nome as insumo_nome,
                i.unidade_medida,
                f.nome as fornecedor_nome,
                u.nome as usuario_nome
                FROM {$this->table} e
                LEFT JOIN insumos i ON e.insumo_id = i.id
                LEFT JOIN fornecedores f ON e.fornecedor_id = f.id
                LEFT JOIN usuarios u ON e.usuario_id = u.id
                ORDER BY e.data_entrada DESC, e.created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limite]);
    }

    /**
     * Busca entradas por período
     */
    public function getByPeriodo($dataInicio, $dataFim) {
        $sql = "SELECT e.*,
                i.nome as insumo_nome,
                f.nome as fornecedor_nome,
                u.nome as usuario_nome
                FROM {$this->table} e
                LEFT JOIN insumos i ON e.insumo_id = i.id
                LEFT JOIN fornecedores f ON e.fornecedor_id = f.id
                LEFT JOIN usuarios u ON e.usuario_id = u.id
                WHERE e.data_entrada BETWEEN ? AND ?
                ORDER BY e.data_entrada DESC";

        return $this->db->fetchAll($sql, [$dataInicio, $dataFim]);
    }

    /**
     * Busca entradas próximas do vencimento
     */
    public function getProximasVencimento($dias = 30) {
        $sql = "SELECT e.*,
                i.nome as insumo_nome,
                DATEDIFF(e.data_validade, CURDATE()) as dias_vencimento
                FROM {$this->table} e
                LEFT JOIN insumos i ON e.insumo_id = i.id
                WHERE e.data_validade IS NOT NULL
                AND e.data_validade > CURDATE()
                AND DATEDIFF(e.data_validade, CURDATE()) <= ?
                ORDER BY e.data_validade ASC";

        return $this->db->fetchAll($sql, [$dias]);
    }

    /**
     * Estatísticas de entradas
     */
    public function getStats($dataInicio = null, $dataFim = null) {
        $where = '1=1';
        $params = [];

        if ($dataInicio && $dataFim) {
            $where = "data_entrada BETWEEN ? AND ?";
            $params = [$dataInicio, $dataFim];
        }

        $sql = "SELECT
                COUNT(*) as total_entradas,
                SUM(quantidade) as quantidade_total,
                SUM(preco_total) as valor_total,
                COUNT(DISTINCT insumo_id) as insumos_diferentes,
                COUNT(DISTINCT fornecedor_id) as fornecedores_diferentes
                FROM {$this->table}
                WHERE {$where}";

        return $this->db->fetchOne($sql, $params);
    }
}
?>