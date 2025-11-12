<?php
$pageTitle = 'Produtos Finais - ' . APP_NAME;
$activeMenu = 'produtos';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">🍻 Produtos Finais</h1>
    <div class="page-actions">
        <a href="/produtos/create" class="btn btn-primary">+ Novo Produto</a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/produtos" class="row">
            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Nome ou SKU" value="<?= $_GET['search'] ?? '' ?>">
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-control form-select">
                        <option value="">Todos os tipos</option>
                        <option value="garrafa" <?= ($_GET['tipo'] ?? '') == 'garrafa' ? 'selected' : '' ?>>Garrafa</option>
                        <option value="lata" <?= ($_GET['tipo'] ?? '') == 'lata' ? 'selected' : '' ?>>Lata</option>
                        <option value="barril" <?= ($_GET['tipo'] ?? '') == 'barril' ? 'selected' : '' ?>>Barril</option>
                        <option value="growler" <?= ($_GET['tipo'] ?? '') == 'growler' ? 'selected' : '' ?>>Growler</option>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="ativo" class="form-control form-select">
                        <option value="">Todos</option>
                        <option value="1" <?= ($_GET['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativos</option>
                        <option value="0" <?= ($_GET['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativos</option>
                    </select>
                </div>
            </div>

            <div class="col col-3">
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="/produtos" class="btn btn-secondary">Limpar</a>
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
                <h3 style="color: #3498db; margin-bottom: 0.5rem;"><?= $stats['total_produtos'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Total de Produtos</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #27ae60; margin-bottom: 0.5rem;"><?= $stats['estoque_disponivel'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Unidades em Estoque</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #f39c12; margin-bottom: 0.5rem;"><?= formatMoney($stats['valor_estoque'] ?? 0) ?></h3>
                <p style="margin: 0; color: #6c757d;">Valor em Estoque</p>
            </div>
        </div>
    </div>

    <div class="col col-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 style="color: #e74c3c; margin-bottom: 0.5rem;"><?= $stats['produtos_baixo_estoque'] ?? 0 ?></h3>
                <p style="margin: 0; color: #6c757d;">Estoque Baixo</p>
            </div>
        </div>
    </div>
</div>

<!-- Listagem de Produtos -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Lista de Produtos</h3>
        <span class="badge badge-info"><?= count($produtos) ?> produtos</span>
    </div>
    <div class="card-body">
        <?php if (empty($produtos)): ?>
            <div class="alert alert-info">
                Nenhum produto encontrado. <a href="/produtos/create">Cadastre o primeiro produto</a>.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Nome</th>
                        <th>Tipo/Volume</th>
                        <th>Lote</th>
                        <th>Estoque</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($produto['nome'] ?? 'N/A') ?></strong></td><td><?= $produto['nome'] ?></td>
                        <td>
                            <?= isset($produto['estilo']) ? ucfirst($produto['estilo']) : 'Não definido' ?>
                            <small style="color: #6c757d;">
                                <?= isset($produto['tipo_embalagem']) ? ucfirst($produto['tipo_embalagem']) : 'Não definido' ?>
                            </small>
                        </td>
                        <td>
                            <?php if (isset($produto['lote_producao_id']) && isset($produto['lote_codigo'])): ?>
                                <a href="/producao/viewProducao?id=<?= $produto['lote_producao_id'] ?>">
                                    <?= htmlspecialchars($produto['lote_codigo']) ?>
                                </a>
                            <?php else: ?>
                                <span style="color: #6c757d;">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $produto['estoque_atual'] ?> un
                            <?php if ($produto['estoque_atual'] < $produto['estoque_minimo']): ?>
                                <br><span class="badge badge-warning">Baixo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= formatMoney($produto['preco_venda']) ?></td>
                        <td>
                            <?php if ($produto['ativo']): ?>
                                <span class="badge badge-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/produtos/viewProduto?id=<?= $produto['id'] ?>" class="btn-icon btn-primary" data-tooltip="Ver detalhes"><?= icon('view', '', 18) ?></a>
                                <a href="/produtos/edit?id=<?= $produto['id'] ?>" class="btn-icon btn-warning" data-tooltip="Editar"><?= icon('edit', '', 18) ?></a>
                                <form method="POST" action="/produtos/delete" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja inativar este produto?')">
                                    <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                                    <button type="submit" class="btn-icon btn-danger" data-tooltip="Excluir"><?= icon('delete', '', 18) ?></button>
                                </form>
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