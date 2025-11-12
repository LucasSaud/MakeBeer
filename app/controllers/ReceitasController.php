<?php
require_once 'BaseController.php';

class ReceitasController extends BaseController {

    private $model;

    public function __construct() {
        $this->model = new Receita();
    }

    /**
     * Lista receitas
     */
    public function index() {
        $filters = [
            'nome' => $this->getGet('nome'),
            'estilo' => $this->getGet('estilo')
        ];

        $receitas = empty(array_filter($filters))
            ? $this->model->getAtivas()
            : $this->model->search($filters);

        $this->view('receitas/index', ['receitas' => $receitas, 'filters' => $filters]);
    }

    /**
     * Detalhes da receita
     */
    public function viewReceita() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Receita não encontrada');
            redirect('/receitas');
        }

        $receita = $this->model->getComIngredientes($id);

        if (!$receita) {
            setFlashMessage('error', 'Receita não encontrada');
            redirect('/receitas');
        }

        // Calcular custo
        $receita['custo_total'] = $this->model->calcularCusto($id);

        // Garantir que volume existe
        $volume = $receita['volume_batch'] ?? $receita['volume'] ?? 0;
        $receita['volume'] = $volume;

        $receita['custo_por_litro'] = $volume > 0
            ? $receita['custo_total'] / $volume
            : 0;

        // Verificar disponibilidade
        $disponibilidade = $this->model->verificarDisponibilidadeEstoque($id);
        $receita['pode_produzir'] = $disponibilidade['disponivel'] ?? false;
        $receita['lotes_possiveis'] = $disponibilidade['lotes_possiveis'] ?? 0;

        // Buscar ingredientes
        $ingredientes = $this->model->getIngredientesDetalhados($id);

        // Ingredientes faltantes
        $ingredientes_faltantes = [];
        foreach ($ingredientes as $ing) {
            if ($ing['estoque_disponivel'] < $ing['quantidade']) {
                $ingredientes_faltantes[] = [
                    'nome' => $ing['insumo_nome'],
                    'falta' => $ing['quantidade'] - $ing['estoque_disponivel']
                ];
            }
        }

        // Histórico de produção
        $loteModel = new LoteProducao();
        $historico_producao = $loteModel->getHistoricoByReceita($id, 10);

        // Estatísticas
        $stats = $loteModel->getStatsByReceita($id);

        $receita['lotes_produzidos'] = $stats['lotes_produzidos'] ?? 0;
        $receita['total_produzido'] = $stats['total_produzido'] ?? 0;

        $this->view('receitas/view', [
            'receita' => $receita,
            'ingredientes' => $ingredientes,
            'ingredientes_faltantes' => $ingredientes_faltantes,
            'historico_producao' => $historico_producao
        ]);
    }

    /**
     * Formulário nova receita
     */
    public function create() {
        $insumoModel = new Insumo();

        $this->view('receitas/form', [
            'receita' => null,
            'insumos' => $insumoModel->getAllWithDetails()
        ]);
    }

    /**
     * Salva nova receita
     */
    public function store() {
        if (!$this->isPost()) {
            redirect('/receitas');
        }

        // Validar volume_batch
        $volumeBatch = $this->getPost('volume_batch');
        if (empty($volumeBatch) || !is_numeric($volumeBatch) || $volumeBatch <= 0) {
            $volumeBatch = null;
        }

        $dadosReceita = [
            'nome' => $this->getPost('nome'),
            'estilo' => $this->getPost('estilo'),
            'descricao' => $this->getPost('descricao'),
            'volume_batch' => $volumeBatch,
            'densidade_inicial' => $this->getPost('densidade_inicial') ?: null,
            'densidade_final' => $this->getPost('densidade_final') ?: null,
            'ibu' => $this->getPost('ibu') ?: null,
            'srm' => $this->getPost('srm') ?: null,
            'abv' => $this->getPost('abv') ?: null,
            'tempo_fermentacao' => $this->getPost('tempo_fermentacao') ?: null,
            'temperatura_fermentacao' => $this->getPost('temperatura_fermentacao') ?: null,
            'instrucoes' => $this->getPost('instrucoes'),
            'ativo' => 1
        ];

        // Processar ingredientes
        $ingredientes = [];
        $insumosIds = $this->getPost('ingrediente_insumo_id') ?: [];

        foreach ($insumosIds as $key => $insumoId) {
            $ingredientes[] = [
                'insumo_id' => $insumoId,
                'quantidade' => $_POST['ingrediente_quantidade'][$key],
                'unidade' => $_POST['ingrediente_unidade'][$key],
                'fase' => $_POST['ingrediente_fase'][$key],
                'tempo_adicao' => $_POST['ingrediente_tempo'][$key] ?? 0,
                'observacoes' => $_POST['ingrediente_obs'][$key] ?? ''
            ];
        }

        try {
            $id = $this->model->criarComIngredientes($dadosReceita, $ingredientes);
            logActivity('receita_criada', "Receita criada: {$dadosReceita['nome']}");
            setFlashMessage('success', 'Receita criada com sucesso');
            redirect('/receitas/view?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao criar receita: ' . $e->getMessage());
            redirect('/receitas/create');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Receita não encontrada');
            redirect('/receitas');
        }

        $receita = $this->model->getComIngredientes($id);

        if (!$receita) {
            setFlashMessage('error', 'Receita não encontrada');
            redirect('/receitas');
        }

        $insumoModel = new Insumo();

        $this->view('receitas/form', [
            'receita' => $receita,
            'insumos' => $insumoModel->getAllWithDetails()
        ]);
    }

    /**
     * Atualiza receita
     */
    public function update() {
        if (!$this->isPost()) {
            redirect('/receitas');
        }

        $id = $this->getPost('id');

        // Validar volume_batch
        $volumeBatch = $this->getPost('volume_batch');
        if (empty($volumeBatch) || !is_numeric($volumeBatch) || $volumeBatch <= 0) {
            $volumeBatch = null;
        }

        $dadosReceita = [
            'nome' => $this->getPost('nome'),
            'estilo' => $this->getPost('estilo'),
            'descricao' => $this->getPost('descricao'),
            'volume_batch' => $volumeBatch,
            'densidade_inicial' => $this->getPost('densidade_inicial') ?: null,
            'densidade_final' => $this->getPost('densidade_final') ?: null,
            'ibu' => $this->getPost('ibu') ?: null,
            'srm' => $this->getPost('srm') ?: null,
            'abv' => $this->getPost('abv') ?: null,
            'tempo_fermentacao' => $this->getPost('tempo_fermentacao') ?: null,
            'temperatura_fermentacao' => $this->getPost('temperatura_fermentacao') ?: null,
            'instrucoes' => $this->getPost('instrucoes')
        ];

        try {
            $this->model->update($id, $dadosReceita);

            // Remover ingredientes antigos
            $db = Database::getInstance();
            $db->query("DELETE FROM receita_ingredientes WHERE receita_id = ?", [$id]);

            // Adicionar novos ingredientes
            $insumosIds = $this->getPost('ingrediente_insumo_id') ?: [];
            foreach ($insumosIds as $key => $insumoId) {
                $ingrediente = [
                    'insumo_id' => $insumoId,
                    'quantidade' => $_POST['ingrediente_quantidade'][$key],
                    'unidade' => $_POST['ingrediente_unidade'][$key],
                    'fase' => $_POST['ingrediente_fase'][$key],
                    'tempo_adicao' => $_POST['ingrediente_tempo'][$key] ?? 0,
                    'observacoes' => $_POST['ingrediente_obs'][$key] ?? ''
                ];
                $this->model->adicionarIngrediente($id, $ingrediente);
            }

            logActivity('receita_atualizada', "Receita atualizada: {$dadosReceita['nome']}");
            setFlashMessage('success', 'Receita atualizada com sucesso');
            redirect('/receitas/view?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar receita: ' . $e->getMessage());
            redirect('/receitas/edit?id=' . $id);
        }
    }

    /**
     * Duplicar receita
     */
    public function duplicar() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Receita não encontrada');
            redirect('/receitas');
        }

        try {
            $novoId = $this->model->duplicar($id);
            setFlashMessage('success', 'Receita duplicada com sucesso');
            redirect('/receitas/edit?id=' . $novoId);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao duplicar receita: ' . $e->getMessage());
            redirect('/receitas/view?id=' . $id);
        }
    }

    /**
     * Deleta receita (soft delete)
     */
    public function delete() {
        if (!$this->isPost()) {
            redirect('/receitas');
        }

        $id = $this->getPost('id');

        try {
            $receita = $this->model->find($id);
            $this->model->update($id, ['ativo' => 0]);
            logActivity('receita_deletada', "Receita inativada: {$receita['nome']}");
            setFlashMessage('success', 'Receita inativada com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao inativar receita: ' . $e->getMessage());
        }

        redirect('/receitas');
    }
}
?>