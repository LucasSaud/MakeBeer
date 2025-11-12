<?php
require_once 'BaseController.php';

class FornecedoresController extends BaseController {

    private $model;

    public function __construct() {
        $this->model = new Fornecedor();
    }

    /**
     * Lista fornecedores
     */
    public function index() {
        $filters = [
            'nome' => $this->getGet('nome'),
            'cidade' => $this->getGet('cidade'),
            'estado' => $this->getGet('estado'),
            'ativo' => $this->getGet('ativo')
        ];

        $fornecedores = empty(array_filter($filters))
            ? $this->model->all('nome')
            : $this->model->search($filters);

        $this->view('fornecedores/index', [
            'fornecedores' => $fornecedores,
            'filters' => $filters
        ]);
    }

    /**
     * Detalhes do fornecedor
     */
    public function viewFornecedor() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Fornecedor não encontrado');
            redirect('/fornecedores');
        }

        $fornecedor = $this->model->getWithStats($id);

        if (!$fornecedor) {
            setFlashMessage('error', 'Fornecedor não encontrado');
            redirect('/fornecedores');
        }

        $insumos = $this->model->getInsumosFornecidos($id);
        $historico = $this->model->getHistoricoCompras($id, 20);

        $this->view('fornecedores/view', [
            'fornecedor' => $fornecedor,
            'insumos' => $insumos,
            'historico' => $historico
        ]);
    }

    /**
     * Formulário novo fornecedor
     */
    public function create() {
        $this->view('fornecedores/form', ['fornecedor' => null]);
    }

    /**
     * Salva novo fornecedor
     */
    public function store() {
        if (!$this->isPost()) {
            redirect('/fornecedores');
        }

        $data = [
            'nome' => $this->getPost('nome'),
            'cnpj' => $this->getPost('cnpj'),
            'email' => $this->getPost('email'),
            'telefone' => $this->getPost('telefone'),
            'endereco' => $this->getPost('endereco'),
            'cidade' => $this->getPost('cidade'),
            'estado' => $this->getPost('estado'),
            'cep' => $this->getPost('cep'),
            'contato_principal' => $this->getPost('contato_principal'),
            'observacoes' => $this->getPost('observacoes'),
            'ativo' => 1
        ];

        try {
            $id = $this->model->create($data);
            logActivity('fornecedor_criado', "Fornecedor criado: {$data['nome']}");
            setFlashMessage('success', 'Fornecedor criado com sucesso');
            redirect('/fornecedores/view?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao criar fornecedor: ' . $e->getMessage());
            redirect('/fornecedores/create');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Fornecedor não encontrado');
            redirect('/fornecedores');
        }

        $fornecedor = $this->model->find($id);

        if (!$fornecedor) {
            setFlashMessage('error', 'Fornecedor não encontrado');
            redirect('/fornecedores');
        }

        $this->view('fornecedores/form', ['fornecedor' => $fornecedor]);
    }

    /**
     * Atualiza fornecedor
     */
    public function update() {
        if (!$this->isPost()) {
            redirect('/fornecedores');
        }

        $id = $this->getPost('id');

        $data = [
            'nome' => $this->getPost('nome'),
            'cnpj' => $this->getPost('cnpj'),
            'email' => $this->getPost('email'),
            'telefone' => $this->getPost('telefone'),
            'endereco' => $this->getPost('endereco'),
            'cidade' => $this->getPost('cidade'),
            'estado' => $this->getPost('estado'),
            'cep' => $this->getPost('cep'),
            'contato_principal' => $this->getPost('contato_principal'),
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            $this->model->update($id, $data);
            logActivity('fornecedor_atualizado', "Fornecedor atualizado: {$data['nome']}");
            setFlashMessage('success', 'Fornecedor atualizado com sucesso');
            redirect('/fornecedores/view?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar fornecedor: ' . $e->getMessage());
            redirect('/fornecedores/edit?id=' . $id);
        }
    }

    /**
     * Deleta fornecedor (soft delete)
     */
    public function delete() {
        if (!$this->isPost()) {
            redirect('/fornecedores');
        }

        $id = $this->getPost('id');

        try {
            $fornecedor = $this->model->find($id);
            $this->model->update($id, ['ativo' => 0]);
            logActivity('fornecedor_deletado', "Fornecedor inativado: {$fornecedor['nome']}");
            setFlashMessage('success', 'Fornecedor inativado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao inativar fornecedor: ' . $e->getMessage());
        }

        redirect('/fornecedores');
    }
}
?>
