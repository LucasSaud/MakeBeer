<?php
$pageTitle = 'Relatório de Custos - ' . APP_NAME;
$activeMenu = 'relatorios';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Relatório de Custos e Rentabilidade</h1>
        <p class="page-subtitle">Análise de custos, preços e margens</p>
    </div>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-secondary">Imprimir</button>
        <a href="/relatorios" class="btn btn-primary">Voltar</a>
    </div>
</div>

<!-- Resumo Geral -->
<div class="row mb-4">
    <div class="col col-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #e74c3c; margin-bottom: 0.5rem;"><?= formatMoney($resumo['custo_medio_litro'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Custo Médio por Litro</p>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #27ae60; margin-bottom: 0.5rem;"><?= formatMoney($resumo['preco_medio_venda'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Preço Médio de Venda</p>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= number_format($resumo['margem_media'] ?? 0, 2) ?>%</h3>
                <p style="margin: 0; color: #6c757d;">Margem Média</p>
            </div>
        </div>
    </div>
</div>

<!-- Custos por Receita -->
<?php if (!empty($por_receita)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Custos por Receita</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Receita</th>
                    <th>Volume Padrão</th>
                    <th>Custo Total</th>
                    <th>Custo/Litro</th>
                    <th>Lotes Produzidos</th>
                    <th>Custo Médio Real</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($por_receita as $rec): ?>
                <tr>
                    <td><strong><?= $rec['nome'] ?></strong></td>
                    <td><?= formatQuantity($rec['volume'], 'L') ?></td>
                    <td><?= formatMoney($rec['custo_estimado']) ?></td>
                    <td><?= formatMoney($rec['custo_litro']) ?></td>
                    <td><?= $rec['lotes_produzidos'] ?></td>
                    <td>
                        <?php if ($rec['custo_real']): ?>
                            <?= formatMoney($rec['custo_real']) ?>
                            <?php
                            $variacao = (($rec['custo_real'] - $rec['custo_estimado']) / $rec['custo_estimado']) * 100;
                            $variacaoClass = $variacao > 0 ? 'danger' : 'success';
                            ?>
                            <br><small class="badge badge-<?= $variacaoClass ?>"><?= ($variacao > 0 ? '+' : '') . number_format($variacao, 2) ?>%</small>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Rentabilidade por Produto -->
<?php if (!empty($produtos)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Rentabilidade por Produto</h3>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Custo Produção</th>
                    <th>Preço Venda</th>
                    <th>Margem</th>
                    <th>Lucro Unitário</th>
                    <th>Estoque</th>
                    <th>Lucro Potencial</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $prod): ?>
                <tr>
                    <td><strong><?= $prod['nome'] ?></strong></td>
                    <td><?= formatMoney($prod['custo_producao']) ?></td>
                    <td><?= formatMoney($prod['preco_venda']) ?></td>
                    <td>
                        <?php
                        $margem = $prod['custo_producao'] > 0
                            ? (($prod['preco_venda'] - $prod['custo_producao']) / $prod['custo_producao']) * 100
                            : 0;
                        $margemClass = $margem > 30 ? 'success' : ($margem > 15 ? 'warning' : 'danger');
                        ?>
                        <span class="badge badge-<?= $margemClass ?>"><?= number_format($margem, 2) ?>%</span>
                    </td>
                    <td><?= formatMoney($prod['preco_venda'] - $prod['custo_producao']) ?></td>
                    <td><?= $prod['estoque_atual'] ?? 0 ?> un</td>
                    <td><?= formatMoney(($prod['preco_venda'] - $prod['custo_producao']) * ($prod['estoque_atual'] ?? 0)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Análise de Insumos Mais Caros -->
<?php if (!empty($insumos_caros)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Insumos com Maior Impacto nos Custos</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th>Categoria</th>
                    <th>Custo Médio</th>
                    <th>Consumo Mensal</th>
                    <th>Gasto Mensal</th>
                    <th>% do Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($insumos_caros as $insumo): ?>
                <tr>
                    <td><strong><?= $insumo['nome'] ?></strong></td>
                    <td><?= $insumo['categoria_nome'] ?></td>
                    <td><?= formatMoney($insumo['preco_medio'] ?? 0) ?></td>
                    <td><?= formatQuantity($insumo['consumo_mensal'], $insumo['unidade_medida']) ?></td>
                    <td><?= formatMoney($insumo['gasto_mensal']) ?></td>
                    <td><?= number_format($insumo['percentual'], 2) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div style="margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #e9ecef; text-align: center; color: #6c757d;">
    <p>Relatório gerado em: <?= formatDateTime(date('Y-m-d H:i:s')) ?></p>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
