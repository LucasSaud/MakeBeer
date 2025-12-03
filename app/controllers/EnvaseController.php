<?php
require_once 'BaseController.php';

class EnvaseController extends BaseController {

    private $model;
    private $barrilModel;

    public function __construct() {
        $this->model = new Envase();
        $this->barrilModel = new Barril();
    }

    /**
     * Lista envases
     */
    public function index() {
        $status = $this->getGet('status');
        $barrilModel = $this->barrilModel;

        $envases = $status
            ? $this->model->getByStatus($status)
            : $this->model->all('data_envase DESC', 100);

        // Enriquecer com dados
        foreach ($envases as &$envase) {
            $loteProducaoId = $envase['lote_producao_id'] ?? null;
            if ($loteProducaoId) {
                $lote = (new LoteProducao())->find($loteProducaoId);
                if ($lote) {
                    $envase['lote_codigo'] = $lote['codigo'];
                    if ($lote['receita_id']) {
                        $receita = (new Receita())->find($lote['receita_id']);
                        $envase['estilo'] = $receita['estilo'];
                        $envase['receita_nome'] = $receita['nome'];
                    }
                }
            }
            
            // Calcular totais reais
            $barris = $this->barrilModel->getByEnvase($envase['id']);
            $envase['total_barris'] = count($barris);
            $envase['total_litros'] = array_sum(array_column($barris, 'quantidade_litros'));
        }

        $this->view('envase/index', [
            'envases' => $envases,
            'status_filter' => $status,
            'barrilModel' => $barrilModel
        ]);
    }

