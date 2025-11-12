<?php
$pageTitle = 'Relatório de Compras - ' . APP_NAME;
$activeMenu = 'relatorios';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Relatório de Compras</h1>
        <p class="page-subtitle">Análise de entradas e fornecedores</p>
    </div>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-secondary">Imprimir</button>
        <a href="/relatorios" class="btn btn-primary">Voltar</a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/relatorios/compras" class="row">
            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?= $_GET['data_inicio'] ?? date('Y-m-01') ?>">
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="<?= $_GET['data_fim'] ?? date('Y-m-d') ?>">
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Fornecedor</label>
                    <select name="fornecedor_id" class="form-control form-select">
                        <option value="">Todos</option>
                        <?php foreach ($fornecedores as $forn): ?>
                            <option value="<?= $forn['id'] ?>" <?= ($_GET['fornecedor_id'] ?? '') == $forn['id'] ? 'selected' : '' ?>>
                                <?= $forn['nome'] ?>
                            </option>
                        <?php endforeach; ?>
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

<!-- Resumo do Período -->
<div class="row mb-4">
    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= $resumo['total_entradas'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Total de Entradas</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #27ae60; margin-bottom: 0.5rem;"><?= formatMoney($resumo['valor_total'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Valor Total</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #f39c12; margin-bottom: 0.5rem;"><?= formatMoney($resumo['ticket_medio'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Ticket Médio</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #e74c3c; margin-bottom: 0.5rem;"><?= $resumo['total_fornecedores'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Fornecedores Ativos</p>
            </div>
        </div>
    </div>
</div>

<!-- Compras por Fornecedor -->
<?php if (!empty($por_fornecedor)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Compras por Fornecedor</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Fornecedor</th>
                    <th>Entradas</th>
                    <th>Valor Total</th>
                    <th>Ticket Médio</th>
                    <th>% do Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($por_fornecedor as $forn): ?>
                <tr>
                    <td><strong><?= $forn['nome'] ?></strong></td>
                    <td><?= $forn['total_entradas'] ?></td>
                    <td><?= formatMoney($forn['valor_total']) ?></td>
                    <td><?= formatMoney($forn['ticket_medio']) ?></td>
                    <td><?= number_format($forn['percentual'], 2) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Compras por Categoria -->
<?php if (!empty($por_categoria)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Compras por Categoria de Insumo</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Entradas</th>
                    <th>Valor Total</th>
                    <th>% do Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($por_categoria as $cat): ?>
                <tr>
                    <td><strong><?= $cat['nome'] ?></strong></td>
                    <td><?= $cat['total_entradas'] ?></td>
                    <td><?= formatMoney($cat['valor_total']) ?></td>
                    <td><?= number_format($cat['percentual'], 2) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Detalhamento de Entradas -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detalhamento de Entradas</h3>
    </div>
    <div class="card-body">
        <?php if (empty($entradas)): ?>
            <p style="color: #6c757d;">Nenhuma entrada encontrada no período selecionado.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Fornecedor</th>
                        <th>Insumo</th>
                        <th>Quantidade</th>
                        <th>Custo Unitário</th>
                        <th>Valor Total</th>
                        <th>Nota Fiscal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entradas as $entrada): ?>
                    <tr>
                        <td><?= formatDate($entrada['data_entrada']) ?></td>
                        <td><?= $entrada['fornecedor_nome'] ?></td>
                        <td><?= $entrada['insumo_nome'] ?></td>
                        <td><?= formatQuantity($entrada['quantidade'], $entrada['unidade_medida']) ?></td>
                        <td><?= formatMoney($entrada['custo_unitario']) ?></td>
                        <td><strong><?= formatMoney($entrada['valor_total']) ?></strong></td>
                        <td><?= $entrada['numero_nota_fiscal'] ?: '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: #f8f9fa;">
                        <td colspan="5" class="text-right">TOTAL:</td>
                        <td><?= formatMoney(array_sum(array_column($entradas, 'valor_total'))) ?></td>
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
