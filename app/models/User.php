<?php
require_once 'BaseModel.php';

class User extends BaseModel {
    protected $table = 'usuarios';
    protected $fillable = ['nome', 'email', 'senha', 'perfil', 'ativo'];

    /**
     * Autentica usuário
     */
    public function authenticate($email, $password) {
        $user = $this->whereFirst('email', $email);

        if ($user && verifyPassword($password, $user['senha'])) {
            // Atualizar último acesso
            $this->update($user['id'], ['ultimo_acesso' => date('Y-m-d H:i:s')]);

            // Remover senha dos dados retornados
            unset($user['senha']);

            return $user;
        }

        return false;
    }

    /**
     * Cria novo usuário
     */
    public function createUser($data) {
        // Hash da senha
        if (isset($data['senha'])) {
            $data['senha'] = hashPassword($data['senha']);
        }

        return $this->create($data);
    }

    /**
     * Atualiza senha do usuário
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = hashPassword($newPassword);
        return $this->update($userId, ['senha' => $hashedPassword]);
    }

    /**
     * Verifica se email já existe
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result !== false;
    }

    /**
     * Busca usuários ativos
     */
    public function getActiveUsers() {
        return $this->where('ativo', 1);
    }

    /**
     * Busca usuários por perfil
     */
    public function getUsersByProfile($profile) {
        $sql = "SELECT * FROM {$this->table} WHERE perfil = ? AND ativo = 1";
        return $this->db->fetchAll($sql, [$profile]);
    }

    /**
     * Obtém estatísticas de usuários
     */
    public function getStats() {
        $sql = "
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as inativos,
                SUM(CASE WHEN perfil = 'administrador' THEN 1 ELSE 0 END) as administradores,
                SUM(CASE WHEN perfil = 'producao' THEN 1 ELSE 0 END) as producao,
                SUM(CASE WHEN perfil = 'consulta' THEN 1 ELSE 0 END) as consulta,
                SUM(CASE WHEN perfil = 'comprador' THEN 1 ELSE 0 END) as compradores
            FROM {$this->table}
        ";

        return $this->db->fetchOne($sql);
    }

    /**
     * Busca usuários com filtros
     */
    public function search($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['nome'])) {
            $where[] = "nome LIKE ?";
            $params[] = "%{$filters['nome']}%";
        }

        if (!empty($filters['email'])) {
            $where[] = "email LIKE ?";
            $params[] = "%{$filters['email']}%";
        }

        if (!empty($filters['perfil'])) {
            $where[] = "perfil = ?";
            $params[] = $filters['perfil'];
        }

        if (isset($filters['ativo'])) {
            $where[] = "ativo = ?";
            $params[] = $filters['ativo'];
        }

        $whereClause = !empty($where) ? implode(' AND ', $where) : null;

        $sql = "SELECT id, nome, email, perfil, ativo, ultimo_acesso, created_at FROM {$this->table}";
        if ($whereClause) {
            $sql .= " WHERE {$whereClause}";
        }
        $sql .= " ORDER BY nome";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Valida dados do usuário
     */
    protected function validate($data) {
        $errors = [];

        // Validar nome
        if (empty($data['nome'])) {
            $errors[] = 'Nome é obrigatório';
        } elseif (strlen($data['nome']) < 2) {
            $errors[] = 'Nome deve ter pelo menos 2 caracteres';
        }

        // Validar email
        if (empty($data['email'])) {
            $errors[] = 'Email é obrigatório';
        } elseif (!isValidEmail($data['email'])) {
            $errors[] = 'Email inválido';
        }

        // Validar perfil
        $perfisValidos = ['administrador', 'producao', 'consulta', 'comprador'];
        if (!empty($data['perfil']) && !in_array($data['perfil'], $perfisValidos)) {
            $errors[] = 'Perfil inválido';
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Hook antes de salvar
     */
    protected function beforeSave($data) {
        // Verificar se email já existe
        if (isset($data['email'])) {
            $excludeId = isset($data['id']) ? $data['id'] : null;
            if ($this->emailExists($data['email'], $excludeId)) {
                throw new Exception('Email já está em uso');
            }
        }

        return $data;
    }
}
?>