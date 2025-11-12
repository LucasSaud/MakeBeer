<?php
$isEdit = isset($produto) && !empty($produto['id']);
$pageTitle = ($isEdit ? 'Editar' : 'Novo') . ' Produto - ' . APP_NAME;
$activeMenu = 'produtos';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title"><?= $isEdit ? 'Editar' : 'Novo' ?> Produto</h1>
    <div class="page-actions">
        <a href="/produtos" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dados do Produto</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/produtos/<?= $isEdit ? 'update/' . $produto['id'] : 'store' ?>" id="formProduto">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Nome do Produto *</label>
                                <input
                                    type="text"
                                    name="nome"
                                    class="form-control"
                                    value="<?= $produto['nome'] ?? '' ?>"
                                    required
                                    placeholder="Ex: IPA Americana 355ml">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">SKU *</label>
                                <input
                                    type="text"
                                    name="sku"
                                    class="form-control"
                                    value="<?= $produto['sku'] ?? '' ?>"
                                    required
                                    placeholder="Ex: IPA-355">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descrição</label>
                        <textarea
                            name="descricao"
                            class="form-control"
                            rows="2"
                            placeholder="Descrição do produto"><?= $produto['descricao'] ?? '' ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Tipo de Embalagem *</label>
                                <select name="tipo" class="form-control form-select" required>
                                    <option value="">Selecione</option>
                                    <option value="garrafa" <?= ($produto['tipo'] ?? '') == 'garrafa' ? 'selected' : '' ?>>Garrafa</option>
                                    <option value="lata" <?= ($produto['tipo'] ?? '') == 'lata' ? 'selected' : '' ?>>Lata</option>
                                    <option value="barril" <?= ($produto['tipo'] ?? '') == 'barril' ? 'selected' : '' ?>>Barril</option>
                                    <option value="growler" <?= ($produto['tipo'] ?? '') == 'growler' ? 'selected' : '' ?>>Growler</option>
                                </select>
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Volume (ml) *</label>
                                <input
                                    type="number"
                                    name="volume_embalagem"
                                    class="form-control"
                                    value="<?= $produto['volume_embalagem'] ?? '' ?>"
                                    required
                                    placeholder="Ex: 355">
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Código de Barras</label>
                                <input
                                    type="text"
                                    name="codigo_barras"
                                    class="form-control"
                                    value="<?= $produto['codigo_barras'] ?? '' ?>"
                                    placeholder="Ex: 7898765432109">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h4 style="color: #2c3e50; margin-bottom: 1rem;">Origem da Produção</h4>

                    <div class="form-group">
                        <label class="form-label">Lote de Produção *</label>
                        <select name="lote_producao_id" id="loteSelect" class="form-control form-select" required <?= $isEdit ? 'disabled' : '' ?>>
                            <option value="">Selecione um lote</option>
                            <?php foreach ($lotes as $lote): ?>
                                <option
                                    value="<?= $lote['id'] ?>"
                                    data-custo="<?= $lote['custo_total'] ?? 0 ?>"
                                    data-volume="<?= $lote['quantidade_produzida'] ?>"
                                    <?= ($produto['lote_producao_id'] ?? '') == $lote['id'] ? 'selected' : '' ?>>
                                    <?= $lote['codigo_lote'] ?> - <?= $lote['receita_nome'] ?> (<?= formatQuantity($lote['quantidade_produzida'], 'L') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="lote_producao_id" value="<?= $produto['lote_producao_id'] ?>">
                        <?php endif; ?>
                    </div>

                    <hr>

                    <h4 style="color: #2c3e50; margin-bottom: 1rem;">Envase e Validade</h4>

                    <div class="row">
                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Data de Envase *</label>
                                <input
                                    type="date"
                                    name="data_envase"
                                    id="dataEnvaseInput"
                                    class="form-control"
                                    value="<?= $produto['data_envase'] ?? date('Y-m-d') ?>"
                                    required>
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Validade (meses) *</label>
                                <input
                                    type="number"
                                    name="validade_meses"
                                    id="validadeMesesInput"
                                    class="form-control"
                                    value="<?= $produto['validade_meses'] ?? '12' ?>"
                                    required>
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Data de Validade</label>
                                <input
                                    type="date"
                                    name="data_validade"
                                    id="dataValidadeInput"
                                    class="form-control"
                                    value="<?= $produto['data_validade'] ?? '' ?>"
                                    readonly
                                    style="background: #f8f9fa;">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h4 style="color: #2c3e50; margin-bottom: 1rem;">Estoque e Precificação</h4>

                    <div class="row">
                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Quantidade em Estoque *</label>
                                <input
                                    type="number"
                                    name="estoque_atual"
                                    class="form-control"
                                    value="<?= $produto['estoque_atual'] ?? '' ?>"
                                    required
                                    placeholder="0">
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Estoque Mínimo *</label>
                                <input
                                    type="number"
                                    name="estoque_minimo"
                                    class="form-control"
                                    value="<?= $produto['estoque_minimo'] ?? '' ?>"
                                    required
                                    placeholder="0">
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Custo de Produção (R$)</label>
                                <input
                                    type="number"
                                    name="custo_producao"
                                    id="custoProducaoInput"
                                    class="form-control"
                                    value="<?= $produto['custo_producao'] ?? '' ?>"
                                    step="0.01"
                                    readonly
                                    style="background: #f8f9fa;">
                                <small style="color: #6c757d;">Calculado automaticamente</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Preço de Venda (R$) *</label>
                                <input
                                    type="number"
                                    name="preco_venda"
                                    id="precoVendaInput"
                                    class="form-control"
                                    value="<?= $produto['preco_venda'] ?? '' ?>"
                                    step="0.01"
                                    required
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Margem de Lucro</label>
                                <input
                                    type="text"
                                    id="margemLucroDisplay"
                                    class="form-control"
                                    readonly
                                    style="background: #f8f9fa; font-weight: bold;"
                                    placeholder="0%">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input
                                type="checkbox"
                                name="ativo"
                                value="1"
                                <?= ($produto['ativo'] ?? true) ? 'checked' : '' ?>>
                            <span class="form-label" style="margin: 0;">Produto ativo</span>
                        </label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center" style="margin-top: 2rem;">
                        <a href="/produtos" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <?= $isEdit ? 'Atualizar' : 'Cadastrar' ?> Produto
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
                    <strong>Custo Unitário:</strong>
                    <h4 id="resumoCusto" style="color: #e74c3c; margin: 0.5rem 0;">
                        R$ 0,00
                    </h4>
                </div>

                <div class="mb-3">
                    <strong>Preço de Venda:</strong>
                    <h3 id="resumoPreco" style="color: #27ae60; margin: 0.5rem 0;">
                        R$ 0,00
                    </h3>
                </div>

                <div>
                    <strong>Margem:</strong>
                    <h4 id="resumoMargem" style="color: #3498db; margin: 0.5rem 0;">
                        0%
                    </h4>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Instruções</h3>
            </div>
            <div class="card-body">
                <ul style="line-height: 1.8; color: #6c757d;">
                    <li>Selecione um lote de produção finalizado</li>
                    <li>O custo é calculado com base no lote</li>
                    <li>A validade é calculada automaticamente</li>
                    <li>Defina um preço de venda adequado</li>
                    <li>Monitore a margem de lucro</li>
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
                    Excluir este produto removerá todo o histórico relacionado.
                </p>
                <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    onclick="confirmarExclusao(<?= $produto['id'] ?>)">
                    Excluir Produto
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Calcular custo de produção ao selecionar lote
document.getElementById('loteSelect')?.addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const custoLote = parseFloat(option.getAttribute('data-custo')) || 0;
    const volumeLote = parseFloat(option.getAttribute('data-volume')) || 0;
    const volumeEmbalagem = parseFloat(document.querySelector('input[name="volume_embalagem"]')?.value) || 0;

    if (custoLote > 0 && volumeLote > 0 && volumeEmbalagem > 0) {
        // Custo por litro
        const custoPorLitro = custoLote / volumeLote;
        // Custo por embalagem (converter ml para litros)
        const custoUnitario = custoPorLitro * (volumeEmbalagem / 1000);
        document.getElementById('custoProducaoInput').value = custoUnitario.toFixed(2);
        calcularMargem();
    }
});

