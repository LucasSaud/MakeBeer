<?php
require_once 'BaseModel.php';

class Fornecedor extends BaseModel {
    protected $table = 'fornecedores';
    protected $fillable = [
        'nome', 'cnpj', 'email', 'telefone', 'endereco', 'cidade',
        'estado', 'cep', 'contato_principal', 'observacoes', 'ativo'
    ];

    /**
     * Busca fornecedores ativos
     */
    public function getAtivos() {
        return $this->where('ativo', 1);
    }

    /**
     * Verifica se CNPJ já existe
     */
    public function cnpjExists($cnpj, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table} WHERE cnpj = ?";
        $params = [$cnpj];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result !== false;
    }

    /**
     * Busca fornecedor com estatísticas
     */
    public function getWithStats($fornecedorId) {
        $fornecedor = $this->find($fornecedorId);

        if (!$fornecedor) {
            return null;
        }

        // Estatísticas de compras
        $sql = "SELECT
                COUNT(DISTINCT e.id) as total_compras,
                SUM(e.preco_total) as valor_total_comprado,
                COUNT(DISTINCT e.insumo_id) as total_insumos_diferentes,
                MAX(e.data_entrada) as ultima_compra
                FROM entradas_estoque e
                WHERE e.fornecedor_id = ?";

        $stats = $this->db->fetchOne($sql, [$fornecedorId]);
        $fornecedor['stats'] = $stats;

        return $fornecedor;
    }

    /**
     * Lista insumos fornecidos por um fornecedor
     */
    public function getInsumosFornecidos($fornecedorId) {
        $sql = "SELECT i.*
                FROM insumos i
                WHERE i.fornecedor_principal_id = ? AND i.ativo = 1
                ORDER BY i.nome";

        return $this->db->fetchAll($sql, [$fornecedorId]);
    }

    /**
     * Histórico de compras do fornecedor
     */
    public function getHistoricoCompras($fornecedorId, $limite = 50) {
        $sql = "SELECT e.*, i.nome as insumo_nome, u.nome as usuario_nome
                FROM entradas_estoque e
                LEFT JOIN insumos i ON e.insumo_id = i.id
                LEFT JOIN usuarios u ON e.usuario_id = u.id
                WHERE e.fornecedor_id = ?
                ORDER BY e.data_entrada DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$fornecedorId, $limite]);
    }

    /**
     * Pesquisa fornecedores
     */
    public function search($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['nome'])) {
            $where[] = "nome LIKE ?";
            $params[] = "%{$filters['nome']}%";
        }

        if (!empty($filters['cidade'])) {
            $where[] = "cidade LIKE ?";
            $params[] = "%{$filters['cidade']}%";
        }

        if (!empty($filters['estado'])) {
            $where[] = "estado = ?";
            $params[] = $filters['estado'];
        }

        if (isset($filters['ativo'])) {
            $where[] = "ativo = ?";
            $params[] = $filters['ativo'];
        }

        $whereClause = !empty($where) ? implode(' AND ', $where) : '1=1';

        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY nome";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Validação customizada
     */
    protected function validate($data) {
        $errors = [];

        if (empty($data['nome'])) {
            $errors[] = 'Nome é obrigatório';
        }

        if (!empty($data['cnpj']) && !$this->validarCNPJ($data['cnpj'])) {
            $errors[] = 'CNPJ inválido';
        }

        if (!empty($data['email']) && !isValidEmail($data['email'])) {
            $errors[] = 'Email inválido';
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Valida CNPJ
     */
    private function validarCNPJ($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) != 14) {
            return false;
        }

        // Validação básica - pode ser expandida
        return true;
    }
}
?>
