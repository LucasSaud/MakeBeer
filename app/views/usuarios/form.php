<?php
$isEdit = isset($usuario) && !empty($usuario['id']);
$pageTitle = ($isEdit ? 'Editar' : 'Novo') . ' Usuário - ' . APP_NAME;
$activeMenu = 'usuarios';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title"><?= $isEdit ? 'Editar' : 'Novo' ?> Usuário</h1>
    <div class="page-actions">
        <a href="/usuarios" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dados do Usuário</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/usuarios/<?= $isEdit ? 'update/' . $usuario['id'] : 'store' ?>" id="formUsuario">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="form-group">
                        <label class="form-label">Nome Completo *</label>
                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            value="<?= $usuario['nome'] ?? '' ?>"
                            required
                            placeholder="Nome completo do usuário">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= $usuario['email'] ?? '' ?>"
                            required
                            placeholder="email@exemplo.com">
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Perfil de Acesso *</label>
                                <select name="perfil" id="perfilSelect" class="form-control form-select" required>
                                    <option value="">Selecione um perfil</option>
                                    <option value="administrador" <?= ($usuario['perfil'] ?? '') == 'administrador' ? 'selected' : '' ?>>Administrador</option>
                                    <option value="producao" <?= ($usuario['perfil'] ?? '') == 'producao' ? 'selected' : '' ?>>Produção</option>
                                    <option value="comprador" <?= ($usuario['perfil'] ?? '') == 'comprador' ? 'selected' : '' ?>>Comprador</option>
                                    <option value="consulta" <?= ($usuario['perfil'] ?? '') == 'consulta' ? 'selected' : '' ?>>Consulta</option>
                                </select>
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="ativo" class="form-control form-select">
                                    <option value="1" <?= ($usuario['ativo'] ?? true) ? 'selected' : '' ?>>Ativo</option>
                                    <option value="0" <?= isset($usuario['ativo']) && !$usuario['ativo'] ? 'selected' : '' ?>>Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php if (!$isEdit): ?>
                    <hr>
                    <h4 style="color: #2c3e50; margin-bottom: 1rem;">Senha</h4>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Senha *</label>
                                <input
                                    type="password"
                                    name="senha"
                                    id="senhaInput"
                                    class="form-control"
                                    required
                                    minlength="6"
                                    placeholder="Mínimo 6 caracteres">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Confirmar Senha *</label>
                                <input
                                    type="password"
                                    name="confirmar_senha"
                                    id="confirmarSenhaInput"
                                    class="form-control"
                                    required
                                    minlength="6"
                                    placeholder="Confirme a senha">
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        Para alterar a senha do usuário, use a opção "Resetar Senha" na página de detalhes.
                    </div>
                    <?php endif; ?>

                    <hr>

                    <div class="form-group">
                        <label class="form-label">Observações</label>
                        <textarea
                            name="observacoes"
                            class="form-control"
                            rows="3"
                            placeholder="Observações sobre o usuário"><?= $usuario['observacoes'] ?? '' ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center" style="margin-top: 2rem;">
                        <a href="/usuarios" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <?= $isEdit ? 'Atualizar' : 'Cadastrar' ?> Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Perfis de Acesso</h3>
            </div>
            <div class="card-body" id="perfilInfo">
                <p style="color: #6c757d;">Selecione um perfil para ver as permissões</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Instruções</h3>
            </div>
            <div class="card-body">
                <ul style="line-height: 1.8; color: #6c757d;">
                    <li>Preencha todos os campos obrigatórios (*)</li>
                    <li>O email será usado para login</li>
                    <li>A senha deve ter no mínimo 6 caracteres</li>
                    <li>Escolha o perfil adequado às funções do usuário</li>
                    <li>Usuários inativos não podem acessar o sistema</li>
                </ul>
            </div>
        </div>

        <?php if ($isEdit && $usuario['id'] != getCurrentUser()['id']): ?>
        <div class="card" style="border-color: #e74c3c; margin-top: 1rem;">
            <div class="card-header" style="background: #fee; border-bottom-color: #e74c3c;">
                <h3 class="card-title" style="color: #e74c3c;">Zona de Perigo</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Excluir este usuário removerá todo o histórico relacionado.
                </p>
                <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    onclick="confirmarExclusao(<?= $usuario['id'] ?>)">
                    Excluir Usuário
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Informações dos perfis
const perfisInfo = {
    'administrador': {
        titulo: 'Administrador',
        descricao: 'Acesso total ao sistema',
        permissoes: [
            'Acesso total ao sistema',
            'Gestão de usuários',
            'Configurações do sistema',
            'Todos os relatórios',
            'Gestão de fornecedores',
            'Entrada de estoque',
            'Registro de produção',
            'Consulta de estoque'
        ]
    },
    'producao': {
        titulo: 'Produção',
        descricao: 'Gerenciamento da produção',
        permissoes: [
            'Entrada de estoque',
            'Registro de produção',
            'Consulta de estoque',
            'Gestão de receitas',
            'Gestão de lotes'
        ]
    },
    'comprador': {
        titulo: 'Comprador',
        descricao: 'Gestão de compras e fornecedores',
        permissoes: [
            'Entrada de estoque',
            'Gestão de fornecedores',
            'Consulta de estoque',
            'Relatórios de compras'
        ]
    },
    'consulta': {
        titulo: 'Consulta',
        descricao: 'Apenas visualização',
        permissoes: [
            'Consulta de estoque',
            'Visualização de relatórios'
        ]
    }
};

// Atualizar informações ao selecionar perfil
document.getElementById('perfilSelect').addEventListener('change', function() {
    const perfil = this.value;
    const infoDiv = document.getElementById('perfilInfo');

    if (perfil && perfisInfo[perfil]) {
        const info = perfisInfo[perfil];
        let html = `
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">${info.titulo}</h4>
            <p style="color: #6c757d; margin-bottom: 1rem;">${info.descricao}</p>
            <strong>Permissões:</strong>
            <ul style="margin-top: 0.5rem; line-height: 1.8;">
        `;

        info.permissoes.forEach(perm => {
            html += `<li>${perm}</li>`;
        });

        html += '</ul>';
        infoDiv.innerHTML = html;
    } else {
        infoDiv.innerHTML = '<p style="color: #6c757d;">Selecione um perfil para ver as permissões</p>';
    }
});

<?php if (!$isEdit): ?>
// Validação de senhas
document.getElementById('formUsuario').addEventListener('submit', function(e) {
    const senha = document.getElementById('senhaInput').value;
    const confirmarSenha = document.getElementById('confirmarSenhaInput').value;

    if (senha !== confirmarSenha) {
        e.preventDefault();
        alert('As senhas não coincidem.');
        return false;
    }

    if (senha.length < 6) {
        e.preventDefault();
        alert('A senha deve ter no mínimo 6 caracteres.');
        return false;
    }

    return true;
});
<?php endif; ?>

<?php if ($isEdit): ?>
function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')) {
        window.location.href = '/usuarios/delete/' + id;
    }
}
<?php endif; ?>

// Inicializar informações do perfil se já houver um selecionado
<?php if ($isEdit && !empty($usuario['perfil'])): ?>
    document.getElementById('perfilSelect').dispatchEvent(new Event('change'));
<?php endif; ?>
</script>

<?php include 'app/views/layouts/footer.php'; ?>
