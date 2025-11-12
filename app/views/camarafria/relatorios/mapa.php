<?php
$pageTitle = 'Mapa de Ocupação - Câmara Fria';
$activeMenu = 'camarafria';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">🗺️ Mapa de Ocupação da Câmara Fria</h1>
    <div>
        <a href="/camarafria" class="btn btn-secondary">
            Voltar ao Dashboard
        </a>
    </div>
</div>

<!-- Estatísticas Gerais -->
<div class="row mb-4">
    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= $estatisticas['capacidade_total'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Capacidade Total</p>
            </div>
        </div>
    </div>
    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #2ecc71; margin-bottom: 0.5rem;"><?= $estatisticas['quantidade_total_estocada'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Quantidade Estocada</p>
            </div>
        </div>
    </div>
    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #9b59b6; margin-bottom: 0.5rem;"><?= $estatisticas['produtos_estocados'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Produtos Diferentes</p>
            </div>
        </div>
    </div>
    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: <?= ($estatisticas['ocupacao_geral'] ?? 0) > 80 ? '#e74c3c' : (($estatisticas['ocupacao_geral'] ?? 0) > 50 ? '#f39c12' : '#2ecc71') ?>; margin-bottom: 0.5rem;">
                    <?= number_format($estatisticas['ocupacao_geral'] ?? 0, 1) ?>%
                </h3>
                <p style="margin: 0; color: #6c757d;">Ocupação Geral</p>
            </div>
        </div>
    </div>
</div>

<!-- Mapa Visual dos Setores -->
<div class="card mb-4">
    <div class="card-header">
        <h5 style="margin: 0;">Ocupação por Setor</h5>
    </div>
    <div class="card-body">
        <?php if (empty($setores)): ?>
            <div class="alert alert-info">Nenhum setor cadastrado</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($setores as $setor): ?>
                    <?php
                        $ocupacao = $setor['percentual_ocupacao'] ?? 0;
                        $cor = $ocupacao > 80 ? '#e74c3c' : ($ocupacao > 50 ? '#f39c12' : '#2ecc71');
                        $corTexto = $ocupacao > 80 ? 'danger' : ($ocupacao > 50 ? 'warning' : 'success');
                    ?>
                    <div class="col col-6 mb-4">
                        <div class="card" style="border-left: 4px solid <?= $cor ?>;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 style="margin: 0;"><?= htmlspecialchars($setor['nome']) ?></h4>
                                    <span class="badge badge-<?= $corTexto ?>" style="font-size: 1rem;">
                                        <?= number_format($ocupacao, 1) ?>%
                                    </span>
                                </div>

                                <?php if ($setor['descricao']): ?>
                                    <p style="color: #6c757d; margin-bottom: 1rem;"><?= htmlspecialchars($setor['descricao']) ?></p>
                                <?php endif; ?>

                                <div class="row mb-3">
                                    <div class="col col-4">
                                        <div style="text-align: center; padding: 0.5rem; background: #f8f9fa; border-radius: 5px;">
                                            <div style="font-size: 1.5rem; font-weight: bold; color: #3498db;">
                                                <?= $setor['quantidade_total'] ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #6c757d;">Atual</div>
                                        </div>
                                    </div>
                                    <div class="col col-4">
                                        <div style="text-align: center; padding: 0.5rem; background: #f8f9fa; border-radius: 5px;">
                                            <div style="font-size: 1.5rem; font-weight: bold; color: #2ecc71;">
                                                <?= $setor['capacidade_maxima'] ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #6c757d;">Máxima</div>
                                        </div>
                                    </div>
                                    <div class="col col-4">
                                        <div style="text-align: center; padding: 0.5rem; background: #f8f9fa; border-radius: 5px;">
                                            <div style="font-size: 1.5rem; font-weight: bold; color: #9b59b6;">
                                                <?= $setor['produtos_diferentes'] ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #6c757d;">Produtos</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Barra de Progresso -->
                                <div style="background: #ecf0f1; border-radius: 10px; height: 30px; position: relative; overflow: hidden; margin-bottom: 1rem;">
                                    <div style="background: <?= $cor ?>; height: 100%; width: <?= min($ocupacao, 100) ?>%; transition: width 0.3s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                        <?php if ($ocupacao > 10): ?>
                                            <?= number_format($ocupacao, 1) ?>%
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Informações Adicionais -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge badge-info"><?= $setor['total_localizacoes'] ?> localização(ões)</span>
                                        <?php if ($setor['temperatura_ideal']): ?>
                                            <span class="badge badge-secondary"><?= $setor['temperatura_ideal'] ?>°C ideal</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="/camarafria/verSetor?id=<?= $setor['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        Ver Detalhes →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Legenda -->
<div class="card">
    <div class="card-header">
        <h5 style="margin: 0;">Legenda de Ocupação</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col col-4">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; background: #2ecc71; border-radius: 5px;"></div>
                    <div>
                        <strong>Normal (0-50%)</strong>
                        <div style="font-size: 0.875rem; color: #6c757d;">Capacidade disponível</div>
                    </div>
                </div>
            </div>
            <div class="col col-4">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; background: #f39c12; border-radius: 5px;"></div>
                    <div>
                        <strong>Atenção (50-80%)</strong>
                        <div style="font-size: 0.875rem; color: #6c757d;">Ocupação moderada</div>
                    </div>
                </div>
            </div>
            <div class="col col-4">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; background: #e74c3c; border-radius: 5px;"></div>
                    <div>
                        <strong>Crítico (80-100%)</strong>
                        <div style="font-size: 0.875rem; color: #6c757d;">Capacidade quase esgotada</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
