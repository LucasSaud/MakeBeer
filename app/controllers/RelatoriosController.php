<?php
require_once 'BaseController.php';

class RelatoriosController extends BaseController {

    /**
     * Página principal de relatórios
     */
    public function index() {
        $this->view('relatorios/index');
    }

    /**
     * Relatório de estoque
     */
    public function estoque() {
        $insumoModel = new Insumo();
        $produtoModel = new ProdutoFinal();

        $data = [
            'insumos' => $insumoModel->getAllWithDetails(),
            'produtos' => $produtoModel->where('ativo', 1),
            'insumos_baixo' => $insumoModel->getEstoqueBaixo(),
            'produtos_baixo' => $produtoModel->getEstoqueBaixo(),
            'stats_insumos' => $insumoModel->getStats(),
            'stats_produtos' => $produtoModel->getStats()
        ];

        $this->view('relatorios/estoque', $data);
    }

    /**
     * Relatório de produção
     */
    public function producao() {
        $dataInicio = $this->getGet('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->getGet('data_fim') ?: date('Y-m-d');

        $loteModel = new LoteProducao();

        $stats = $loteModel->getStats($dataInicio, $dataFim);

        $sql = "SELECT l.*, r.nome as receita_nome, u.nome as responsavel_nome
                FROM lotes_producao l
                LEFT JOIN receitas r ON l.receita_id = r.id
                LEFT JOIN usuarios u ON l.responsavel_id = u.id
                WHERE l.data_inicio BETWEEN ? AND ?
                ORDER BY l.data_inicio DESC";

        $db = Database::getInstance();
        $lotes = $db->fetchAll($sql, [$dataInicio, $dataFim]);

        $this->view('relatorios/producao', [
            'lotes' => $lotes,
            'stats' => $stats,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
    }

    /**
     * Relatório de compras/entradas
     */
    public function compras() {
        $dataInicio = $this->getGet('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->getGet('data_fim') ?: date('Y-m-d');

        $entradaModel = new EntradaEstoque();

        $entradas = $entradaModel->getByPeriodo($dataInicio, $dataFim);
        $stats = $entradaModel->getStats($dataInicio, $dataFim);

        // Agrupar por fornecedor
        $db = Database::getInstance();
        $sql = "SELECT f.nome as fornecedor_nome,
                COUNT(e.id) as total_compras,
                SUM(e.preco_total) as valor_total
                FROM entradas_estoque e
                LEFT JOIN fornecedores f ON e.fornecedor_id = f.id
                WHERE e.data_entrada BETWEEN ? AND ?
                GROUP BY f.id, f.nome
                ORDER BY valor_total DESC";

        $porFornecedor = $db->fetchAll($sql, [$dataInicio, $dataFim]);

        $this->view('relatorios/compras', [
            'entradas' => $entradas,
            'stats' => $stats,
            'por_fornecedor' => $porFornecedor,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
    }

    /**
     * Relatório de custo de produção
     */
    public function custos() {
        $receitaModel = new Receita();

        $receitas = $receitaModel->getAtivas();

        // Calcular custos de cada receita
        foreach ($receitas as &$receita) {
            $receita['custo_total'] = $receitaModel->calcularCusto($receita['id']);
            $receita['custo_por_litro'] = $receita['volume_batch'] > 0
                ? $receita['custo_total'] / $receita['volume_batch']
                : 0;
        }

        $this->view('relatorios/custos', ['receitas' => $receitas]);
    }

    /**
     * Relatório de validade
     */
    public function validade() {
        $dias = $this->getGet('dias') ?: 30;

        $entradaModel = new EntradaEstoque();
        $proximosVencimento = $entradaModel->getProximasVencimento($dias);

        // Vencidos
        $db = Database::getInstance();
        $sql = "SELECT e.*, i.nome as insumo_nome
                FROM entradas_estoque e
                LEFT JOIN insumos i ON e.insumo_id = i.id
                WHERE e.data_validade < CURDATE()
                ORDER BY e.data_validade DESC
                LIMIT 100";

        $vencidos = $db->fetchAll($sql);

        $this->view('relatorios/validade', [
            'proximos_vencimento' => $proximosVencimento,
            'vencidos' => $vencidos,
            'dias' => $dias
        ]);
    }

    /**
     * Exportar relatório (CSV)
     */
    public function exportar() {
        $tipo = $this->getGet('tipo');

        // Implementar exportação conforme tipo
        // Por enquanto, apenas placeholder

        setFlashMessage('info', 'Funcionalidade de exportação em desenvolvimento');
        redirect('/relatorios');
    }
}
?>
