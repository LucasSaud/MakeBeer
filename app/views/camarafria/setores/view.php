<?php
$pageTitle = 'Detalhes do Setor';
$activeMenu = 'camarafria';
include 'app/views/layouts/header.php';
?>

        <div class="row mb-3">
            <div class="col">
                <h2><?= htmlspecialchars($setor['nome']) ?></h2>
            </div>
            <div class="col-auto">
                <a href="/camarafria/setores" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="/camarafria/editarSetor?id=<?= $setor['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>

        <!-- Informações do Setor -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3><?= $setor['capacidade_maxima'] ?></h3>
                        <p class="mb-0 text-muted">Capacidade Máxima</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3><?= $setor['quantidade_total'] ?></h3>
                        <p class="mb-0 text-muted">Quantidade Atual</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3><?= number_format($setor['percentual_ocupacao'], 1) ?>%</h3>
                        <p class="mb-0 text-muted">Ocupação</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3><?= $setor['temperatura_ideal'] ?? '--' ?>°C</h3>
                        <p class="mb-0 text-muted">Temperatura Ideal</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produtos no Setor -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Produtos Estocados neste Setor</h5>
            </div>
            <div class="card-body">
                <?php if (empty($produtos)): ?>
                    <div class="alert alert-info">Nenhum produto neste setor</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Lote</th>
                                    <th>Quantidade</th>
                                    <th>Status</th>
                                    <th>Validade</th>
                                    <th>Data Entrada</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $produto): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($produto['produto_nome']) ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($produto['tipo_embalagem']) ?></small>
                                        </td>
                                        <td><?= $produto['numero_lote'] ?? 'S/N' ?></td>
                                        <td><strong><?= $produto['quantidade'] ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?=
                                                $produto['status'] == 'disponivel' ? 'success' :
                                                ($produto['status'] == 'quarentena' ? 'warning' :
                                                ($produto['status'] == 'vencido' ? 'danger' : 'secondary'))
                                            ?>">
                                                <?= ucfirst($produto['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($produto['data_validade']): ?>
                                                <?= date('d/m/Y', strtotime($produto['data_validade'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/D</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($produto['data_entrada'])) ?></td>
                                        <td>
                                            <a href="/camarafria/transferir?id=<?= $produto['id'] ?>"
                                               class="btn btn-sm btn-outline-primary" title="Transferir">
                                                <i class="fas fa-exchange-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


<?php include 'app/views/layouts/footer.php'; ?>
