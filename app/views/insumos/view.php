<?php
$pageTitle = 'Detalhes do Insumo - ' . APP_NAME;
$activeMenu = 'insumos';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">Detalhes do Insumo</h1>
    <div class="page-actions d-flex gap-2">
        <a href="/entradas/create?insumo_id=<?= $insumo['id'] ?>" class="btn btn-success">+ Registrar Entrada</a>
        <a href="/insumos/edit?id=<?= $insumo['id'] ?>" class="btn btn-warning">Editar</a>
        <a href="/insumos" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<!-- Informações Principais -->
<div class="row mb-4">
    <div class="col col-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações do Insumo</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Código Interno:</strong>
                        <p><?= $insumo['codigo_interno'] ?? 'N/A' ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Nome:</strong>
                        <p><?= $insumo['nome'] ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Categoria:</strong>
                        <p><?= $insumo['categoria_nome'] ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Unidade de Medida:</strong>
                        <p><?= strtoupper($insumo['unidade_medida']) ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-12 mb-3">
                        <strong>Descrição:</strong>
                        <p><?= $insumo['descricao'] ?: 'Sem descrição' ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Fornecedor Principal:</strong>
                        <p>
                            <?php if (!empty($insumo['fornecedor_nome'])): ?>
                                <a href="/fornecedores/viewFornecedor?id=<?= $insumo['fornecedor_principal_id'] ?>"><?= $insumo['fornecedor_nome'] ?></a>
                            <?php else: ?>
                                Não definido
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Tipo:</strong>
                        <p><?= ucfirst($insumo['tipo'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col col-4">
        <!-- Status do Estoque -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Status do Estoque</h3>
            </div>
            <div class="card-body text-center">
                <h2 style="color: #3498db; margin-bottom: 0.5rem;">
                    <?= formatQuantity($insumo['estoque_atual'], $insumo['unidade_medida']) ?>
                </h2>
                <p style="margin: 0; color: #6c757d;">Estoque Atual</p>

                <hr>

                <div class="mb-3">
                    <strong>Estoque Mínimo:</strong>
                    <p><?= formatQuantity($insumo['estoque_minimo'], $insumo['unidade_medida']) ?></p>
                </div>

                <?php
                $percentual = ($insumo['estoque_minimo'] > 0)
                    ? ($insumo['estoque_atual'] / $insumo['estoque_minimo']) * 100
                    : 100;

                if ($insumo['estoque_atual'] <= 0) {
                    $statusBadge = '<span class="badge badge-danger">Sem Estoque</span>';
                } elseif ($percentual < 50) {
                    $statusBadge = '<span class="badge badge-danger">Crítico</span>';
                } elseif ($percentual < 100) {
                    $statusBadge = '<span class="badge badge-warning">Baixo</span>';
                } else {
                    $statusBadge = '<span class="badge badge-success">Normal</span>';
                }
                ?>

                <div>
                    <strong>Status:</strong><br>
                    <?= $statusBadge ?>
                </div>
            </div>
        </div>

        <!-- Informações Financeiras -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações Financeiras</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Custo Médio:</strong>
                    <h3 style="color: #27ae60; margin: 0.5rem 0;">
                        <?= formatMoney($insumo['preco_medio'] ?? 0) ?>
                    </h3>
                    <small style="color: #6c757d;">por <?= $insumo['unidade_medida'] ?></small>
                </div>

                <div>
                    <strong>Valor Total em Estoque:</strong>
                    <h4 style="color: #3498db; margin: 0.5rem 0;">
                        <?= formatMoney(($insumo['preco_medio'] ?? 0) * $insumo['estoque_atual']) ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Histórico de Entradas -->
<?php if (!empty($historico_entradas)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Histórico de Entradas</h3>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Fornecedor</th>
                    <th>Quantidade</th>
                    <th>Custo Unitário</th>
                    <th>Valor Total</th>
                    <th>Lote</th>
                    <th>Validade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historico_entradas as $entrada): ?>
                <tr>
                    <td><?= formatDate($entrada['data_entrada']) ?></td>
                    <td><?= $entrada['fornecedor_nome'] ?></td>
                    <td><?= formatQuantity($entrada['quantidade'], $insumo['unidade_medida']) ?></td>
                    <td><?= formatMoney($entrada['custo_unitario']) ?></td>
                    <td><?= formatMoney($entrada['valor_total']) ?></td>
                    <td><?= $entrada['numero_lote'] ?></td>
                    <td><?= formatDate($entrada['data_validade']) ?></td>
                    <td>
                        <a href="/entradas/viewEntradas?id=<?= $entrada['id'] ?>" class="btn btn-sm btn-primary">Ver</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Receitas que Utilizam este Insumo -->
<?php if (!empty($receitas_relacionadas)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Receitas que Utilizam este Insumo</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Receita</th>
                    <th>Quantidade por Lote</th>
                    <th>Lotes Possíveis</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($receitas_relacionadas as $receita): ?>
                <tr>
                    <td><?= $receita['nome'] ?></td>
                    <td><?= formatQuantity($receita['quantidade_necessaria'], $insumo['unidade_medida']) ?></td>
                    <td>
                        <?php
                        $lotes_possiveis = floor($insumo['estoque_atual'] / $receita['quantidade_necessaria']);
                        echo '<strong>' . $lotes_possiveis . '</strong> lotes';
                        ?>
                    </td>
                    <td>
                        <a href="/receitas/viewReceita?id=<?= $receita['id'] ?>" class="btn btn-sm btn-primary">Ver Receita</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include 'app/views/layouts/footer.php'; ?>
