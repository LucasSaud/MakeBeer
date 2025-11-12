<?php
$pageTitle = 'Detalhes do Produto - ' . APP_NAME;
$activeMenu = 'produtos';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">Detalhes do Produto</h1>
    <div class="page-actions d-flex gap-2">
        <a href="/produtos/edit?id=<?= $produto['id'] ?>" class="btn-icon btn-warning" data-tooltip="Editar produto"><?= icon('edit') ?></a>
        <a href="/produtos" class="btn-icon btn-secondary" data-tooltip="Voltar"><?= icon('back') ?></a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <!-- Informações do Produto -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Informações do Produto</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Nome:</strong>
                        <h3 style="color: #2c3e50; margin: 0.5rem 0;"><?= $produto['nome'] ?></h3>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>SKU:</strong>
                        <p style="font-size: 1.2rem;"><strong><?= $produto['sku'] ?? 'N/A' ?></strong></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-4 mb-3">
                        <strong>Tipo de Embalagem:</strong>
                        <p><?= isset($produto['tipo']) ? ucfirst($produto['tipo']) : 'N/A' ?></p>
                    </div>
                    <div class="col col-4 mb-3">
                        <strong>Volume da Embalagem:</strong>
                        <p><?= $produto['volume_embalagem'] ?? 'N/A' ?> ml</p>
                    </div>
                    <div class="col col-4 mb-3">
                        <strong>Código de Barras:</strong>
                        <p><?= $produto['codigo_barras'] ?? '-' ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-12 mb-3">
                        <strong>Descrição:</strong>
                        <p><?= $produto['descricao'] ?: 'Sem descrição' ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Lote de Produção:</strong>
                        <p>
                            <?php if (isset($produto['lote_producao_id']) && isset($produto['lote_codigo'])): ?>
                                <a href="/producao/viewProducao?id=<?= $produto['lote_producao_id'] ?>">
                                    <strong><?= htmlspecialchars($produto['lote_codigo']) ?></strong>
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Receita Base:</strong>
                        <p>
                            <?php if (isset($produto['receita_id']) && isset($produto['receita_nome'])): ?>
                                <a href="/receitas/viewReceita?id=<?= $produto['receita_id'] ?>">
                                    <?= htmlspecialchars($produto['receita_nome']) ?>
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-4 mb-3">
                        <strong>Data de Envase:</strong>
                        <p><?= isset($produto['data_envase']) ? formatDate($produto['data_envase']) : 'N/A' ?></p>
                    </div>
                    <div class="col col-4 mb-3">
                        <strong>Data de Validade:</strong>
                        <p>
                            <?php if (isset($produto['data_validade'])): ?>
                                <?= formatDate($produto['data_validade']) ?>
                                <?php
                                $diasRestantes = daysUntil($produto['data_validade']);
                                if ($diasRestantes <= 0) {
                                    echo '<br><span class="badge badge-danger">Vencido</span>';
                                } elseif ($diasRestantes <= 30) {
                                    echo '<br><span class="badge badge-warning">' . $diasRestantes . ' dias</span>';
                                }
                                ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col col-4 mb-3">
                        <strong>Validade (meses):</strong>
                        <p><?= $produto['validade_meses'] ?? 'N/A' ?> meses</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Histórico de Movimentações -->
        <?php if (!empty($movimentacoes)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Histórico de Movimentações</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Quantidade</th>
                            <th>Observação</th>
                            <th>Usuário</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimentacoes as $mov): ?>
                        <tr>
                            <td><?= formatDateTime($mov['data']) ?></td>
                            <td>
                                <?php
                                $tipoClass = $mov['tipo'] == 'entrada' ? 'success' : 'danger';
                                $tipoIcon = $mov['tipo'] == 'entrada' ? '+' : '-';
                                ?>
                                <span class="badge badge-<?= $tipoClass ?>">
                                    <?= $tipoIcon ?> <?= ucfirst($mov['tipo']) ?>
                                </span>
                            </td>
                            <td><?= $mov['quantidade'] ?> un</td>
                            <td><?= $mov['observacao'] ?: '-' ?></td>
                            <td><?= $mov['usuario_nome'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col col-4">
        <!-- Estoque -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Estoque</h3>
            </div>
            <div class="card-body text-center">
                <h2 style="color: #3498db; margin-bottom: 1rem;">
                    <?= $produto['estoque_atual'] ?> unidades
                </h2>

                <div class="mb-3">
                    <strong>Estoque Mínimo:</strong>
                    <p><?= $produto['estoque_minimo'] ?> un</p>
                </div>

                <?php if ($produto['estoque_atual'] < $produto['estoque_minimo']): ?>
                    <div class="alert alert-warning" style="text-align: center;">
                        Estoque abaixo do mínimo
                    </div>
                <?php endif; ?>

                <div>
                    <strong>Status:</strong>
                    <p>
                        <?php if ($produto['ativo']): ?>
                            <span class="badge badge-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inativo</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Precificação -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Precificação</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Custo de Produção:</strong>
                    <h4 style="color: #e74c3c; margin: 0.5rem 0;">
                        <?= formatMoney($produto['custo_producao'] ?? 0) ?>
                    </h4>
                </div>

                <div class="mb-3">
                    <strong>Preço de Venda:</strong>
                    <h3 style="color: #27ae60; margin: 0.5rem 0;">
                        <?= formatMoney($produto['preco_venda']) ?>
                    </h3>
                </div>

                <div>
                    <strong>Margem de Lucro:</strong>
                    <?php
                    $custoProducao = $produto['custo_producao'] ?? 0;
                    $margem = $custoProducao > 0
                        ? (($produto['preco_venda'] - $custoProducao) / $custoProducao) * 100
                        : 0;
                    $margemClass = $margem > 30 ? 'success' : ($margem > 15 ? 'warning' : 'danger');
                    ?>
                    <h4 style="color: <?= $margemClass == 'success' ? '#27ae60' : ($margemClass == 'warning' ? '#f39c12' : '#e74c3c') ?>; margin: 0.5rem 0;">
                        <?= number_format($margem, 2) ?>%
                    </h4>
                </div>
            </div>
        </div>

        <!-- Valor em Estoque -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Valor Total</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Valor em Estoque (Custo):</strong>
                    <h4 style="color: #e74c3c; margin: 0.5rem 0;">
                        <?= formatMoney(($produto['custo_producao'] ?? 0) * $produto['estoque_atual']) ?>
                    </h4>
                </div>

                <div>
                    <strong>Valor em Estoque (Venda):</strong>
                    <h3 style="color: #27ae60; margin: 0.5rem 0;">
                        <?= formatMoney($produto['preco_venda'] * $produto['estoque_atual']) ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
