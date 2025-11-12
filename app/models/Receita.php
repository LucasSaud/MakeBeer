<?php
require_once 'BaseModel.php';

class Receita extends BaseModel {
    protected $table = 'receitas';
    protected $fillable = [
        'nome', 'estilo', 'descricao', 'volume_batch', 'densidade_inicial',
        'densidade_final', 'ibu', 'srm', 'abv', 'tempo_fermentacao',
        'temperatura_fermentacao', 'instrucoes', 'ativo', 'criado_por'
    ];

    /**
     * Cria receita com ingredientes
     */
    public function criarComIngredientes($dadosReceita, $ingredientes) {
        $user = getCurrentUser();
        $dadosReceita['criado_por'] = $user['id'];

        // Garantir que volume_batch seja null se vazio
        if (isset($dadosReceita['volume_batch']) && ($dadosReceita['volume_batch'] === '' || $dadosReceita['volume_batch'] === null)) {
            $dadosReceita['volume_batch'] = null;
        }

        // Criar receita
        $receitaId = $this->create($dadosReceita);

        if ($receitaId && !empty($ingredientes)) {
            // Adicionar ingredientes
            foreach ($ingredientes as $ingrediente) {
                $this->adicionarIngrediente($receitaId, $ingrediente);
            }
        }

        return $receitaId;
    }

    /**
     * Adiciona ingrediente à receita
     */
    public function adicionarIngrediente($receitaId, $dados) {
        $sql = "INSERT INTO receita_ingredientes
                (receita_id, insumo_id, quantidade, unidade, fase, tempo_adicao, observacoes)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->db->query($sql, [
            $receitaId,
            $dados['insumo_id'],
            $dados['quantidade'],
            $dados['unidade'],
            $dados['fase'],
            $dados['tempo_adicao'] ?? 0,
            $dados['observacoes'] ?? ''
        ]);
    }

    /**
     * Busca receita com ingredientes
     */
    public function getComIngredientes($receitaId) {
        $receita = $this->find($receitaId);

        if (!$receita) {
            return null;
        }

        // Buscar ingredientes
        $sql = "SELECT ri.*, i.nome as insumo_nome, i.unidade_medida, i.tipo
                FROM receita_ingredientes ri
                LEFT JOIN insumos i ON ri.insumo_id = i.id
                WHERE ri.receita_id = ?
                ORDER BY
                    CASE ri.fase
                        WHEN 'mostura' THEN 1
                        WHEN 'fervura' THEN 2
                        WHEN 'fermentacao' THEN 3
                        WHEN 'maturacao' THEN 4
                        WHEN 'envase' THEN 5
                    END,
                    ri.tempo_adicao DESC";

        $receita['ingredientes'] = $this->db->fetchAll($sql, [$receitaId]);

        return $receita;
    }

    /**
     * Lista todas receitas ativas
     */
    public function getAtivas() {
        $sql = "SELECT r.*, u.nome as criador_nome
                FROM {$this->table} r
                LEFT JOIN usuarios u ON r.criado_por = u.id
                WHERE r.ativo = 1
                ORDER BY r.nome";

        return $this->db->fetchAll($sql);
    }

    /**
     * Calcula custo da receita
     */
    public function calcularCusto($receitaId) {
        $sql = "SELECT SUM(ri.quantidade * i.preco_medio) as custo_total
                FROM receita_ingredientes ri
                LEFT JOIN insumos i ON ri.insumo_id = i.id
                WHERE ri.receita_id = ?";

        $result = $this->db->fetchOne($sql, [$receitaId]);
        return $result['custo_total'] ?? 0;
    }

    /**
     * Verifica disponibilidade de estoque para produzir
     */
    public function verificarDisponibilidadeEstoque($receitaId, $multiplicador = 1) {
        $sql = "SELECT
                ri.insumo_id,
                i.nome as insumo_nome,
                ri.quantidade * ? as quantidade_necessaria,
                i.estoque_atual,
                i.unidade_medida,
                CASE
                    WHEN i.estoque_atual >= (ri.quantidade * ?) THEN 1
                    ELSE 0
                END as disponivel
                FROM receita_ingredientes ri
                LEFT JOIN insumos i ON ri.insumo_id = i.id
                WHERE ri.receita_id = ?";

        return $this->db->fetchAll($sql, [$multiplicador, $multiplicador, $receitaId]);
    }

    /**
     * Duplica receita
     */
    public function duplicar($receitaId, $novoNome = null) {
        $receita = $this->getComIngredientes($receitaId);

        if (!$receita) {
            throw new Exception('Receita não encontrada');
        }

        // Preparar dados da nova receita
        $novaReceita = $receita;
        unset($novaReceita['id'], $novaReceita['created_at'], $novaReceita['updated_at']);
        unset($novaReceita['ingredientes'], $novaReceita['criador_nome']);

        $novaReceita['nome'] = $novoNome ?? ($receita['nome'] . ' (Cópia)');

        $user = getCurrentUser();
        $novaReceita['criado_por'] = $user['id'];

        // Garantir que volume_batch seja null se vazio
        if (isset($novaReceita['volume_batch']) && ($novaReceita['volume_batch'] === '' || $novaReceita['volume_batch'] === null)) {
            $novaReceita['volume_batch'] = null;
        }

        // Criar nova receita
        $novaReceitaId = $this->create($novaReceita);

        // Copiar ingredientes
        if ($novaReceitaId) {
            foreach ($receita['ingredientes'] as $ingrediente) {
                unset($ingrediente['id'], $ingrediente['receita_id']);
                unset($ingrediente['insumo_nome'], $ingrediente['unidade_medida'], $ingrediente['tipo']);
                $this->adicionarIngrediente($novaReceitaId, $ingrediente);
            }
        }

        return $novaReceitaId;
    }

    /**
     * Pesquisa receitas
     */
    public function search($filters = []) {
        $where = ['r.ativo = 1'];
        $params = [];

        if (!empty($filters['nome'])) {
            $where[] = "r.nome LIKE ?";
            $params[] = "%{$filters['nome']}%";
        }

        if (!empty($filters['estilo'])) {
            $where[] = "r.estilo LIKE ?";
            $params[] = "%{$filters['estilo']}%";
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT r.*, u.nome as criador_nome
                FROM {$this->table} r
                LEFT JOIN usuarios u ON r.criado_por = u.id
                WHERE {$whereClause}
                ORDER BY r.nome";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Busca ingredientes detalhados da receita
     */
    public function getIngredientesDetalhados($receitaId) {
        $sql = "SELECT ri.*,
                       i.nome as insumo_nome,
                       i.unidade_medida,
                       i.estoque_atual as estoque_disponivel,
                       i.preco_medio as custo_medio
                FROM receita_ingredientes ri
                INNER JOIN insumos i ON ri.insumo_id = i.id
                WHERE ri.receita_id = ?
                ORDER BY
                    CASE ri.fase
                        WHEN 'mostura' THEN 1
                        WHEN 'fervura' THEN 2
                        WHEN 'fermentacao' THEN 3
                        WHEN 'maturacao' THEN 4
                        WHEN 'envase' THEN 5
                        ELSE 6
                    END,
                    i.nome";

        return $this->db->fetchAll($sql, [$receitaId]);
    }
}
?>