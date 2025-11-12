<?php
$pageTitle = 'Estoque de Barris - Atomos';
$activeMenu = 'estoque';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">🏭 Estoque de Barris</h1>
            <p class="page-subtitle">Controle de barris na câmara fria</p>
        </div>
        <div>
            <a href="/estoque/relatorio" class="btn btn-secondary">
                <?= icon('relatorios', '', 18) ?> Relatório
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/estoque" class="row">
            <div class="col-3">
                <label class="form-label">Lote</label>
                <input type="text" name="lote" class="form-control" value="<?= htmlspecialchars($filtros['lote_codigo'] ?? '') ?>" placeholder="Código do lote">
            </div>
            <div class="col-3">
                <label class="form-label">Estilo</label>
                <select name="estilo" class="form-control form-select">
                    <option value="">Todos</option>
                    <?php foreach ($estilos as $e): ?>
                        <option value="<?= htmlspecialchars($e['estilo']) ?>" <?= ($filtros['estilo'] ?? '') === $e['estilo'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['estilo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control form-select">
                    <option value="">Todos</option>
                    <option value="disponivel" <?= ($filtros['status'] ?? '') === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                    <option value="reservado" <?= ($filtros['status'] ?? '') === 'reservado' ? 'selected' : '' ?>>Reservado</option>
                </select>
            </div>
            <div class="col-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><?= icon('filter', '', 18) ?> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Estoque -->
<div class="card">
    <div class="card-body">
        <?php if (empty($estoque)): ?>
            <p class="text-center">Nenhum barril em estoque.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Código Barril</th>
                        <th>Lote</th>
                        <th>Estilo</th>
                        <th>Barril</th>
                        <th>Litros</th>
                        <th>Data Entrada</th>
                        <th>Localização</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estoque as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['codigo_barril']) ?></strong></td>
                            <td><?= htmlspecialchars($item['lote_codigo']) ?></td>
                            <td><?= htmlspecialchars($item['estilo'] ?? '-') ?></td>
                            <td>#<?= $item['numero_barril'] ?></td>
                            <td><?= formatQuantity($item['quantidade_litros'], 'L') ?></td>
                            <td><?= formatDate($item['data_entrada']) ?></td>
                            <td><?= htmlspecialchars($item['localizacao'] ?? '-') ?></td>
                            <td>
                                <span class="badge badge-<?= $item['status'] === 'disponivel' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= iconButton('view', '/estoque/viewEstoque?id=' . $item['id'], 'primary', 'Visualizar') ?>
                                <?php if ($item['status'] === 'disponivel'): ?>
                                    <a href="/saidabarril/create?estoque_barril_id=<?= $item['id'] ?>" class="btn-icon btn-danger" data-tooltip="Dar Baixa">
                                        <?= icon('barril', '', 18) ?>
                                    </a>
                                    <form method="POST" action="/estoque/delete" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este barril? Esta ação não pode ser desfeita.')">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn-icon btn-danger" data-tooltip="Excluir">
                                            <?= icon('delete', '', 18) ?>
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