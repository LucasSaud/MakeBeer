<?php
require_once 'BaseController.php';

class CamarafriaController extends BaseController {

    private $setorModel;
    private $localizacaoModel;
    private $movimentacaoModel;

    public function __construct() {
        $this->setorModel = new CamaraFriaSetor();
        $this->localizacaoModel = new EstoqueLocalizacao();
        $this->movimentacaoModel = new CamaraFriaMovimentacao();
    }

    /**
     * Dashboard da Câmara Fria
     */
    public function index() {
        $estatisticas = $this->setorModel->getEstatisticasGerais();
        $setores = $this->setorModel->getAllComEstatisticas();
        $movimentacoesRecentes = $this->movimentacaoModel->getRecentes(10);
        $produtosProximosVencimento = $this->localizacaoModel->getProdutosProximosVencimento(15);

        $this->view('camarafria/dashboard', [
            'estatisticas' => $estatisticas,
            'setores' => $setores,
            'movimentacoes' => $movimentacoesRecentes,
            'produtos_vencimento' => $produtosProximosVencimento
        ]);
    }

    // ==================== SETORES ====================

    /**
     * Lista de setores
     */
    public function setores() {
        $setores = $this->setorModel->getAllComEstatisticas();

        $this->view('camarafria/setores/index', [
            'setores' => $setores
        ]);
    }

    /**
     * Formulário novo setor
     */
    public function criarSetor() {
        $this->view('camarafria/setores/form', [
            'setor' => null
        ]);
    }

    /**
     * Salva novo setor
     */
    public function salvarSetor() {
        if (!$this->isPost()) {
            redirect('/camarafria/setores');
        }

        $data = [
            'nome' => $this->getPost('nome'),
            'descricao' => $this->getPost('descricao'),
            'capacidade_maxima' => $this->getPost('capacidade_maxima') ?: 0,
            'temperatura_ideal' => $this->getPost('temperatura_ideal') ?: null,
            'ativo' => 1
        ];

        try {
            $id = $this->setorModel->create($data);
            logActivity('setor_camarafria_criado', "Setor criado: {$data['nome']}");
            setFlashMessage('success', 'Setor criado com sucesso');
            redirect('/camarafria/setores');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao criar setor: ' . $e->getMessage());
            redirect('/camarafria/criarSetor');
        }
    }

