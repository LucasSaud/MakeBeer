<?php
$isEdit = isset($lote) && !empty($lote['id']);
$pageTitle = ($isEdit ? 'Editar' : 'Novo') . ' Lote de Produção - ' . APP_NAME;
$activeMenu = 'producao';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title"><?= $isEdit ? 'Editar' : 'Novo' ?> Lote de Produção</h1>
    <div class="page-actions">
        <a href="/producao" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dados do Lote</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/producao/<?= $isEdit ? 'atualizar' : 'store' ?>" id="formProducao">
                    <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $lote['id'] ?>">
                    <?php endif; ?>
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Código do Lote *</label>
                                <input
                                    type="text"
                                    name="codigo_lote"
                                    class="form-control"
                                    value="<?= $lote['codigo_lote'] ?? generateLoteCode() ?>"
                                    required
                                    placeholder="LT-20240101-0001">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Receita *</label>
                                <select name="receita_id" id="receitaSelect" class="form-control form-select" required <?= $isEdit ? 'disabled' : '' ?>>
                                    <option value="">Selecione uma receita</option>
                                    <?php foreach ($receitas as $rec): ?>
                                        <option
                                            value="<?= $rec['id'] ?>"
                                            data-volume="<?= $rec['volume'] ?? 0 ?>"
                                            data-custo="<?= $rec['custo_total'] ?? 0 ?>"
                                            data-tempo="<?= $rec['tempo_producao'] ?? 0 ?>"
                                            <?= ($lote['receita_id'] ?? $_GET['receita_id'] ?? '') == $rec['id'] ? 'selected' : '' ?>>
                                            <?= $rec['nome'] ?> (<?= formatQuantity($rec['volume'] ?? 0, 'L') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($isEdit): ?>
                                    <input type="hidden" name="receita_id" value="<?= $lote['receita_id'] ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Quantidade (Litros) *</label>
                                <input
                                    type="number"
                                    name="volume_planejado"
                                    id="quantidadeInput"
                                    class="form-control"
                                    value="<?= $lote['volume_planejado'] ?? '' ?>"
                                    step="0.01"
                                    min="0.01"
                                    required
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Data de Início *</label>
                                <input
                                    type="date"
                                    name="data_inicio"
                                    id="dataInicioInput"
                                    class="form-control"
                                    value="<?= $lote['data_inicio'] ?? date('Y-m-d') ?>"
                                    required>
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Previsão de Conclusão *</label>
                                <input
                                    type="date"
                                    name="previsao_conclusao"
                                    id="previsaoConclusaoInput"
                                    class="form-control"
                                    value="<?= $lote['previsao_conclusao'] ?? '' ?>"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-control form-select" required>
                                    <option value="planejado" <?= ($lote['status'] ?? 'planejado') == 'planejado' ? 'selected' : '' ?>>Planejado</option>
                                    <option value="em_producao" <?= ($lote['status'] ?? '') == 'em_producao' ? 'selected' : '' ?>>Em Produção</option>
                                    <option value="fermentando" <?= ($lote['status'] ?? '') == 'fermentando' ? 'selected' : '' ?>>Fermentando</option>
                                    <option value="maturando" <?= ($lote['status'] ?? '') == 'maturando' ? 'selected' : '' ?>>Maturando</option>
                                    <option value="finalizado" <?= ($lote['status'] ?? '') == 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                                    <option value="cancelado" <?= ($lote['status'] ?? '') == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                            </div>
                        </div>

                        <?php if ($isEdit && $lote['status'] == 'finalizado'): ?>
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Data de Conclusão</label>
                                <input
                                    type="date"
                                    name="data_conclusao"
                                    class="form-control"
                                    value="<?= $lote['data_conclusao'] ?? '' ?>">
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observações</label>
                        <textarea
                            name="observacoes"
                            class="form-control"
                            rows="4"
                            placeholder="Anotações sobre este lote de produção"><?= $lote['observacoes'] ?? '' ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center" style="margin-top: 2rem;">
                        <a href="/producao" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <?= $isEdit ? 'Atualizar' : 'Iniciar' ?> Lote
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <!-- Resumo -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Resumo</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Receita:</strong>
                    <p id="resumoReceita">Selecione uma receita</p>
                </div>

                <div class="mb-3">
                    <strong>Quantidade:</strong>
                    <p id="resumoQuantidade">-</p>
                </div>

                <div class="mb-3">
                    <strong>Custo Estimado:</strong>
                    <h3 id="resumoCusto" style="color: #27ae60; margin: 0.5rem 0;">
                        R$ 0,00
                    </h3>
                </div>

                <div>
                    <strong>Tempo Estimado:</strong>
                    <p id="resumoTempo">-</p>
                </div>
            </div>
        </div>

        <!-- Verificação de Disponibilidade -->
        <div class="card mb-3" id="disponibilidadeCard" style="display: none;">
            <div class="card-header">
                <h3 class="card-title">Disponibilidade</h3>
            </div>
            <div class="card-body" id="disponibilidadeContent">
                <!-- Preenchido via JavaScript -->
            </div>
        </div>

        <!-- Instruções -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Instruções</h3>
            </div>
            <div class="card-body">
                <ul style="line-height: 1.8; color: #6c757d;">
                    <li>Selecione a receita a ser produzida</li>
                    <li>Informe a quantidade a ser produzida</li>
                    <li>O sistema verificará a disponibilidade de insumos</li>
                    <li>Os insumos serão consumidos automaticamente</li>
                    <li>Acompanhe o progresso na listagem de lotes</li>
                </ul>
            </div>
        </div>

        <?php if ($isEdit): ?>
        <div class="card" style="border-color: #e74c3c; margin-top: 1rem;">
            <div class="card-header" style="background: #fee; border-bottom-color: #e74c3c;">
                <h3 class="card-title" style="color: #e74c3c;">Zona de Perigo</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Cancelar este lote não devolverá os insumos ao estoque.
                </p>
                <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    onclick="confirmarCancelamento(<?= $lote['id'] ?>)">
                    Cancelar Lote
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Atualizar resumo ao selecionar receita
document.getElementById('receitaSelect')?.addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const nome = option.text;
    const volume = option.getAttribute('data-volume');
    const custo = parseFloat(option.getAttribute('data-custo')) || 0;
    const tempo = option.getAttribute('data-tempo');

    document.getElementById('resumoReceita').textContent = nome || 'Selecione uma receita';
    document.getElementById('resumoTempo').textContent = tempo ? tempo + ' dias' : '-';

    // Preencher quantidade automaticamente
    if (volume && !document.getElementById('quantidadeInput').value) {
        document.getElementById('quantidadeInput').value = volume;
    }

    // Calcular data de conclusão
    if (tempo && document.getElementById('dataInicioInput').value) {
        const dataInicio = new Date(document.getElementById('dataInicioInput').value);
        dataInicio.setDate(dataInicio.getDate() + parseInt(tempo));
        document.getElementById('previsaoConclusaoInput').value = dataInicio.toISOString().split('T')[0];
    }

    atualizarResumo();
});

