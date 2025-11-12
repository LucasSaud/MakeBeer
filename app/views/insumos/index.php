<?php
$pageTitle = 'Insumos - ' . APP_NAME;
$activeMenu = 'insumos';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">📦 Gestão de Insumos</h1>
    <div class="page-actions">
        <a href="/insumos/create" class="btn btn-primary">+ Novo Insumo</a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/insumos" class="row">
            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Nome ou código" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" class="form-control form-select">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($_GET['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Status Estoque</label>
                    <select name="status_estoque" class="form-control form-select">
                        <option value="">Todos</option>
                        <option value="normal" <?= ($_GET['status_estoque'] ?? '') == 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="baixo" <?= ($_GET['status_estoque'] ?? '') == 'baixo' ? 'selected' : '' ?>>Estoque Baixo</option>
                        <option value="critico" <?= ($_GET['status_estoque'] ?? '') == 'critico' ? 'selected' : '' ?>>Crítico</option>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="/insumos" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Listagem de Insumos -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Lista de Insumos</h3>
        <span class="badge badge-info"><?= count($insumos) ?> insumos</span>
    </div>
    <div class="card-body">
        <?php if (empty($insumos)): ?>
            <div class="alert alert-info">
                Nenhum insumo encontrado. <a href="/insumos/create">Cadastre o primeiro insumo</a>.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Estoque Atual</th>
                        <th>Estoque Mínimo</th>
                        <th>Status</th>
                        <th>Custo Médio</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($insumos as $insumo): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($insumo['codigo_interno'] ?? 'N/A') ?></strong></td>
                        <td><?= htmlspecialchars($insumo['nome'] ?? '') ?></td>
                        <td><?= htmlspecialchars($insumo['categoria_nome'] ?? 'Sem categoria') ?></td>
                        <td><?= formatQuantity($insumo['estoque_atual'] ?? 0, $insumo['unidade_medida'] ?? 'un') ?></td>
                        <td><?= formatQuantity($insumo['estoque_minimo'] ?? 0, $insumo['unidade_medida'] ?? 'un') ?></td>
                        <td>
                            <?php
                            $quantidade = $insumo['estoque_atual'] ?? 0;
                            $minimo = $insumo['estoque_minimo'] ?? 0;

                            if ($quantidade <= 0) {
                                echo '<span class="badge badge-danger">Sem Estoque</span>';
                            } elseif ($minimo > 0 && $quantidade < $minimo * 0.5) {
                                echo '<span class="badge badge-danger">Crítico</span>';
                            } elseif ($minimo > 0 && $quantidade < $minimo) {
                                echo '<span class="badge badge-warning">Baixo</span>';
                            } else {
                                echo '<span class="badge badge-success">Normal</span>';
                            }
                            ?>
                        </td>
                        <td><?= formatMoney($insumo['preco_medio'] ?? 0) ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/insumos/viewInsumo?id=<?= $insumo['id'] ?>" class="btn btn-sm btn-primary" title="Ver detalhes">Ver</a>
                                <a href="/insumos/edit?id=<?= $insumo['id'] ?>" class="btn btn-sm btn-warning" title="Editar">Editar</a>
                                <a href="/entradas/create?insumo_id=<?= $insumo['id'] ?>" class="btn btn-sm btn-success" title="Nova entrada">Entrada</a>
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