<?php
$pageTitle = 'Envase - Atomos';
$activeMenu = 'envase';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">🛢️ Envase de Barris</h1>
            <p class="page-subtitle">Gerenciamento de envase de lotes em barris</p>
        </div>
        <div>
            <a href="/envase/create" class="btn btn-primary">
                <?= icon('add', '', 18) ?> Novo Envase
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/envase" class="row">
            <div class="col-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-control form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="em_processo" <?= $status_filter === 'em_processo' ? 'selected' : '' ?>>Em Processo</option>
                    <option value="finalizado" <?= $status_filter === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                    <option value="cancelado" <?= $status_filter === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Listagem -->
<div class="card">
    <div class="card-body">
        <?php if (empty($envases)): ?>
            <p class="text-center">Nenhum envase encontrado.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Lote</th>
                        <th>Estilo</th>
                        <th>Data Envase</th>
                        <th>Total Barris</th>
                        <th>Total Litros</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($envases as $envase): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars('ENV-' . $envase['id']) ?></strong></td>
                            <td><?= htmlspecialchars($envase['lote_codigo'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($envase['estilo'] ?? '-') ?></td>
                            <td><?= formatDate($envase['data_envase'] ?? null) ?></td>
                            <td><?= count($barrilModel->getByEnvase($envase['id'])) ?></td>
                            <td><?= formatQuantity($envase['quantidade_litros'], 'L') ?></td>
                            <td>
                                <?php
                                $statusClass = [
                                    'envasado' => 'badge-warning',
                                    'em_estoque' => 'badge-info',
                                    'baixado' => 'badge-success'
                                ];
                                $statusLabel = [
                                    'envasado' => 'Envasado',
                                    'em_estoque' => 'Em Estoque',
                                    'baixado' => 'Baixado'
                                ];
                                ?>
                                <span class="badge <?= $statusClass[$envase['status']] ?? 'badge-secondary' ?>">
                                    <?= $statusLabel[$envase['status']] ?? $envase['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?= iconButton('view', '/envase/viewEnvase?id=' . $envase['id'], 'primary', 'Visualizar') ?>
                                <?php if ($envase['status'] === 'envasado'): ?>
                                    <form method="POST" action="/envase/delete" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este envase? Esta ação não pode ser desfeita.')">
                                        <input type="hidden" name="id" value="<?= $envase['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                            <?= icon('delete', '', 16) ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>