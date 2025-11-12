<?php
require_once 'BaseModel.php';

class ProdutoFinal extends BaseModel {
    protected $table = 'produtos_finais';
    protected $fillable = [
        'nome', 'estilo', 'descricao', 'abv', 'ibu', 'tipo_embalagem',
        'preco_venda', 'estoque_atual', 'estoque_minimo', 'ativo'
    ];

    /**
     * Busca produtos ativos
     */
    public function getAtivos() {
        return $this->where('ativo', 1);
    }

    /**
     * Busca produtos com estoque baixo
     */
    public function getEstoqueBaixo() {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE estoque_atual <= estoque_minimo AND ativo = 1
                ORDER BY (estoque_atual / estoque_minimo) ASC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Atualiza estoque
     */
    public function atualizarEstoque($produtoId, $quantidade, $operacao = 'adicionar') {
        $produto = $this->find($produtoId);

        if (!$produto) {
            throw new Exception('Produto não encontrado');
        }

        $novoEstoque = $produto['estoque_atual'];

        if ($operacao === 'adicionar') {
            $novoEstoque += $quantidade;
        } elseif ($operacao === 'subtrair') {
            $novoEstoque -= $quantidade;
        } else {
            $novoEstoque = $quantidade;
        }

        if ($novoEstoque < 0) {
            throw new Exception('Estoque não pode ser negativo');
        }

        return $this->update($produtoId, ['estoque_atual' => $novoEstoque]);
    }

    /**
     * Registra produção de produto
     */
    public function registrarProducao($loteProducaoId, $produtoId, $quantidade, $dados = []) {
        $user = getCurrentUser();

        $sql = "INSERT INTO producao_produtos
                (lote_producao_id, produto_final_id, quantidade_produzida, data_envase,
                 data_validade, lote_produto, observacoes, responsavel_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $loteProducaoId,
            $produtoId,
            $quantidade,
            $dados['data_envase'] ?? date('Y-m-d'),
            $dados['data_validade'] ?? null,
            $dados['lote_produto'] ?? null,
            $dados['observacoes'] ?? '',
            $user['id']
        ];

        $result = $this->db->query($sql, $params);

        if ($result) {
            // Atualizar estoque
            $this->atualizarEstoque($produtoId, $quantidade, 'adicionar');

            // Registrar movimentação
            $sqlMov = "INSERT INTO movimentacoes_estoque
                       (produto_final_id, tipo, quantidade, lote_producao_id, usuario_id, data_movimentacao)
                       VALUES (?, 'entrada', ?, ?, ?, NOW())";

            $this->db->query($sqlMov, [$produtoId, $quantidade, $loteProducaoId, $user['id']]);
        }

        return $result;
    }

    /**
     * Histórico de produção
     */
    public function getHistoricoProducao($produtoId, $limite = 50) {
        $sql = "SELECT pp.*, lp.codigo as lote_codigo, u.nome as responsavel_nome
                FROM producao_produtos pp
                LEFT JOIN lotes_producao lp ON pp.lote_producao_id = lp.id
                LEFT JOIN usuarios u ON pp.responsavel_id = u.id
                WHERE pp.produto_final_id = ?
                ORDER BY pp.data_envase DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$produtoId, $limite]);
    }

    /**
     * Estatísticas de produtos
     */
    public function getStats() {
        $sql = "SELECT
                COUNT(*) as total_produtos,
                SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN estoque_atual <= estoque_minimo THEN 1 ELSE 0 END) as estoque_baixo,
                SUM(estoque_atual) as quantidade_total_estoque,
                SUM(estoque_atual * preco_venda) as valor_total_estoque
                FROM {$this->table}";

        return $this->db->fetchOne($sql);
    }

    /**
     * Produtos por tipo de embalagem
     */
    public function getByTipoEmbalagem($tipo) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tipo_embalagem = ? AND ativo = 1
                ORDER BY nome";

        return $this->db->fetchAll($sql, [$tipo]);
    }
}
?>
