<?php
require_once 'BaseModel.php';

class LoteProducao extends BaseModel {
    protected $table = 'lotes_producao';
    protected $fillable = [
        'codigo', 'receita_id', 'volume_planejado', 'volume_real', 'data_inicio',
        'data_fim', 'status', 'densidade_inicial', 'densidade_final', 'ph_inicial',
        'ph_final', 'temperatura_fermentacao', 'rendimento', 'observacoes', 'responsavel_id'
    ];

    /**
     * Busca lotes ativos (não finalizados ou cancelados)
     */
    public function getLotesAtivos() {
        $sql = "SELECT * FROM {$this->table}
                WHERE status NOT IN ('finalizado', 'cancelado')
                ORDER BY data_inicio DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Cria novo lote de produção
     */
    public function criarLote($dados) {
        // Gerar código se não fornecido
        if (empty($dados['codigo'])) {
            $dados['codigo'] = generateLoteCode('LT');
        }

        // Definir responsável
        if (empty($dados['responsavel_id'])) {
            $user = getCurrentUser();
            $dados['responsavel_id'] = $user['id'];
        }

        // Definir status inicial
        if (empty($dados['status'])) {
            $dados['status'] = 'planejado';
        }

        // Criar lote
        $loteId = $this->create($dados);

        if ($loteId && !empty($dados['receita_id'])) {
            // Copiar ingredientes da receita para consumos planejados
            $this->copiarIngredientesReceita($loteId, $dados['receita_id']);
        }

        return $loteId;
    }

    /**
     * Copia ingredientes da receita para o lote
     */
    private function copiarIngredientesReceita($loteId, $receitaId) {
        $sql = "INSERT INTO lote_consumos
                (lote_id, insumo_id, quantidade_planejada, fase)
                SELECT ?, insumo_id, quantidade, fase
                FROM receita_ingredientes
                WHERE receita_id = ?";

        return $this->db->query($sql, [$loteId, $receitaId]);
    }

    /**
     * Inicia produção do lote
     */
    public function iniciarProducao($loteId) {
        $lote = $this->find($loteId);

        if (!$lote) {
            throw new Exception('Lote não encontrado');
        }

        if ($lote['status'] !== 'planejado') {
            throw new Exception('Lote não está no status planejado');
        }

        // Verificar disponibilidade de estoque
        $disponibilidade = $this->verificarDisponibilidadeInsumos($loteId);

        foreach ($disponibilidade as $item) {
            if (!$item['disponivel']) {
                throw new Exception("Estoque insuficiente de: {$item['insumo_nome']}");
            }
        }

        // Atualizar status
        return $this->update($loteId, [
            'status' => 'em_producao',
            'data_inicio' => date('Y-m-d')
        ]);
    }

    /**
     * Registra consumo real de insumo
     */
    public function registrarConsumo($loteId, $insumoId, $quantidadeReal, $fase) {
        // Atualizar consumo no lote
        $sql = "UPDATE lote_consumos
                SET quantidade_real = ?, data_consumo = NOW()
                WHERE lote_id = ? AND insumo_id = ? AND fase = ?";

        $this->db->query($sql, [$quantidadeReal, $loteId, $insumoId, $fase]);

        // Atualizar estoque do insumo
        $insumoModel = new Insumo();
        $insumoModel->atualizarEstoque($insumoId, $quantidadeReal, 'subtrair');

        // Registrar movimentação
        $user = getCurrentUser();
        $sqlMov = "INSERT INTO movimentacoes_estoque
                   (insumo_id, tipo, quantidade, lote_producao_id, usuario_id, data_movimentacao)
                   VALUES (?, 'saida', ?, ?, ?, NOW())";

        $this->db->query($sqlMov, [$insumoId, $quantidadeReal, $loteId, $user['id']]);
    }

    /**
     * Finaliza lote
     */
    public function finalizar($loteId, $dados = []) {
        $updateData = [
            'status' => 'finalizado',
            'data_fim' => date('Y-m-d')
        ];

        if (!empty($dados['volume_real'])) {
            $updateData['volume_real'] = $dados['volume_real'];
        }

        if (!empty($dados['densidade_final'])) {
            $updateData['densidade_final'] = $dados['densidade_final'];
        }

        if (!empty($dados['ph_final'])) {
            $updateData['ph_final'] = $dados['ph_final'];
        }

        // Calcular rendimento
        $lote = $this->find($loteId);
        if ($lote && !empty($dados['volume_real'])) {
            $rendimento = ($dados['volume_real'] / $lote['volume_planejado']) * 100;
            $updateData['rendimento'] = $rendimento;
        }

        return $this->update($loteId, $updateData);
    }

    /**
     * Busca lote com detalhes completos
     */
    public function getComDetalhes($loteId) {
        $sql = "SELECT l.*, r.nome as receita_nome, u.nome as responsavel_nome
                FROM {$this->table} l
                LEFT JOIN receitas r ON l.receita_id = r.id
                LEFT JOIN usuarios u ON l.responsavel_id = u.id
                WHERE l.id = ?";

        $lote = $this->db->fetchOne($sql, [$loteId]);

        if ($lote) {
            // Buscar consumos
            $sqlConsumos = "SELECT lc.*, i.nome as insumo_nome, i.unidade_medida
                           FROM lote_consumos lc
                           LEFT JOIN insumos i ON lc.insumo_id = i.id
                           WHERE lc.lote_id = ?
                           ORDER BY lc.fase";

            $lote['consumos'] = $this->db->fetchAll($sqlConsumos, [$loteId]);
        }

        return $lote;
    }

    /**
     * Lista lotes por status
     */
    public function getByStatus($status) {
        $sql = "SELECT l.*, r.nome as receita_nome, u.nome as responsavel_nome
                FROM {$this->table} l
                LEFT JOIN receitas r ON l.receita_id = r.id
                LEFT JOIN usuarios u ON l.responsavel_id = u.id
                WHERE l.status = ?
                ORDER BY l.data_inicio DESC";

        return $this->db->fetchAll($sql, [$status]);
    }

    /**
     * Verifica disponibilidade de insumos
     */
    public function verificarDisponibilidadeInsumos($loteId) {
        $sql = "SELECT
                lc.insumo_id,
                i.nome as insumo_nome,
                lc.quantidade_planejada,
                i.estoque_atual,
                i.unidade_medida,
                CASE
                    WHEN i.estoque_atual >= lc.quantidade_planejada THEN 1
                    ELSE 0
                END as disponivel
                FROM lote_consumos lc
                LEFT JOIN insumos i ON lc.insumo_id = i.id
                WHERE lc.lote_id = ?";

        return $this->db->fetchAll($sql, [$loteId]);
    }

    /**
     * Estatísticas de produção
     */
    public function getStats($dataInicio = null, $dataFim = null) {
        $where = '1=1';
        $params = [];

        if ($dataInicio && $dataFim) {
            $where = "data_inicio BETWEEN ? AND ?";
            $params = [$dataInicio, $dataFim];
        }

        $sql = "SELECT
                COUNT(*) as total_lotes,
                SUM(CASE WHEN status = 'finalizado' THEN 1 ELSE 0 END) as finalizados,
                SUM(CASE WHEN status = 'em_producao' THEN 1 ELSE 0 END) as em_producao,
                SUM(volume_real) as volume_total_produzido,
                AVG(rendimento) as rendimento_medio
                FROM {$this->table}
                WHERE {$where}";

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Busca histórico de produção por receita
     */
    public function getHistoricoByReceita($receitaId, $limit = 10) {
        $sql = "SELECT * FROM {$this->table}
                WHERE receita_id = ?
                ORDER BY data_inicio DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$receitaId, $limit]);
    }

    /**
     * Busca estatísticas de produção por receita
     */
    public function getStatsByReceita($receitaId) {
        $sql = "SELECT COUNT(*) as lotes_produzidos,
                       COALESCE(SUM(volume_real), 0) as total_produzido
                FROM {$this->table}
                WHERE receita_id = ? AND status = 'finalizado'";

        return $this->db->fetchOne($sql, [$receitaId]);
    }
}
?>
