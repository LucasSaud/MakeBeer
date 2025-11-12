<?php
require_once 'BaseModel.php';

class Barril extends BaseModel {
    // IMPORTANTE: Usa estoque_barris, não barris (que é catálogo de barris físicos)
    protected $table = 'estoque_barris';
    protected $fillable = [
        'barril_fisico_id', 'envase_id', 'lote_codigo', 'numero_barril',
        'codigo_barril', 'quantidade_litros', 'estilo', 'data_entrada', 'status',
        'localizacao', 'temperatura_armazenamento', 'observacoes'
    ];

    /**
     * Cria novo barril no estoque
     */
    public function criarBarril($dados) {
        // Preencher lote_codigo e estilo se fornecido envase_id
        if (!empty($dados['envase_id'])) {
            $envase = (new Envase())->getComDetalhes($dados['envase_id']);
            if ($envase) {
                $dados['lote_codigo'] = $envase['lote_codigo'];
                $dados['estilo'] = $envase['estilo'];
            }
        }

        // Gerar código do barril se não fornecido
        if (empty($dados['codigo_barril'])) {
            $prefixo = $dados['lote_codigo'] ?? 'BARRIL';
            $numero = $dados['numero_barril'] ?? rand(1, 999);
            $dados['codigo_barril'] = $prefixo . '-B' . str_pad($numero, 3, '0', STR_PAD_LEFT);
        }

        // Definir data se não fornecida
        if (empty($dados['data_entrada'])) {
            $dados['data_entrada'] = date('Y-m-d');
        }

        // Definir status inicial
        if (empty($dados['status'])) {
            $dados['status'] = 'disponivel';
        }

        return $this->create($dados);
    }

    /**
     * Busca barris por envase
     */
    public function getByEnvase($envaseId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE envase_id = ?
                ORDER BY numero_barril";

        return $this->db->fetchAll($sql, [$envaseId]);
    }

    /**
     * Busca barril com detalhes
     */
    public function getComDetalhes($barrilId) {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE id = ?";

        return $this->db->fetchOne($sql, [$barrilId]);
    }

    /**
     * Busca barris disponíveis
     */
    public function getDisponiveis() {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE status = 'disponivel'
                ORDER BY data_entrada DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Dar baixa em barril
     */
    public function darBaixa($barrilId) {
        return $this->update($barrilId, [
            'status' => 'baixado'
        ]);
    }

    /**
     * Busca barris por código de lote
     */
    public function getByLoteCodigo($loteCodigo) {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE lote_codigo = ?
                ORDER BY numero_barril";

        return $this->db->fetchAll($sql, [$loteCodigo]);
    }

    /**
     * Estatísticas de barris
     */
    public function getStats() {
        $sql = "SELECT
                COUNT(*) as total_barris,
                SUM(CASE WHEN status = 'disponivel' THEN 1 ELSE 0 END) as barris_disponiveis,
                SUM(CASE WHEN status = 'baixado' THEN 1 ELSE 0 END) as barris_baixados,
                SUM(CASE WHEN status = 'disponivel' THEN quantidade_litros ELSE 0 END) as litros_disponiveis,
                SUM(quantidade_litros) as total_litros_envasados
                FROM {$this->table}";

        return $this->db->fetchOne($sql);
    }
}
?>
