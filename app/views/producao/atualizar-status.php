<?php
$pageTitle = 'Atualizar Status do Lote - ' . APP_NAME;
$activeMenu = 'producao';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">Atualizar Status do Lote</h1>
    <div class="page-actions">
        <a href="/producao/viewProducao?id=<?= $lote['id'] ?>" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações do Lote</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Código do Lote:</strong>
                    <p><?= $lote['codigo'] ?? 'N/A' ?></p>
                </div>
                <div class="mb-3">
                    <strong>Status Atual:</strong>
                    <p>
                        <?php
                        $statusClass = match($lote['status']) {
                            'planejado' => 'secondary',
                            'em_andamento' => 'info',
                            'fermentacao' => 'warning',
                            'maturacao' => 'warning',
                            'finalizado' => 'success',
                            'cancelado' => 'danger',
                            default => 'secondary'
                        };
                        ?>
                        <span class="badge badge-<?= $statusClass ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                            <?= ucfirst(str_replace('_', ' ', $lote['status'])) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Atualizar Status</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/producao/atualizar-status">
                    <input type="hidden" name="id" value="<?= $lote['id'] ?>">

                    <div class="form-group">
                        <label class="form-label">Novo Status: *</label>
                        <select name="novo_status" class="form-control form-select" required>
                            <option value="">Selecione...</option>
                            <option value="planejado" <?= $lote['status'] == 'planejado' ? 'selected' : '' ?>>Planejado</option>
                            <option value="em_producao" <?= $lote['status'] == 'em_producao' ? 'selected' : '' ?>>Em Produção</option>
                            <option value="fermentando" <?= $lote['status'] == 'fermentando' ? 'selected' : '' ?>>Fermentando</option>
                            <option value="maturando" <?= $lote['status'] == 'maturando' ? 'selected' : '' ?>>Maturando</option>
                            <option value="finalizado" <?= $lote['status'] == 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                            <option value="cancelado" <?= $lote['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Atualizar Status</button>
                        <a href="/producao/viewProducao?id=<?= $lote['id'] ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
