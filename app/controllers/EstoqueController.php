<?php
require_once 'BaseController.php';

class EstoqueController extends BaseController {

    private $model;

    public function __construct() {
        $this->model = new EstoqueBarril();
    }

    /**
     * Lista estoque de barris
     */
    public function index() {
        $filtros = [
            'lote_codigo' => $this->getGet('lote'),
            'estilo' => $this->getGet('estilo'),
            'status' => $this->getGet('status'),
            'localizacao' => $this->getGet('localizacao')
        ];

        // Remover filtros vazios
        $filtros = array_filter($filtros);

        $estoqueBarris = !empty($filtros)
            ? $this->model->buscar($filtros)
            : $this->model->getDisponiveis();

        // Buscar estilos disponíveis para o filtro
        $estilos = $this->model->getLitrosPorEstilo();

        $this->view('estoque/index', [
            'estoque' => $estoqueBarris,
            'filtros' => $filtros,
            'estilos' => $estilos
        ]);
    }

    /**
     * Detalhes do barril em estoque
     */
    public function viewEstoque() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Registro não encontrado');
            redirect('/estoque');
        }

        $estoque = $this->model->getComDetalhes($id);

        if (!$estoque) {
            setFlashMessage('error', 'Registro não encontrado');
            redirect('/estoque');
        }

        $this->view('estoque/view', ['estoque' => $estoque]);
    }

    /**
     * Atualizar localização
     */
    public function atualizarLocalizacao() {
        if (!$this->isPost()) {
            redirect('/estoque');
        }

        $id = $this->getPost('id');
        $localizacao = $this->getPost('localizacao');
        $temperatura = $this->getPost('temperatura_armazenamento');

        try {
            $this->model->atualizarLocalizacao($id, $localizacao, $temperatura);
            logActivity('localizacao_atualizada', "Localização atualizada - Estoque ID: {$id}");
            setFlashMessage('success', 'Localização atualizada com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar localização: ' . $e->getMessage());
        }

        redirect('/estoque/viewEstoque?id=' . $id);
    }

    /**
     * Relatório de estoque
     */
    public function relatorio() {
        $stats = $this->model->getStats();
        $relatorioPorEstilo = $this->model->getRelatorioPorEstilo();
        $relatorioPorLote = $this->model->getRelatorioPorLote();
        $barrisAntigos = $this->model->getBarrisAntigos(30);

        $this->view('estoque/relatorio', [
            'stats' => $stats,
            'relatorio_por_estilo' => $relatorioPorEstilo,
            'relatorio_por_lote' => $relatorioPorLote,
            'barris_antigos' => $barrisAntigos
        ]);
    }

    /**
     * Painel de estoque (dashboard)
     */
    public function painel() {
        $stats = $this->model->getStats();
        $litrosPorEstilo = $this->model->getLitrosPorEstilo();
        $barrisDisponiveis = $this->model->getDisponiveis();
        $barrisAntigos = $this->model->getBarrisAntigos(45);

        $this->view('estoque/painel', [
            'stats' => $stats,
            'litros_por_estilo' => $litrosPorEstilo,
            'barris_disponiveis' => $barrisDisponiveis,
            'barris_antigos' => $barrisAntigos
        ]);
    }

    /**
     * Buscar por lote
     */
    public function porLote() {
        $loteCodigo = $this->getGet('lote');

        if (!$loteCodigo) {
            redirect('/estoque');
        }

        $estoqueBarris = $this->model->getByLote($loteCodigo);

        $this->view('estoque/por-lote', [
            'estoque' => $estoqueBarris,
            'lote_codigo' => $loteCodigo
        ]);
    }

    /**
     * Buscar por estilo
     */
    public function porEstilo() {
        $estilo = $this->getGet('estilo');

        if (!$estilo) {
            redirect('/estoque');
        }

        $estoqueBarris = $this->model->getByEstilo($estilo);

        $this->view('estoque/por-estilo', [
            'estoque' => $estoqueBarris,
            'estilo' => $estilo
        ]);
    }

    /**
     * Atualizar observações
     */
    public function atualizarObservacoes() {
        if (!$this->isPost()) {
            redirect('/estoque');
        }

        $id = $this->getPost('id');
        $observacoes = $this->getPost('observacoes');

        try {
            $this->model->update($id, ['observacoes' => $observacoes]);
            logActivity('observacoes_atualizadas', "Observações atualizadas - Estoque ID: {$id}");
            setFlashMessage('success', 'Observações atualizadas com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar observações: ' . $e->getMessage());
        }

        redirect('/estoque/viewEstoque?id=' . $id);
    }

    /**
     * Excluir barril do estoque
     */
    public function delete() {
        if (!$this->isPost()) {
            redirect('/estoque');
        }

        $id = $this->getPost('id');

        try {
            // Verificar se o barril pode ser excluído (apenas barris disponíveis)
            $estoque = $this->model->find($id);
            if (!$estoque) {
                setFlashMessage('error', 'Registro não encontrado');
                redirect('/estoque');
                return;
            }

            if ($estoque['status'] !== 'disponivel') {
                setFlashMessage('error', 'Apenas barris disponíveis podem ser excluídos');
                redirect('/estoque/viewEstoque?id=' . $id);
                return;
            }

            // Excluir o barril
            $this->model->delete($id);
            logActivity('barril_excluido', "Barril excluído do estoque: ID {$id}");
            setFlashMessage('success', 'Barril excluído com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao excluir barril: ' . $e->getMessage());
        }

        redirect('/estoque');
    }
}
?>