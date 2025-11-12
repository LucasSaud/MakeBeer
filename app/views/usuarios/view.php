<?php
$pageTitle = 'Detalhes do Usuário - ' . APP_NAME;
$activeMenu = 'usuarios';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">Detalhes do Usuário</h1>
    <div class="page-actions d-flex gap-2">
        <a href="/usuarios/edit?id=<?= $usuario['id'] ?>" class="btn btn-warning">Editar</a>
        <a href="/usuarios" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <!-- Informações do Usuário -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Informações do Usuário</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Nome:</strong>
                        <h3 style="color: #2c3e50; margin: 0.5rem 0;"><?= $usuario['nome'] ?></h3>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Email:</strong>
                        <p style="font-size: 1.1rem;"><?= $usuario['email'] ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Perfil:</strong>
                        <p>
                            <?php
                            $perfilClass = match($usuario['perfil']) {
                                'administrador' => 'danger',
                                'producao' => 'primary',
                                'comprador' => 'warning',
                                'consulta' => 'secondary',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge badge-<?= $perfilClass ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                <?= ucfirst($usuario['perfil']) ?>
                            </span>
                        </p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Status:</strong>
                        <p>
                            <?php if ($usuario['ativo']): ?>
                                <span class="badge badge-success" style="font-size: 1rem; padding: 0.5rem 1rem;">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 1rem; padding: 0.5rem 1rem;">Inativo</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Data de Cadastro:</strong>
                        <p><?= formatDateTime($usuario['created_at']) ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Último Acesso:</strong>
                        <p><?= $usuario['ultimo_acesso'] ? formatDateTime($usuario['ultimo_acesso']) : 'Nunca acessou' ?></p>
                    </div>
                </div>

                <?php if (isset($usuario['observacoes']) && $usuario['observacoes']): ?>
                <div class="row">
                    <div class="col col-12">
                        <strong>Observações:</strong>
                        <p><?= nl2br($usuario['observacoes']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Permissões -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Permissões do Perfil</h3>
            </div>
            <div class="card-body">
                <?php
                $permissoes = [
                    'administrador' => [
                        'Acesso total ao sistema',
                        'Gestão de usuários',
                        'Configurações do sistema',
                        'Todos os relatórios',
                        'Gestão de fornecedores',
                        'Entrada de estoque',
                        'Registro de produção',
                        'Consulta de estoque'
                    ],
                    'producao' => [
                        'Entrada de estoque',
                        'Registro de produção',
                        'Consulta de estoque',
                        'Gestão de receitas',
                        'Gestão de lotes'
                    ],
                    'comprador' => [
                        'Entrada de estoque',
                        'Gestão de fornecedores',
                        'Consulta de estoque',
                        'Relatórios de compras'
                    ],
                    'consulta' => [
                        'Consulta de estoque',
                        'Visualização de relatórios'
                    ]
                ];

                $perfilPermissoes = $permissoes[$usuario['perfil']] ?? [];
                ?>

                <?php if (!empty($perfilPermissoes)): ?>
                    <ul style="line-height: 2;">
                        <?php foreach ($perfilPermissoes as $perm): ?>
                            <li><?= $perm ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #6c757d;">Nenhuma permissão definida.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Atividades Recentes -->
        <?php if (!empty($atividades_recentes)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Atividades Recentes</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Ação</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($atividades_recentes as $ativ): ?>
                        <tr>
                            <td><?= formatDateTime($ativ['data']) ?></td>
                            <td><?= $ativ['acao'] ?></td>
                            <td><?= $ativ['detalhes'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col col-4">
        <!-- Estatísticas -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Estatísticas</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Total de Acessos:</strong>
                    <h3 style="color: #3498db; margin: 0.5rem 0;">
                        <?= $usuario['total_acessos'] ?? 0 ?>
                    </h3>
                </div>

                <div class="mb-3">
                    <strong>Entradas Registradas:</strong>
                    <p><?= $usuario['total_entradas'] ?? 0 ?></p>
                </div>

                <div class="mb-3">
                    <strong>Lotes Criados:</strong>
                    <p><?= $usuario['total_lotes'] ?? 0 ?></p>
                </div>

                <div>
                    <strong>IP do Último Acesso:</strong>
                    <p style="font-family: monospace;"><?= $usuario['ultimo_ip'] ?? '-' ?></p>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ações Rápidas</h3>
            </div>
            <div class="card-body">
                <a href="/usuarios/resetar-senha/<?= $usuario['id'] ?>" class="btn btn-warning" style="width: 100%; margin-bottom: 0.5rem;">
                    Resetar Senha
                </a>

                <?php if ($usuario['ativo']): ?>
                    <button onclick="desativarUsuario(<?= $usuario['id'] ?>)" class="btn btn-secondary" style="width: 100%; margin-bottom: 0.5rem;">
                        Desativar Usuário
                    </button>
                <?php else: ?>
                    <button onclick="ativarUsuario(<?= $usuario['id'] ?>)" class="btn btn-success" style="width: 100%; margin-bottom: 0.5rem;">
                        Ativar Usuário
                    </button>
                <?php endif; ?>

                <?php if ($usuario['id'] != getCurrentUser()['id']): ?>
                    <button onclick="excluirUsuario(<?= $usuario['id'] ?>)" class="btn btn-danger" style="width: 100%;">
                        Excluir Usuário
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function desativarUsuario(id) {
    if (confirm('Tem certeza que deseja desativar este usuário?')) {
        window.location.href = '/usuarios/desativar/' + id;
    }
}

function ativarUsuario(id) {
    if (confirm('Tem certeza que deseja ativar este usuário?')) {
        window.location.href = '/usuarios/ativar/' + id;
    }
}

function excluirUsuario(id) {
    if (confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')) {
        window.location.href = '/usuarios/delete/' + id;
    }
}
</script>

<?php include 'app/views/layouts/footer.php'; ?>
