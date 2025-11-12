<?php
$pageTitle = 'Saída de Barril - Atomos';
$activeMenu = 'saidabarril';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">📤 Saída de Barril</h1>
            <p class="page-subtitle">Registro de baixa e saída de barris</p>
        </div>
        <div>
            <a href="/saidabarril/create" class="btn btn-primary">
                <?= icon('add', '', 18) ?> Nova Saída
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($saidas)): ?>
            <p class="text-center">Nenhuma saída registrada.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Data Saída</th>
                        <th>Lote</th>
                        <th>Barril</th>
                        <th>Estilo</th>
                        <th>Litros</th>
                        <th>Destino</th>
                        <th>Responsável</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($saidas as $saida): ?>
                        <tr>
                            <td><?= formatDate($saida['data_saida']) ?></td>
                            <td><?= htmlspecialchars($saida['lote_codigo']) ?></td>
                            <td><strong>#<?= $saida['numero_barril'] ?></strong></td>
                            <td><?= htmlspecialchars($saida['estilo'] ?? '-') ?></td>
                            <td><?= formatQuantity($saida['quantidade_litros'], 'L') ?></td>
                            <td><?= htmlspecialchars($saida['destino'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($saida['responsavel_nome'] ?? '-') ?></td>
                            <td>
                                <?= iconButton('view', '/saidabarril/viewSaida?id=' . $saida['id'], 'primary', 'Visualizar') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
