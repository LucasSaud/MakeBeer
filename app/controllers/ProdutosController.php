<?php
require_once 'BaseController.php';

class ProdutosController extends BaseController {

    private $model;

    public function __construct() {
        $this->model = new ProdutoFinal();
    }

    /**
     * Lista produtos finais
     */
    public function index() {
        $produtos = $this->model->where('ativo', 1);

        $this->view('produtos/index', ['produtos' => $produtos]);
    }

    /**
     * Detalhes do produto
     */
    public function viewProduto() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Produto não encontrado');
            redirect('/produtos');
        }

        $produto = $this->model->find($id);

        if (!$produto) {
            setFlashMessage('error', 'Produto não encontrado');
            redirect('/produtos');
        }

        $historico = $this->model->getHistoricoProducao($id);

        $this->view('produtos/view', [
            'produto' => $produto,
            'historico' => $historico
        ]);
    }

    /**
     * Formulário novo produto
     */
    public function create() {
        $this->view('produtos/form', ['produto' => null]);
    }

    /**
     * Salva novo produto
     */
    public function store() {
        if (!$this->isPost()) {
            redirect('/produtos');
        }

        $data = [
            'nome' => $this->getPost('nome'),
            'estilo' => $this->getPost('estilo'),
            'descricao' => $this->getPost('descricao'),
            'abv' => $this->getPost('abv') ?: null,
            'ibu' => $this->getPost('ibu') ?: null,
            'tipo_embalagem' => $this->getPost('tipo_embalagem'),
            'preco_venda' => $this->getPost('preco_venda') ?: 0,
            'estoque_minimo' => $this->getPost('estoque_minimo') ?: 0,
            'ativo' => 1
        ];

        try {
            $id = $this->model->create($data);
            logActivity('produto_criado', "Produto criado: {$data['nome']}");
            setFlashMessage('success', 'Produto criado com sucesso');
            redirect('/produtos/view?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao criar produto: ' . $e->getMessage());
            redirect('/produtos/create');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Produto não encontrado');
            redirect('/produtos');
        }

        $produto = $this->model->find($id);

        if (!$produto) {
            setFlashMessage('error', 'Produto não encontrado');
            redirect('/produtos');
        }

        $this->view('produtos/form', ['produto' => $produto]);
    }

    /**
     * Atualiza produto
     */
    public function update() {
        if (!$this->isPost()) {
            redirect('/produtos');
        }

        $id = $this->getPost('id');

        $data = [
            'nome' => $this->getPost('nome'),
            'estilo' => $this->getPost('estilo'),
            'descricao' => $this->getPost('descricao'),
            'abv' => $this->getPost('abv') ?: null,
            'ibu' => $this->getPost('ibu') ?: null,
            'tipo_embalagem' => $this->getPost('tipo_embalagem'),
            'preco_venda' => $this->getPost('preco_venda'),
            'estoque_minimo' => $this->getPost('estoque_minimo')
        ];

        try {
            $this->model->update($id, $data);
            logActivity('produto_atualizado', "Produto atualizado: {$data['nome']}");
            setFlashMessage('success', 'Produto atualizado com sucesso');
            redirect('/produtos/view?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar produto: ' . $e->getMessage());
            redirect('/produtos/edit?id=' . $id);
        }
    }

    /**
     * Registrar produção de produto (envase)
     */
    public function registrarProducao() {
        if (!$this->isPost()) {
            redirect('/produtos');
        }

        $loteProducaoId = $this->getPost('lote_producao_id');
        $produtoId = $this->getPost('produto_id');
        $quantidade = $this->getPost('quantidade');

        $dados = [
            'data_envase' => $this->getPost('data_envase') ?: date('Y-m-d'),
            'data_validade' => $this->getPost('data_validade') ?: null,
            'lote_produto' => $this->getPost('lote_produto'),
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            $this->model->registrarProducao($loteProducaoId, $produtoId, $quantidade, $dados);
            logActivity('producao_produto', "Produção registrada - Produto: {$produtoId}, Qtd: {$quantidade}");
            setFlashMessage('success', 'Produção registrada com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao registrar produção: ' . $e->getMessage());
        }

        redirect('/produtos/view?id=' . $produtoId);
    }

    /**
     * Deleta produto (soft delete)
     */
    public function delete() {
        if (!$this->isPost()) {
            redirect('/produtos');
        }

        $id = $this->getPost('id');

        try {
            $produto = $this->model->find($id);
            $this->model->update($id, ['ativo' => 0]);
            logActivity('produto_deletado', "Produto inativado: {$produto['nome']}");
            setFlashMessage('success', 'Produto inativado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao inativar produto: ' . $e->getMessage());
        }

        redirect('/produtos');
    }
}
?>