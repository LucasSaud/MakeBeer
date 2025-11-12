<?php
$isEdit = isset($fornecedor) && !empty($fornecedor['id']);
$pageTitle = ($isEdit ? 'Editar' : 'Novo') . ' Fornecedor - ' . APP_NAME;
$activeMenu = 'fornecedores';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title"><?= $isEdit ? 'Editar' : 'Novo' ?> Fornecedor</h1>
    <div class="page-actions">
        <a href="/fornecedores" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dados do Fornecedor</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/fornecedores/<?= $isEdit ? 'update/' . $fornecedor['id'] : 'store' ?>" id="formFornecedor">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <h4 style="color: #2c3e50; margin-bottom: 1rem;">Informações Básicas</h4>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Nome/Razão Social *</label>
                                <input
                                    type="text"
                                    name="nome"
                                    class="form-control"
                                    value="<?= $fornecedor['nome'] ?? '' ?>"
                                    required
                                    placeholder="Nome do fornecedor">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Nome Fantasia</label>
                                <input
                                    type="text"
                                    name="nome_fantasia"
                                    class="form-control"
                                    value="<?= $fornecedor['nome_fantasia'] ?? '' ?>"
                                    placeholder="Nome fantasia">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">CNPJ</label>
                                <input
                                    type="text"
                                    name="cnpj"
                                    class="form-control"
                                    value="<?= $fornecedor['cnpj'] ?? '' ?>"
                                    placeholder="00.000.000/0000-00"
                                    maxlength="18">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Inscrição Estadual</label>
                                <input
                                    type="text"
                                    name="inscricao_estadual"
                                    class="form-control"
                                    value="<?= $fornecedor['inscricao_estadual'] ?? '' ?>"
                                    placeholder="000.000.000.000">
                            </div>
                        </div>
                    </div>

                    <h4 style="color: #2c3e50; margin: 2rem 0 1rem 0;">Contato</h4>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= $fornecedor['email'] ?? '' ?>"
                                    placeholder="contato@fornecedor.com">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Telefone *</label>
                                <input
                                    type="text"
                                    name="telefone"
                                    class="form-control"
                                    value="<?= $fornecedor['telefone'] ?? '' ?>"
                                    required
                                    placeholder="(00) 0000-0000">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pessoa de Contato</label>
                        <input
                            type="text"
                            name="contato"
                            class="form-control"
                            value="<?= $fornecedor['contato'] ?? '' ?>"
                            placeholder="Nome da pessoa responsável">
                    </div>

                    <h4 style="color: #2c3e50; margin: 2rem 0 1rem 0;">Endereço</h4>

                    <div class="row">
                        <div class="col col-3">
                            <div class="form-group">
                                <label class="form-label">CEP</label>
                                <input
                                    type="text"
                                    name="cep"
                                    class="form-control"
                                    value="<?= $fornecedor['cep'] ?? '' ?>"
                                    placeholder="00000-000"
                                    maxlength="9">
                            </div>
                        </div>

                        <div class="col col-9">
                            <div class="form-group">
                                <label class="form-label">Endereço</label>
                                <input
                                    type="text"
                                    name="endereco"
                                    class="form-control"
                                    value="<?= $fornecedor['endereco'] ?? '' ?>"
                                    placeholder="Rua, número, complemento">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Cidade</label>
                                <input
                                    type="text"
                                    name="cidade"
                                    class="form-control"
                                    value="<?= $fornecedor['cidade'] ?? '' ?>"
                                    placeholder="Cidade">
                            </div>
                        </div>

                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-control form-select">
                                    <option value="">Selecione</option>
                                    <?php
                                    $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                    foreach ($estados as $uf) {
                                        $selected = ($fornecedor['estado'] ?? '') == $uf ? 'selected' : '';
                                        echo "<option value='$uf' $selected>$uf</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h4 style="color: #2c3e50; margin: 2rem 0 1rem 0;">Informações Adicionais</h4>

                    <div class="form-group">
                        <label class="form-label">Observações</label>
                        <textarea
                            name="observacoes"
                            class="form-control"
                            rows="3"
                            placeholder="Observações sobre o fornecedor"><?= $fornecedor['observacoes'] ?? '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input
                                type="checkbox"
                                name="ativo"
                                value="1"
                                <?= ($fornecedor['ativo'] ?? true) ? 'checked' : '' ?>>
                            <span class="form-label" style="margin: 0;">Fornecedor ativo</span>
                        </label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center" style="margin-top: 2rem;">
                        <a href="/fornecedores" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <?= $isEdit ? 'Atualizar' : 'Cadastrar' ?> Fornecedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Instruções</h3>
            </div>
            <div class="card-body">
                <ul style="line-height: 1.8; color: #6c757d;">
                    <li>Preencha todos os campos obrigatórios (*)</li>
                    <li>O telefone é essencial para contato</li>
                    <li>CNPJ e email são opcionais mas recomendados</li>
                    <li>Use "Observações" para anotar prazos, condições especiais, etc.</li>
                    <li>Fornecedores inativos não aparecem nas seleções</li>
                </ul>
            </div>
        </div>

        <?php if ($isEdit): ?>
        <div class="card" style="border-color: #e74c3c;">
            <div class="card-header" style="background: #fee; border-bottom-color: #e74c3c;">
                <h3 class="card-title" style="color: #e74c3c;">Zona de Perigo</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Excluir este fornecedor removerá todo o histórico relacionado.
                </p>
                <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    onclick="confirmarExclusao(<?= $fornecedor['id'] ?>)">
                    Excluir Fornecedor
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Validação do formulário
document.getElementById('formFornecedor').addEventListener('submit', function(e) {
    const nome = document.querySelector('input[name="nome"]').value.trim();
    const telefone = document.querySelector('input[name="telefone"]').value.trim();

    if (!nome || !telefone) {
        e.preventDefault();
        alert('Por favor, preencha todos os campos obrigatórios.');
        return false;
    }

    return true;
});

// Máscara para CNPJ
document.querySelector('input[name="cnpj"]')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 14) {
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
        e.target.value = value;
    }
});

// Máscara para CEP
document.querySelector('input[name="cep"]')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 8) {
        value = value.replace(/^(\d{5})(\d)/, '$1-$2');
        e.target.value = value;
    }
});

<?php if ($isEdit): ?>
function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir este fornecedor? Esta ação não pode ser desfeita.')) {
        window.location.href = '/fornecedores/delete/' + id;
    }
}
<?php endif; ?>
</script>

<?php include 'app/views/layouts/footer.php'; ?>
