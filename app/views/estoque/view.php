<?php
$pageTitle = 'Detalhes do Estoque - Atomos';
$activeMenu = 'estoque';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title">Barril: <?= htmlspecialchars($estoque['codigo_barril']) ?></h1>
        <a href="/estoque" class="btn btn-secondary"><?= icon('back', '', 18) ?> Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações do Barril</h3>
            </div>
            <div class="card-body">
                <p><strong>Código:</strong> <?= htmlspecialchars($estoque['codigo_barril']) ?></p>
                <p><strong>Lote:</strong> <?= htmlspecialchars($estoque['lote_codigo']) ?></p>
                <p><strong>Número:</strong> #<?= $estoque['numero_barril'] ?></p>
                <p><strong>Estilo:</strong> <?= htmlspecialchars($estoque['estilo'] ?? '-') ?></p>
                <p><strong>Quantidade:</strong> <?= formatQuantity($estoque['quantidade_litros'], 'L') ?></p>
                <p><strong>Data Entrada:</strong> <?= formatDate($estoque['data_entrada']) ?></p>
                <p><strong>Status:</strong>
                    <span class="badge badge-<?= $estoque['status'] === 'disponivel' ? 'success' : 'warning' ?>">
                        <?= ucfirst($estoque['status']) ?>
                    </span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Armazenamento</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/estoque/atualizarLocalizacao">
                    <input type="hidden" name="id" value="<?= $estoque['id'] ?>">
                    <div class="form-group">
                        <label class="form-label">Localização</label>
                        <input type="text" name="localizacao" class="form-control"
                               value="<?= htmlspecialchars($estoque['localizacao'] ?? '') ?>"
                               placeholder="Ex: Setor A, Prateleira 3">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Temperatura (°C)</label>
                        <input type="number" step="0.1" name="temperatura_armazenamento" class="form-control"
                               value="<?= $estoque['temperatura_armazenamento'] ?? '' ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <?= icon('save', '', 16) ?> Atualizar
                    </button>
                </form>

                <?php if ($estoque['observacoes']): ?>
                    <hr>
                    <p><strong>Observações:</strong><br><?= nl2br(htmlspecialchars($estoque['observacoes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($estoque['status'] === 'disponivel'): ?>
<div class="card mt-3">
    <div class="card-body text-center">
        <a href="/saidabarril/create?estoque_barril_id=<?= $estoque['id'] ?>" class="btn btn-danger">
            <?= icon('barril', '', 18) ?> Registrar Saída deste Barril
        </a>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
