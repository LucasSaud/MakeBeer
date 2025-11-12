<?php
$pageTitle = 'Meu Perfil - ' . APP_NAME;
$activeMenu = 'usuarios';
include 'app/views/layouts/header.php';
$user = getCurrentUser();
?>

<div class="page-header">
    <h1 class="page-title">👤 Meu Perfil</h1>
    <p class="page-subtitle">Gerencie suas informações pessoais e configurações</p>
</div>

<div class="row">
    <div class="col col-8">
        <!-- Informações Pessoais -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Informações Pessoais</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/usuarios/atualizar-perfil" id="formPerfil">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="form-group">
                        <label class="form-label">Nome Completo *</label>
                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            value="<?= $user['nome'] ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= $user['email'] ?>"
                            required>
                    </div>

                    <div class="d-flex justify-content-end" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">
                            Atualizar Informações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alterar Senha -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Alterar Senha</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/usuarios/alterar-senha" id="formSenha">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="form-group">
                        <label class="form-label">Senha Atual *</label>
                        <input
                            type="password"
                            name="senha_atual"
                            class="form-control"
                            required
                            placeholder="Digite sua senha atual">
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Nova Senha *</label>
                                <input
                                    type="password"
                                    name="nova_senha"
                                    id="novaSenhaInput"
                                    class="form-control"
                                    required
                                    minlength="6"
                                    placeholder="Mínimo 6 caracteres">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Confirmar Nova Senha *</label>
                                <input
                                    type="password"
                                    name="confirmar_senha"
                                    id="confirmarNovaSenhaInput"
                                    class="form-control"
                                    required
                                    minlength="6"
                                    placeholder="Confirme a nova senha">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-warning">
                            Alterar Senha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <!-- Informações da Conta -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Informações da Conta</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Perfil de Acesso:</strong>
                    <p>
                        <?php
                        $perfilClass = match($user['perfil']) {
                            'administrador' => 'danger',
                            'producao' => 'primary',
                            'comprador' => 'warning',
                            'consulta' => 'secondary',
                            default => 'secondary'
                        };
                        ?>
                        <span class="badge badge-<?= $perfilClass ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                            <?= ucfirst($user['perfil']) ?>
                        </span>
                    </p>
                </div>

                <div class="mb-3">
                    <strong>Membro desde:</strong>
                    <p><?= formatDate($user['created_at']) ?></p>
                </div>

                <div>
                    <strong>Último Acesso:</strong>
                    <p><?= formatDateTime($user['ultimo_acesso'] ?? date('Y-m-d H:i:s')) ?></p>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Suas Estatísticas</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Acessos ao Sistema:</strong>
                    <h3 style="color: #3498db; margin: 0.5rem 0;">
                        <?= $user['total_acessos'] ?? 0 ?>
                    </h3>
                </div>

                <div class="mb-3">
                    <strong>Entradas Registradas:</strong>
                    <p><?= $user['total_entradas'] ?? 0 ?></p>
                </div>

                <div>
                    <strong>Lotes Criados:</strong>
                    <p><?= $user['total_lotes'] ?? 0 ?></p>
                </div>
            </div>
        </div>

        <!-- Dicas de Segurança -->
        <div class="card">
            <div class="card-header" style="background: #d1ecf1; border-bottom-color: #3498db;">
                <h3 class="card-title" style="color: #0c5460;">Dicas de Segurança</h3>
            </div>
            <div class="card-body">
                <ul style="color: #0c5460; line-height: 1.8;">
                    <li>Use uma senha forte e única</li>
                    <li>Nunca compartilhe sua senha</li>
                    <li>Altere sua senha periodicamente</li>
                    <li>Sempre faça logout ao sair</li>
                    <li>Verifique o último acesso regularmente</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Validação do formulário de senha
document.getElementById('formSenha').addEventListener('submit', function(e) {
    const novaSenha = document.getElementById('novaSenhaInput').value;
    const confirmarSenha = document.getElementById('confirmarNovaSenhaInput').value;

    if (novaSenha !== confirmarSenha) {
        e.preventDefault();
        alert('A nova senha e a confirmação não coincidem.');
        return false;
    }

    if (novaSenha.length < 6) {
        e.preventDefault();
        alert('A senha deve ter no mínimo 6 caracteres.');
        return false;
    }

    return true;
});
</script>

<?php include 'app/views/layouts/footer.php'; ?>
