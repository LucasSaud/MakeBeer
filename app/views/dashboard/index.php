<?php
$pageTitle = 'Dashboard - ' . APP_NAME;
$activeMenu = 'dashboard';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">📊 Dashboard</h1>
    <div>
        <span class="badge badge-info">Atualizado: <?= formatDateTime(date('Y-m-d H:i:s')) ?></span>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= $stats['total_insumos'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Total de Insumos</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #e74c3c; margin-bottom: 0.5rem;"><?= $stats['insumos_estoque_baixo'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Estoque Baixo</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #f39c12; margin-bottom: 0.5rem;"><?= $stats['lotes_em_producao'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Lotes em Produção</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #27ae60; margin-bottom: 0.5rem;"><?= $stats['produtos_finalizados'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Produtos Finalizados</p>
            </div>
        </div>
    </div>
</div>

<!-- Alertas e Avisos -->
<?php if (!empty($alertas) && is_array($alertas)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Alertas e Avisos</h3>
    </div>
    <div class="card-body">
        <?php foreach ($alertas as $alerta): ?>
            <?php if (is_array($alerta) && isset($alerta['tipo'], $alerta['titulo'], $alerta['mensagem'])): ?>
                <div class="alert alert-<?= htmlspecialchars($alerta['tipo']) ?>">
                    <strong><?= htmlspecialchars($alerta['titulo']) ?>:</strong> <?= htmlspecialchars($alerta['mensagem']) ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Insumos com Estoque Baixo -->
<?php if (!empty($insumos_estoque_baixo)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Insumos com Estoque Baixo</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th>Estoque Atual</th>
                    <th>Estoque Mínimo</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($insumos_estoque_baixo as $insumo): ?>
                <tr>
                    <td><?= $insumo['nome'] ?></td>
                    <td><?= formatQuantity($insumo['estoque_atual'], $insumo['unidade_medida']) ?></td>
                    <td><?= formatQuantity($insumo['estoque_minimo'], $insumo['unidade_medida']) ?></td>
                    <td>
                        <?php if ($insumo['estoque_atual'] <= 0): ?>
                            <span class="badge badge-danger">Sem Estoque</span>
                        <?php elseif ($insumo['estoque_atual'] < $insumo['estoque_minimo'] * 0.5): ?>
                            <span class="badge badge-danger">Crítico</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Baixo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/insumos/viewInsumo?id=<?= $insumo['id'] ?>" class="btn btn-sm btn-primary">Ver Detalhes</a>
                        <a href="/entradas/create?insumo_id=<?= $insumo['id'] ?>" class="btn btn-sm btn-success">Registrar Entrada</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Produtos com Validade Próxima -->
<?php if (!empty($validades_proximas)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Validades Próximas (30 dias)</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th>Lote</th>
                    <th>Quantidade</th>
                    <th>Validade</th>
                    <th>Dias Restantes</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($validades_proximas as $item): ?>
                <tr>
                    <td><?= $item['insumo_nome'] ?></td>
                    <td><?= $item['lote'] ?></td>
                    <td><?= formatQuantity($item['quantidade'], $item['unidade_medida']) ?></td>
                    <td><?= formatDate($item['data_validade']) ?></td>
                    <td><?= $item['dias_restantes'] ?> dias</td>
                    <td>
                        <?php if ($item['dias_restantes'] <= 0): ?>
                            <span class="badge badge-danger">Vencido</span>
                        <?php elseif ($item['dias_restantes'] <= 7): ?>
                            <span class="badge badge-danger">Urgente</span>
                        <?php elseif ($item['dias_restantes'] <= 15): ?>
                            <span class="badge badge-warning">Atenção</span>
                        <?php else: ?>
                            <span class="badge badge-info">Próximo</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Lotes em Produção -->
<?php if (!empty($lotes_em_producao)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Lotes em Produção</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Receita</th>
                    <th>Quantidade</th>
                    <th>Data Início</th>
                    <th>Previsão Conclusão</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lotes_em_producao as $lote): ?>
                <tr>
                    <td><strong><?= $lote['codigo_lote'] ?? $lote['codigo'] ?? $lote['id'] ?? 'N/A' ?></strong></td>
                    <td><?= $lote['receita_nome'] ?? $lote['receita'] ?? $lote['nome'] ?? 'N/A' ?></td>
                    <td><?= formatQuantity($lote['quantidade_produzida'] ?? $lote['quantidade'] ?? 0, 'L') ?></td>
                    <td><?= formatDate($lote['data_inicio'] ?? '') ?></td>
                    <td><?= formatDate($lote['previsao_conclusao'] ?? '') ?></td>
                    <td>
                        <?php
                        $status = $lote['status'] ?? 'desconhecido';
                        $statusClass = match($status) {
                            'planejado' => 'secondary',
                            'em_andamento', 'em_producao' => 'info',
                            'fermentacao' => 'warning',
                            'maturacao' => 'warning',
                            'finalizado' => 'success',
                            'cancelado' => 'danger',
                            default => 'secondary'
                        };
                        ?>
                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(str_replace(['_', 'em '], ' ', $status)) ?></span>
                    </td>
                    <td>
                        <a href="/producao/viewProducao?id=<?= $lote['id'] ?? '' ?>" class="btn btn-sm btn-primary">Ver Detalhes</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Estatísticas do Mês -->
<div class="row">
    <div class="col col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Entradas do Mês</h3>
            </div>
            <div class="card-body">
                <h2 style="color: #27ae60; margin: 0;"><?= formatMoney($stats['valor_entradas_mes'] ?? 0) ?></h2>
                <p style="margin: 0.5rem 0 0 0; color: #6c757d;"><?= $stats['total_entradas_mes'] ?? 0 ?> entradas registradas</p>
            </div>
        </div>
    </div>

    <div class="col col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Produção do Mês</h3>
            </div>
            <div class="card-body">
                <h2 style="color: #3498db; margin: 0;"><?= formatQuantity($stats['litros_produzidos_mes'] ?? 0, 'L') ?></h2>
                <p style="margin: 0.5rem 0 0 0; color: #6c757d;"><?= $stats['lotes_finalizados_mes'] ?? 0 ?> lotes finalizados</p>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
