<?php
$isEdit = isset($insumo) && !empty($insumo['id']);
$pageTitle = ($isEdit ? 'Editar' : 'Novo') . ' Insumo - ' . APP_NAME;
$activeMenu = 'insumos';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title"><?= $isEdit ? 'Editar' : 'Novo' ?> Insumo</h1>
    <div class="page-actions">
        <a href="/insumos" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dados do Insumo</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/insumos/<?= $isEdit ? 'update' : 'store' ?>" id="formInsumo">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $insumo['id'] ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Código Interno</label>
                                <input
                                    type="text"
                                    name="codigo_interno"
                                    class="form-control"
                                    value="<?= $insumo['codigo_interno'] ?? '' ?>"
                                    placeholder="Ex: MAL-001">
                                <small style="color: #6c757d;">Código interno do insumo (opcional)</small>
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Categoria *</label>
                                <select name="categoria_id" class="form-control form-select" required>
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"
                                            <?= ($insumo['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= $cat['nome'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nome *</label>
                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            value="<?= $insumo['nome'] ?? '' ?>"
                            required
                            placeholder="Ex: Malte Pilsen">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descrição</label>
                        <textarea
                            name="descricao"
                            class="form-control"
                            rows="3"
                            placeholder="Informações adicionais sobre o insumo"><?= $insumo['descricao'] ?? '' ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col col-3">
                            <div class="form-group">
                                <label class="form-label">Tipo *</label>
                                <select name="tipo" class="form-control form-select" required>
                                    <option value="">Selecione</option>
                                    <option value="malte" <?= ($insumo['tipo'] ?? '') == 'malte' ? 'selected' : '' ?>>Malte</option>
                                    <option value="lupulo" <?= ($insumo['tipo'] ?? '') == 'lupulo' ? 'selected' : '' ?>>Lúpulo</option>
                                    <option value="levedura" <?= ($insumo['tipo'] ?? '') == 'levedura' ? 'selected' : '' ?>>Levedura</option>
                                    <option value="adjunto" <?= ($insumo['tipo'] ?? '') == 'adjunto' ? 'selected' : '' ?>>Adjunto</option>
                                    <option value="aditivo" <?= ($insumo['tipo'] ?? '') == 'aditivo' ? 'selected' : '' ?>>Aditivo</option>
                                    <option value="embalagem" <?= ($insumo['tipo'] ?? '') == 'embalagem' ? 'selected' : '' ?>>Embalagem</option>
                                    <option value="outros" <?= ($insumo['tipo'] ?? '') == 'outros' ? 'selected' : '' ?>>Outros</option>
                                </select>
                            </div>
                        </div>

                        <div class="col col-3">
                            <div class="form-group">
                                <label class="form-label">Unidade de Medida *</label>
                                <select name="unidade_medida" class="form-control form-select" required>
                                    <option value="">Selecione</option>
                                    <option value="kg" <?= ($insumo['unidade_medida'] ?? '') == 'kg' ? 'selected' : '' ?>>Quilograma (kg)</option>
                                    <option value="g" <?= ($insumo['unidade_medida'] ?? '') == 'g' ? 'selected' : '' ?>>Grama (g)</option>
                                    <option value="l" <?= ($insumo['unidade_medida'] ?? '') == 'l' ? 'selected' : '' ?>>Litro (L)</option>
                                    <option value="ml" <?= ($insumo['unidade_medida'] ?? '') == 'ml' ? 'selected' : '' ?>>Mililitro (ml)</option>
                                    <option value="un" <?= ($insumo['unidade_medida'] ?? '') == 'un' ? 'selected' : '' ?>>Unidade (un)</option>
                                    <option value="m" <?= ($insumo['unidade_medida'] ?? '') == 'm' ? 'selected' : '' ?>>Metro (m)</option>
                                    <option value="cm" <?= ($insumo['unidade_medida'] ?? '') == 'cm' ? 'selected' : '' ?>>Centímetro (cm)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col col-4">
                            <div class="form-group">
                                <label class="form-label">Estoque Mínimo *</label>
                                <input
                                    type="number"
                                    name="estoque_minimo"
                                    class="form-control"
                                    value="<?= $insumo['estoque_minimo'] ?? '' ?>"
                                    step="0.001"
                                    required
                                    placeholder="0.000">
                                <small style="color: #6c757d;">Quantidade mínima em estoque</small>
                            </div>
                        </div>

                        <div class="col col-3">
                            <div class="form-group">
                                <label class="form-label">Estoque Atual</label>
                                <input
                                    type="number"
                                    name="estoque_atual"
                                    class="form-control"
                                    value="<?= $insumo['estoque_atual'] ?? '0' ?>"
                                    step="0.001"
                                    <?= $isEdit ? 'readonly' : '' ?>
                                    placeholder="0.000">
                                <?php if ($isEdit): ?>
                                    <small style="color: #6c757d;">Use "Registrar Entrada" para adicionar estoque</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Fornecedor Principal</label>
                                <select name="fornecedor_principal_id" class="form-control form-select">
                                    <option value="">Selecione um fornecedor</option>
                                    <?php foreach ($fornecedores as $forn): ?>
                                        <option value="<?= $forn['id'] ?>"
                                            <?= ($insumo['fornecedor_principal_id'] ?? '') == $forn['id'] ? 'selected' : '' ?>>
                                            <?= $forn['nome'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Código EAN</label>
                                <input
                                    type="text"
                                    name="ean"
                                    class="form-control"
                                    value="<?= $insumo['ean'] ?? '' ?>"
                                    placeholder="Ex: 7891234567890">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observações</label>
                        <textarea
                            name="observacoes"
                            class="form-control"
                            rows="2"
                            placeholder="Observações adicionais"><?= $insumo['observacoes'] ?? '' ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center" style="margin-top: 2rem;">
                        <a href="/insumos" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <?= $isEdit ? 'Atualizar' : 'Cadastrar' ?> Insumo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Instruções</h3>
            </div>
            <div class="card-body">
                <ul style="line-height: 1.8; color: #6c757d;">
                    <li>Preencha todos os campos obrigatórios (*)</li>
                    <li>O código deve ser único para cada insumo</li>
                    <li>O estoque mínimo é usado para alertas</li>
                    <li>A localização ajuda na organização física</li>
                    <?php if (!$isEdit): ?>
                        <li>Após cadastrar, use "Registrar Entrada" para adicionar estoque inicial</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <?php if ($isEdit): ?>
        <div class="card" style="border-color: #e74c3c;">
            <div class="card-header" style="background: #fee; border-bottom-color: #e74c3c;">
                <h3 class="card-title" style="color: #e74c3c;">Zona de Perigo</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Excluir este insumo removerá todo o histórico relacionado.
                </p>
                <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    onclick="confirmarExclusao(<?= $insumo['id'] ?>)">
                    Excluir Insumo
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Validação do formulário
document.getElementById('formInsumo').addEventListener('submit', function(e) {
    const nome = document.querySelector('input[name="nome"]').value.trim();
    const tipo = document.querySelector('select[name="tipo"]').value;
    const estoqueMinimo = parseFloat(document.querySelector('input[name="estoque_minimo"]').value);

    if (!nome || !tipo) {
        e.preventDefault();
        alert('Por favor, preencha todos os campos obrigatórios.');
        return false;
    }

    if (estoqueMinimo < 0) {
        e.preventDefault();
        alert('O estoque mínimo não pode ser negativo.');
        return false;
    }

    return true;
});

<?php if ($isEdit): ?>
function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir este insumo? Esta ação não pode ser desfeita.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/insumos/delete';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;

        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}
<?php endif; ?>
</script>

<?php include 'app/views/layouts/footer.php'; ?>
