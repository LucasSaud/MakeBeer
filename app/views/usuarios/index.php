<?php
$pageTitle = 'Usuários - ' . APP_NAME;
$activeMenu = 'usuarios';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">👥 Gestão de Usuários</h1>
    <div class="page-actions">
        <a href="/usuarios/create" class="btn btn-primary">+ Novo Usuário</a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/usuarios" class="row">
            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="nome" class="form-control" placeholder="Nome" value="<?= $_GET['nome'] ?? '' ?>">
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Perfil</label>
                    <select name="perfil" class="form-control form-select">
                        <option value="">Todos os perfis</option>
                        <option value="administrador" <?= ($_GET['perfil'] ?? '') == 'administrador' ? 'selected' : '' ?>>Administrador</option>
                        <option value="producao" <?= ($_GET['perfil'] ?? '') == 'producao' ? 'selected' : '' ?>>Produção</option>
                        <option value="comprador" <?= ($_GET['perfil'] ?? '') == 'comprador' ? 'selected' : '' ?>>Comprador</option>
                        <option value="consulta" <?= ($_GET['perfil'] ?? '') == 'consulta' ? 'selected' : '' ?>>Consulta</option>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="ativo" class="form-control form-select">
                        <option value="1" <?= ($_GET['ativo'] ?? '1') === '1' ? 'selected' : '' ?>>Ativos</option>
                        <option value="0" <?= ($_GET['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativos</option>
                        <option value="" <?= ($_GET['ativo'] ?? '') === '' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="/usuarios" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Listagem de Usuários -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Lista de Usuários</h3>
        <span class="badge badge-info"><?= count($usuarios) ?> usuários</span>
    </div>
    <div class="card-body">
        <?php if (empty($usuarios)): ?>
            <div class="alert alert-info">
                Nenhum usuário encontrado. <a href="/usuarios/create">Cadastre o primeiro usuário</a>.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Perfil</th>
                        <th>Último Acesso</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>
                            <strong><?= $usuario['nome'] ?></strong>
                            <?php if ($usuario['id'] == getCurrentUser()['id']): ?>
                                <span class="badge badge-info">Você</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $usuario['email'] ?></td>
                        <td>
                            <?php
                            $perfilClass = match($usuario['perfil']) {
                                'administrador' => 'danger',
                                'producao' => 'success',
                                'comprador' => 'warning',
                                'consulta' => 'secondary',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge badge-<?= $perfilClass ?>"><?= ucfirst($usuario['perfil']) ?></span>
                        </td>
                        <td><?= $usuario['ultimo_acesso'] ? formatDateTime($usuario['ultimo_acesso']) : 'Nunca' ?></td>
                        <td>
                            <?php if ($usuario['ativo']): ?>
                                <span class="badge badge-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/usuarios/viewUsuario?id=<?= $usuario['id'] ?>" class="btn btn-sm btn-primary">Ver</a>
                                <a href="/usuarios/edit?id=<?= $usuario['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <?php if ($usuario['id'] != getCurrentUser()['id']): ?>
                                    <form method="POST" action="/usuarios/delete" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja inativar este usuário?');">
                                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Inativar</button>
                                    </form>
                                <?php endif; ?>
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
