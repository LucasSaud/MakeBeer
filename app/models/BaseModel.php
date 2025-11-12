<?php
abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Busca todos os registros
     */
    public function all($orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * Busca um registro por ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Busca registros com condições
     */
    public function where($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?";
        return $this->db->fetchAll($sql, [$value]);
    }

    /**
     * Busca o primeiro registro com condições
     */
    public function whereFirst($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ? LIMIT 1";
        return $this->db->fetchOne($sql, [$value]);
    }

    /**
     * Cria um novo registro
     */
    public function create($data) {
        $data = $this->filterFillable($data);

        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        return $this->db->insert($sql, $data);
    }

    /**
     * Atualiza um registro
     */
    public function update($id, $data) {
        $data = $this->filterFillable($data);

        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);

        $data[$this->primaryKey] = $id;

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :{$this->primaryKey}";

        $this->db->query($sql, $data);
        return $this->find($id);
    }

    /**
     * Deleta um registro
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql, [$id]);
    }

    /**
     * Conta registros
     */
    public function count($where = null, $params = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        if ($where) {
            $sql .= " WHERE {$where}";
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Paginação
     */
    public function paginate($page = 1, $perPage = 20, $where = null, $params = []) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $data = $this->db->fetchAll($sql, $params);
        $total = $this->count($where, $params);

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    /**
     * Filtra apenas campos permitidos
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Executa validações personalizadas
     */
    protected function validate($data) {
        return true;
    }

    /**
     * Hook executado antes de salvar
     */
    protected function beforeSave($data) {
        return $data;
    }

    /**
     * Hook executado após salvar
     */
    protected function afterSave($data, $id) {
        return $data;
    }


}
?>