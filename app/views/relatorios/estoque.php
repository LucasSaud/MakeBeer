<?php
$pageTitle = 'Relatório de Estoque - ' . APP_NAME;
$activeMenu = 'relatorios';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Relatório de Estoque</h1>
        <p class="page-subtitle">Situação atual do estoque de insumos</p>
    </div>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-secondary">Imprimir</button>
        <a href="/relatorios" class="btn btn-primary">Voltar</a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/relatorios/estoque" class="row">
            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" class="form-control form-select">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($_GET['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= $cat['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-select">
                        <option value="">Todos</option>
                        <option value="normal" <?= ($_GET['status'] ?? '') == 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="baixo" <?= ($_GET['status'] ?? '') == 'baixo' ? 'selected' : '' ?>>Estoque Baixo</option>
                        <option value="critico" <?= ($_GET['status'] ?? '') == 'critico' ? 'selected' : '' ?>>Crítico</option>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumo Geral -->
<div class="row mb-4">
    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= $resumo['total_insumos'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Total de Insumos</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #27ae60; margin-bottom: 0.5rem;"><?= formatMoney($resumo['valor_total'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Valor Total em Estoque</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #f39c12; margin-bottom: 0.5rem;"><?= $resumo['estoque_baixo'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Com Estoque Baixo</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #e74c3c; margin-bottom: 0.5rem;"><?= $resumo['estoque_critico'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Estoque Crítico</p>
            </div>
        </div>
    </div>
</div>

<!-- Estoque por Categoria -->
<?php if (!empty($por_categoria)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Estoque por Categoria</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Quantidade de Insumos</th>
                    <th>Valor Total</th>
                    <th>Com Estoque Baixo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($por_categoria as $cat): ?>
                <tr>
                    <td><strong><?= $cat['nome'] ?></strong></td>
                    <td><?= $cat['total_insumos'] ?></td>
                    <td><?= formatMoney($cat['valor_total']) ?></td>
                    <td>
                        <?php if ($cat['estoque_baixo'] > 0): ?>
                            <span class="badge badge-warning"><?= $cat['estoque_baixo'] ?></span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Detalhamento de Insumos -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detalhamento de Insumos</h3>
    </div>
    <div class="card-body">
        <?php if (empty($insumos)): ?>
            <p style="color: #6c757d;">Nenhum insumo encontrado com os filtros selecionados.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Estoque Atual</th>
                        <th>Estoque Mínimo</th>
                        <th>Custo Médio</th>
                        <th>Valor Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($insumos as $insumo): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($insumo['codigo_interno'] ?? $insumo['nome'] ?? 'N/A') ?></strong></td>
                        <td><strong><?= $insumo['nome'] ?></strong></td>
                        <td><?= $insumo['categoria_nome'] ?></td>
                       <td><?= formatQuantity($insumo['estoque_atual'] ?? 0, $insumo['unidade_medida'] ?? 'kg') ?></td>
                        <td><?= formatQuantity($insumo['estoque_minimo'] ?? 0, $insumo['unidade_medida'] ?? 'kg') ?></td>
                        <td><?= formatMoney($insumo['preco_medio'] ?? 0) ?></td>
                        <td><?= formatMoney(($insumo['preco_medio'] ?? 0) * ($insumo['estoque_atual'] ?? 0)) ?></td>
                        <td>
                            <?php
                            $estoque = $insumo['estoque_atual'] ?? 0;
                            $minimo = $insumo['estoque_minimo'] ?? 0;
                            if ($estoque <= 0) {
                                echo '<span class="badge badge-danger">Sem Estoque</span>';
                            } elseif ($estoque < $minimo * 0.5) {
                                echo '<span class="badge badge-danger">Crítico</span>';
                            } elseif ($estoque < $minimo) {
                                echo '<span class="badge badge-warning">Baixo</span>';
                            } else {
                                echo '<span class="badge badge-success">Normal</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: #f8f9fa;">
                        <td colspan="6" class="text-right">TOTAL:</td>
                        <td><?= formatMoney(array_sum(array_map(fn($i) => ($i['preco_medio'] ?? 0) * ($i['estoque_atual'] ?? 0), $insumos))) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>
</div>

<div style="margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #e9ecef; text-align: center; color: #6c757d;">
    <p>Relatório gerado em: <?= formatDateTime(date('Y-m-d H:i:s')) ?></p>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
