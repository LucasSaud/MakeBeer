<?php
$pageTitle = 'Receitas - ' . APP_NAME;
$activeMenu = 'receitas';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">📝 Gestão de Receitas</h1>
    <div class="page-actions">
        <a href="/receitas/create" class="btn btn-primary">+ Nova Receita</a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/receitas" class="row">
            <div class="col col-4">
                <div class="form-group">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Nome da receita" value="<?= $_GET['search'] ?? '' ?>">
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="ativo" class="form-control form-select">
                        <option value="">Todas</option>
                        <option value="1" <?= ($_GET['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativas</option>
                        <option value="0" <?= ($_GET['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativas</option>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="/receitas" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Listagem de Receitas -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Lista de Receitas</h3>
        <span class="badge badge-info"><?= count($receitas) ?> receitas</span>
    </div>
    <div class="card-body">
        <?php if (empty($receitas)): ?>
            <div class="alert alert-info">
                Nenhuma receita encontrada. <a href="/receitas/create">Cadastre a primeira receita</a>.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Volume</th>
                        <th>Ingredientes</th>
                        <th>Custo Estimado</th>
                        <th>Disponibilidade</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receitas as $receita): ?>
                    <tr>
                        <td><strong><?= $receita['nome'] ?></strong></td>
                        <td><?= isset($receita['estilo']) ? ucfirst($receita['estilo']) : 'Não definido' ?></td>
                        <td><?= isset($receita['volume_batch']) ? formatQuantity($receita['volume_batch'], 'L') : '0 L' ?></td>
                        <td><?= $receita['total_ingredientes'] ?? 0 ?> itens</td>
                        <td><?= formatMoney($receita['custo_total'] ?? 0) ?></td>
                        <td>
                            <?php if (isset($receita['pode_produzir']) && $receita['pode_produzir']): ?>
                                <span class="badge badge-success">Disponível</span><br>
                                <small style="color: #6c757d;"><?= $receita['lotes_possiveis'] ?? 0 ?> lotes</small>
                            <?php elseif (isset($receita['pode_produzir'])): ?>
                                <span class="badge badge-danger">Indisponível</span><br>
                                <small style="color: #6c757d;">Faltam insumos</small>
                            <?php else: ?>
                                <span class="badge badge-secondary">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($receita['ativo']): ?>
                                <span class="badge badge-success">Ativa</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inativa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/receitas/viewReceita?id=<?= $receita['id'] ?>" class="btn btn-sm btn-primary">Ver</a>
                                <a href="/receitas/edit?id=<?= $receita['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <?php if (isset($receita['pode_produzir']) && $receita['pode_produzir']): ?>
                                    <a href="/producao/create?receita_id=<?= $receita['id'] ?>" class="btn btn-sm btn-success">Produzir</a>
                                <?php endif; ?>
                                <form method="POST" action="/receitas/delete" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja inativar esta receita?')">
                                    <input type="hidden" name="id" value="<?= $receita['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>