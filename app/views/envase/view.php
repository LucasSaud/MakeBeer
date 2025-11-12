<?php
$pageTitle = 'Envase - Atomos';
$activeMenu = 'envase';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Envase: <?= htmlspecialchars('ENV-' . $envase['id']) ?></h1>
            <p class="page-subtitle">Detalhes do processo de envase</p>
        </div>
        <div>
            <a href="/envase" class="btn btn-secondary">
                <?= icon('arrow_back', '', 18) ?> Voltar
            </a>
        </div>
    </div>
</div>

<!-- Informações do Envase -->
<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações do Envase</h3>
            </div>
            <div class="card-body">
                <p><strong>Código:</strong> <?= htmlspecialchars('ENV-' . $envase['id']) ?></p>
                <p><strong>Lote:</strong> <?= htmlspecialchars($envase['lote_codigo'] ?? '-') ?></p>
                <p><strong>Estilo:</strong> <?= htmlspecialchars($envase['estilo'] ?? '-') ?></p>
                <p><strong>Data de Envase:</strong> <?= formatDate($envase['data_envase'] ?? null) ?></p>
                <p><strong>Status:</strong> 
                    <?php
                    $statusClass = [
                        'envasado' => 'warning',
                        'em_estoque' => 'info',
                        'baixado' => 'success'
                    ];
                    $statusLabel = [
                        'envasado' => 'Envasado',
                        'em_estoque' => 'Em Estoque',
                        'baixado' => 'Baixado'
                    ];
                    ?>
                    <span class="badge badge-<?= $statusClass[$envase['status']] ?? 'secondary' ?>">
                        <?= $statusLabel[$envase['status']] ?? $envase['status'] ?>
                    </span>
                </p>
                <p><strong>Responsável:</strong> <?= htmlspecialchars($envase['responsavel_nome'] ?? '-') ?></p>
                <p><strong>Observações:</strong> <?= htmlspecialchars($envase['observacoes'] ?? '-') ?></p>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Totais</h3>
            </div>
            <div class="card-body">
                <p><strong>Total de Barris:</strong> <?= count($envase['barris'] ?? []) ?></p>
                <p><strong>Total de Litros:</strong> <?= formatQuantity(array_sum(array_column($envase['barris'] ?? [], 'quantidade_litros')), 'L') ?></p>
                <p><strong>Volume do Lote:</strong> <?= formatQuantity($envase['lote_volume'] ?? 0, 'L') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Adicionar Barril -->
<?php if ($envase['status'] === 'envasado'): ?>
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Adicionar Barril</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/envase/adicionarBarril" class="row">
            <input type="hidden" name="envase_id" value="<?= $envase['id'] ?>">
            <div class="col-3">
                <label class="form-label">Número do Barril</label>
                <input type="number" name="numero_barril" class="form-control" required
                       value="<?= count($envase['barris'] ?? []) + 1 ?>">
            </div>
            <div class="col-3">
                <label class="form-label">Quantidade (Litros)</label>
                <input type="number" step="0.01" name="quantidade_litros" class="form-control" required min="50">
                <small class="form-text text-muted">Mínimo 50 litros</small>
            </div>
            <div class="col-4">
                <label class="form-label">Observações</label>
                <input type="text" name="observacoes" class="form-control">
            </div>
            <div class="col-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><?= icon('add', '', 18) ?> Adicionar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Lista de Barris -->
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Barris Envasados (<?= count($envase['barris'] ?? []) ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($envase['barris'])): ?>
            <p class="text-center">Nenhum barril adicionado ainda.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código do Barril</th>
                        <th>Quantidade (L)</th>
                        <th>Data Envase</th>
                        <th>Status</th>
                        <th>Observações</th>
                        <?php if ($envase['status'] === 'envasado'): ?>
                            <th>Ações</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($envase['barris'] as $barril): ?>
                        <tr>
                            <td><?= $barril['numero_barril'] ?></td>
                            <td><strong><?= htmlspecialchars($barril['codigo_barril']) ?></strong></td>
                            <td><?= formatQuantity($barril['quantidade_litros'], 'L') ?></td>
                            <td><?= formatDate($barril['data_entrada']) ?></td>
                            <td><span class="badge badge-<?= $barril['status'] === 'disponivel' ? 'success' : 'secondary' ?>"><?= ucfirst($barril['status']) ?></span></td>
                            <td><?= htmlspecialchars($barril['observacoes'] ?? '-') ?></td>
                            <?php if ($envase['status'] === 'envasado'): ?>
                                <td>
                                    <form method="POST" action="/envase/removerBarril" style="display: inline;" onsubmit="return confirm('Remover este barril?')">
                                        <input type="hidden" name="barril_id" value="<?= $barril['id'] ?>">
                                        <input type="hidden" name="envase_id" value="<?= $envase['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>