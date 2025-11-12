<?php
require_once 'BaseModel.php';

class CamaraFriaSetor extends BaseModel {
    protected $table = 'camarafria_setores';
    protected $fillable = [
        'nome', 'descricao', 'capacidade_maxima', 'temperatura_ideal', 'ativo'
    ];

    /**
     * Busca setores ativos
     */
    public function getAtivos() {
        return $this->where('ativo', 1);
    }

    /**
     * Busca setor com estatísticas de ocupação
     */
    public function getComEstatisticas($id) {
        $sql = "SELECT
                s.*,
                COUNT(DISTINCT el.id) as total_localizacoes,
                COALESCE(SUM(el.quantidade), 0) as quantidade_total,
                COUNT(DISTINCT el.produto_id) as produtos_diferentes,
                ROUND((COALESCE(SUM(el.quantidade), 0) / NULLIF(s.capacidade_maxima, 0) * 100), 2) as percentual_ocupacao
                FROM {$this->table} s
                LEFT JOIN estoque_localizacao el ON s.id = el.setor_id
                WHERE s.id = ?
                GROUP BY s.id";

        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Busca todos os setores com estatísticas
     */
    public function getAllComEstatisticas() {
        $sql = "SELECT
                s.*,
                COUNT(DISTINCT el.id) as total_localizacoes,
                COALESCE(SUM(el.quantidade), 0) as quantidade_total,
                COUNT(DISTINCT el.produto_id) as produtos_diferentes,
                ROUND((COALESCE(SUM(el.quantidade), 0) / NULLIF(s.capacidade_maxima, 0) * 100), 2) as percentual_ocupacao
                FROM {$this->table} s
                LEFT JOIN estoque_localizacao el ON s.id = el.setor_id AND el.status = 'disponivel'
                WHERE s.ativo = 1
                GROUP BY s.id
                ORDER BY s.nome";

        return $this->db->fetchAll($sql);
    }

    /**
     * Verifica se setor tem capacidade disponível
     */
    public function temCapacidade($setorId, $quantidadeAdicional) {
        $setor = $this->getComEstatisticas($setorId);

        if (!$setor || !$setor['capacidade_maxima']) {
            return true; // Se não tem limite definido, aceita
        }

        $ocupacaoAtual = $setor['quantidade_total'] ?? 0;
        return ($ocupacaoAtual + $quantidadeAdicional) <= $setor['capacidade_maxima'];
    }

    /**
     * Busca produtos em um setor específico
     */
    public function getProdutosNoSetor($setorId) {
        $sql = "SELECT
                el.*,
                p.nome as produto_nome,
                p.tipo_embalagem,
                l.codigo as numero_lote,
                pp.data_validade
                FROM estoque_localizacao el
                INNER JOIN produtos_finais p ON el.produto_id = p.id
                LEFT JOIN lotes_producao l ON el.lote_id = l.id
                LEFT JOIN producao_produtos pp ON l.id = pp.lote_producao_id AND pp.produto_final_id = el.produto_id
                WHERE el.setor_id = ?
                ORDER BY el.status, p.nome";

        return $this->db->fetchAll($sql, [$setorId]);
    }

    /**
     * Estatísticas gerais da câmara fria
     */
    public function getEstatisticasGerais() {
        $sql = "SELECT
                COUNT(DISTINCT s.id) as total_setores,
                SUM(s.capacidade_maxima) as capacidade_total,
                COUNT(DISTINCT el.produto_id) as produtos_estocados,
                COALESCE(SUM(el.quantidade), 0) as quantidade_total_estocada,
                ROUND((COALESCE(SUM(el.quantidade), 0) / NULLIF(SUM(s.capacidade_maxima), 0) * 100), 2) as ocupacao_geral
                FROM {$this->table} s
                LEFT JOIN estoque_localizacao el ON s.id = el.setor_id AND el.status = 'disponivel'
                WHERE s.ativo = 1";

        return $this->db->fetchOne($sql);
    }
}
?>
