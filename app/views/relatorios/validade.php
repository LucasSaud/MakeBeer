<?php
$pageTitle = 'Relatório de Validades - ' . APP_NAME;
$activeMenu = 'relatorios';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Relatório de Validades</h1>
        <p class="page-subtitle">Controle de vencimentos de insumos e produtos</p>
    </div>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-secondary">Imprimir</button>
        <a href="/relatorios" class="btn btn-primary">Voltar</a>
    </div>
</div>

<!-- Resumo de Alertas -->
<div class="row mb-4">
    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #e74c3c; margin-bottom: 0.5rem;"><?= $resumo['vencidos'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Itens Vencidos</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #f39c12; margin-bottom: 0.5rem;"><?= $resumo['vence_7_dias'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Vence em 7 Dias</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= $resumo['vence_30_dias'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Vence em 30 Dias</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #27ae60; margin-bottom: 0.5rem;"><?= formatMoney($resumo['valor_em_risco'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Valor em Risco</p>
            </div>
        </div>
    </div>
</div>

<!-- Insumos Vencidos ou Próximos ao Vencimento -->
<?php if (!empty($insumos_vencendo)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Insumos - Vencimentos</h3>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th>Lote</th>
                    <th>Quantidade</th>
                    <th>Data de Validade</th>
                    <th>Dias Restantes</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($insumos_vencendo as $item): ?>
                <tr>
                    <td>
                        <strong><?= $item['insumo_nome'] ?></strong><br>
                        <small style="color: #6c757d;"><?= $item['categoria_nome'] ?></small>
                    </td>
                    <td><?= $item['lote'] ?></td>
                    <td><?= formatQuantity($item['quantidade'], $item['unidade_medida']) ?></td>
                    <td><?= formatDate($item['data_validade']) ?></td>
                    <td>
                        <?php
                        $diasRestantes = daysUntil($item['data_validade']);
                        if ($diasRestantes < 0) {
                            echo '<strong style="color: #e74c3c;">' . abs($diasRestantes) . ' dias atrás</strong>';
                        } else {
                            echo $diasRestantes . ' dias';
                        }
                        ?>
                    </td>
                    <td><?= formatMoney($item['valor']) ?></td>
                    <td>
                        <?php
                        if ($diasRestantes <= 0) {
                            echo '<span class="badge badge-danger">VENCIDO</span>';
                        } elseif ($diasRestantes <= 7) {
                            echo '<span class="badge badge-danger">URGENTE</span>';
                        } elseif ($diasRestantes <= 15) {
                            echo '<span class="badge badge-warning">ATENÇÃO</span>';
                        } elseif ($diasRestantes <= 30) {
                            echo '<span class="badge badge-info">PRÓXIMO</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Produtos Próximos ao Vencimento -->
<?php if (!empty($produtos_vencendo)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Produtos Finais - Vencimentos</h3>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>SKU</th>
                    <th>Quantidade</th>
                    <th>Data Envase</th>
                    <th>Data Validade</th>
                    <th>Dias Restantes</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos_vencendo as $prod): ?>
                <tr>
                    <td><strong><?= $prod['nome'] ?></strong></td>
                    <td><?= $prod['sku'] ?></td>
                    <td><?= $prod['estoque_atual'] ?? 0 ?> un</td>
                    <td><?= formatDate($prod['data_envase']) ?></td>
                    <td><?= formatDate($prod['data_validade']) ?></td>
                    <td>
                        <?php
                        $diasRestantes = daysUntil($prod['data_validade']);
                        if ($diasRestantes < 0) {
                            echo '<strong style="color: #e74c3c;">' . abs($diasRestantes) . ' dias atrás</strong>';
                        } else {
                            echo $diasRestantes . ' dias';
                        }
                        ?>
                    </td>
                    <td><?= formatMoney($prod['preco_venda'] * ($prod['estoque_atual'] ?? 0)) ?></td>
                    <td>
                        <?php
                        if ($diasRestantes <= 0) {
                            echo '<span class="badge badge-danger">VENCIDO</span>';
                        } elseif ($diasRestantes <= 15) {
                            echo '<span class="badge badge-danger">URGENTE</span>';
                        } elseif ($diasRestantes <= 30) {
                            echo '<span class="badge badge-warning">ATENÇÃO</span>';
                        } elseif ($diasRestantes <= 60) {
                            echo '<span class="badge badge-info">PRÓXIMO</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Recomendações -->
<div class="card">
    <div class="card-header" style="background: #fff3cd; border-bottom-color: #f39c12;">
        <h3 class="card-title" style="color: #856404;">Recomendações</h3>
    </div>
    <div class="card-body">
        <ul style="color: #856404; line-height: 2;">
            <?php if ($resumo['vencidos'] > 0): ?>
                <li><strong>ATENÇÃO:</strong> Existem <?= $resumo['vencidos'] ?> itens vencidos que devem ser descartados imediatamente.</li>
            <?php endif; ?>

            <?php if ($resumo['vence_7_dias'] > 0): ?>
                <li><strong>URGENTE:</strong> <?= $resumo['vence_7_dias'] ?> itens vencerão nos próximos 7 dias. Priorize o uso.</li>
            <?php endif; ?>

            <?php if ($resumo['vence_30_dias'] > 0): ?>
                <li>Planeje a produção considerando <?= $resumo['vence_30_dias'] ?> itens que vencerão em 30 dias.</li>
            <?php endif; ?>

            <li>Implementar sistema FIFO (First In, First Out) rigorosamente.</li>
            <li>Revisar quantidades de compra para evitar desperdícios.</li>
            <li>Verificar diariamente este relatório para ações preventivas.</li>
        </ul>
    </div>
</div>

<div style="margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #e9ecef; text-align: center; color: #6c757d;">
    <p>Relatório gerado em: <?= formatDateTime(date('Y-m-d H:i:s')) ?></p>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
