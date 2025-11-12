<?php
$pageTitle = 'Nova Saída de Barril - Atomos';
$activeMenu = 'saidabarril';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1 class="page-title">Registrar Saída de Barril</h1>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/saidabarril/store">
            <?php if ($barril): ?>
                <!-- Informações do Barril Selecionado -->
                <input type="hidden" name="barril_id" value="<?= $barril['id'] ?>">
                <div class="alert alert-info">
                    <strong>Barril Selecionado:</strong> <?= htmlspecialchars($barril['codigo_barril']) ?><br>
                    <strong>Lote:</strong> <?= htmlspecialchars($barril['lote_codigo']) ?> |
                    <strong>Estilo:</strong> <?= htmlspecialchars($barril['estilo']) ?> |
                    <strong>Litros:</strong> <?= formatQuantity($barril['quantidade_litros'], 'L') ?>
                </div>
            <?php else: ?>
                <!-- Seleção de Barril -->
                <div class="form-group">
                    <label class="form-label">Selecione o Barril *</label>
                    <select name="barril_id" class="form-control form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($barris_disponiveis as $b): ?>
                            <option value="<?= $b['id'] ?>">
                                <?= htmlspecialchars($b['codigo_barril']) ?> -
                                Lote: <?= htmlspecialchars($b['lote_codigo']) ?> -
                                <?= htmlspecialchars($b['estilo']) ?> -
                                <?= formatQuantity($b['quantidade_litros'], 'L') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Data da Saída</label>
                        <input type="date" name="data_saida" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Destino</label>
                        <input type="text" name="destino" class="form-control" placeholder="Ex: Venda, Consumo, etc.">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Observações</label>
                <textarea name="observacoes" class="form-control" rows="3"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <?= icon('save', '', 18) ?> Registrar Saída
                </button>
                <a href="/saidabarril" class="btn btn-secondary">
                    <?= icon('back', '', 18) ?> Voltar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
