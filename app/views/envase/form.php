<?php
$pageTitle = ($envase ? 'Editar' : 'Novo') . ' Envase - Atomos';
$activeMenu = 'envase';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><?= $envase ? 'Editar' : 'Novo' ?> Envase</h1>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/envase/store">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">Lote de Produção *</label>
                        <select name="lote_id" class="form-control form-select" required>
                            <option value="">Selecione o lote</option>
                            <?php foreach ($lotes as $lote): ?>
                                <option value="<?= $lote['id'] ?>">
                                    <?= htmlspecialchars($lote['codigo']) ?> -
                                    <?= htmlspecialchars($lote['receita_nome']) ?>
                                    (<?= htmlspecialchars($lote['estilo']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Data do Envase</label>
                        <input type="date" name="data_envase" class="form-control"
                               value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Observações</label>
                <textarea name="observacoes" class="form-control" rows="3"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <?= icon('save', '', 18) ?> Salvar
                </button>
                <a href="/envase" class="btn btn-secondary">
                    <?= icon('back', '', 18) ?> Voltar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>