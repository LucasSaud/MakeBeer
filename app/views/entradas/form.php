<?php
$pageTitle = ($entrada ? 'Editar' : 'Nova') . ' Entrada de Estoque - ' . APP_NAME;
$activeMenu = 'entradas';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title"><?= $entrada ? 'Editar' : 'Nova' ?> Entrada de Estoque</h1>
    <div class="page-actions">
        <a href="/entradas" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dados da Entrada</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $entrada ? '/entradas/update' : '/entradas/store' ?>" id="formEntrada" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <?php if ($entrada): ?>
                        <input type="hidden" name="id" value="<?= $entrada['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Data da Entrada *</label>
                        <input
                            type="date"
                            name="data_entrada"
                            class="form-control"
                            value="<?= $entrada['data_entrada'] ?? date('Y-m-d') ?>"
                            required>
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Insumo *</label>
                                <select name="insumo_id" id="insumoSelect" class="form-control form-select" required>
                                    <option value="">Selecione um insumo</option>
                                    <?php foreach ($insumos as $insumo): ?>
                                        <option
                                            value="<?= $insumo['id'] ?>"
                                            data-unidade="<?= $insumo['unidade_medida'] ?>"
                                            data-codigo="<?= $insumo['codigo_interno'] ?? '' ?>"
                                            <?= (($entrada['insumo_id'] ?? $_GET['insumo_id'] ?? '') == $insumo['id']) ? 'selected' : '' ?>>
                                            <?= ($insumo['codigo_interno'] ?? 'N/A') ?> - <?= $insumo['nome'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Fornecedor *</label>
                                <select name="fornecedor_id" class="form-control form-select" required>
                                    <option value="">Selecione um fornecedor</option>
                                    <?php foreach ($fornecedores as $forn): ?>
                                        <option
                                            value="<?= $forn['id'] ?>"
                                            <?= (($entrada['fornecedor_id'] ?? $_GET['fornecedor_id'] ?? '') == $forn['id']) ? 'selected' : '' ?>>
                                            <?= $forn['nome'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Quantidade *</label>
                                <input
                                    type="number"
                                    name="quantidade"
                                    id="quantidadeInput"
                                    class="form-control"
                                    step="0.001"
                                    required
                                    placeholder="0.000"
                                    value="<?= $entrada['quantidade'] ?? '' ?>">
                                <small id="unidadeInfo" style="color: #6c757d;">Selecione um insumo</small>
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Custo Unitário (R$) *</label>
                                <input
                                    type="number"
                                    name="preco_unitario"
                                    id="custoUnitarioInput"
                                    class="form-control"
                                    step="0.01"
                                    required
                                    placeholder="0.00"
                                    value="<?= $entrada['preco_unitario'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Valor Total (R$)</label>
                                <input
                                    type="text"
                                    id="valorTotalDisplay"
                                    class="form-control"
                                    readonly
                                    style="background: #f8f9fa; font-weight: bold; color: #27ae60;"
                                    placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h4 style="color: #2c3e50; margin-bottom: 1rem;">Informações do Lote</h4>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Número do Lote *</label>
                                <input
                                    type="text"
                                    name="lote_fornecedor"
                                    class="form-control"
                                    required
                                    placeholder="Ex: LT-2024-001"
                                    value="<?= $entrada['lote_fornecedor'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Data de Validade *</label>
                                <input
                                    type="date"
                                    name="data_validade"
                                    class="form-control"
                                    required
                                    value="<?= $entrada['data_validade'] ?? '' ?>">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h4 style="color: #2c3e50; margin-bottom: 1rem;">Nota Fiscal (opcional)</h4>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Número da Nota Fiscal</label>
                                <input
                                    type="text"
                                    name="numero_nota_fiscal"
                                    class="form-control"
                                    placeholder="Ex: 12345"
                                    value="<?= $entrada['numero_nota_fiscal'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Data da Nota Fiscal</label>
                                <input
                                    type="date"
                                    name="data_nota_fiscal"
                                    class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Anexar Nota Fiscal (PDF/Imagem)</label>
                        <input
                            type="file"
                            name="arquivo_nota"
                            class="form-control"
                            accept=".pdf,.jpg,.jpeg,.png">
                        <small style="color: #6c757d;">Formatos aceitos: PDF, JPG, PNG (máx. 5MB)</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observações</label>
                        <textarea
                            name="observacoes"
                            class="form-control"
                            rows="3"
                            placeholder="Observações sobre esta entrada"><?= $entrada['observacoes'] ?? '' ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center" style="margin-top: 2rem;">
                        <a href="/entradas" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">
                            <?= $entrada ? 'Atualizar Entrada' : 'Registrar Entrada' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Resumo</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Valor Total:</strong>
                    <h2 id="resumoValorTotal" style="color: #27ae60; margin: 0.5rem 0;">
                        R$ 0,00
                    </h2>
                </div>

                <div class="mb-3">
                    <strong>Quantidade:</strong>
                    <p id="resumoQuantidade">-</p>
                </div>

                <div>
                    <strong>Insumo:</strong>
                    <p id="resumoInsumo">Nenhum selecionado</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Instruções</h3>
            </div>
            <div class="card-body">
                <ul style="line-height: 1.8; color: #6c757d;">
                    <li>Preencha todos os campos obrigatórios (*)</li>
                    <li>O lote identifica cada entrada</li>
                    <li>A validade é importante para controle FIFO</li>
                    <li>Anexe a nota fiscal para melhor controle</li>
                    <li>O estoque será atualizado automaticamente</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Atualizar informações ao selecionar insumo
document.getElementById('insumoSelect').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const unidade = option.getAttribute('data-unidade');
    const nome = option.text;

    document.getElementById('unidadeInfo').textContent = unidade ? 'Unidade: ' + unidade.toUpperCase() : 'Selecione um insumo';
    document.getElementById('resumoInsumo').textContent = nome || 'Nenhum selecionado';

    calcularTotal();
});

// Calcular valor total
function calcularTotal() {
    const quantidade = parseFloat(document.getElementById('quantidadeInput').value) || 0;
    const custoUnitario = parseFloat(document.getElementById('custoUnitarioInput').value) || 0;
    const total = quantidade * custoUnitario;

    document.getElementById('valorTotalDisplay').value = total.toFixed(2);
    document.getElementById('resumoValorTotal').textContent = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    const option = document.getElementById('insumoSelect').options[document.getElementById('insumoSelect').selectedIndex];
    const unidade = option.getAttribute('data-unidade');
    document.getElementById('resumoQuantidade').textContent = quantidade.toFixed(3) + ' ' + (unidade || '');
}

document.getElementById('quantidadeInput').addEventListener('input', calcularTotal);
document.getElementById('custoUnitarioInput').addEventListener('input', calcularTotal);

// Validação do formulário
document.getElementById('formEntrada').addEventListener('submit', function(e) {
    const quantidade = parseFloat(document.getElementById('quantidadeInput').value);
    const custoUnitario = parseFloat(document.getElementById('custoUnitarioInput').value);

    if (quantidade <= 0) {
        e.preventDefault();
        alert('A quantidade deve ser maior que zero.');
        return false;
    }

    if (custoUnitario <= 0) {
        e.preventDefault();
        alert('O custo unitário deve ser maior que zero.');
        return false;
    }

    return true;
});
</script>

<?php include 'app/views/layouts/footer.php'; ?>
