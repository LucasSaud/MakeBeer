<?php
$pageTitle = 'Lotes de Produção - ' . APP_NAME;
$activeMenu = 'producao';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">🍺 Lotes de Produção</h1>
    <div class="page-actions">
        <a href="/producao/create" class="btn btn-primary">+ Novo Lote</a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/producao" class="row">
            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Código do lote" value="<?= $_GET['search'] ?? '' ?>">
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-select">
                        <option value="">Todos os status</option>
                        <option value="planejado" <?= ($_GET['status'] ?? '') == 'planejado' ? 'selected' : '' ?>>Planejado</option>
                        <option value="em_andamento" <?= ($_GET['status'] ?? '') == 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                        <option value="fermentacao" <?= ($_GET['status'] ?? '') == 'fermentacao' ? 'selected' : '' ?>>Fermentação</option>
                        <option value="maturacao" <?= ($_GET['status'] ?? '') == 'maturacao' ? 'selected' : '' ?>>Maturação</option>
                        <option value="finalizado" <?= ($_GET['status'] ?? '') == 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                        <option value="cancelado" <?= ($_GET['status'] ?? '') == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Receita</label>
                    <select name="receita_id" class="form-control form-select">
                        <option value="">Todas as receitas</option>
                        <?php foreach ($receitas as $rec): ?>
                            <option value="<?= $rec['id'] ?>" <?= ($_GET['receita_id'] ?? '') == $rec['id'] ? 'selected' : '' ?>>
                                <?= $rec['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="/producao" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #f39c12; margin-bottom: 0.5rem;"><?= $stats['em_andamento'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Em Produção</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= formatQuantity($stats['litros_mes'] ?? 0, 'L') ?></h3>
                <p style="margin: 0; color: #6c757d;">Produzidos no Mês</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #27ae60; margin-bottom: 0.5rem;"><?= $stats['finalizados_mes'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Finalizados no Mês</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #e74c3c; margin-bottom: 0.5rem;"><?= $stats['atrasados'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Atrasados</p>
            </div>
        </div>
    </div>
</div>

<!-- Listagem de Lotes -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Lista de Lotes</h3>
        <span class="badge badge-info"><?= count($lotes) ?> lotes</span>
    </div>
    <div class="card-body">
        <?php if (empty($lotes)): ?>
            <div class="alert alert-info">
                Nenhum lote de produção encontrado. <a href="/producao/create">Inicie o primeiro lote</a>.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Receita</th>
                        <th>Quantidade</th>
                        <th>Data Início</th>
                        <th>Previsão Conclusão</th>
                        <th>Status</th>
                        <th>Progresso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lotes as $lote): ?>
                    <tr>
                        <td><strong><?= $lote['codigo'] ?></strong></td>
                        <td><?= $lote['receita_nome'] ?? 'N/A' ?></td>
                        <td><?= formatQuantity($lote['volume_planejado'], 'L') ?></td>
                        <td><?= formatDate($lote['data_inicio']) ?></td>
                        <td>
                            <?= formatDate($lote['data_fim']) ?>
                            <?php
                            if ($lote['status'] != 'finalizado' && $lote['status'] != 'cancelado') {
                                $diasRestantes = daysUntil($lote['data_fim']);
                                if ($diasRestantes < 0) {
                                    echo '<br><span class="badge badge-danger">Atrasado</span>';
                                } elseif ($diasRestantes <= 3) {
                                    echo '<br><span class="badge badge-warning">' . $diasRestantes . ' dias</span>';
                                }
                            }
                            ?>
                        </td>
                        <td>
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
                            <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $lote['status'])) ?></span>
                        </td>
                        <td>
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
                            <div style="width: 100px; background: #e9ecef; border-radius: 10px; height: 10px; overflow: hidden;">
                                <div style="width: <?= $progresso ?>%; background: #3498db; height: 100%;"></div>
                            </div>
                            <small style="color: #6c757d;"><?= $progresso ?>%</small>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/producao/viewProducao?id=<?= $lote['id'] ?>" class="btn btn-sm btn-primary">Ver</a>
                                <?php if ($lote['status'] != 'finalizado' && $lote['status'] != 'cancelado'): ?>
                                    <a href="/producao/editar?id=<?= $lote['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <?php endif; ?>
                                <?php if ($lote['status'] == 'planejado'): ?>
                                    <form method="POST" action="/producao/delete" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este lote?')">
                                        <input type="hidden" name="id" value="<?= $lote['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>