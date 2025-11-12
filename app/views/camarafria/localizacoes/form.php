<?php
$pageTitle = 'Nova Localização - Câmara Fria';
$activeMenu = 'camarafria';
include 'app/views/layouts/header.php';
?>

        <div class="row mb-3">
            <div class="col">
                <h2>Adicionar Produto na Câmara Fria</h2>
            </div>
            <div class="col-auto">
                <a href="/camarafria/localizacoes" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="/camarafria/registrarEntrada">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="produto_id" class="form-label">Produto *</label>
                                <select class="form-select" id="produto_id" name="produto_id" required>
                                    <option value="">Selecione o produto</option>
                                    <?php foreach ($produtos as $produto): ?>
                                        <option value="<?= $produto['id'] ?>">
                                            <?= htmlspecialchars($produto['nome']) ?> - <?= htmlspecialchars($produto['tipo_embalagem']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="lote_id" class="form-label">Lote</label>
                                <select class="form-select" id="lote_id" name="lote_id">
                                    <option value="">Sem lote específico</option>
                                    <?php foreach ($lotes as $lote): ?>
                                        <option value="<?= $lote['id'] ?>">
                                            <?= htmlspecialchars($lote['numero_lote']) ?>
                                            <?php if ($lote['data_validade']): ?>
                                                - Val: <?= date('d/m/Y', strtotime($lote['data_validade'])) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="setor_id" class="form-label">Setor *</label>
                                <select class="form-select" id="setor_id" name="setor_id" required>
                                    <option value="">Selecione o setor</option>
                                    <?php foreach ($setores as $setor): ?>
                                        <option value="<?= $setor['id'] ?>">
                                            <?= htmlspecialchars($setor['nome']) ?>
                                            (Capacidade: <?= $setor['capacidade_maxima'] ?> un.)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="quantidade" class="form-label">Quantidade *</label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade"
                                       min="0.01" step="0.01" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="disponivel">Disponível</option>
                                    <option value="quarentena">Quarentena</option>
                                    <option value="reservado">Reservado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="/camarafria/localizacoes" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Registrar Entrada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<?php include 'app/views/layouts/footer.php'; ?>
