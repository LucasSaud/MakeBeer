<?php
require_once 'BaseController.php';

class SaidaBarrilController extends BaseController {

    private $model;
    private $barrilModel;

    public function __construct() {
        $this->model = new SaidaBarril();
        $this->barrilModel = new Barril();
    }

    /**
     * Lista saídas de barril
     */
    public function index() {
        $filtroLote = $this->getGet('lote');
        $filtroDataInicio = $this->getGet('data_inicio');
        $filtroDataFim = $this->getGet('data_fim');

        if ($filtroLote) {
            $saidas = $this->model->getByLote($filtroLote);
        } elseif ($filtroDataInicio && $filtroDataFim) {
            $saidas = $this->model->getByPeriodo($filtroDataInicio, $filtroDataFim);
        } else {
            $saidas = $this->model->getRecentes(100);
        }

        $this->view('saidabarril/index', [
            'saidas' => $saidas,
            'filtro_lote' => $filtroLote,
            'filtro_data_inicio' => $filtroDataInicio,
            'filtro_data_fim' => $filtroDataFim
        ]);
    }

    /**
     * Detalhes da saída
     */
    public function viewSaida() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Saída não encontrada');
            redirect('/saidabarril');
        }

        $saida = $this->model->getComDetalhes($id);

        if (!$saida) {
            setFlashMessage('error', 'Saída não encontrada');
            redirect('/saidabarril');
        }

        $this->view('saidabarril/view', ['saida' => $saida]);
    }

    /**
     * Formulário nova saída
     */
    public function create() {
        $estoqueBarrilId = $this->getGet('estoque_barril_id');
        $barril = null;

        if ($estoqueBarrilId) {
            $barril = $this->barrilModel->getComDetalhes($estoqueBarrilId);
        }

        // Buscar barris disponíveis
        $barrisDisponiveis = $this->barrilModel->getDisponiveis();

        $this->view('saidabarril/form', [
            'saida' => null,
            'barril' => $barril,
            'barris_disponiveis' => $barrisDisponiveis
        ]);
    }

    /**
     * Salva nova saída
     */
    public function store() {
        if (!$this->isPost()) {
            redirect('/saidabarril');
        }

        $dados = [
            'estoque_barril_id' => $this->getPost('barril_id'),
            'data_saida' => $this->getPost('data_saida') ?: date('Y-m-d'),
            'destino' => $this->getPost('destino'),
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            $id = $this->model->registrarSaida($dados);
            setFlashMessage('success', 'Saída de barril registrada com sucesso');
            redirect('/saidabarril/viewSaida?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao registrar saída: ' . $e->getMessage());
            redirect('/saidabarril/create');
        }
    }

    /**
     * Buscar barril por código
     */
    public function buscarBarril() {
        if (!$this->isPost()) {
            redirect('/saidabarril');
        }

        $codigoBarril = $this->getPost('barril_id');

        try {
            // Buscar no estoque de barris (não existe tabela barris separada)
            $sql = "SELECT eb.*, e.codigo as envase_codigo
                    FROM estoque_barris eb
                    LEFT JOIN envases e ON eb.envase_id = e.id
                    WHERE eb.codigo_barril = ? AND eb.status = 'disponivel'";

            $db = Database::getInstance();
            $barril = $db->fetchOne($sql, [$codigoBarril]);

            if ($barril) {
                redirect('/saidabarril/create?estoque_barril_id=' . $barril['id']);
            } else {
                setFlashMessage('error', 'Barril não encontrado ou já teve saída registrada');
                redirect('/saidabarril/create');
            }
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao buscar barril: ' . $e->getMessage());
            redirect('/saidabarril/create');
        }
    }

    /**
     * Relatório de saídas
     */
    public function relatorio() {
        $dataInicio = $this->getGet('data_inicio') ?: date('Y-m-01');
        $dataFim = $this->getGet('data_fim') ?: date('Y-m-t');

        $stats = $this->model->getStats($dataInicio, $dataFim);
        $relatorioPorEstilo = $this->model->getRelatorioPorEstilo($dataInicio, $dataFim);
        $saidas = $this->model->getByPeriodo($dataInicio, $dataFim);

        $this->view('saidabarril/relatorio', [
            'stats' => $stats,
            'relatorio_por_estilo' => $relatorioPorEstilo,
            'saidas' => $saidas,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
    }
}
?>
