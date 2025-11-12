<?php
$pageTitle = 'Movimentações - Câmara Fria';
$activeMenu = 'camarafria';
include 'app/views/layouts/header.php';
?>

        <div class="row mb-3">
            <div class="col">
                <h2><i class="fas fa-exchange-alt"></i> Histórico de Movimentações</h2>
            </div>
            <div class="col-auto">
                <a href="/camarafria" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="/camarafria/movimentacoes" class="row g-3">
                    <div class="col-md-3">
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

                    <div class="col-md-2">
                        <label class="form-label">Setor</label>
                        <select name="setor_id" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($setores as $setor): ?>
                                <option value="<?= $setor['id'] ?>"
                                    <?= ($filtros['setor_id'] ?? '') == $setor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($setor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo_movimentacao" class="form-select">
                            <option value="">Todos</option>
                            <option value="entrada" <?= ($filtros['tipo_movimentacao'] ?? '') == 'entrada' ? 'selected' : '' ?>>Entrada</option>
                            <option value="transferencia" <?= ($filtros['tipo_movimentacao'] ?? '') == 'transferencia' ? 'selected' : '' ?>>Transferência</option>
                            <option value="saida" <?= ($filtros['tipo_movimentacao'] ?? '') == 'saida' ? 'selected' : '' ?>>Saída</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control"
                               value="<?= $filtros['data_inicio'] ?? '' ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control"
                               value="<?= $filtros['data_fim'] ?? '' ?>">
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de Movimentações -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($movimentacoes)): ?>
                    <div class="alert alert-info">Nenhuma movimentação encontrada</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Tipo</th>
                                    <th>Produto</th>
                                    <th>Lote</th>
                                    <th>Origem</th>
                                    <th>Destino</th>
                                    <th>Quantidade</th>
                                    <th>Responsável</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimentacoes as $mov): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($mov['data_movimentacao'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?=
                                                $mov['tipo_movimentacao'] == 'entrada' ? 'success' :
                                                ($mov['tipo_movimentacao'] == 'saida' ? 'danger' :
                                                ($mov['tipo_movimentacao'] == 'transferencia' ? 'info' : 'secondary'))
                                            ?>">
                                                <?= ucfirst($mov['tipo_movimentacao']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($mov['produto_nome']) ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($mov['tipo_embalagem']) ?></small>
                                        </td>
                                        <td><?= $mov['numero_lote'] ?? 'S/N' ?></td>
                                        <td>
                                            <?php if ($mov['setor_origem_nome']): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($mov['setor_origem_nome']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($mov['setor_destino_nome']): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($mov['setor_destino_nome']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= $mov['quantidade'] ?></strong></td>
                                        <td><?= htmlspecialchars($mov['responsavel'] ?? $mov['usuario_nome'] ?? '-') ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($mov['motivo'] ?? '-') ?></td>
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