// Calcular data de validade
function calcularValidade() {
    const dataEnvase = document.getElementById('dataEnvaseInput').value;
    const validadeMeses = parseInt(document.getElementById('validadeMesesInput').value) || 0;

    if (dataEnvase && validadeMeses > 0) {
        const data = new Date(dataEnvase);
        data.setMonth(data.getMonth() + validadeMeses);
        document.getElementById('dataValidadeInput').value = data.toISOString().split('T')[0];
    }
}

document.getElementById('dataEnvaseInput')?.addEventListener('change', calcularValidade);
document.getElementById('validadeMesesInput')?.addEventListener('input', calcularValidade);

// Calcular margem de lucro
function calcularMargem() {
    const custo = parseFloat(document.getElementById('custoProducaoInput').value) || 0;
    const preco = parseFloat(document.getElementById('precoVendaInput').value) || 0;

    if (custo > 0 && preco > 0) {
        const margem = ((preco - custo) / custo) * 100;
        document.getElementById('margemLucroDisplay').value = margem.toFixed(2) + '%';
        document.getElementById('resumoCusto').textContent = 'R$ ' + custo.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        document.getElementById('resumoPreco').textContent = 'R$ ' + preco.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        document.getElementById('resumoMargem').textContent = margem.toFixed(2) + '%';
    } else {
        document.getElementById('margemLucroDisplay').value = '0%';
    }
}

document.getElementById('precoVendaInput')?.addEventListener('input', calcularMargem);

// Validação do formulário
document.getElementById('formProduto')?.addEventListener('submit', function(e) {
    const preco = parseFloat(document.getElementById('precoVendaInput').value);
    const custo = parseFloat(document.getElementById('custoProducaoInput').value);

    if (preco <= 0) {
        e.preventDefault();
        alert('O preço de venda deve ser maior que zero.');
        return false;
    }

    if (preco < custo) {
        if (!confirm('O preço de venda é menor que o custo de produção. Deseja continuar mesmo assim?')) {
            e.preventDefault();
            return false;
        }
    }

    return true;
});

<?php if ($isEdit): ?>
function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.')) {
        window.location.href = '/produtos/delete/' + id;
    }
}
<?php endif; ?>

// Inicializar ao carregar
<?php if ($isEdit): ?>
    calcularValidade();
    calcularMargem();
<?php endif; ?>
</script>

<?php include 'app/views/layouts/footer.php'; ?>
