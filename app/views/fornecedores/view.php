<?php
$pageTitle = 'Detalhes do Fornecedor - ' . APP_NAME;
$activeMenu = 'fornecedores';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">Detalhes do Fornecedor</h1>
    <div class="page-actions d-flex gap-2">
        <a href="/entradas/create?fornecedor_id=<?= $fornecedor['id'] ?>" class="btn btn-success">+ Nova Compra</a>
        <a href="/fornecedores/edit?id=<?= $fornecedor['id'] ?>" class="btn btn-warning">Editar</a>
        <a href="/fornecedores" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col col-8">
        <!-- Informações Principais -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Informações do Fornecedor</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Nome/Razão Social:</strong>
                        <p><?= $fornecedor['nome'] ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Nome Fantasia:</strong>
                        <p><?= $fornecedor['nome_fantasia'] ?? '-' ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>CNPJ:</strong>
                        <p><?= $fornecedor['cnpj'] ?: '-' ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Inscrição Estadual:</strong>
                        <p><?= $fornecedor['inscricao_estadual'] ?? '-' ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Email:</strong>
                        <p><?= $fornecedor['email'] ? '<a href="mailto:' . $fornecedor['email'] . '">' . $fornecedor['email'] . '</a>' : '-' ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Telefone:</strong>
                        <p><?= $fornecedor['telefone'] ?: '-' ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-12 mb-3">
                        <strong>Endereço:</strong>
                        <p>
                            <?= $fornecedor['endereco'] ?: '-' ?><br>
                            <?php if ($fornecedor['cidade']): ?>
                                <?= $fornecedor['cidade'] ?>/<?= $fornecedor['estado'] ?> - CEP: <?= $fornecedor['cep'] ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Contato:</strong>
                        <p><?= $fornecedor['contato'] ?? '-' ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Status:</strong>
                        <p>
                            <?php if ($fornecedor['ativo']): ?>
                                <span class="badge badge-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inativo</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <?php if ($fornecedor['observacoes']): ?>
                <div class="row">
                    <div class="col col-12">
                        <strong>Observações:</strong>
                        <p><?= nl2br($fornecedor['observacoes']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Insumos Fornecidos -->
        <?php if (!empty($insumos_fornecidos)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Insumos Fornecidos</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Categoria</th>
                            <th>Último Preço</th>
                            <th>Total Comprado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($insumos_fornecidos as $insumo): ?>
                        <tr>
                            <td><?= $insumo['nome'] ?></td>
                            <td><?= $insumo['categoria_nome'] ?></td>
                            <td><?= formatMoney($insumo['ultimo_preco'] ?? 0) ?></td>
                            <td><?= formatQuantity($insumo['total_comprado'], $insumo['unidade_medida']) ?></td>
                            <td>
                                <a href="/insumos/viewInsumo?id=<?= $insumo['id'] ?>" class="btn btn-sm btn-primary">Ver Insumo</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col col-4">
        <!-- Estatísticas -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Estatísticas</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Total de Compras:</strong>
                    <h3 style="color: #27ae60; margin: 0.5rem 0;">
                        <?= formatMoney($fornecedor['total_compras'] ?? 0) ?>
                    </h3>
                </div>

                <div class="mb-3">
                    <strong>Número de Compras:</strong>
                    <h4 style="color: #3498db; margin: 0.5rem 0;">
                        <?= $fornecedor['numero_compras'] ?? 0 ?> compras
                    </h4>
                </div>

                <div class="mb-3">
                    <strong>Última Compra:</strong>
                    <p><?= isset($fornecedor['ultima_compra']) && $fornecedor['ultima_compra'] ? formatDate($fornecedor['ultima_compra']) : 'Nenhuma compra' ?></p>
                </div>

                <div>
                    <strong>Ticket Médio:</strong>
                    <p><?= formatMoney($fornecedor['ticket_medio'] ?? 0) ?></p>
                </div>
            </div>
        </div>

        <!-- Avaliação -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Avaliação</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($fornecedor['avaliacao'])): ?>
                    <div class="text-center mb-2">
                        <h2 style="color: #f39c12; margin: 0;">
                            <?= number_format($fornecedor['avaliacao'], 1) ?>/5
                        </h2>
                    </div>
                <?php else: ?>
                    <p style="color: #6c757d; text-align: center;">Sem avaliação</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Histórico de Compras -->
<?php if (!empty($historico_compras)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Histórico de Compras</h3>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Insumo</th>
                    <th>Quantidade</th>
                    <th>Custo Unitário</th>
                    <th>Valor Total</th>
                    <th>Nota Fiscal</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historico_compras as $compra): ?>
                <tr>
                    <td><?= formatDate($compra['data_entrada']) ?></td>
                    <td><?= $compra['insumo_nome'] ?></td>
                    <td><?= formatQuantity($compra['quantidade'], $compra['unidade_medida']) ?></td>
                    <td><?= formatMoney($compra['custo_unitario']) ?></td>
                    <td><?= formatMoney($compra['valor_total']) ?></td>
                    <td><?= $compra['numero_nota_fiscal'] ?: '-' ?></td>
                    <td>
                        <a href="/entradas/viewEntradas?id=<?= $compra['id'] ?>" class="btn btn-sm btn-primary">Ver</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include 'app/views/layouts/footer.php'; ?>
