<?php
$pageTitle = 'Detalhes da Saída - Atomos';
$activeMenu = 'saidabarril';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title">Saída de Barril #<?= $saida['id'] ?></h1>
        <a href="/saidabarril" class="btn btn-secondary"><?= icon('back', '', 18) ?> Voltar</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <p><strong>Data da Saída:</strong> <?= formatDate($saida['data_saida']) ?></p>
                <p><strong>Lote:</strong> <?= htmlspecialchars($saida['lote_codigo']) ?></p>
                <p><strong>Número do Barril:</strong> <?= $saida['numero_barril'] ?></p>
                <p><strong>Código do Barril:</strong> <?= htmlspecialchars($saida['codigo_barril']) ?></p>
            </div>
            <div class="col-6">
                <p><strong>Estilo:</strong> <?= htmlspecialchars($saida['estilo'] ?? '-') ?></p>
                <p><strong>Quantidade:</strong> <?= formatQuantity($saida['quantidade_litros'], 'L') ?></p>
                <p><strong>Destino:</strong> <?= htmlspecialchars($saida['destino'] ?? '-') ?></p>
                <p><strong>Responsável:</strong> <?= htmlspecialchars($saida['responsavel_nome'] ?? '-') ?></p>
            </div>
        </div>

        <?php if ($saida['observacoes']): ?>
            <hr>
            <p><strong>Observações:</strong><br><?= nl2br(htmlspecialchars($saida['observacoes'])) ?></p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
