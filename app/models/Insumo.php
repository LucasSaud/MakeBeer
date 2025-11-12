<?php
require_once 'BaseModel.php';

class Insumo extends BaseModel {
    protected $table = 'insumos';
    protected $fillable = [
        'nome', 'descricao', 'categoria_id', 'tipo', 'unidade_medida',
        'estoque_atual', 'estoque_minimo', 'preco_medio', 'fornecedor_principal_id',
        'codigo_interno', 'ean', 'ativo', 'observacoes'
    ];

    /**
     * Busca insumos com estoque baixo
     */
    public function getEstoqueBaixo() {
        $sql = "SELECT i.*, c.nome as categoria_nome, f.nome as fornecedor_nome
                FROM {$this->table} i
                LEFT JOIN categorias_insumos c ON i.categoria_id = c.id
                LEFT JOIN fornecedores f ON i.fornecedor_principal_id = f.id
                WHERE i.estoque_atual <= i.estoque_minimo AND i.ativo = 1
                ORDER BY (i.estoque_atual / i.estoque_minimo) ASC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Busca insumos por tipo
     */
    public function getByTipo($tipo) {
        $sql = "SELECT i.*, c.nome as categoria_nome
                FROM {$this->table} i
                LEFT JOIN categorias_insumos c ON i.categoria_id = c.id
                WHERE i.tipo = ? AND i.ativo = 1
                ORDER BY i.nome";

        return $this->db->fetchAll($sql, [$tipo]);
    }

    /**
     * Atualiza estoque do insumo
     */
    public function atualizarEstoque($insumoId, $quantidade, $operacao = 'adicionar') {
        $insumo = $this->find($insumoId);

        if (!$insumo) {
            throw new Exception('Insumo não encontrado');
        }

        $novoEstoque = $insumo['estoque_atual'];

        if ($operacao === 'adicionar') {
            $novoEstoque += $quantidade;
        } elseif ($operacao === 'subtrair') {
            $novoEstoque -= $quantidade;
        } else {
            $novoEstoque = $quantidade; // Definir valor absoluto
        }

        if ($novoEstoque < 0) {
            throw new Exception('Estoque não pode ser negativo');
        }

        return $this->update($insumoId, ['estoque_atual' => $novoEstoque]);
    }

    // Adicione este método no seu model Insumo
public function getWithDetails($id) {
    $db = Database::getInstance();
    $sql = "SELECT i.*,
                   c.nome as categoria_nome,
                   f.nome as fornecedor_nome,
                   COALESCE(i.estoque_atual, 0) as estoque_atual,
                   COALESCE(i.estoque_minimo, 0) as estoque_minimo,
                   COALESCE(i.preco_medio, 0) as preco_medio
            FROM insumos i
            LEFT JOIN categorias_insumos c ON i.categoria_id = c.id
            LEFT JOIN fornecedores f ON i.fornecedor_principal_id = f.id
            WHERE i.id = ? AND i.ativo = 1";

    return $db->fetchOne($sql, [$id]);
}

public function getAllWithDetails() {
    $db = Database::getInstance();
    $sql = "SELECT i.*, 
                   c.nome as categoria_nome,
                   f.nome as fornecedor_nome,
                   COALESCE(i.estoque_atual, 0) as estoque_atual,
                   COALESCE(i.estoque_minimo, 0) as estoque_minimo,
                   COALESCE(i.preco_medio, 0) as preco_medio
            FROM insumos i
            LEFT JOIN categorias_insumos c ON i.categoria_id = c.id
            LEFT JOIN fornecedores f ON i.fornecedor_principal_id = f.id
            WHERE i.ativo = 1
            ORDER BY i.nome";
    
    return $db->fetchAll($sql);
}

    /**
     * Busca estatísticas de insumos
     */
    public function getStats() {
        $sql = "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN estoque_atual <= estoque_minimo THEN 1 ELSE 0 END) as estoque_baixo,
                SUM(estoque_atual * preco_medio) as valor_total_estoque
                FROM {$this->table}";

        return $this->db->fetchOne($sql);
    }

    /**
     * Pesquisa insumos
     */
    public function search($filters = []) {
        $where = ['i.ativo = 1'];
        $params = [];

        if (!empty($filters['nome'])) {
            $where[] = "i.nome LIKE ?";
            $params[] = "%{$filters['nome']}%";
        }

        if (!empty($filters['tipo'])) {
            $where[] = "i.tipo = ?";
            $params[] = $filters['tipo'];
        }

        if (!empty($filters['categoria_id'])) {
            $where[] = "i.categoria_id = ?";
            $params[] = $filters['categoria_id'];
        }

        if (isset($filters['estoque_baixo']) && $filters['estoque_baixo']) {
            $where[] = "i.estoque_atual <= i.estoque_minimo";
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT i.*, c.nome as categoria_nome, f.nome as fornecedor_nome
                FROM {$this->table} i
                LEFT JOIN categorias_insumos c ON i.categoria_id = c.id
                LEFT JOIN fornecedores f ON i.fornecedor_principal_id = f.id
                WHERE {$whereClause}
                ORDER BY i.nome";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Histórico de movimentações do insumo
     */
    public function getHistoricoMovimentacoes($insumoId, $limite = 50) {
        $sql = "SELECT m.*, u.nome as usuario_nome
                FROM movimentacoes_estoque m
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                WHERE m.insumo_id = ?
                ORDER BY m.data_movimentacao DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$insumoId, $limite]);
    }
}
?>
