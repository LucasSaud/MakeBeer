<?php
$pageTitle = 'Configurações - ' . APP_NAME;
$activeMenu = 'configuracoes';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">⚙️ Configurações do Sistema</h1>
</div>

<div class="row">
    <div class="col col-8">
        <!-- Configurações Gerais -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Configurações Gerais</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/configuracoes/update">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="form-group">
                        <label class="form-label">Nome da Empresa</label>
                        <input type="text" name="empresa_nome" class="form-control" value="<?= APP_NAME ?>" readonly>
                        <small style="color: #6c757d;">Definido em app/config/config.php</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Moeda Padrão</label>
                        <select name="moeda" class="form-control form-select" disabled>
                            <option value="BRL">Real (R$)</option>
                            <option value="USD">Dólar (US$)</option>
                            <option value="EUR">Euro (€)</option>
                        </select>
                        <small style="color: #6c757d;">Em desenvolvimento</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fuso Horário</label>
                        <select name="timezone" class="form-control form-select" disabled>
                            <option value="America/Sao_Paulo">América/São Paulo (BRT)</option>
                            <option value="America/New_York">América/Nova York (EST)</option>
                            <option value="Europe/London">Europa/Londres (GMT)</option>
                        </select>
                        <small style="color: #6c757d;">Em desenvolvimento</small>
                    </div>
                </form>
            </div>
        </div>

        <!-- Configurações de Estoque -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Configurações de Estoque</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Método de Valorização</label>
                    <select name="metodo_valorizacao" class="form-control form-select" disabled>
                        <option value="media">Custo Médio Ponderado (Ativo)</option>
                        <option value="fifo">FIFO (First In, First Out)</option>
                        <option value="lifo">LIFO (Last In, First Out)</option>
                    </select>
                    <small style="color: #6c757d;">Sistema atualmente usa Custo Médio Ponderado</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Alerta de Estoque Baixo</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="alertaEmail" checked disabled>
                        <label class="form-check-label" for="alertaEmail">
                            Enviar e-mail quando estoque atingir nível mínimo
                        </label>
                    </div>
                    <small style="color: #6c757d;">Em desenvolvimento</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <!-- Informações do Sistema -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Informações do Sistema</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Versão:</strong>
                    <p>1.0.0</p>
                </div>

                <div class="mb-3">
                    <strong>Banco de Dados:</strong>
                    <p>MySQL</p>
                </div>

                <div class="mb-3">
                    <strong>PHP:</strong>
                    <p><?= phpversion() ?></p>
                </div>

                <div class="mb-3">
                    <strong>Usuário Logado:</strong>
                    <p><?= $user['nome'] ?> (<?= ucfirst($user['perfil']) ?>)</p>
                </div>
            </div>
        </div>

        <!-- Ações do Sistema -->
        <?php if ($user['perfil'] === 'administrador'): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Ações do Sistema</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h5>Backup do Banco de Dados</h5>
                    <p style="color: #6c757d; font-size: 0.9em;">Criar backup completo do banco de dados</p>
                    <button onclick="fazerBackup()" class="btn btn-primary btn-sm">
                        Fazer Backup
                    </button>
                </div>

                <hr>

                <div>
                    <h5>Limpar Cache</h5>
                    <p style="color: #6c757d; font-size: 0.9em;">Limpar cache de sessões e arquivos temporários</p>
                    <button onclick="limparCache()" class="btn btn-secondary btn-sm">
                        Limpar Cache
                    </button>
                </div>
            </div>
        </div>

        <div class="card" style="border-color: #e74c3c;">
            <div class="card-header" style="background: #fee; border-bottom-color: #e74c3c;">
                <h3 class="card-title" style="color: #e74c3c;">Zona de Perigo</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Ações irreversíveis que afetam todo o sistema.
                </p>
                <button type="button" class="btn btn-danger btn-sm" disabled>
                    Resetar Sistema
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function fazerBackup() {
    if (confirm('Deseja fazer backup do banco de dados?')) {
        window.location.href = '/configuracoes/backup';
    }
}

function limparCache() {
    if (confirm('Deseja limpar o cache do sistema?')) {
        alert('Funcionalidade em desenvolvimento');
    }
}
</script>

<?php include 'app/views/layouts/footer.php'; ?>
