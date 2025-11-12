<?php
require_once 'BaseModel.php';

class SaidaBarril extends BaseModel {
    protected $table = 'saida_barril';
    protected $fillable = [
        'estoque_barril_id', 'envase_id', 'lote_codigo', 'numero_barril',
        'quantidade_litros', 'estilo', 'data_saida', 'destino',
        'responsavel_id', 'observacoes'
    ];

    /**
     * Registra saída de barril
     */
    public function registrarSaida($dados) {
        // Buscar informações do estoque de barris
        $sql = "SELECT * FROM estoque_barris WHERE id = ? AND status = 'disponivel'";
        $barril = $this->db->fetchOne($sql, [$dados['estoque_barril_id']]);

        if (!$barril) {
            throw new Exception('Barril não encontrado ou já baixado');
        }

        // Preencher dados automaticamente
        $dados['envase_id'] = $barril['envase_id'];
        $dados['lote_codigo'] = $barril['lote_codigo'];
        $dados['numero_barril'] = $barril['numero_barril'];
        $dados['quantidade_litros'] = $barril['quantidade_litros'];
        $dados['estilo'] = $barril['estilo'];

        // Definir responsável
        if (empty($dados['responsavel_id'])) {
            $user = getCurrentUser();
            $dados['responsavel_id'] = $user['id'];
        }

        // Definir data se não fornecida
        if (empty($dados['data_saida'])) {
            $dados['data_saida'] = date('Y-m-d');
        }

        // Registrar saída
        $saidaId = $this->create($dados);

        if ($saidaId) {
            // Atualizar estoque para baixado
            $this->db->query("UPDATE estoque_barris SET status = 'baixado' WHERE id = ?",
                [$dados['estoque_barril_id']]);

            // Log de atividade
            logActivity('saida_barril', "Saída registrada - Lote: {$barril['lote_codigo']}, Barril: {$barril['numero_barril']}");
        }

        return $saidaId;
    }

    /**
     * Busca saída com detalhes
     */
    public function getComDetalhes($saidaId) {
        $sql = "SELECT sb.*,
                       u.nome as responsavel_nome,
                       eb.codigo_barril
                FROM {$this->table} sb
                LEFT JOIN usuarios u ON sb.responsavel_id = u.id
                LEFT JOIN estoque_barris eb ON sb.estoque_barril_id = eb.id
                WHERE sb.id = ?";

        return $this->db->fetchOne($sql, [$saidaId]);
    }

    /**
     * Lista saídas por período
     */
    public function getByPeriodo($dataInicio, $dataFim) {
        $sql = "SELECT sb.*,
                       u.nome as responsavel_nome
                FROM {$this->table} sb
                LEFT JOIN usuarios u ON sb.responsavel_id = u.id
                WHERE sb.data_saida BETWEEN ? AND ?
                ORDER BY sb.data_saida DESC, sb.id DESC";

        return $this->db->fetchAll($sql, [$dataInicio, $dataFim]);
    }

    /**
     * Lista saídas recentes
     */
    public function getRecentes($limit = 50) {
        $sql = "SELECT sb.*,
                       u.nome as responsavel_nome
                FROM {$this->table} sb
                LEFT JOIN usuarios u ON sb.responsavel_id = u.id
                ORDER BY sb.data_saida DESC, sb.id DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Busca saídas por lote
     */
    public function getByLote($loteCodigo) {
        $sql = "SELECT sb.*,
                       u.nome as responsavel_nome
                FROM {$this->table} sb
                LEFT JOIN usuarios u ON sb.responsavel_id = u.id
                WHERE sb.lote_codigo = ?
                ORDER BY sb.data_saida DESC";

        return $this->db->fetchAll($sql, [$loteCodigo]);
    }

    /**
     * Estatísticas de saídas
     */
    public function getStats($dataInicio = null, $dataFim = null) {
        $where = '1=1';
        $params = [];

        if ($dataInicio && $dataFim) {
            $where = "data_saida BETWEEN ? AND ?";
            $params = [$dataInicio, $dataFim];
        }

        $sql = "SELECT
                COUNT(*) as total_saidas,
                COUNT(DISTINCT lote_codigo) as lotes_diferentes,
                SUM(quantidade_litros) as total_litros_saida,
                COUNT(DISTINCT estilo) as estilos_diferentes
                FROM {$this->table}
                WHERE {$where}";

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Relatório de saídas por estilo
     */
    public function getRelatorioPorEstilo($dataInicio = null, $dataFim = null) {
        $where = '1=1';
        $params = [];

        if ($dataInicio && $dataFim) {
            $where = "data_saida BETWEEN ? AND ?";
            $params = [$dataInicio, $dataFim];
        }

        $sql = "SELECT
                estilo,
                COUNT(*) as total_barris,
                SUM(quantidade_litros) as total_litros
                FROM {$this->table}
                WHERE {$where}
                GROUP BY estilo
                ORDER BY total_litros DESC";

        return $this->db->fetchAll($sql, $params);
    }
}
?>