// Atualizar resumo ao mudar quantidade
document.getElementById('quantidadeInput')?.addEventListener('input', atualizarResumo);

// Atualizar data de conclusão ao mudar data de início
document.getElementById('dataInicioInput')?.addEventListener('change', function() {
    const option = document.getElementById('receitaSelect').options[document.getElementById('receitaSelect').selectedIndex];
    const tempo = option.getAttribute('data-tempo');

    if (tempo && this.value) {
        const dataInicio = new Date(this.value);
        dataInicio.setDate(dataInicio.getDate() + parseInt(tempo));
        document.getElementById('previsaoConclusaoInput').value = dataInicio.toISOString().split('T')[0];
    }
});

function atualizarResumo() {
    const option = document.getElementById('receitaSelect')?.options[document.getElementById('receitaSelect').selectedIndex];
    const volumeReceita = parseFloat(option?.getAttribute('data-volume')) || 0;
    const custoReceita = parseFloat(option?.getAttribute('data-custo')) || 0;
    const quantidade = parseFloat(document.getElementById('quantidadeInput')?.value) || 0;

    document.getElementById('resumoQuantidade').textContent = quantidade > 0 ? quantidade.toFixed(2) + ' L' : '-';

    // Calcular custo proporcional
    if (volumeReceita > 0 && quantidade > 0) {
        const fator = quantidade / volumeReceita;
        const custoTotal = custoReceita * fator;
        document.getElementById('resumoCusto').textContent = 'R$ ' + custoTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    } else {
        document.getElementById('resumoCusto').textContent = 'R$ 0,00';
    }
}

// Validação do formulário
document.getElementById('formProducao')?.addEventListener('submit', function(e) {
    const quantidade = parseFloat(document.getElementById('quantidadeInput').value);

    if (quantidade <= 0) {
        e.preventDefault();
        alert('A quantidade deve ser maior que zero.');
        return false;
    }

    return true;
});

<?php if ($isEdit): ?>
function confirmarCancelamento(id) {
    if (confirm('Tem certeza que deseja cancelar este lote? Os insumos já consumidos não serão devolvidos ao estoque.')) {
        window.location.href = '/producao/cancelar/' + id;
    }
}
<?php endif; ?>

// Inicializar ao carregar a página
<?php if (($lote['receita_id'] ?? $_GET['receita_id'] ?? '') != ''): ?>
    atualizarResumo();
<?php endif; ?>
</script>

<?php include 'app/views/layouts/footer.php'; ?>
