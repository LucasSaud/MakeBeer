<?php
$pageTitle = 'Detalhes da Entrada - ' . APP_NAME;
$activeMenu = 'entradas';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">Detalhes da Entrada de Estoque</h1>
    <div class="page-actions d-flex gap-2">
        <a href="/entradas" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <!-- Informações da Entrada -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Informações da Entrada</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Data da Entrada:</strong>
                        <p><?= formatDate($entrada['data_entrada']) ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Registrado por:</strong>
                        <p><?= $entrada['usuario_nome'] ?? 'N/A' ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Insumo:</strong>
                        <p>
                            <?php if (isset($entrada['insumo_id']) && isset($entrada['insumo_nome'])): ?>
                                <a href="/insumos/viewInsumo?id=<?= $entrada['insumo_id'] ?>">
                                    <strong><?= htmlspecialchars($entrada['insumo_nome']) ?></strong>
                                </a><br>
                                <small style="color: #6c757d;">Código: <?= htmlspecialchars($entrada['insumo_codigo'] ?? 'N/A') ?></small>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Fornecedor:</strong>
                        <p>
                            <?php if (isset($entrada['fornecedor_id']) && isset($entrada['fornecedor_nome'])): ?>
                                <a href="/fornecedores/viewFornecedor?id=<?= $entrada['fornecedor_id'] ?>">
                                    <?= htmlspecialchars($entrada['fornecedor_nome']) ?>
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-4 mb-3">
                        <strong>Quantidade:</strong>
                        <h3 style="color: #3498db; margin: 0.5rem 0;">
                            <?= formatQuantity($entrada['quantidade'], $entrada['unidade_medida'] ?? '') ?>
                        </h3>
                    </div>
                    <div class="col col-4 mb-3">
                        <strong>Custo Unitário:</strong>
                        <h3 style="color: #27ae60; margin: 0.5rem 0;">
                            <?= formatMoney($entrada['custo_unitario'] ?? 0) ?>
                        </h3>
                    </div>
                    <div class="col col-4 mb-3">
                        <strong>Valor Total:</strong>
                        <h3 style="color: #2c3e50; margin: 0.5rem 0;">
                            <?= formatMoney($entrada['preco_total']) ?>
                        </h3>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Número do Lote:</strong>
                        <p><?= $entrada['numero_lote'] ?? 'N/A' ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Data de Validade:</strong>
                        <p>
                            <?= formatDate($entrada['data_validade']) ?>
                            <?php
                            $diasRestantes = daysUntil($entrada['data_validade']);
                            if ($diasRestantes <= 0) {
                                echo '<span class="badge badge-danger ml-2">Vencido</span>';
                            } elseif ($diasRestantes <= 30) {
                                echo '<span class="badge badge-warning ml-2">' . $diasRestantes . ' dias</span>';
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Nota Fiscal:</strong>
                        <p><?= $entrada['numero_nota_fiscal'] ?: 'Não informado' ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Data da Nota:</strong>
                        <p><?= isset($entrada['data_nota_fiscal']) && $entrada['data_nota_fiscal'] ? formatDate($entrada['data_nota_fiscal']) : 'Não informado' ?></p>
                    </div>
                </div>

                <?php if ($entrada['observacoes']): ?>
                <div class="row">
                    <div class="col col-12">
                        <strong>Observações:</strong>
                        <p><?= nl2br($entrada['observacoes']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Documentos Anexados -->
        <?php if (!empty($entrada['documentos'])): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Documentos Anexados</h3>
            </div>
            <div class="card-body">
                <ul>
                    <?php foreach ($entrada['documentos'] as $doc): ?>
                    <li>
                        <a href="/uploads/<?= $doc['arquivo'] ?>" target="_blank">
                            <?= $doc['nome'] ?>
                        </a>
                        <small style="color: #6c757d;">(<?= formatBytes($doc['tamanho']) ?>)</small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col col-4">
        <!-- Resumo Financeiro -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Resumo Financeiro</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Valor Total:</strong>
                    <h3 style="color: #27ae60; margin: 0.5rem 0;">
                        <?= formatMoney($entrada['preco_total']) ?>
                    </h3>
                </div>

                <div class="mb-3">
                    <strong>Custo por <?= isset($entrada['unidade_medida']) ? strtoupper($entrada['unidade_medida']) : 'UN' ?>:</strong>
                    <p><?= formatMoney($entrada['custo_unitario'] ?? 0) ?></p>
                </div>

                <div>
                    <strong>Impacto no Estoque:</strong>
                    <p style="color: #27ae60;">
                        + <?= formatQuantity($entrada['quantidade'], $entrada['unidade_medida'] ?? '') ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Status da Entrada -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Status</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Data de Registro:</strong>
                    <p><?= formatDateTime($entrada['created_at']) ?></p>
                </div>

                <?php if (isset($entrada['updated_at']) && $entrada['updated_at'] != $entrada['created_at']): ?>
                <div class="mb-3">
                    <strong>Última Atualização:</strong>
                    <p><?= formatDateTime($entrada['updated_at']) ?></p>
                </div>
                <?php endif; ?>

                <div>
                    <span class="badge badge-success">Entrada Confirmada</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
