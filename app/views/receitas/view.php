<?php
$pageTitle = 'Detalhes da Receita - ' . APP_NAME;
$activeMenu = 'receitas';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">Detalhes da Receita</h1>
    <div class="page-actions d-flex gap-2">
        <?php if (isset($receita['pode_produzir']) && $receita['pode_produzir']): ?>
            <a href="/producao/create?receita_id=<?= $receita['id'] ?>" class="btn btn-success">Iniciar Produção</a>
        <?php endif; ?>
        <a href="/receitas/edit?id=<?= $receita['id'] ?>" class="btn btn-warning">Editar</a>
        <form method="POST" action="/receitas/delete" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja inativar esta receita?')">
            <input type="hidden" name="id" value="<?= $receita['id'] ?>">
            <button type="submit" class="btn btn-danger">Excluir</button>
        </form>
        <a href="/receitas" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <!-- Informações da Receita -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Informações da Receita</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Nome:</strong>
                        <h3 style="color: #2c3e50; margin: 0.5rem 0;"><?= $receita['nome'] ?></h3>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Estilo:</strong>
                        <p style="font-size: 1.2rem;"><?= $receita['estilo'] ?? 'Não especificado' ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Volume Produzido:</strong>
                        <h3 style="color: #3498db; margin: 0.5rem 0;">
                            <?= formatQuantity($receita['volume'] ?? 0, 'L') ?>
                        </h3>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Tempo de Produção:</strong>
                        <p style="font-size: 1.2rem;"><?= $receita['tempo_producao'] ?? 'Não especificado' ?> dias</p>
                    </div>
                </div>

                <?php if (!empty($receita['descricao'])): ?>
                <div class="row">
                    <div class="col col-12 mb-3">
                        <strong>Descrição:</strong>
                        <p><?= nl2br($receita['descricao']) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($receita['instrucoes'])): ?>
                <div class="row">
                    <div class="col col-12">
                        <strong>Instruções de Produção:</strong>
                        <p><?= nl2br($receita['instrucoes']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ingredientes -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Ingredientes da Receita</h3>
            </div>
            <div class="card-body">
                <?php if (empty($ingredientes)): ?>
                    <p style="color: #6c757d;">Nenhum ingrediente cadastrado para esta receita.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Quantidade Necessária</th>
                                <th>Estoque Disponível</th>
                                <th>Status</th>
                                <th>Custo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ingredientes as $ing): ?>
                            <tr>
                                <td>
                                    <a href="/insumos/viewInsumo?id=<?= $ing['insumo_id'] ?>">
                                        <strong><?= $ing['insumo_nome'] ?></strong>
                                    </a>
                                </td>
                                <td><?= formatQuantity($ing['quantidade'], $ing['unidade_medida']) ?></td>
                                <td><?= formatQuantity($ing['estoque_disponivel'], $ing['unidade_medida']) ?></td>
                                <td>
                                    <?php if ($ing['estoque_disponivel'] >= $ing['quantidade']): ?>
                                        <span class="badge badge-success">Disponível</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Insuficiente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatMoney(($ing['custo_medio'] ?? 0) * $ing['quantidade']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background: #f8f9fa;">
                                <td colspan="4" class="text-right">CUSTO TOTAL DA RECEITA:</td>
                                <td><?= formatMoney($receita['custo_total'] ?? 0) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Histórico de Produção -->
        <?php if (!empty($historico_producao)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Histórico de Produção</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Código Lote</th>
                            <th>Data Início</th>
                            <th>Quantidade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico_producao as $lote): ?>
                        <tr>
                            <td><strong><?= $lote['codigo'] ?? $lote['codigo_lote'] ?? 'N/A' ?></strong></td>
                            <td><?= formatDate($lote['data_inicio']) ?></td>
                            <td><?= formatQuantity($lote['volume_real'] ?? 0, 'L') ?></td>
                            <td>
                                <?php
                                $statusClass = match($lote['status']) {
                                    'finalizado' => 'success',
                                    'em_andamento' => 'info',
                                    'cancelado' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $lote['status'])) ?></span>
                            </td>
                            <td>
                                <a href="/producao/viewProducao?id=<?= $lote['id'] ?>" class="btn btn-sm btn-primary">Ver</a>
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
        <!-- Disponibilidade -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Disponibilidade</h3>
            </div>
            <div class="card-body text-center">
                <?php if (isset($receita['pode_produzir']) && $receita['pode_produzir']): ?>
                    <h2 style="color: #27ae60; margin-bottom: 1rem;">Disponível</h2>
                    <p>Lotes possíveis: <strong><?= $receita['lotes_possiveis'] ?? 0 ?></strong></p>
                    <a href="/producao/create?receita_id=<?= $receita['id'] ?>" class="btn btn-success">Iniciar Produção</a>
                <?php elseif (isset($receita['pode_produzir'])): ?>
                    <h2 style="color: #e74c3c; margin-bottom: 1rem;">Indisponível</h2>
                    <p style="color: #6c757d;">Insumos insuficientes</p>
                    <?php if (!empty($ingredientes_faltantes)): ?>
                        <hr>
                        <strong>Faltam:</strong>
                        <ul style="text-align: left; margin-top: 0.5rem;">
                            <?php foreach ($ingredientes_faltantes as $faltante): ?>
                                <li><?= $faltante['nome'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="color: #6c757d;">Informação não disponível</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Custo -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Custo</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Custo Total:</strong>
                    <h3 style="color: #27ae60; margin: 0.5rem 0;">
                        <?= formatMoney($receita['custo_total'] ?? 0) ?>
                    </h3>
                </div>

                <div>
                    <strong>Custo por Litro:</strong>
                    <h4 style="color: #3498db; margin: 0.5rem 0;">
                        <?php
                        $volume = $receita['volume'] ?? 0;
                        $custoTotal = $receita['custo_total'] ?? 0;
                        $custoLitro = $volume > 0 ? $custoTotal / $volume : 0;
                        echo formatMoney($custoLitro);
                        ?>
                    </h4>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estatísticas</h3>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Total Produzido:</strong>
                    <p><?= formatQuantity($receita['total_produzido'] ?? 0, 'L') ?></p>
                </div>

                <div class="mb-2">
                    <strong>Lotes Produzidos:</strong>
                    <p><?= $receita['lotes_produzidos'] ?? 0 ?></p>
                </div>

                <div>
                    <strong>Status:</strong>
                    <p>
                        <?php if (!empty($receita['ativo'])): ?>
                            <span class="badge badge-success">Ativa</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inativa</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>