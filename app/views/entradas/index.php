<?php
$pageTitle = 'Entradas de Estoque - ' . APP_NAME;
$activeMenu = 'entradas';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">📥 Entradas de Estoque</h1>
    <div class="page-actions">
        <a href="/entradas/create" class="btn btn-primary">+ Nova Entrada</a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/entradas" class="row">
            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?= $_GET['data_inicio'] ?? '' ?>">
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="<?= $_GET['data_fim'] ?? '' ?>">
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Fornecedor</label>
                    <select name="fornecedor_id" class="form-control form-select">
                        <option value="">Todos os fornecedores</option>
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
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="/entradas" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Estatísticas do Período -->
<div class="row mb-4">
    <div class="col col-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= $stats['total_entradas'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Entradas no Período</p>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #27ae60; margin-bottom: 0.5rem;"><?= formatMoney($stats['preco_total'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Valor Total</p>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #f39c12; margin-bottom: 0.5rem;"><?= formatMoney($stats['ticket_medio'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Ticket Médio</p>
            </div>
        </div>
    </div>
</div>

<!-- Listagem de Entradas -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Lista de Entradas</h3>
        <span class="badge badge-info"><?= count($entradas) ?> entradas</span>
    </div>
    <div class="card-body">
        <?php if (empty($entradas)): ?>
            <div class="alert alert-info">
                Nenhuma entrada encontrada. <a href="/entradas/create">Registre a primeira entrada</a>.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Insumo</th>
                        <th>Fornecedor</th>
                        <th>Quantidade</th>
                        <th>Custo Unitário</th>
                        <th>Valor Total</th>
                        <th>Nota Fiscal</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entradas as $entrada): ?>
                    <tr>
                        <td><?= formatDate($entrada['data_entrada']) ?></td>
                        <td>
                            <strong><?= $entrada['insumo_nome'] ?></strong><br>
                            <small style="color: #6c757d;">Lote: <?= $entrada['lote_fornecedor'] ? $entrada['lote_fornecedor'] : 'N/A' ?></small>
                        </td>
                        <td><?= $entrada['fornecedor_nome'] ?></td>
                        <td><?= formatQuantity($entrada['quantidade'], $entrada['unidade_medida']) ?></td>
                        <td><?= formatMoney($entrada['preco_unitario'] ?? 0) ?></td>
                        <td><strong><?= formatMoney($entrada['preco_total']) ?></strong></td>
                        <td><?= $entrada['numero_nota_fiscal'] ?: '-' ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/entradas/viewEntradas?id=<?= $entrada['id'] ?>" class="btn btn-sm btn-primary">Ver</a>
                                <a href="/entradas/edit?id=<?= $entrada['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <form method="POST" action="/entradas/delete" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta entrada?');">
                                    <input type="hidden" name="id" value="<?= $entrada['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: #f8f9fa;">
                        <td colspan="5" class="text-right">TOTAL:</td>
                        <td><?= formatMoney(array_sum(array_column($entradas, 'preco_total'))) ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
