<?php
$pageTitle = 'Detalhes do Lote - ' . APP_NAME;
$activeMenu = 'producao';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">Detalhes do Lote de Produção</h1>
    <div class="page-actions d-flex gap-2 align-items-center">
        <div class="d-flex gap-1">
            <form method="GET" action="/producao/atualizarStatus" style="display: inline;">
                <input type="hidden" name="id" value="<?= $lote['id'] ?>">
                <button type="submit" class="btn btn-sm btn-primary">
                    <span style="display: inline-block; padding: 0.5rem 1rem;">Atualizar Status</span>
                </button>
            </form>
            <?php if ($lote['status'] != 'finalizado'): ?>
                <a href="/producao/editar?id=<?= $lote['id'] ?>" class="btn btn-sm btn-warning">
                    <span style="display: inline-block; padding: 0.5rem 1rem;">Editar</span>
                </a>
            <?php endif; ?>
            <?php if ($lote['status'] == 'planejado'): ?>
                <form method="POST" action="/producao/delete" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este lote? Esta ação não pode ser desfeita.')">
                    <input type="hidden" name="id" value="<?= $lote['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <span style="display: inline-block; padding: 0.5rem 1rem;">Excluir</span>
                    </button>
                </form>
            <?php elseif ($lote['status'] != 'finalizado' && $lote['status'] != 'cancelado'): ?>
                <form method="POST" action="/producao/cancelar" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja cancelar este lote? Esta ação não pode ser desfeita.')">
                    <input type="hidden" name="id" value="<?= $lote['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <span style="display: inline-block; padding: 0.5rem 1rem;">Cancelar</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <a href="/producao" class="btn btn-sm btn-secondary">
            <span style="display: inline-block; padding: 0.5rem 1rem;">Voltar</span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <!-- Informações do Lote -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Informações do Lote</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Código do Lote:</strong>
                        <h3 style="color: #2c3e50; margin: 0.5rem 0;"><?= $lote['codigo_lote'] ?? 'N/A' ?></h3>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Status:</strong>
                        <p>
                            <?php
                            $statusClass = match($lote['status']) {
                                'planejado' => 'secondary',
                                'em_producao' => 'info',
                                'fermentando' => 'warning',
                                'maturando' => 'warning',
                                'finalizado' => 'success',
                                'cancelado' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge badge-<?= $statusClass ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                <?= ucfirst(str_replace('_', ' ', $lote['status'])) ?>
                            </span>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Receita:</strong>
                        <p>
                            <a href="/receitas/viewReceita?id=<?= $lote['receita_id'] ?>">
                                <strong><?= $lote['receita_nome'] ?></strong>
                            </a>
                        </p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Quantidade Produzida:</strong>
                        <h3 style="color: #3498db; margin: 0.5rem 0;">
                            <?= formatQuantity($lote['volume_real'], 'L') ?>
                        </h3>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-4 mb-3">
                        <strong>Data de Início:</strong>
                        <p><?= formatDate($lote['data_inicio']) ?></p>
                    </div>
                    <div class="col col-4 mb-3">
                        <strong>Previsão de Conclusão:</strong>
                        <p><?= isset($lote['previsao_conclusao']) ? formatDate($lote['previsao_conclusao']) : 'N/A' ?></p>
                    </div>
                    <div class="col col-4 mb-3">
                        <strong>Data de Conclusão:</strong>
                        <p><?= isset($lote['data_conclusao']) && $lote['data_conclusao'] ? formatDate($lote['data_conclusao']) : '-' ?></p>
                    </div>
                </div>

                <?php if ($lote['observacoes']): ?>
                <div class="row">
                    <div class="col col-12">
                        <strong>Observações:</strong>
                        <p><?= nl2br($lote['observacoes']) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col col-6 mb-3">
                        <strong>Responsável:</strong>
                        <p><?= $lote['usuario_nome'] ?? 'N/A' ?></p>
                    </div>
                    <div class="col col-6 mb-3">
                        <strong>Data de Criação:</strong>
                        <p><?= formatDateTime($lote['created_at']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consumo de Insumos -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Consumo de Insumos</h3>
            </div>
            <div class="card-body">
                <?php if (empty($consumos)): ?>
                    <p style="color: #6c757d;">Nenhum consumo registrado para este lote.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Quantidade Consumida</th>
                                <th>Custo Unitário</th>
                                <th>Custo Total</th>
                                <th>Lote do Insumo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consumos as $consumo): ?>
                            <tr>
                                <td>
                                    <a href="/insumos/viewInsumo?id=<?= $consumo['insumo_id'] ?>">
                                        <?= $consumo['insumo_nome'] ?>
                                    </a>
                                </td>
                                <td><?= formatQuantity($consumo['quantidade_consumida'], $consumo['unidade_medida']) ?></td>
                                <td><?= formatMoney($consumo['custo_unitario']) ?></td>
                                <td><strong><?= formatMoney($consumo['custo_total']) ?></strong></td>
                                <td><?= $consumo['lote_insumo'] ?? '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background: #f8f9fa;">
                                <td colspan="3" class="text-right">CUSTO TOTAL:</td>
                                <td><?= formatMoney(array_sum(array_column($consumos, 'custo_total'))) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Linha do Tempo -->
        <?php if (!empty($historico)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Histórico / Linha do Tempo</h3>
            </div>
            <div class="card-body">
                <div style="border-left: 3px solid #3498db; padding-left: 1.5rem;">
                    <?php foreach ($historico as $evento): ?>
                        <div style="margin-bottom: 1.5rem; position: relative;">
                            <div style="position: absolute; left: -1.875rem; width: 12px; height: 12px; background: #3498db; border-radius: 50%; border: 3px solid white;"></div>
                            <strong style="color: #2c3e50;"><?= $evento['titulo'] ?></strong>
                            <p style="margin: 0.25rem 0; color: #6c757d;">
                                <small><?= formatDateTime($evento['data']) ?> - <?= $evento['usuario'] ?></small>
                            </p>
                            <?php if (!empty($evento['descricao'])): ?>
                                <p style="margin: 0.5rem 0;"><?= $evento['descricao'] ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col col-4">
        <!-- Progresso -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Progresso</h3>
            </div>
            <div class="card-body text-center">
                <?php
                $progresso = match($lote['status']) {
                    'planejado' => 0,
                    'em_producao' => 25,
                    'fermentando' => 50,
                    'maturando' => 75,
                    'finalizado' => 100,
                    'cancelado' => 0,
                    default => 0
                };
                ?>
                <div style="width: 150px; height: 150px; margin: 0 auto 1rem; position: relative;">
                    <svg viewBox="0 0 36 36" style="display: block; width: 100%;">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none" stroke="#e9ecef" stroke-width="3"/>
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none" stroke="#3498db" stroke-width="3"
                            stroke-dasharray="<?= $progresso ?>, 100"/>
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <h2 style="margin: 0; color: #2c3e50;"><?= $progresso ?>%</h2>
                    </div>
                </div>

                <?php if ($lote['status'] != 'finalizado' && $lote['status'] != 'cancelado'): ?>
                    <p style="color: #6c757d;">
                        <?php
                        if (isset($lote['previsao_conclusao'])) {
                            $diasRestantes = daysUntil($lote['previsao_conclusao']);
                            if ($diasRestantes < 0) {
                                echo '<span class="badge badge-danger">Atrasado em ' . abs($diasRestantes) . ' dias</span>';
                            } else {
                                echo 'Faltam <strong>' . $diasRestantes . ' dias</strong> para conclusão';
                            }
                        } else {
                            echo 'Sem previsão de conclusão';
                        }
                        ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Custos -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Custos</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Custo Total:</strong>
                    <h3 style="color: #27ae60; margin: 0.5rem 0;">
                        <?= formatMoney($lote['custo_total'] ?? 0) ?>
                    </h3>
                </div>

                <div>
                    <strong>Custo por Litro:</strong>
                    <h4 style="color: #3498db; margin: 0.5rem 0;">
                        <?php
                        $quantidadeProduzida = $lote['quantidade_produzida'] ?? 0;
                        $custoTotal = $lote['custo_total'] ?? 0;
                        $custoPorLitro = $quantidadeProduzida > 0 ? $custoTotal / $quantidadeProduzida : 0;
                        echo formatMoney($custoPorLitro);
                        ?>
                    </h4>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <?php if ($lote['status'] != 'finalizado' && $lote['status'] != 'cancelado'): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ações Rápidas</h3>
            </div>
            <div class="card-body text-center">
                <a href="/producao/atualizarStatus?id=<?= $lote['id'] ?>" class="btn btn-sm btn-primary mb-2" style="width: 100%;">
                    <span style="display: inline-block; padding: 0.5rem 1rem;">Atualizar Status</span>
                </a>
                <form method="POST" action="/producao/cancelar" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja cancelar este lote? Esta ação não pode ser desfeita.')">
                    <input type="hidden" name="id" value="<?= $lote['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" style="width: 100%;">
                        <span style="display: inline-block; padding: 0.5rem 1rem;">Cancelar Lote</span>
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
