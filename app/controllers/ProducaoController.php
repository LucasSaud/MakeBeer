<?php
require_once 'BaseController.php';

class ProducaoController extends BaseController {

    private $model;

    public function __construct() {
        $this->model = new LoteProducao();
    }

    /**
     * Lista lotes de produção
     */
    public function index() {
        $status = $this->getGet('status');

        $lotes = $status
            ? $this->model->getByStatus($status)
            : $this->model->all('data_inicio DESC', 100);

        // Enriquecer com dados das receitas
        foreach ($lotes as &$lote) {
            if ($lote['receita_id']) {
                $receita = (new Receita())->find($lote['receita_id']);
                $lote['receita_nome'] = $receita['nome'];
            }
            if ($lote['responsavel_id']) {
                $user = (new User())->find($lote['responsavel_id']);
                $lote['responsavel_nome'] = $user['nome'];
            }
        }

        $this->view('producao/index', ['lotes' => $lotes, 'status_filter' => $status]);
    }

    /**
     * Detalhes do lote
     */
    public function viewProducao() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Lote não encontrado');
            redirect('/producao');
        }

        $lote = $this->model->getComDetalhes($id);

        if (!$lote) {
            setFlashMessage('error', 'Lote não encontrado');
            redirect('/producao');
        }

        $this->view('producao/view', ['lote' => $lote]);
    }

    /**
     * Formulário novo lote
     */
    public function create() {
        $receitaModel = new Receita();

        $this->view('producao/form', [
            'lote' => null,
            'receitas' => $receitaModel->getAtivas()
        ]);
    }

    /**
     * Salva novo lote
     */
    public function store() {
        if (!$this->isPost()) {
            redirect('/producao');
        }

        // Validar volume planejado
        $volumePlanejado = $this->getPost('volume_planejado');
        if (empty($volumePlanejado) || !is_numeric($volumePlanejado) || $volumePlanejado <= 0) {
            setFlashMessage('error', 'O volume planejado deve ser maior que zero');
            redirect('/producao/create');
            return;
        }

        $dados = [
            'receita_id' => $this->getPost('receita_id'),
            'volume_planejado' => floatval($volumePlanejado),
            'data_inicio' => $this->getPost('data_inicio') ?: date('Y-m-d'),
            'status' => $this->getPost('status') ?: 'planejado',
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            $id = $this->model->criarLote($dados);
            logActivity('lote_criado', "Lote de produção criado");
            setFlashMessage('success', 'Lote criado com sucesso');
            redirect('/producao/viewProducao?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao criar lote: ' . $e->getMessage());
            redirect('/producao/create');
        }
    }

    /**
     * Inicia produção
     */
    public function iniciar() {
        if (!$this->isPost()) {
            redirect('/producao');
        }

        $id = $this->getPost('id');

        try {
            $this->model->iniciarProducao($id);
            logActivity('producao_iniciada', "Produção iniciada - Lote ID: {$id}");
            setFlashMessage('success', 'Produção iniciada com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao iniciar produção: ' . $e->getMessage());
        }

        redirect('/producao/viewProducao?id=' . $id);
    }

    /**
     * Registrar consumo de insumo
     */
    public function registrarConsumo() {
        if (!$this->isPost()) {
            redirect('/producao');
        }

        $loteId = $this->getPost('lote_id');
        $insumoId = $this->getPost('insumo_id');
        $quantidade = $this->getPost('quantidade');
        $fase = $this->getPost('fase');

        try {
            $this->model->registrarConsumo($loteId, $insumoId, $quantidade, $fase);
            logActivity('consumo_registrado', "Consumo registrado - Lote: {$loteId}, Insumo: {$insumoId}");
            setFlashMessage('success', 'Consumo registrado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao registrar consumo: ' . $e->getMessage());
        }

        redirect('/producao/viewProducao?id=' . $loteId);
    }

    /**
     * Atualizar dados do lote
     */
    public function atualizar() {
        if (!$this->isPost()) {
            redirect('/producao');
        }

        $id = $this->getPost('id');

        $dados = [
            'densidade_inicial' => $this->getPost('densidade_inicial') ?: null,
            'densidade_final' => $this->getPost('densidade_final') ?: null,
            'ph_inicial' => $this->getPost('ph_inicial') ?: null,
            'ph_final' => $this->getPost('ph_final') ?: null,
            'temperatura_fermentacao' => $this->getPost('temperatura_fermentacao') ?: null,
            'observacoes' => $this->getPost('observacoes')
        ];

        // Atualizar status se fornecido
        if ($this->getPost('status')) {
            $dados['status'] = $this->getPost('status');
        }

        try {
            $this->model->update($id, array_filter($dados));
            logActivity('lote_atualizado', "Lote atualizado: ID {$id}");
            setFlashMessage('success', 'Lote atualizado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar lote: ' . $e->getMessage());
        }

        redirect('/producao/viewProducao?id=' . $id);
    }

    /**
     * Atualizar status do lote
     */
    public function atualizarStatus() {
        $id = $this->getGet('id') ?: $this->getPost('id');

        if (!$id) {
            setFlashMessage('error', 'Lote não encontrado');
            redirect('/producao');
        }

        // Se for GET, mostrar formulário
        if (!$this->isPost()) {
            $lote = $this->model->find($id);
            if (!$lote) {
                setFlashMessage('error', 'Lote não encontrado');
                redirect('/producao');
            }
            $this->view('producao/atualizar-status', ['lote' => $lote]);
            return;
        }

        // Se for POST, atualizar status
        $novoStatus = $this->getPost('novo_status');

        try {
            $this->model->update($id, ['status' => $novoStatus]);
            logActivity('status_atualizado', "Status do lote {$id} atualizado para: {$novoStatus}");
            setFlashMessage('success', 'Status atualizado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar status: ' . $e->getMessage());
        }

        redirect('/producao/viewProducao?id=' . $id);
    }

    /**
     * Formulário editar lote
     */
    public function editar() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Lote não encontrado');
            redirect('/producao');
        }

        $lote = $this->model->find($id);
        if (!$lote) {
            setFlashMessage('error', 'Lote não encontrado');
            redirect('/producao');
        }

        $receitaModel = new Receita();
        $this->view('producao/form', [
            'lote' => $lote,
            'receitas' => $receitaModel->getAtivas()
        ]);
    }

    /**
     * Finalizar lote
     */
    public function finalizar() {
        if (!$this->isPost()) {
            redirect('/producao');
        }

        $id = $this->getPost('id');

        $dados = [
            'volume_real' => $this->getPost('volume_real'),
            'densidade_final' => $this->getPost('densidade_final') ?: null,
            'ph_final' => $this->getPost('ph_final') ?: null
        ];

        try {
            $this->model->finalizar($id, $dados);
            logActivity('lote_finalizado', "Lote finalizado: ID {$id}");
            setFlashMessage('success', 'Lote finalizado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao finalizar lote: ' . $e->getMessage());
        }

        redirect('/producao/viewProducao?id=' . $id);
    }

    /**
     * Excluir lote de produção
     */
    public function delete() {
        if (!$this->isPost()) {
            redirect('/producao');
        }

        $id = $this->getPost('id');

        try {
            // Verificar se o lote pode ser excluído (apenas lotes planejados)
            $lote = $this->model->find($id);
            if (!$lote) {
                setFlashMessage('error', 'Lote não encontrado');
                redirect('/producao');
                return;
            }

            if ($lote['status'] !== 'planejado') {
                setFlashMessage('error', 'Apenas lotes no status "planejado" podem ser excluídos');
                redirect('/producao/viewProducao?id=' . $id);
                return;
            }

            // Excluir o lote
            $this->model->delete($id);
            logActivity('lote_excluido', "Lote excluído: ID {$id}");
            setFlashMessage('success', 'Lote excluído com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao excluir lote: ' . $e->getMessage());
        }

        redirect('/producao');
    }

    /**
     * Cancelar lote de produção
     */
    public function cancelar() {
        if (!$this->isPost()) {
            redirect('/producao');
        }

        $id = $this->getPost('id');

        try {
            // Verificar se o lote existe
            $lote = $this->model->find($id);
            if (!$lote) {
                setFlashMessage('error', 'Lote não encontrado');
                redirect('/producao');
                return;
            }

            // Verificar se o lote pode ser cancelado (apenas lotes não finalizados)
            if ($lote['status'] === 'finalizado') {
                setFlashMessage('error', 'Lotes finalizados não podem ser cancelados');
                redirect('/producao/viewProducao?id=' . $id);
                return;
            }

            // Cancelar o lote
            $this->model->update($id, ['status' => 'cancelado']);
            logActivity('lote_cancelado', "Lote cancelado: ID {$id}");
            setFlashMessage('success', 'Lote cancelado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao cancelar lote: ' . $e->getMessage());
        }

        redirect('/producao/viewProducao?id=' . $id);
    }
}
?>