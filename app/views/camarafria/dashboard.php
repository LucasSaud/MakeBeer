<?php
$pageTitle = 'Câmara Fria - Dashboard';
$activeMenu = 'camarafria';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">❄️ Câmara Fria</h1>
    <div class="d-flex gap-2">
        <a href="/camarafria/mapaOcupacao" class="btn btn-primary btn-sm">Mapa de Ocupação</a>
        <a href="/camarafria/produtosVencimento" class="btn btn-warning btn-sm">Produtos Vencendo</a>
        <a href="/camarafria/movimentacoes" class="btn btn-secondary btn-sm">Histórico</a>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= $estatisticas['total_setores'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Setores Ativos</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #27ae60; margin-bottom: 0.5rem;"><?= $estatisticas['capacidade_total'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Capacidade Total (un)</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= $estatisticas['produtos_estocados'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Produtos Estocados</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: <?= ($estatisticas['ocupacao_geral'] ?? 0) > 80 ? '#e74c3c' : (($estatisticas['ocupacao_geral'] ?? 0) > 50 ? '#f39c12' : '#27ae60') ?>; margin-bottom: 0.5rem;">
                    <?= number_format($estatisticas['ocupacao_geral'] ?? 0, 1) ?>%
                </h3>
                <p style="margin: 0; color: #6c757d;">Ocupação Geral</p>
            </div>
        </div>
    </div>
</div>

<!-- Setores da Câmara Fria -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Setores da Câmara Fria</h3>
        <a href="/camarafria/setores" class="btn btn-primary btn-sm">Ver Todos os Setores</a>
    </div>
    <div class="card-body">
        <?php if (empty($setores)): ?>
            <div class="alert alert-info">
                Nenhum setor cadastrado. <a href="/camarafria/setores/create">Cadastre o primeiro setor</a>.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($setores as $setor): ?>
                    <div class="col col-4 mb-3">
                        <div class="card" style="border: 1px solid #e9ecef;">
                            <div class="card-body">
                                <h4 style="margin-bottom: 1rem; color: #2c3e50; font-size: 1.1rem;">
                                    <?= htmlspecialchars($setor['nome']) ?>
                                </h4>

                                <div style="margin-bottom: 1rem;">
                                    <p style="margin: 0; color: #6c757d; font-size: 0.9rem;">
                                        <strong>Ocupação:</strong> <?= $setor['quantidade_total'] ?> / <?= $setor['capacidade_maxima'] ?> un.
                                    </p>

                                    <!-- Barra de progresso customizada -->
                                    <div style="background: #e9ecef; height: 12px; border-radius: 6px; margin-top: 0.5rem; overflow: hidden;">
                                        <?php
                                        $percentual = $setor['percentual_ocupacao'];
                                        $cor = $percentual > 80 ? '#e74c3c' : ($percentual > 50 ? '#f39c12' : '#27ae60');
                                        ?>
                                        <div style="background: <?= $cor ?>; height: 100%; width: <?= min($percentual, 100) ?>%; transition: width 0.3s ease;"></div>
                                    </div>

                                    <p style="margin: 0.5rem 0 0 0; color: #6c757d; font-size: 0.85rem;">
                                        <?= number_format($percentual, 1) ?>% ocupado • <?= $setor['produtos_diferentes'] ?> produto(s)
                                    </p>
                                </div>

                                <a href="/camarafria/verSetor?id=<?= $setor['id'] ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Seção de 2 Colunas -->
<div class="row">
    <!-- Movimentações Recentes -->
    <div class="col col-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Movimentações Recentes</h3>
                <a href="/camarafria/movimentacoes" class="btn btn-secondary btn-sm">Ver Todas</a>
            </div>
            <div class="card-body">
                <?php if (empty($movimentacoes)): ?>
                    <div class="alert alert-info">
                        Nenhuma movimentação registrada ainda.
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Data/Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($movimentacoes, 0, 5) as $mov): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $badgeClass = match($mov['tipo_movimentacao']) {
                                            'entrada' => 'success',
                                            'saida' => 'danger',
                                            'transferencia' => 'info',
                                            'ajuste' => 'warning',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?>">
                                            <?= ucfirst($mov['tipo_movimentacao']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($mov['produto_nome'] ?? 'N/A') ?></td>
                                    <td><?= $mov['quantidade'] ?> un.</td>
                                    <td style="color: #6c757d; font-size: 0.9rem;">
                                        <?= date('d/m H:i', strtotime($mov['data_movimentacao'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Produtos Próximos ao Vencimento -->
    <div class="col col-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Produtos Próximos ao Vencimento</h3>
                <a href="/camarafria/produtosVencimento" class="btn btn-warning btn-sm">Ver Todos</a>
            </div>
            <div class="card-body">
                <?php if (empty($produtos_vencimento)): ?>
                    <div class="alert alert-success">
                        Nenhum produto próximo ao vencimento. Tudo sob controle!
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Setor</th>
                                <th>Dias Restantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($produtos_vencimento, 0, 5) as $prod): ?>
                                <tr>
                                    <td><?= htmlspecialchars($prod['produto_nome'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($prod['setor_nome'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php
                                        $dias = $prod['dias_restantes'];
                                        $badgeClass = $dias <= 7 ? 'danger' : ($dias <= 15 ? 'warning' : 'info');
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?>">
                                            <?= $dias ?> dias
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
