<?php
require_once 'BaseModel.php';

class CamaraFriaMovimentacao extends BaseModel {
    protected $table = 'camarafria_movimentacoes';
    protected $timestamps = false; // Apenas created_at
    protected $fillable = [
        'produto_id', 'lote_id', 'setor_origem_id', 'setor_destino_id',
        'quantidade', 'tipo_movimentacao', 'motivo', 'responsavel',
        'usuario_id', 'data_movimentacao', 'observacoes'
    ];

    /**
     * Registra uma transferência entre setores
     */
    public function registrarTransferencia($produtoId, $loteId, $setorOrigemId, $setorDestinoId, $quantidade, $motivo = '') {
        $user = getCurrentUser();

        $data = [
            'produto_id' => $produtoId,
            'lote_id' => $loteId,
            'setor_origem_id' => $setorOrigemId,
            'setor_destino_id' => $setorDestinoId,
            'quantidade' => $quantidade,
            'tipo_movimentacao' => 'transferencia',
            'motivo' => $motivo,
            'responsavel' => $user['nome'],
            'usuario_id' => $user['id'],
            'data_movimentacao' => date('Y-m-d H:i:s')
        ];

        return $this->create($data);
    }

    /**
     * Registra entrada na câmara fria
     */
    public function registrarEntrada($produtoId, $loteId, $setorDestinoId, $quantidade, $motivo = '') {
        $user = getCurrentUser();

        $data = [
            'produto_id' => $produtoId,
            'lote_id' => $loteId,
            'setor_origem_id' => null,
            'setor_destino_id' => $setorDestinoId,
            'quantidade' => $quantidade,
            'tipo_movimentacao' => 'entrada',
            'motivo' => $motivo,
            'responsavel' => $user['nome'],
            'usuario_id' => $user['id'],
            'data_movimentacao' => date('Y-m-d H:i:s')
        ];

        return $this->create($data);
    }

    /**
     * Registra saída da câmara fria
     */
    public function registrarSaida($produtoId, $loteId, $setorOrigemId, $quantidade, $motivo = '') {
        $user = getCurrentUser();

        $data = [
            'produto_id' => $produtoId,
            'lote_id' => $loteId,
            'setor_origem_id' => $setorOrigemId,
            'setor_destino_id' => null,
            'quantidade' => $quantidade,
            'tipo_movimentacao' => 'saida',
            'motivo' => $motivo,
            'responsavel' => $user['nome'],
            'usuario_id' => $user['id'],
            'data_movimentacao' => date('Y-m-d H:i:s')
        ];

        return $this->create($data);
    }

    /**
     * Busca movimentações com detalhes
     */
    public function getAllComDetalhes($limite = 100, $filtros = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filtros['produto_id'])) {
            $where[] = "m.produto_id = ?";
            $params[] = $filtros['produto_id'];
        }

        if (!empty($filtros['setor_id'])) {
            $where[] = "(m.setor_origem_id = ? OR m.setor_destino_id = ?)";
            $params[] = $filtros['setor_id'];
            $params[] = $filtros['setor_id'];
        }

        if (!empty($filtros['tipo_movimentacao'])) {
            $where[] = "m.tipo_movimentacao = ?";
            $params[] = $filtros['tipo_movimentacao'];
        }

        if (!empty($filtros['data_inicio'])) {
            $where[] = "DATE(m.data_movimentacao) >= ?";
            $params[] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $where[] = "DATE(m.data_movimentacao) <= ?";
            $params[] = $filtros['data_fim'];
        }

        $whereClause = implode(' AND ', $where);
        $params[] = $limite;

        $sql = "SELECT
                m.*,
                p.nome as produto_nome,
                p.tipo_embalagem,
                l.codigo as numero_lote,
                so.nome as setor_origem_nome,
                sd.nome as setor_destino_nome,
                u.nome as usuario_nome
                FROM {$this->table} m
                INNER JOIN produtos_finais p ON m.produto_id = p.id
                LEFT JOIN lotes_producao l ON m.lote_id = l.id
                LEFT JOIN camarafria_setores so ON m.setor_origem_id = so.id
                LEFT JOIN camarafria_setores sd ON m.setor_destino_id = sd.id
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                WHERE {$whereClause}
                ORDER BY m.data_movimentacao DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Histórico de movimentações de um produto
     */
    public function getHistoricoProduto($produtoId, $limite = 50) {
        $sql = "SELECT
                m.*,
                l.codigo as numero_lote,
                so.nome as setor_origem_nome,
                sd.nome as setor_destino_nome,
                u.nome as usuario_nome
                FROM {$this->table} m
                LEFT JOIN lotes_producao l ON m.lote_id = l.id
                LEFT JOIN camarafria_setores so ON m.setor_origem_id = so.id
                LEFT JOIN camarafria_setores sd ON m.setor_destino_id = sd.id
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                WHERE m.produto_id = ?
                ORDER BY m.data_movimentacao DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$produtoId, $limite]);
    }

    /**
     * Estatísticas de movimentações por período
     */
    public function getEstatisticas($dataInicio = null, $dataFim = null) {
        $where = '1=1';
        $params = [];

        if ($dataInicio && $dataFim) {
            $where = "DATE(data_movimentacao) BETWEEN ? AND ?";
            $params = [$dataInicio, $dataFim];
        }

        $sql = "SELECT
                tipo_movimentacao,
                COUNT(*) as total_movimentacoes,
                SUM(quantidade) as quantidade_total,
                COUNT(DISTINCT produto_id) as produtos_diferentes
                FROM {$this->table}
                WHERE {$where}
                GROUP BY tipo_movimentacao";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Movimentações recentes (dashboard)
     */
    public function getRecentes($limite = 10) {
        return $this->getAllComDetalhes($limite);
    }
}
?>
