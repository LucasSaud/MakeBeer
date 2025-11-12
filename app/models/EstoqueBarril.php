<?php
require_once 'BaseModel.php';

class EstoqueBarril extends BaseModel {
    protected $table = 'estoque_barris';
    protected $fillable = [
        'barril_fisico_id', 'envase_id', 'lote_codigo', 'numero_barril',
        'codigo_barril', 'estilo', 'quantidade_litros', 'data_entrada',
        'status', 'localizacao', 'temperatura_armazenamento', 'observacoes'
    ];

    /**
     * Busca estoque disponível
     */
    public function getDisponiveis() {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'disponivel'
                ORDER BY data_entrada ASC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Busca estoque com detalhes
     */
    public function getComDetalhes($estoqueId) {
        $sql = "SELECT eb.*
                FROM {$this->table} eb
                WHERE eb.id = ?";

        return $this->db->fetchOne($sql, [$estoqueId]);
    }

    /**
     * Busca estoque por status
     */
    public function getByStatus($status) {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = ?
                ORDER BY data_entrada ASC";

        return $this->db->fetchAll($sql, [$status]);
    }

    /**
     * Busca estoque por lote
     */
    public function getByLote($loteCodigo) {
        $sql = "SELECT * FROM {$this->table}
                WHERE lote_codigo = ?
                ORDER BY numero_barril ASC";

        return $this->db->fetchAll($sql, [$loteCodigo]);
    }

    /**
     * Busca estoque por estilo
     */
    public function getByEstilo($estilo) {
        $sql = "SELECT * FROM {$this->table}
                WHERE estilo = ? AND status = 'disponivel'
                ORDER BY data_entrada ASC";

        return $this->db->fetchAll($sql, [$estilo]);
    }

    /**
     * Atualiza localização do barril
     */
    public function atualizarLocalizacao($estoqueId, $localizacao, $temperatura = null) {
        $dados = ['localizacao' => $localizacao];

        if ($temperatura !== null) {
            $dados['temperatura_armazenamento'] = $temperatura;
        }

        return $this->update($estoqueId, $dados);
    }

    /**
     * Estatísticas de estoque
     */
    public function getStats() {
        $sql = "SELECT
                COUNT(*) as total_barris_estoque,
                SUM(CASE WHEN status = 'disponivel' THEN 1 ELSE 0 END) as barris_disponiveis,
                SUM(CASE WHEN status = 'disponivel' THEN quantidade_litros ELSE 0 END) as litros_disponiveis,
                SUM(quantidade_litros) as total_litros,
                COUNT(DISTINCT estilo) as estilos_diferentes,
                COUNT(DISTINCT lote_codigo) as lotes_diferentes
                FROM {$this->table}
                WHERE status != 'baixado'";

        return $this->db->fetchOne($sql);
    }

    /**
     * Relatório de estoque por estilo
     */
    public function getRelatorioPorEstilo() {
        $sql = "SELECT
                estilo,
                COUNT(*) as total_barris,
                SUM(quantidade_litros) as total_litros,
                MIN(data_entrada) as data_mais_antiga,
                MAX(data_entrada) as data_mais_recente
                FROM {$this->table}
                WHERE status = 'disponivel'
                GROUP BY estilo
                ORDER BY total_litros DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Relatório de estoque por lote
     */
    public function getRelatorioPorLote() {
        $sql = "SELECT
                lote_codigo,
                estilo,
                COUNT(*) as total_barris,
                SUM(quantidade_litros) as total_litros,
                data_entrada
                FROM {$this->table}
                WHERE status = 'disponivel'
                GROUP BY lote_codigo, estilo, data_entrada
                ORDER BY data_entrada DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Busca barris antigos (para controle FIFO)
     */
    public function getBarrisAntigos($dias = 30) {
        $dataLimite = date('Y-m-d', strtotime("-{$dias} days"));

        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'disponivel'
                AND data_entrada <= ?
                ORDER BY data_entrada ASC";

        return $this->db->fetchAll($sql, [$dataLimite]);
    }

    /**
     * Busca total de litros disponíveis por estilo
     */
    public function getLitrosPorEstilo() {
        $sql = "SELECT
                estilo,
                SUM(quantidade_litros) as litros_disponiveis
                FROM {$this->table}
                WHERE status = 'disponivel'
                GROUP BY estilo
                ORDER BY estilo";

        return $this->db->fetchAll($sql);
    }

    /**
     * Busca estoque com filtros
     */
    public function buscar($filtros = []) {
        $where = ["status != 'baixado'"];
        $params = [];

        if (!empty($filtros['lote_codigo'])) {
            $where[] = "lote_codigo LIKE ?";
            $params[] = "%{$filtros['lote_codigo']}%";
        }

        if (!empty($filtros['estilo'])) {
            $where[] = "estilo = ?";
            $params[] = $filtros['estilo'];
        }

        if (!empty($filtros['status'])) {
            $where[] = "status = ?";
            $params[] = $filtros['status'];
        }

        if (!empty($filtros['localizacao'])) {
            $where[] = "localizacao LIKE ?";
            $params[] = "%{$filtros['localizacao']}%";
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT * FROM {$this->table}
                WHERE {$whereClause}
                ORDER BY data_entrada ASC";

        return $this->db->fetchAll($sql, $params);
    }
}
?>
