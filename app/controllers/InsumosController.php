<?php
require_once 'BaseController.php';

class InsumosController extends BaseController {

    private $model;

    public function __construct() {
        $this->model = new Insumo();
    }

    /**
     * Lista todos os insumos
     */
    public function index() {
        $filters = [
            'search' => $this->getGet('search'),
            'categoria_id' => $this->getGet('categoria_id'),
            'status_estoque' => $this->getGet('status_estoque')
        ];

        // Limpar filtros vazios
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        $insumos = empty($filters)
            ? $this->model->getAllWithDetails()
            : $this->model->search($filters);

        $categoriaModel = new CategoriaInsumo();
        $categorias = $categoriaModel->all();

        $this->view('insumos/index', [
            'insumos' => $insumos,
            'categorias' => $categorias,
            'filters' => $filters
        ]);
    }

    /**
     * Exibe detalhes do insumo
     */
    public function viewInsumo() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Insumo não encontrado');
            redirect('/insumos');
        }

        $insumo = $this->model->find($id);

        if (!$insumo) {
            setFlashMessage('error', 'Insumo não encontrado');
            redirect('/insumos');
        }

        // Buscar dados completos com joins
        $insumoCompleto = $this->model->getWithDetails($id);
        
        if (!$insumoCompleto) {
            setFlashMessage('error', 'Insumo não encontrado');
            redirect('/insumos');
        }

        // Buscar histórico de movimentações
        $historico = $this->model->getHistoricoMovimentacoes($id);

        $this->view('insumos/view', [
            'insumo' => $insumoCompleto,
            'historico' => $historico
        ]);
    }

    /**
     * Formulário de novo insumo
     */
    public function create() {
        $categoriaModel = new CategoriaInsumo();
        $fornecedorModel = new Fornecedor();

        $this->view('insumos/form', [
            'insumo' => null,
            'categorias' => $categoriaModel->all(),
            'fornecedores' => $fornecedorModel->getAtivos()
        ]);
    }

    /**
     * Salva novo insumo
     */
    public function store() {
        if (!$this->isPost()) {
            redirect('/insumos');
        }

        $data = [
            'codigo_interno' => $this->getPost('codigo_interno'),
            'nome' => $this->getPost('nome'),
            'descricao' => $this->getPost('descricao'),
            'categoria_id' => $this->getPost('categoria_id') ?: null,
            'tipo' => $this->getPost('tipo'),
            'unidade_medida' => $this->getPost('unidade_medida'),
            'estoque_minimo' => floatval($this->getPost('estoque_minimo')) ?: 0,
            'estoque_atual' => floatval($this->getPost('estoque_atual')) ?: 0,
            'fornecedor_principal_id' => $this->getPost('fornecedor_principal_id') ?: null,
            'ean' => $this->getPost('ean'),
            'observacoes' => $this->getPost('observacoes'),
            'ativo' => 1
        ];

        try {
            $id = $this->model->create($data);
            logActivity('insumo_criado', "Insumo criado: {$data['nome']}");
            setFlashMessage('success', 'Insumo criado com sucesso');
            redirect('/insumos/viewInsumo?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao criar insumo: ' . $e->getMessage());
            redirect('/insumos/create');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Insumo não encontrado');
            redirect('/insumos');
        }

        $insumo = $this->model->find($id);

        if (!$insumo) {
            setFlashMessage('error', 'Insumo não encontrado');
            redirect('/insumos');
        }

        $categoriaModel = new CategoriaInsumo();
        $fornecedorModel = new Fornecedor();

        $this->view('insumos/form', [
            'insumo' => $insumo,
            'categorias' => $categoriaModel->all(),
            'fornecedores' => $fornecedorModel->getAtivos()
        ]);
    }

    /**
     * Atualiza insumo
     */
    public function update() {
        if (!$this->isPost()) {
            redirect('/insumos');
        }

        $id = $this->getPost('id');

        $data = [
            'codigo_interno' => $this->getPost('codigo_interno'),
            'nome' => $this->getPost('nome'),
            'descricao' => $this->getPost('descricao'),
            'categoria_id' => $this->getPost('categoria_id') ?: null,
            'tipo' => $this->getPost('tipo'),
            'unidade_medida' => $this->getPost('unidade_medida'),
            'estoque_minimo' => floatval($this->getPost('estoque_minimo')) ?: 0,
            'fornecedor_principal_id' => $this->getPost('fornecedor_principal_id') ?: null,
            'ean' => $this->getPost('ean'),
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            $this->model->update($id, $data);
            logActivity('insumo_atualizado', "Insumo atualizado: {$data['nome']}");
            setFlashMessage('success', 'Insumo atualizado com sucesso');
            redirect('/insumos/viewInsumo?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar insumo: ' . $e->getMessage());
            redirect('/insumos/edit?id=' . $id);
        }
    }

    /**
     * Deleta insumo (soft delete)
     */
    public function delete() {
        if (!$this->isPost()) {
            redirect('/insumos');
        }

        $id = $this->getPost('id');

        try {
            $insumo = $this->model->find($id);
            $this->model->update($id, ['ativo' => 0]);
            logActivity('insumo_deletado', "Insumo inativado: {$insumo['nome']}");
            setFlashMessage('success', 'Insumo inativado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao inativar insumo: ' . $e->getMessage());
        }

        redirect('/insumos');
    }

    /**
     * Ajuste manual de estoque
     */
    public function ajustarEstoque() {
        if (!$this->isPost()) {
            redirect('/insumos');
        }

        $id = $this->getPost('id');
        $quantidade = $this->getPost('quantidade');
        $motivo = $this->getPost('motivo');

        try {
            $this->model->atualizarEstoque($id, $quantidade, 'definir');

            // Registrar movimentação
            $user = getCurrentUser();
            $db = Database::getInstance();
            $sql = "INSERT INTO movimentacoes_estoque
                    (insumo_id, tipo, quantidade, motivo, usuario_id, data_movimentacao)
                    VALUES (?, 'ajuste', ?, ?, ?, NOW())";
            $db->query($sql, [$id, $quantidade, $motivo, $user['id']]);

            logActivity('estoque_ajustado', "Ajuste de estoque do insumo ID: {$id}");
            setFlashMessage('success', 'Estoque ajustado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao ajustar estoque: ' . $e->getMessage());
        }

        redirect('/insumos/viewInsumo?id=' . $id);
    }
}
?>
