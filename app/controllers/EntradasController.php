<?php
require_once 'BaseController.php';

class EntradasController extends BaseController {

    private $model;

    public function __construct() {
        $this->model = new EntradaEstoque();
    }

    /**
     * Lista entradas de estoque
     */
    public function index() {
        $entradas = $this->model->getAllWithDetails(200);

        $this->view('entradas/index', ['entradas' => $entradas]);
    }

    /**
     * Formulário nova entrada
     */
    public function create() {
        $insumoModel = new Insumo();
        $fornecedorModel = new Fornecedor();

        $this->view('entradas/form', [
            'entrada' => null,
            'insumos' => $insumoModel->getAllWithDetails(),
            'fornecedores' => $fornecedorModel->getAtivos()
        ]);
    }

    /**
     * Salva nova entrada
     */
    public function store() {
        if (!$this->isPost()) {
            redirect('/entradas');
        }

        $data = [
            'insumo_id' => $this->getPost('insumo_id'),
            'fornecedor_id' => $this->getPost('fornecedor_id') ?: null,
            'quantidade' => $this->getPost('quantidade'),
            'preco_unitario' => $this->getPost('preco_unitario') ?: null,
            'lote_fornecedor' => $this->getPost('lote_fornecedor'),
            'data_entrada' => $this->getPost('data_entrada') ?: date('Y-m-d'),
            'data_validade' => $this->getPost('data_validade') ?: null,
            'numero_nota_fiscal' => $this->getPost('numero_nota_fiscal'),
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            $id = $this->model->registrarEntrada($data);

            $insumo = (new Insumo())->find($data['insumo_id']);
            logActivity('entrada_registrada', "Entrada de estoque: {$insumo['nome']} - {$data['quantidade']}{$insumo['unidade_medida']}");

            setFlashMessage('success', 'Entrada registrada com sucesso');
            redirect('/entradas');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao registrar entrada: ' . $e->getMessage());
            redirect('/entradas/create');
        }
    }

    /**
     * Visualiza detalhes da entrada
     */
    public function viewEntradas() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Entrada não encontrada');
            redirect('/entradas');
        }

        $entrada = $this->model->find($id);

        if (!$entrada) {
            setFlashMessage('error', 'Entrada não encontrada');
            redirect('/entradas');
        }

        // Carregar dados relacionados
        $insumo = (new Insumo())->find($entrada['insumo_id']);
        $fornecedor = $entrada['fornecedor_id'] ? (new Fornecedor())->find($entrada['fornecedor_id']) : null;

        $this->view('entradas/view', [
            'entrada' => $entrada,
            'insumo' => $insumo,
            'fornecedor' => $fornecedor
        ]);
    }

    /**
     * Formulário de edição de entrada
     */
    public function edit() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Entrada não encontrada');
            redirect('/entradas');
        }

        $entrada = $this->model->find($id);

        if (!$entrada) {
            setFlashMessage('error', 'Entrada não encontrada');
            redirect('/entradas');
        }

        $insumoModel = new Insumo();
        $fornecedorModel = new Fornecedor();

        $this->view('entradas/form', [
            'entrada' => $entrada,
            'insumos' => $insumoModel->getAllWithDetails(),
            'fornecedores' => $fornecedorModel->getAtivos()
        ]);
    }

    /**
     * Atualiza entrada
     */
    public function update() {
        if (!$this->isPost()) {
            redirect('/entradas');
        }

        $id = $this->getPost('id');

        $data = [
            'insumo_id' => $this->getPost('insumo_id'),
            'fornecedor_id' => $this->getPost('fornecedor_id') ?: null,
            'quantidade' => $this->getPost('quantidade'),
            'preco_unitario' => $this->getPost('preco_unitario') ?: null,
            'preco_total' => $this->getPost('quantidade') * $this->getPost('preco_unitario'),
            'lote_fornecedor' => $this->getPost('lote_fornecedor'),
            'data_entrada' => $this->getPost('data_entrada'),
            'data_validade' => $this->getPost('data_validade') ?: null,
            'numero_nota_fiscal' => $this->getPost('numero_nota_fiscal'),
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            // Buscar entrada antiga para ajustar estoque
            $entradaAntiga = $this->model->find($id);

            // Atualizar entrada
            $this->model->update($id, $data);

            // Ajustar estoque se quantidade mudou
            if ($entradaAntiga['quantidade'] != $data['quantidade']) {
                $insumoModel = new Insumo();
                $diferenca = $data['quantidade'] - $entradaAntiga['quantidade'];

                if ($diferenca > 0) {
                    $insumoModel->atualizarEstoque($data['insumo_id'], $diferenca, 'adicionar');
                } else {
                    $insumoModel->atualizarEstoque($data['insumo_id'], abs($diferenca), 'subtrair');
                }
            }

            logActivity('entrada_atualizada', "Entrada de estoque atualizada ID: {$id}");
            setFlashMessage('success', 'Entrada atualizada com sucesso');
            redirect('/entradas');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar entrada: ' . $e->getMessage());
            redirect('/entradas/edit?id=' . $id);
        }
    }

    /**
     * Deleta entrada
     */
    public function delete() {
        if (!$this->isPost()) {
            redirect('/entradas');
        }

        $id = $this->getPost('id');

        try {
            $entrada = $this->model->find($id);

            if (!$entrada) {
                throw new Exception('Entrada não encontrada');
            }

            // Reverter estoque
            $insumoModel = new Insumo();
            $insumoModel->atualizarEstoque($entrada['insumo_id'], $entrada['quantidade'], 'subtrair');

            // Deletar entrada
            $this->model->delete($id);

            logActivity('entrada_deletada', "Entrada de estoque deletada ID: {$id}");
            setFlashMessage('success', 'Entrada excluída com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao excluir entrada: ' . $e->getMessage());
        }

        redirect('/entradas');
    }

    /**
     * Relatório de entradas por período
     */
    public function relatorio() {
        $dataInicio = $this->getGet('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->getGet('data_fim') ?: date('Y-m-d');

        $entradas = $this->model->getByPeriodo($dataInicio, $dataFim);
        $stats = $this->model->getStats($dataInicio, $dataFim);

        $this->view('entradas/relatorio', [
            'entradas' => $entradas,
            'stats' => $stats,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
    }
}
?>
