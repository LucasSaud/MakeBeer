<?php
$pageTitle = 'Fornecedores - ' . APP_NAME;
$activeMenu = 'fornecedores';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">🏢 Gestão de Fornecedores</h1>
    <div class="page-actions">
        <a href="/fornecedores/create" class="btn btn-primary">+ Novo Fornecedor</a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/fornecedores" class="row">
            <div class="col col-4">
                <div class="form-group">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Nome, CNPJ ou email" value="<?= $_GET['search'] ?? '' ?>">
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="ativo" class="form-control form-select">
                        <option value="">Todos</option>
                        <option value="1" <?= ($_GET['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativos</option>
                        <option value="0" <?= ($_GET['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativos</option>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="/fornecedores" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Listagem de Fornecedores -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Lista de Fornecedores</h3>
        <span class="badge badge-info"><?= count($fornecedores) ?> fornecedores</span>
    </div>
    <div class="card-body">
        <?php if (empty($fornecedores)): ?>
            <div class="alert alert-info">
                Nenhum fornecedor encontrado. <a href="/fornecedores/create">Cadastre o primeiro fornecedor</a>.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CNPJ</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Cidade/UF</th>
                        <th>Total Compras</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fornecedores as $fornecedor): ?>
                    <tr>
                        <td><strong><?= $fornecedor['nome'] ?></strong></td>
                        <td><?= $fornecedor['cnpj'] ?? '-' ?></td>
                        <td><?= $fornecedor['email'] ?? '-' ?></td>
                        <td><?= $fornecedor['telefone'] ?? '-' ?></td>
                        <td><?= ($fornecedor['cidade'] ?? '') . '/' . ($fornecedor['estado'] ?? '') ?></td>
                        <td><?= formatMoney($fornecedor['total_compras'] ?? 0) ?></td>
                        <td>
                            <?php if ($fornecedor['ativo']): ?>
                                <span class="badge badge-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/fornecedores/viewFornecedor?id=<?= $fornecedor['id'] ?>" class="btn btn-sm btn-primary">Ver</a>
                                <a href="/fornecedores/edit?id=<?= $fornecedor['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
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
