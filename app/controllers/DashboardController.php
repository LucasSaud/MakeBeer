<?php
require_once 'BaseController.php';

class DashboardController extends BaseController {

    /**
     * Dashboard principal
     */
    public function index() {
        // Estatísticas gerais
        $insumoModel = new Insumo();
        $fornecedorModel = new Fornecedor();
        $loteModel = new LoteProducao();
        $produtoModel = new ProdutoFinal();
        $entradaModel = new EntradaEstoque();

        // Obter estatísticas individuais
        $insumoStats = $insumoModel->getStats();
        $loteStats = $loteModel->getStats();
        $produtoStats = $produtoModel->getStats();
        $entradaStats = $entradaModel->getStats(date('Y-m-01'), date('Y-m-d'));

        // Organizar as estatísticas conforme esperado pela view
        $stats = [
            'total_insumos' => $insumoStats['total'] ?? 0,
            'insumos_estoque_baixo' => $insumoStats['estoque_baixo'] ?? 0,
            'lotes_em_producao' => $loteStats['em_producao'] ?? 0,
            'produtos_finalizados' => $produtoStats['ativos'] ?? 0,
            'valor_entradas_mes' => $entradaStats['valor_total'] ?? 0,
            'total_entradas_mes' => $entradaStats['total_entradas'] ?? 0,
            'litros_produzidos_mes' => $loteStats['volume_total_produzido'] ?? 0,
            'lotes_finalizados_mes' => $loteStats['finalizados'] ?? 0
        ];

        // Alertas
        $alertas = [
            'estoque_baixo' => $insumoModel->getEstoqueBaixo(),
            'produtos_baixo' => $produtoModel->getEstoqueBaixo(),
            'vencimentos' => $entradaModel->getProximasVencimento(30)
        ];

        // Lotes em produção
        $lotesEmProducao = $loteModel->getByStatus('em_producao');
        
        // Se não houver lotes em produção, buscar lotes em fermentação
        if (empty($lotesEmProducao)) {
            $lotesEmProducao = $loteModel->getByStatus('fermentando');
        }

        $data = [
            'stats' => $stats,
            'alertas' => $alertas,
            'lotes_em_producao' => $lotesEmProducao,
            'insumos_estoque_baixo' => $insumoModel->getEstoqueBaixo(),
            'validades_proximas' => $entradaModel->getProximasVencimento(30)
        ];

        $this->view('dashboard/index', $data);
    }

    /**
     * Widget de estatísticas rápidas
     */
    public function stats() {
        $insumoModel = new Insumo();
        $loteModel = new LoteProducao();
        $produtoModel = new ProdutoFinal();

        // Obter estatísticas individuais
        $insumoStats = $insumoModel->getStats();
        $loteStats = $loteModel->getStats();
        $produtoStats = $produtoModel->getStats();

        $stats = [
            'total_insumos' => $insumoStats['total'] ?? 0,
            'insumos_estoque_baixo' => $insumoStats['estoque_baixo'] ?? 0,
            'lotes_em_producao' => $loteStats['em_producao'] ?? 0,
            'produtos_finalizados' => $produtoStats['ativos'] ?? 0
        ];

        header('Content-Type: application/json');
        echo json_encode($stats);
    }
}
?>