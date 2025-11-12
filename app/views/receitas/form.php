<?php
$isEdit = isset($receita) && !empty($receita['id']);
$pageTitle = ($isEdit ? 'Editar' : 'Nova') . ' Receita - ' . APP_NAME;
$activeMenu = 'receitas';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title"><?= $isEdit ? 'Editar' : 'Nova' ?> Receita</h1>
    <div class="page-actions">
        <a href="/receitas" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<form method="POST" action="/receitas/<?= $isEdit ? 'update/' . $receita['id'] : 'store' ?>" id="formReceita">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

    <div class="row">
        <div class="col col-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Informações Básicas</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Nome da Receita *</label>
                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            value="<?= $receita['nome'] ?? '' ?>"
                            required
                            placeholder="Ex: IPA Americana">
                    </div>

                    <div class="row">
                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Estilo *</label>
                                <select name="estilo" class="form-control form-select" required>
                                    <option value="">Selecione</option>
                                    <option value="ale" <?= ($receita['estilo'] ?? '') == 'ale' ? 'selected' : '' ?>>Ale</option>
                                    <option value="lager" <?= ($receita['estilo'] ?? '') == 'lager' ? 'selected' : '' ?>>Lager</option>
                                    <option value="stout" <?= ($receita['estilo'] ?? '') == 'stout' ? 'selected' : '' ?>>Stout</option>
                                    <option value="porter" <?= ($receita['estilo'] ?? '') == 'porter' ? 'selected' : '' ?>>Porter</option>
                                    <option value="ipa" <?= ($receita['estilo'] ?? '') == 'ipa' ? 'selected' : '' ?>>IPA</option>
                                    <option value="pilsen" <?= ($receita['estilo'] ?? '') == 'pilsen' ? 'selected' : '' ?>>Pilsen</option>
                                    <option value="weiss" <?= ($receita['estilo'] ?? '') == 'weiss' ? 'selected' : '' ?>>Weiss</option>
                                    <option value="outro" <?= ($receita['estilo'] ?? '') == 'outro' ? 'selected' : '' ?>>Outro</option>
                                </select>
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Volume Batch (Litros)</label>
                                <input
                                    type="number"
                                    name="volume_batch"
                                    class="form-control"
                                    value="<?= $receita['volume_batch'] ?? '' ?>"
                                    step="0.01"
                                    placeholder="Ex: 100">
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Tempo de Fermentação (dias)</label>
                                <input
                                    type="number"
                                    name="tempo_fermentacao"
                                    class="form-control"
                                    value="<?= $receita['tempo_fermentacao'] ?? '' ?>"
                                    placeholder="Ex: 14">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descrição</label>
                        <textarea
                            name="descricao"
                            class="form-control"
                            rows="3"
                            placeholder="Descrição da cerveja"><?= $receita['descricao'] ?? '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instruções de Produção</label>
                        <textarea
                            name="instrucoes"
                            class="form-control"
                            rows="5"
                            placeholder="Passo a passo da produção"><?= $receita['instrucoes'] ?? '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input
                                type="checkbox"
                                name="ativo"
                                value="1"
                                <?= ($receita['ativo'] ?? true) ? 'checked' : '' ?>>
                            <span class="form-label" style="margin: 0;">Receita ativa</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Ingredientes -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ingredientes</h3>
                </div>
                <div class="card-body">
                    <div id="ingredientes-container">
                        <?php if ($isEdit && !empty($receita['ingredientes'])): ?>
                            <?php foreach ($receita['ingredientes'] as $index => $ing): ?>
                                <div class="ingrediente-item row mb-3" style="border-bottom: 1px solid #e9ecef; padding-bottom: 1rem;">
                                    <div class="col col-5">
                                        <label class="form-label">Insumo</label>
                                        <select name="ingrediente_insumo_id[]" class="form-control form-select" required>
                                            <option value="">Selecione um insumo</option>
                                            <?php foreach ($insumos as $insumo): ?>
                                                <option
                                                    value="<?= $insumo['id'] ?>"
                                                    data-unidade="<?= $insumo['unidade_medida'] ?>"
                                                    <?= $ing['insumo_id'] == $insumo['id'] ? 'selected' : '' ?>>
                                                    <?= $insumo['nome'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col col-2">
                                        <label class="form-label">Quantidade</label>
                                        <input
                                            type="number"
                                            name="ingrediente_quantidade[]"
                                            class="form-control"
                                            value="<?= $ing['quantidade'] ?>"
                                            step="0.001"
                                            required
                                            placeholder="0.000">
                                    </div>
                                    <div class="col col-2">
                                        <label class="form-label">Unidade</label>
                                        <select name="ingrediente_unidade[]" class="form-control form-select" required>
                                            <option value="kg" <?= $ing['unidade'] == 'kg' ? 'selected' : '' ?>>kg</option>
                                            <option value="g" <?= $ing['unidade'] == 'g' ? 'selected' : '' ?>>g</option>
                                            <option value="l" <?= $ing['unidade'] == 'l' ? 'selected' : '' ?>>l</option>
                                            <option value="ml" <?= $ing['unidade'] == 'ml' ? 'selected' : '' ?>>ml</option>
                                            <option value="un" <?= $ing['unidade'] == 'un' ? 'selected' : '' ?>>un</option>
                                        </select>
                                    </div>
                                    <div class="col col-2">
                                        <label class="form-label">Fase</label>
                                        <select name="ingrediente_fase[]" class="form-control form-select" required>
                                            <option value="mostura" <?= $ing['fase'] == 'mostura' ? 'selected' : '' ?>>Mostura</option>
                                            <option value="fervura" <?= $ing['fase'] == 'fervura' ? 'selected' : '' ?>>Fervura</option>
                                            <option value="fermentacao" <?= $ing['fase'] == 'fermentacao' ? 'selected' : '' ?>>Fermentação</option>
                                            <option value="maturacao" <?= $ing['fase'] == 'maturacao' ? 'selected' : '' ?>>Maturação</option>
                                            <option value="envase" <?= $ing['fase'] == 'envase' ? 'selected' : '' ?>>Envase</option>
                                        </select>
                                    </div>
                                    <div class="col col-1">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removerIngrediente(this)">X</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="btn btn-success btn-sm" onclick="adicionarIngrediente()">
                        + Adicionar Ingrediente
                    </button>
                </div>
            </div>
        </div>

        <div class="col col-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Características</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Densidade Inicial</label>
                                <input
                                    type="number"
                                    name="densidade_inicial"
                                    class="form-control"
                                    value="<?= $receita['densidade_inicial'] ?? '' ?>"
                                    step="0.001"
                                    placeholder="Ex: 1.050">
                            </div>
                        </div>
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Densidade Final</label>
                                <input
                                    type="number"
                                    name="densidade_final"
                                    class="form-control"
                                    value="<?= $receita['densidade_final'] ?? '' ?>"
                                    step="0.001"
                                    placeholder="Ex: 1.010">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">IBU</label>
                                <input
                                    type="number"
                                    name="ibu"
                                    class="form-control"
                                    value="<?= $receita['ibu'] ?? '' ?>"
                                    placeholder="Ex: 45">
                            </div>
                        </div>
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">SRM</label>
                                <input
                                    type="number"
                                    name="srm"
                                    class="form-control"
                                    value="<?= $receita['srm'] ?? '' ?>"
                                    placeholder="Ex: 12">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">ABV (%)</label>
                                <input
                                    type="number"
                                    name="abv"
                                    class="form-control"
                                    value="<?= $receita['abv'] ?? '' ?>"
                                    step="0.1"
                                    placeholder="Ex: 5.5">
                            </div>
                        </div>
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Temperatura (°C)</label>
                                <input
                                    type="number"
                                    name="temperatura_fermentacao"
                                    class="form-control"
                                    value="<?= $receita['temperatura_fermentacao'] ?? '' ?>"
                                    step="0.1"
                                    placeholder="Ex: 18">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Instruções</h3>
                </div>
                <div class="card-body">
                    <ul style="line-height: 1.8; color: #6c757d;">
                        <li>Preencha todos os campos obrigatórios (*)</li>
                        <li>Adicione todos os ingredientes necessários</li>
                        <li>As quantidades devem ser para o volume informado</li>
                        <li>O custo é calculado automaticamente</li>
                        <li>Use instruções detalhadas para facilitar a produção</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <?= $isEdit ? 'Atualizar' : 'Cadastrar' ?> Receita
                    </button>
                    <a href="/receitas" class="btn btn-secondary" style="width: 100%; margin-top: 0.5rem;">
                        Cancelar
                    </a>
                </div>
            </div>

            <?php if ($isEdit): ?>
            <div class="card" style="border-color: #e74c3c; margin-top: 1rem;">
                <div class="card-header" style="background: #fee; border-bottom-color: #e74c3c;">
                    <h3 class="card-title" style="color: #e74c3c;">Zona de Perigo</h3>
                </div>
                <div class="card-body">
                    <p style="color: #6c757d; margin-bottom: 1rem;">
                        Excluir esta receita removerá todo o histórico relacionado.
                    </p>
                    <button
                        type="button"
                        class="btn btn-danger btn-sm"
                        onclick="confirmarExclusao(<?= $receita['id'] ?>)">
                        Excluir Receita
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
let ingredienteIndex = <?= $isEdit && !empty($receita['ingredientes']) ? count($receita['ingredientes']) : 0 ?>;
const insumos = <?= json_encode($insumos) ?>;

function adicionarIngrediente() {
    const container = document.getElementById('ingredientes-container');
    const div = document.createElement('div');
    div.className = 'ingrediente-item row mb-3';
    div.style.borderBottom = '1px solid #e9ecef';
    div.style.paddingBottom = '1rem';

    let optionsHtml = '<option value="">Selecione um insumo</option>';
    insumos.forEach(insumo => {
        optionsHtml += `<option value="${insumo.id}" data-unidade="${insumo.unidade_medida}">${insumo.nome}</option>`;
    });

    div.innerHTML = `
        <div class="col col-5">
            <label class="form-label">Insumo</label>
            <select name="ingrediente_insumo_id[]" class="form-control form-select" required>
                ${optionsHtml}
            </select>
        </div>
        <div class="col col-2">
            <label class="form-label">Quantidade</label>
            <input
                type="number"
                name="ingrediente_quantidade[]"
                class="form-control"
                step="0.001"
                required
                placeholder="0.000">
        </div>
        <div class="col col-2">
            <label class="form-label">Unidade</label>
            <select name="ingrediente_unidade[]" class="form-control form-select" required>
                <option value="kg">kg</option>
                <option value="g">g</option>
                <option value="l">l</option>
                <option value="ml">ml</option>
                <option value="un">un</option>
            </select>
        </div>
        <div class="col col-2">
            <label class="form-label">Fase</label>
            <select name="ingrediente_fase[]" class="form-control form-select" required>
                <option value="mostura">Mostura</option>
                <option value="fervura">Fervura</option>
                <option value="fermentacao">Fermentação</option>
                <option value="maturacao">Maturação</option>
                <option value="envase">Envase</option>
            </select>
        </div>
        <div class="col col-1">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-danger btn-sm" onclick="removerIngrediente(this)">X</button>
        </div>
    `;

    container.appendChild(div);
    ingredienteIndex++;
}

function removerIngrediente(button) {
    button.closest('.ingrediente-item').remove();
}

// Validação do formulário
document.getElementById('formReceita').addEventListener('submit', function(e) {
    const nome = document.querySelector('input[name="nome"]').value.trim();
    const volumeBatch = document.querySelector('input[name="volume_batch"]').value;

    if (!nome) {
        e.preventDefault();
        alert('Por favor, preencha o nome da receita.');
        return false;
    }

    if (volumeBatch && (isNaN(volumeBatch) || parseFloat(volumeBatch) <= 0)) {
        e.preventDefault();
        alert('O volume batch deve ser um número maior que zero ou deixado em branco.');
        return false;
    }

    return true;
});

<?php if ($isEdit): ?>
function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir esta receita? Esta ação não pode ser desfeita.')) {
        window.location.href = '/receitas/delete/' + id;
    }
}
<?php endif; ?>
</script>

<?php include 'app/views/layouts/footer.php'; ?>