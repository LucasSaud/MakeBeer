<?php
$pageTitle = 'Relatório de Produção - ' . APP_NAME;
$activeMenu = 'relatorios';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Relatório de Produção</h1>
        <p class="page-subtitle">Análise da produção de cerveja</p>
    </div>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-secondary">Imprimir</button>
        <a href="/relatorios" class="btn btn-primary">Voltar</a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/relatorios/producao" class="row">
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
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-select">
                        <option value="">Todos</option>
                        <option value="finalizado" <?= ($_GET['status'] ?? '') == 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                        <option value="em_andamento" <?= ($_GET['status'] ?? '') == 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                        <option value="cancelado" <?= ($_GET['status'] ?? '') == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
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
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= $resumo['total_lotes'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Lotes no Período</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #27ae60; margin-bottom: 0.5rem;"><?= formatQuantity($resumo['volume_total'] ?? 0, 'L') ?></h3>
                <p style="margin: 0; color: #6c757d;">Volume Produzido</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #f39c12; margin-bottom: 0.5rem;"><?= formatMoney($resumo['custo_total'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Custo Total</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #e74c3c; margin-bottom: 0.5rem;"><?= formatMoney($resumo['custo_medio_litro'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Custo Médio/Litro</p>
            </div>
        </div>
    </div>
</div>

<!-- Produção por Receita -->
<?php if (!empty($por_receita)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Produção por Receita</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Receita</th>
                    <th>Lotes Produzidos</th>
                    <th>Volume Total</th>
                    <th>Custo Total</th>
                    <th>Custo/Litro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($por_receita as $rec): ?>
                <tr>
                    <td><strong><?= $rec['nome'] ?></strong></td>
                    <td><?= $rec['total_lotes'] ?></td>
                    <td><?= formatQuantity($rec['volume_total'], 'L') ?></td>
                    <td><?= formatMoney($rec['custo_total']) ?></td>
                    <td><?= formatMoney($rec['custo_litro']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Detalhamento de Lotes -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detalhamento de Lotes</h3>
    </div>
    <div class="card-body">
        <?php if (empty($lotes)): ?>
            <p style="color: #6c757d;">Nenhum lote encontrado no período selecionado.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Receita</th>
                        <th>Data Início</th>
                        <th>Data Conclusão</th>
                        <th>Quantidade</th>
                        <th>Custo Total</th>
                        <th>Custo/Litro</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lotes as $lote): ?>
                    <tr>
                        <td><strong><?= $lote['codigo_lote'] ?></strong></td>
                        <td><?= $lote['receita_nome'] ?></td>
                        <td><?= formatDate($lote['data_inicio']) ?></td>
                        <td><?= $lote['data_conclusao'] ? formatDate($lote['data_conclusao']) : '-' ?></td>
                        <td><?= formatQuantity($lote['quantidade_produzida'], 'L') ?></td>
                        <td><?= formatMoney($lote['custo_total']) ?></td>
                        <td><?= formatMoney($lote['custo_total'] / $lote['quantidade_produzida']) ?></td>
                        <td>
                            <?php
                            $statusClass = match($lote['status']) {
                                'finalizado' => 'success',
                                'em_andamento' => 'info',
                                'cancelado' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $lote['status'])) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: #f8f9fa;">
                        <td colspan="4" class="text-right">TOTAL:</td>
                        <td><?= formatQuantity(array_sum(array_column($lotes, 'quantidade_produzida')), 'L') ?></td>
                        <td><?= formatMoney(array_sum(array_column($lotes, 'custo_total'))) ?></td>
                        <td colspan="2"></td>
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
