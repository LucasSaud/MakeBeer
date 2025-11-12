<?php
$pageTitle = 'Localizações - Câmara Fria';
$activeMenu = 'camarafria';
include 'app/views/layouts/header.php';
?>

        <div class="row mb-3">
            <div class="col">
                <h2><i class="fas fa-map-marker-alt"></i> Localizações de Estoque</h2>
            </div>
            <div class="col-auto">
                <a href="/camarafria" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="/camarafria/novaLocalizacao" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nova Localização
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="/camarafria/localizacoes" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Setor</label>
                        <select name="setor_id" class="form-select">
                            <option value="">Todos os setores</option>
                            <?php foreach ($setores as $setor): ?>
                                <option value="<?= $setor['id'] ?>"
                                    <?= ($filtros['setor_id'] ?? '') == $setor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($setor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Produto</label>
                        <select name="produto_id" class="form-select">
                            <option value="">Todos os produtos</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= $produto['id'] ?>"
                                    <?= ($filtros['produto_id'] ?? '') == $produto['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($produto['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="disponivel" <?= ($filtros['status'] ?? '') == 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                            <option value="quarentena" <?= ($filtros['status'] ?? '') == 'quarentena' ? 'selected' : '' ?>>Quarentena</option>
                            <option value="vencido" <?= ($filtros['status'] ?? '') == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                            <option value="reservado" <?= ($filtros['status'] ?? '') == 'reservado' ? 'selected' : '' ?>>Reservado</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de Localizações -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($localizacoes)): ?>
                    <div class="alert alert-info">Nenhuma localização encontrada</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Lote</th>
                                    <th>Setor</th>
                                    <th>Quantidade</th>
                                    <th>Status</th>
                                    <th>Validade</th>
                                    <th>Data Entrada</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($localizacoes as $loc): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($loc['produto_nome']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($loc['tipo_embalagem']) ?></small>
                                        </td>
                                        <td><?= $loc['numero_lote'] ?? 'S/N' ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($loc['setor_nome']) ?></span>
                                        </td>
                                        <td><strong><?= $loc['quantidade'] ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?=
                                                $loc['status'] == 'disponivel' ? 'success' :
                                                ($loc['status'] == 'quarentena' ? 'warning' :
                                                ($loc['status'] == 'vencido' ? 'danger' : 'info'))
                                            ?>">
                                                <?= ucfirst($loc['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($loc['data_validade']): ?>
                                                <?php
                                                    $dias = (strtotime($loc['data_validade']) - time()) / (60*60*24);
                                                    $corData = $dias <= 7 ? 'text-danger' : ($dias <= 15 ? 'text-warning' : '');
                                                ?>
                                                <span class="<?= $corData ?>">
                                                    <?= date('d/m/Y', strtotime($loc['data_validade'])) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">N/D</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($loc['data_entrada'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/camarafria/transferir?id=<?= $loc['id'] ?>"
                                                   class="btn btn-outline-primary" title="Transferir">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-toggle="modal" data-bs-target="#modalStatus<?= $loc['id'] ?>"
                                                        title="Alterar Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>

                                            <!-- Modal Alterar Status -->
                                            <div class="modal fade" id="modalStatus<?= $loc['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="/camarafria/alterarStatus">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Alterar Status</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="localizacao_id" value="<?= $loc['id'] ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Novo Status</label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="disponivel">Disponível</option>
                                                                        <option value="quarentena">Quarentena</option>
                                                                        <option value="vencido">Vencido</option>
                                                                        <option value="reservado">Reservado</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Observações</label>
                                                                    <textarea name="observacoes" class="form-control" rows="2"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-primary">Salvar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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