    /**
     * Formulário editar setor
     */
    public function editarSetor() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Setor não encontrado');
            redirect('/camarafria/setores');
        }

        $setor = $this->setorModel->find($id);

        if (!$setor) {
            setFlashMessage('error', 'Setor não encontrado');
            redirect('/camarafria/setores');
        }

        $this->view('camarafria/setores/form', [
            'setor' => $setor
        ]);
    }

    /**
     * Atualiza setor
     */
    public function atualizarSetor() {
        if (!$this->isPost()) {
            redirect('/camarafria/setores');
        }

        $id = $this->getPost('id');

        $data = [
            'nome' => $this->getPost('nome'),
            'descricao' => $this->getPost('descricao'),
            'capacidade_maxima' => $this->getPost('capacidade_maxima') ?: 0,
            'temperatura_ideal' => $this->getPost('temperatura_ideal') ?: null
        ];

        try {
            $this->setorModel->update($id, $data);
            logActivity('setor_camarafria_atualizado', "Setor atualizado ID: {$id}");
            setFlashMessage('success', 'Setor atualizado com sucesso');
            redirect('/camarafria/setores');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar setor: ' . $e->getMessage());
            redirect('/camarafria/editarSetor?id=' . $id);
        }
    }

    /**
     * Visualizar setor
     */
    public function verSetor() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Setor não encontrado');
            redirect('/camarafria/setores');
        }

        $setor = $this->setorModel->getComEstatisticas($id);
        $produtos = $this->setorModel->getProdutosNoSetor($id);

        $this->view('camarafria/setores/view', [
            'setor' => $setor,
            'produtos' => $produtos
        ]);
    }

    // ==================== LOCALIZAÇÕES ====================

    /**
     * Lista de localizações
     */
    public function localizacoes() {
        $filtros = [
            'setor_id' => $this->getGet('setor_id'),
            'produto_id' => $this->getGet('produto_id'),
            'status' => $this->getGet('status')
        ];

        $filtros = array_filter($filtros);

        $localizacoes = $this->localizacaoModel->getAllComDetalhes($filtros);
        $setores = $this->setorModel->getAtivos();
        $produtoModel = new ProdutoFinal();
        $produtos = $produtoModel->getAtivos();

        $this->view('camarafria/localizacoes/index', [
            'localizacoes' => $localizacoes,
            'setores' => $setores,
            'produtos' => $produtos,
            'filtros' => $filtros
        ]);
    }

    /**
     * Formulário nova localização (entrada de produto)
     */
    public function novaLocalizacao() {
        $setores = $this->setorModel->getAtivos();
        $produtoModel = new ProdutoFinal();
        $produtos = $produtoModel->getAtivos();
        $loteModel = new LoteProducao();
        $lotes = $loteModel->getLotesAtivos();

        $this->view('camarafria/localizacoes/form', [
            'localizacao' => null,
            'setores' => $setores,
            'produtos' => $produtos,
            'lotes' => $lotes
        ]);
    }

    /**
     * Registra entrada de produto na câmara fria
     */
    public function registrarEntrada() {
        if (!$this->isPost()) {
            redirect('/camarafria/localizacoes');
        }

        $data = [
            'produto_id' => $this->getPost('produto_id'),
            'lote_id' => $this->getPost('lote_id') ?: null,
            'setor_id' => $this->getPost('setor_id'),
            'quantidade' => $this->getPost('quantidade'),
            'status' => $this->getPost('status') ?: 'disponivel',
            'observacoes' => $this->getPost('observacoes')
        ];

        try {
            $this->localizacaoModel->registrarEntrada($data);

            // Registrar movimentação
            $this->movimentacaoModel->registrarEntrada(
                $data['produto_id'],
                $data['lote_id'],
                $data['setor_id'],
                $data['quantidade'],
                'Entrada manual via sistema'
            );

            $produto = (new ProdutoFinal())->find($data['produto_id']);
            logActivity('entrada_camarafria', "Produto {$produto['nome']} adicionado à câmara fria");
            setFlashMessage('success', 'Produto adicionado à câmara fria com sucesso');
            redirect('/camarafria/localizacoes');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao adicionar produto: ' . $e->getMessage());
            redirect('/camarafria/novaLocalizacao');
        }
    }

    /**
     * Formulário de transferência
     */
    public function transferir() {
        $localizacaoId = $this->getGet('id');

        if (!$localizacaoId) {
            setFlashMessage('error', 'Localização não encontrada');
            redirect('/camarafria/localizacoes');
        }

        $localizacao = $this->localizacaoModel->getComDetalhes($localizacaoId);
        $setores = $this->setorModel->getAtivos();

        $this->view('camarafria/localizacoes/transferir', [
            'localizacao' => $localizacao,
            'setores' => $setores
        ]);
    }

    /**
     * Executa transferência entre setores
     */
    public function executarTransferencia() {
        if (!$this->isPost()) {
            redirect('/camarafria/localizacoes');
        }

        $localizacaoId = $this->getPost('localizacao_id');
        $setorDestinoId = $this->getPost('setor_destino_id');
        $quantidade = $this->getPost('quantidade');
        $motivo = $this->getPost('motivo');

        try {
            $this->localizacaoModel->transferir($localizacaoId, $setorDestinoId, $quantidade, $motivo);

            logActivity('transferencia_camarafria', "Transferência realizada entre setores");
            setFlashMessage('success', 'Transferência realizada com sucesso');
            redirect('/camarafria/localizacoes');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao transferir: ' . $e->getMessage());
            redirect('/camarafria/transferir?id=' . $localizacaoId);
        }
    }

    /**
     * Alterar status de localização
     */
    public function alterarStatus() {
        if (!$this->isPost()) {
            redirect('/camarafria/localizacoes');
        }

        $localizacaoId = $this->getPost('localizacao_id');
        $novoStatus = $this->getPost('status');
        $observacoes = $this->getPost('observacoes');

        try {
            $this->localizacaoModel->alterarStatus($localizacaoId, $novoStatus, $observacoes);

            logActivity('status_alterado_camarafria', "Status alterado para: {$novoStatus}");
            setFlashMessage('success', 'Status alterado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao alterar status: ' . $e->getMessage());
        }

        redirect('/camarafria/localizacoes');
    }

    // ==================== MOVIMENTAÇÕES ====================

    /**
     * Histórico de movimentações
     */
    public function movimentacoes() {
        $filtros = [
            'produto_id' => $this->getGet('produto_id'),
            'setor_id' => $this->getGet('setor_id'),
            'tipo_movimentacao' => $this->getGet('tipo_movimentacao'),
            'data_inicio' => $this->getGet('data_inicio'),
            'data_fim' => $this->getGet('data_fim')
        ];

        $filtros = array_filter($filtros);

        $movimentacoes = $this->movimentacaoModel->getAllComDetalhes(200, $filtros);
        $setores = $this->setorModel->getAtivos();
        $produtoModel = new ProdutoFinal();
        $produtos = $produtoModel->getAtivos();

        $this->view('camarafria/movimentacoes/index', [
            'movimentacoes' => $movimentacoes,
            'setores' => $setores,
            'produtos' => $produtos,
            'filtros' => $filtros
        ]);
    }

    // ==================== RELATÓRIOS ====================

    /**
     * Mapa de ocupação da câmara fria
     */
    public function mapaOcupacao() {
        $setores = $this->setorModel->getAllComEstatisticas();
        $estatisticasGerais = $this->setorModel->getEstatisticasGerais();

        $this->view('camarafria/relatorios/mapa', [
            'setores' => $setores,
            'estatisticas' => $estatisticasGerais
        ]);
    }

    /**
     * Produtos próximos ao vencimento
     */
    public function produtosVencimento() {
        $dias = $this->getGet('dias') ?: 30;
        $produtos = $this->localizacaoModel->getProdutosProximosVencimento($dias);

        $this->view('camarafria/relatorios/vencimento', [
            'produtos' => $produtos,
            'dias' => $dias
        ]);
    }
}
?>
