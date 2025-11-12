<?php
require_once 'BaseModel.php';

class Envase extends BaseModel {
    protected $table = 'envases';
    protected $fillable = [
        'lote_producao_id', 'barril_id', 'quantidade_litros', 'data_envase',
        'status', 'usuario_id', 'observacoes'
    ];

    /**
     * Cria novo envase
     */
    public function criarEnvase($dados) {
        // Definir usuário responsável
        if (empty($dados['usuario_id'])) {
            $user = getCurrentUser();
            $dados['usuario_id'] = $user['id'];
        }

        // Definir data se não fornecida
        if (empty($dados['data_envase'])) {
            $dados['data_envase'] = date('Y-m-d');
        }

        // Definir status inicial
        if (empty($dados['status'])) {
            $dados['status'] = 'envasado';
        }

        // Definir quantidade de litros com base no lote se não fornecida
        if (empty($dados['quantidade_litros']) && !empty($dados['lote_producao_id'])) {
            $lote = (new LoteProducao())->find($dados['lote_producao_id']);
            if ($lote && $lote['volume_planejado']) {
                $dados['quantidade_litros'] = $lote['volume_planejado'];
            } else {
                $dados['quantidade_litros'] = 0;
            }
        }

        // Criar envase
        $envaseId = $this->create($dados);

        // Atualizar lote de produção
        if ($envaseId) {
            $loteModel = new LoteProducao();
            $loteModel->update($dados['lote_producao_id'], [
                'envase_iniciado' => true,
                'data_envase' => $dados['data_envase']
            ]);
        }

        return $envaseId;
    }

    /**
     * Busca envase com detalhes completos
     */
    public function getComDetalhes($envaseId) {
        $sql = "SELECT e.*,
                       lp.codigo as lote_codigo,
                       lp.volume_real as lote_volume,
                       bc.capacidade_litros as barril_capacidade,
                       u.nome as responsavel_nome
                FROM {$this->table} e
                LEFT JOIN lotes_producao lp ON e.lote_producao_id = lp.id
                LEFT JOIN barris_cadastro bc ON e.barril_id = bc.id
                LEFT JOIN usuarios u ON e.usuario_id = u.id
                WHERE e.id = ?";

        $envase = $this->db->fetchOne($sql, [$envaseId]);

        if ($envase) {
            // Buscar barris do estoque (não existe tabela barris separada)
            // Os barris são registrados diretamente no estoque_barris
            $sqlBarris = "SELECT * FROM estoque_barris
                         WHERE envase_id = ?
                         ORDER BY numero_barril";
            $envase['barris'] = $this->db->fetchAll($sqlBarris, [$envaseId]);
        }

        return $envase;
    }

    /**
     * Lista envases por status
     */
    public function getByStatus($status) {
        $sql = "SELECT e.*,
                       lp.codigo as lote_codigo,
                       bc.capacidade_litros as barril_capacidade,
                       u.nome as responsavel_nome
                FROM {$this->table} e
                LEFT JOIN lotes_producao lp ON e.lote_producao_id = lp.id
                LEFT JOIN barris_cadastro bc ON e.barril_id = bc.id
                LEFT JOIN usuarios u ON e.usuario_id = u.id
                WHERE e.status = ?
                ORDER BY e.data_envase DESC";

        return $this->db->fetchAll($sql, [$status]);
    }

    /**
     * Lista envases ativos
     */
    public function getAtivos() {
        return $this->getByStatus('envasado');
    }

    /**
     * Finaliza envase
     */
    public function finalizar($envaseId) {
        $result = $this->update($envaseId, [
            'status' => 'baixado'
        ]);

        if ($result) {
            // Atualizar lote de produção
            $envase = $this->find($envaseId);
            if ($envase) {
                $loteModel = new LoteProducao();
                $loteModel->update($envase['lote_producao_id'], [
                    'envase_finalizado' => true
                ]);
            }
        }

        return $result;
    }

    /**
     * Busca envase por lote
     */
    public function getByLote($loteId) {
        $sql = "SELECT e.*, u.nome as responsavel_nome
                FROM {$this->table} e
                LEFT JOIN usuarios u ON e.usuario_id = u.id
                WHERE e.lote_producao_id = ?
                ORDER BY e.data_envase DESC";

        return $this->db->fetchAll($sql, [$loteId]);
    }

    /**
     * Estatísticas de envase
     */
    public function getStats($dataInicio = null, $dataFim = null) {
        $where = '1=1';
        $params = [];

        if ($dataInicio && $dataFim) {
            $where = "data_envase BETWEEN ? AND ?";
            $params = [$dataInicio, $dataFim];
        }

        $sql = "SELECT
                COUNT(*) as total_envases,
                SUM(CASE WHEN status = 'baixado' THEN 1 ELSE 0 END) as finalizados,
                SUM(CASE WHEN status = 'envasado' THEN 1 ELSE 0 END) as em_processo,
                SUM(quantidade_litros) as total_litros_envasados
                FROM {$this->table}
                WHERE {$where}";

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Busca lotes disponíveis para envase
     */
    public function getLotesDisponiveis() {
        $sql = "SELECT lp.*, r.nome as receita_nome, r.estilo
                FROM lotes_producao lp
                LEFT JOIN receitas r ON lp.receita_id = r.id
                WHERE lp.status = 'finalizado'
                AND (lp.envase_iniciado = FALSE OR lp.envase_iniciado IS NULL)
                ORDER BY lp.data_fim DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Busca barris disponíveis
     */
    public function getBarrisDisponiveis() {
        $sql = "SELECT * FROM barris_cadastro WHERE ativo = 1 ORDER BY numero";
        return $this->db->fetchAll($sql);
    }
}

?>