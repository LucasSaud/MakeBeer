<?php
$pageTitle = 'Setores da Câmara Fria';
$activeMenu = 'camarafria';
include 'app/views/layouts/header.php';
?>

        <div class="row mb-3">
            <div class="col">
                <h2><i class="fas fa-th-large"></i> Setores da Câmara Fria</h2>
            </div>
            <div class="col-auto">
                <a href="/camarafria" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="/camarafria/criarSetor" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Setor
                </a>
            </div>
        </div>

        <div class="row">
            <?php if (empty($setores)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Nenhum setor cadastrado. <a href="/camarafria/criarSetor">Criar primeiro setor</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($setores as $setor): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?= htmlspecialchars($setor['nome']) ?>
                                    <?php if (!$setor['ativo']): ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </h5>

                                <?php if ($setor['descricao']): ?>
                                    <p class="card-text text-muted small"><?= htmlspecialchars($setor['descricao']) ?></p>
                                <?php endif; ?>

                                <hr>

                                <div class="mb-3">
                                    <strong>Capacidade:</strong> <?= $setor['capacidade_maxima'] ?> unidades<br>
                                    <strong>Temperatura Ideal:</strong> <?= $setor['temperatura_ideal'] ? $setor['temperatura_ideal'] . '°C' : 'N/D' ?><br>
                                    <strong>Ocupação Atual:</strong> <?= $setor['quantidade_total'] ?> un. (<?= number_format($setor['percentual_ocupacao'], 1) ?>%)
                                </div>

                                <div class="progress mb-3" style="height: 20px;">
                                    <div class="progress-bar bg-<?= $setor['percentual_ocupacao'] > 80 ? 'danger' : ($setor['percentual_ocupacao'] > 50 ? 'warning' : 'success') ?>"
                                         style="width: <?= min($setor['percentual_ocupacao'], 100) ?>%">
                                        <?= number_format($setor['percentual_ocupacao'], 1) ?>%
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <span class="badge bg-info"><?= $setor['produtos_diferentes'] ?> produto(s) diferentes</span>
                                    <span class="badge bg-secondary"><?= $setor['total_localizacoes'] ?> localização(ões)</span>
                                </div>

                                <div class="btn-group w-100" role="group">
                                    <a href="/camarafria/verSetor?id=<?= $setor['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="/camarafria/editarSetor?id=<?= $setor['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>


<?php include 'app/views/layouts/footer.php'; ?>