    /**
     * Detalhes do envase
     */
    public function viewEnvase() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Envase não encontrado');
            redirect('/envase');
        }

        $envase = $this->model->getComDetalhes($id);

        if (!$envase) {
            setFlashMessage('error', 'Envase não encontrado');
            redirect('/envase');
        }

        $this->view('envase/view', ['envase' => $envase]);
    }

    /**
     * Formulário novo envase
     */
    public function create() {
        $lotes = $this->model->getLotesDisponiveis();

        $this->view('envase/form', [
            'envase' => null,
            'lotes' => $lotes
        ]);
    }

    /**
     * Salva novo envase
     */
    public function store() {
        if (!$this->isPost()) {
            redirect('/envase');
        }

        $dados = [
            'lote_producao_id' => $this->getPost('lote_id'),
            'data_envase' => $this->getPost('data_envase') ?: date('Y-m-d'),
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            $id = $this->model->criarEnvase($dados);
            logActivity('envase_criado', "Envase criado para lote ID: {$dados['lote_producao_id']}");
            setFlashMessage('success', 'Envase criado com sucesso');
            redirect('/envase/viewEnvase?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao criar envase: ' . $e->getMessage());
            redirect('/envase/create');
        }
    }

    /**
     * Adicionar barril ao envase
     */
    public function adicionarBarril() {
        if (!$this->isPost()) {
            redirect('/envase');
        }

        $envaseId = $this->getPost('envase_id');
        $numeroBarril = $this->getPost('numero_barril');
        $quantidadeLitros = $this->getPost('quantidade_litros');

        // Validar volume mínimo de 50 litros
        if ($quantidadeLitros < 50) {
            setFlashMessage('error', 'O volume mínimo por barril é de 50 litros');
            redirect('/envase/viewEnvase?id=' . $envaseId);
            return;
        }

        $dados = [
            'envase_id' => $envaseId,
            'numero_barril' => $numeroBarril,
            'quantidade_litros' => $quantidadeLitros,
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            $barrilId = $this->barrilModel->criarBarril($dados);
            
            // Atualizar totais no envase
            $this->atualizarTotaisEnvase($envaseId);
            
            logActivity('barril_criado', "Barril {$numeroBarril} adicionado ao envase {$envaseId}");
            setFlashMessage('success', "Barril {$numeroBarril} adicionado com sucesso");
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao adicionar barril: ' . $e->getMessage());
        }

        redirect('/envase/viewEnvase?id=' . $envaseId);
    }

    /**
     * Editar barril
     */
    public function editarBarril() {
        if (!$this->isPost()) {
            redirect('/envase');
        }

        $estoqueBarrilId = $this->getPost('barril_id');
        $envaseId = $this->getPost('envase_id');

        $dados = [
            'quantidade_litros' => $this->getPost('quantidade_litros'),
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            $this->barrilModel->update($estoqueBarrilId, $dados);
            
            // Atualizar totais no envase
            $this->atualizarTotaisEnvase($envaseId);
            
            logActivity('barril_atualizado', "Barril ID {$estoqueBarrilId} atualizado");
            setFlashMessage('success', 'Barril atualizado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar barril: ' . $e->getMessage());
        }

        redirect('/envase/viewEnvase?id=' . $envaseId);
    }

    /**
     * Remover barril
     */
    public function removerBarril() {
        if (!$this->isPost()) {
            redirect('/envase');
        }

        $estoqueBarrilId = $this->getPost('barril_id');
        $envaseId = $this->getPost('envase_id');

        try {
            $this->barrilModel->delete($estoqueBarrilId);
            
            // Atualizar totais no envase
            $this->atualizarTotaisEnvase($envaseId);
            
            logActivity('barril_removido', "Barril ID {$estoqueBarrilId} removido");
            setFlashMessage('success', 'Barril removido com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao remover barril: ' . $e->getMessage());
        }

        redirect('/envase/viewEnvase?id=' . $envaseId);
    }

    /**
     * Finalizar envase
     */
    public function finalizar() {
        if (!$this->isPost()) {
            redirect('/envase');
        }

        $id = $this->getPost('id');

        try {
            $this->model->finalizar($id);
            logActivity('envase_finalizado', "Envase ID {$id} finalizado");
            setFlashMessage('success', 'Envase finalizado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao finalizar envase: ' . $e->getMessage());
        }

        redirect('/envase/viewEnvase?id=' . $id);
    }

    /**
     * Cancelar envase
     */
    public function cancelar() {
        if (!$this->isPost()) {
            redirect('/envase');
        }

        $id = $this->getPost('id');

        try {
            $this->model->update($id, ['status' => 'cancelado']);
            logActivity('envase_cancelado', "Envase ID {$id} cancelado");
            setFlashMessage('success', 'Envase cancelado');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao cancelar envase: ' . $e->getMessage());
        }

        redirect('/envase/viewEnvase?id=' . $id);
    }

    /**
     * Excluir envase
     */
    public function delete() {
        if (!$this->isPost()) {
            redirect('/envase');
        }

        $id = $this->getPost('id');

        try {
            // Verificar se o envase pode ser excluído (apenas envases em processo)
            $envase = $this->model->find($id);
            if (!$envase) {
                setFlashMessage('error', 'Envase não encontrado');
                redirect('/envase');
                return;
            }

            if ($envase['status'] !== 'envasado') {
                setFlashMessage('error', 'Apenas envases no status "envasado" podem ser excluídos');
                redirect('/envase/viewEnvase?id=' . $id);
                return;
            }

            // Excluir o envase
            $this->model->delete($id);
            logActivity('envase_excluido', "Envase excluído: ID {$id}");
            setFlashMessage('success', 'Envase excluído com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao excluir envase: ' . $e->getMessage());
        }

        redirect('/envase');
    }

    /**
     * Atualizar totais do envase
     */
    private function atualizarTotaisEnvase($envaseId) {
        $barris = $this->barrilModel->getByEnvase($envaseId);
        $totalBarris = count($barris);
        $totalLitros = array_sum(array_column($barris, 'quantidade_litros'));

        // Não atualizar totais na tabela pois as colunas não existem
        // $this->model->update($envaseId, [
        //     'total_barris' => $totalBarris,
        //     'total_litros' => $totalLitros
        // ]);
    }
}
?>