<?php
require_once 'BaseModel.php';

class CategoriaInsumo extends BaseModel {
    protected $table = 'categorias_insumos';
    protected $fillable = ['nome', 'descricao'];
    protected $timestamps = false;

    /**
     * Busca categoria com contagem de insumos
     */
    public function getComContagem() {
        $sql = "SELECT c.*,
                COUNT(i.id) as total_insumos,
                SUM(CASE WHEN i.ativo = 1 THEN 1 ELSE 0 END) as insumos_ativos
                FROM {$this->table} c
                LEFT JOIN insumos i ON c.id = i.categoria_id
                GROUP BY c.id
                ORDER BY c.nome";

        return $this->db->fetchAll($sql);
    }

    /**
     * Busca insumos da categoria
     */
    public function getInsumos($categoriaId) {
        $sql = "SELECT * FROM insumos
                WHERE categoria_id = ? AND ativo = 1
                ORDER BY nome";

        return $this->db->fetchAll($sql, [$categoriaId]);
    }
}
?>
