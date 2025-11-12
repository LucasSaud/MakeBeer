<?php
$pageTitle = 'Transferir Produto';
$activeMenu = 'camarafria';
include 'app/views/layouts/header.php';
?>

        <div class="row mb-3">
            <div class="col">
                <h2>Transferir Produto Entre Setores</h2>
            </div>
            <div class="col-auto">
                <a href="/camarafria/localizacoes" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Localização Atual</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Produto:</strong> <?= htmlspecialchars($localizacao['produto_nome']) ?></p>
                        <p><strong>Embalagem:</strong> <?= htmlspecialchars($localizacao['tipo_embalagem']) ?></p>
                        <p><strong>Lote:</strong> <?= $localizacao['numero_lote'] ?? 'S/N' ?></p>
                        <p><strong>Setor Atual:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($localizacao['setor_nome']) ?></span></p>
                        <p><strong>Quantidade Disponível:</strong> <span class="badge bg-success"><?= $localizacao['quantidade'] ?> unidades</span></p>
                        <p class="mb-0"><strong>Status:</strong>
                            <span class="badge bg-<?=
                                $localizacao['status'] == 'disponivel' ? 'success' :
                                ($localizacao['status'] == 'quarentena' ? 'warning' : 'info')
                            ?>">
                                <?= ucfirst($localizacao['status']) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Transferência</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/camarafria/executarTransferencia">
                            <input type="hidden" name="localizacao_id" value="<?= $localizacao['id'] ?>">

                            <div class="mb-3">
                                <label for="setor_destino_id" class="form-label">Setor de Destino *</label>
                                <select class="form-select" id="setor_destino_id" name="setor_destino_id" required>
                                    <option value="">Selecione o setor de destino</option>
                                    <?php foreach ($setores as $setor): ?>
                                        <?php if ($setor['id'] != $localizacao['setor_id']): ?>
                                            <option value="<?= $setor['id'] ?>">
                                                <?= htmlspecialchars($setor['nome']) ?>
                                                (Cap: <?= $setor['capacidade_maxima'] ?> un.)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="quantidade" class="form-label">Quantidade a Transferir *</label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade"
                                       min="0.01" max="<?= $localizacao['quantidade'] ?>"
                                       step="0.01" value="<?= $localizacao['quantidade'] ?>" required>
                                <small class="text-muted">Máximo: <?= $localizacao['quantidade'] ?> unidades</small>
                            </div>

                            <div class="mb-3">
                                <label for="motivo" class="form-label">Motivo</label>
                                <textarea class="form-control" id="motivo" name="motivo"
                                          rows="3" placeholder="Descreva o motivo da transferência"></textarea>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="/camarafria/localizacoes" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-exchange-alt"></i> Executar Transferência
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php include 'app/views/layouts/footer.php'; ?>
