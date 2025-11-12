<?php
$pageTitle = ($setor ? 'Editar' : 'Novo') . ' Setor';
$activeMenu = 'camarafria';
include 'app/views/layouts/header.php';
?>

        <div class="row mb-3">
            <div class="col">
                <h2><?= $setor ? 'Editar' : 'Novo' ?> Setor da Câmara Fria</h2>
            </div>
            <div class="col-auto">
                <a href="/camarafria/setores" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="/camarafria/<?= $setor ? 'atualizarSetor' : 'salvarSetor' ?>">
                    <?php if ($setor): ?>
                        <input type="hidden" name="id" value="<?= $setor['id'] ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Setor *</label>
                                <input type="text" class="form-control" id="nome" name="nome"
                                       value="<?= $setor['nome'] ?? '' ?>" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="capacidade_maxima" class="form-label">Capacidade Máxima</label>
                                <input type="number" class="form-control" id="capacidade_maxima"
                                       name="capacidade_maxima" value="<?= $setor['capacidade_maxima'] ?? 0 ?>"
                                       min="0" step="1">
                                <small class="text-muted">Em unidades</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="temperatura_ideal" class="form-label">Temperatura Ideal</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="temperatura_ideal"
                                           name="temperatura_ideal" value="<?= $setor['temperatura_ideal'] ?? '' ?>"
                                           step="0.1">
                                    <span class="input-group-text">°C</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao"
                                  rows="3"><?= $setor['descricao'] ?? '' ?></textarea>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="/camarafria/setores" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Setor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<?php include 'app/views/layouts/footer.php'; ?>
