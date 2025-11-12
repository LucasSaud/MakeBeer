<?php
require_once 'BaseModel.php';

class EstoqueLocalizacao extends BaseModel {
    protected $table = 'estoque_localizacao';
    protected $fillable = [
        'produto_id', 'lote_id', 'setor_id', 'quantidade',
        'status', 'data_entrada', 'observacoes'
    ];

    /**
     * Busca localização com detalhes completos
     */
    public function getComDetalhes($id) {
        $sql = "SELECT
                el.*,
                p.nome as produto_nome,
                p.tipo_embalagem,
                l.codigo as numero_lote,
                pp.data_validade,
                s.nome as setor_nome
                FROM {$this->table} el
                INNER JOIN produtos_finais p ON el.produto_id = p.id
                LEFT JOIN lotes_producao l ON el.lote_id = l.id
                LEFT JOIN producao_produtos pp ON l.id = pp.lote_producao_id AND pp.produto_final_id = el.produto_id
                INNER JOIN camarafria_setores s ON el.setor_id = s.id
                WHERE el.id = ?";

        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Busca todas as localizações com detalhes
     */
    public function getAllComDetalhes($filtros = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filtros['setor_id'])) {
            $where[] = "el.setor_id = ?";
            $params[] = $filtros['setor_id'];
        }

        if (!empty($filtros['produto_id'])) {
            $where[] = "el.produto_id = ?";
            $params[] = $filtros['produto_id'];
        }

        if (!empty($filtros['status'])) {
            $where[] = "el.status = ?";
            $params[] = $filtros['status'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT
                el.*,
                p.nome as produto_nome,
                p.tipo_embalagem,
                l.codigo as numero_lote,
                pp.data_validade,
                s.nome as setor_nome
                FROM {$this->table} el
                INNER JOIN produtos_finais p ON el.produto_id = p.id
                LEFT JOIN lotes_producao l ON el.lote_id = l.id
                LEFT JOIN producao_produtos pp ON l.id = pp.lote_producao_id AND pp.produto_final_id = el.produto_id
                INNER JOIN camarafria_setores s ON el.setor_id = s.id
                WHERE {$whereClause}
                ORDER BY s.nome, p.nome";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Busca localizações de um produto específico
     */
    public function getLocalizacoesProduto($produtoId) {
        $sql = "SELECT
                el.*,
                s.nome as setor_nome,
                l.codigo as numero_lote,
                pp.data_validade
                FROM {$this->table} el
                INNER JOIN camarafria_setores s ON el.setor_id = s.id
                LEFT JOIN lotes_producao l ON el.lote_id = l.id
                LEFT JOIN producao_produtos pp ON l.id = pp.lote_producao_id AND pp.produto_final_id = el.produto_id
                WHERE el.produto_id = ?
                ORDER BY s.nome";

        return $this->db->fetchAll($sql, [$produtoId]);
    }

    /**
     * Registra novo produto na câmara fria
     */
    public function registrarEntrada($data) {
        // Validar setor tem capacidade
        $setorModel = new CamaraFriaSetor();
        if (!$setorModel->temCapacidade($data['setor_id'], $data['quantidade'])) {
            throw new Exception('Setor não tem capacidade disponível para esta quantidade');
        }

        // Verificar se já existe localização para este produto/lote/setor
        $existente = $this->verificarLocalizacaoExistente(
            $data['produto_id'],
            $data['lote_id'] ?? null,
            $data['setor_id']
        );

        if ($existente) {
            // Atualizar quantidade existente
            $novaQuantidade = $existente['quantidade'] + $data['quantidade'];
            return $this->update($existente['id'], ['quantidade' => $novaQuantidade]);
        }

        // Criar nova localização
        $data['data_entrada'] = $data['data_entrada'] ?? date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'disponivel';

        return $this->create($data);
    }

    /**
     * Verifica se já existe localização para produto/lote/setor
     */
    private function verificarLocalizacaoExistente($produtoId, $loteId, $setorId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE produto_id = ? AND setor_id = ?";

        $params = [$produtoId, $setorId];

        if ($loteId) {
            $sql .= " AND lote_id = ?";
            $params[] = $loteId;
        } else {
            $sql .= " AND lote_id IS NULL";
        }

        $sql .= " LIMIT 1";

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Transferir produto entre setores
     */
    public function transferir($localizacaoId, $setorDestinoId, $quantidade, $motivo = '') {
        $localizacao = $this->find($localizacaoId);

        if (!$localizacao) {
            throw new Exception('Localização não encontrada');
        }

        if ($quantidade > $localizacao['quantidade']) {
            throw new Exception('Quantidade maior que o disponível');
        }

        // Verificar capacidade do setor destino
        $setorModel = new CamaraFriaSetor();
        if (!$setorModel->temCapacidade($setorDestinoId, $quantidade)) {
            throw new Exception('Setor destino não tem capacidade disponível');
        }

        // Registrar movimentação
        $movimentacaoModel = new CamaraFriaMovimentacao();
        $movimentacaoId = $movimentacaoModel->registrarTransferencia(
            $localizacao['produto_id'],
            $localizacao['lote_id'],
            $localizacao['setor_id'],
            $setorDestinoId,
            $quantidade,
            $motivo
        );

        // Atualizar localização origem
        $novaQuantidadeOrigem = $localizacao['quantidade'] - $quantidade;

        if ($novaQuantidadeOrigem <= 0) {
            // Remover localização se quantidade for zero
            $this->delete($localizacaoId);
        } else {
            // Atualizar quantidade
            $this->update($localizacaoId, ['quantidade' => $novaQuantidadeOrigem]);
        }

        // Criar/atualizar localização destino
        $this->registrarEntrada([
            'produto_id' => $localizacao['produto_id'],
            'lote_id' => $localizacao['lote_id'],
            'setor_id' => $setorDestinoId,
            'quantidade' => $quantidade,
            'status' => $localizacao['status']
        ]);

        return $movimentacaoId;
    }

    /**
     * Alterar status de uma localização
     */
    public function alterarStatus($localizacaoId, $novoStatus, $observacoes = '') {
        $statusPermitidos = ['disponivel', 'quarentena', 'vencido', 'reservado'];

        if (!in_array($novoStatus, $statusPermitidos)) {
            throw new Exception('Status inválido');
        }

        $data = ['status' => $novoStatus];

        if ($observacoes) {
            $data['observacoes'] = $observacoes;
        }

        return $this->update($localizacaoId, $data);
    }

    /**
     * Produtos próximos ao vencimento
     */
    public function getProdutosProximosVencimento($dias = 30) {
        $sql = "SELECT
                el.*,
                p.nome as produto_nome,
                p.tipo_embalagem,
                l.codigo as numero_lote,
                pp.data_validade,
                s.nome as setor_nome,
                DATEDIFF(pp.data_validade, CURDATE()) as dias_restantes
                FROM {$this->table} el
                INNER JOIN produtos_finais p ON el.produto_id = p.id
                INNER JOIN lotes_producao l ON el.lote_id = l.id
                INNER JOIN producao_produtos pp ON l.id = pp.lote_producao_id AND pp.produto_final_id = el.produto_id
                INNER JOIN camarafria_setores s ON el.setor_id = s.id
                WHERE pp.data_validade IS NOT NULL
                AND pp.data_validade > CURDATE()
                AND DATEDIFF(pp.data_validade, CURDATE()) <= ?
                AND el.status = 'disponivel'
                ORDER BY pp.data_validade ASC";

        return $this->db->fetchAll($sql, [$dias]);
    }
}
?>
