<?php
$pageTitle = 'Resultado da Importação - ' . APP_NAME;
$activeMenu = 'receitas';
$additionalCSS = '<link rel="stylesheet" href="/public/css/import.css">';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">✅ Resultado da Importação</h1>
    <div class="page-actions">
        <a href="/import" class="btn btn-secondary">Importar Mais</a>
        <a href="/receitas" class="btn btn-primary">Ver Receitas</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Resumo da Importação</h3>
    </div>
    <div class="card-body">
        <?php
        $totalFiles = count($results);
        $successCount = 0;
        $errorCount = 0;
        $totalRecipes = 0;
        $totalIngredients = 0;
        
        foreach ($results as $result) {
            if ($result['status'] === 'success') {
                $successCount++;
                if (isset($result['recipes'])) {
                    $totalRecipes += count($result['recipes']);
                    foreach ($result['recipes'] as $recipe) {
                        $totalIngredients += $recipe['ingredientes'];
                    }
                }
            } else {
                $errorCount++;
            }
        }
        ?>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="mb-1"><?= $totalFiles ?></h4>
                        <p class="mb-0">Arquivos Processados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="mb-1 text-success"><?= $successCount ?></h4>
                        <p class="mb-0">Importados com Sucesso</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="mb-1 text-danger"><?= $errorCount ?></h4>
                        <p class="mb-0">Com Erros</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="mb-1"><?= $totalRecipes ?></h4>
                        <p class="mb-0">Receitas Importadas</p>
                    </div>
                </div>
            </div>
        </div>
        
        <h4>Detalhes da Importação</h4>
        <div class="accordion" id="importResultsAccordion">
            <?php foreach ($results as $index => $result): ?>
            <div class="card mb-2">
                <div class="card-header p-0" id="heading<?= $index ?>">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapse<?= $index ?>" aria-expanded="true" aria-controls="collapse<?= $index ?>">
                            <span>
                                <?php if ($result['status'] === 'success'): ?>
                                    <span class="text-success">✓</span>
                                <?php else: ?>
                                    <span class="text-danger">✗</span>
                                <?php endif; ?>
                                <?= htmlspecialchars($result['file']) ?>
                            </span>
                            <span>
                                <?php if ($result['status'] === 'success'): ?>
                                    <span class="badge badge-success">Sucesso</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Erro</span>
                                <?php endif; ?>
                            </span>
                        </button>
                    </h2>
                </div>
                
                <div id="collapse<?= $index ?>" class="collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $index ?>" data-parent="#importResultsAccordion">
                    <div class="card-body">
                        <?php if ($result['status'] === 'success'): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($result['message']) ?>
                            </div>
                            
                            <?php if (isset($result['recipes']) && !empty($result['recipes'])): ?>
                                <h5>Receitas Importadas:</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Nome da Receita</th>
                                                <th>Estilo</th>
                                                <th>Ingredientes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($result['recipes'] as $recipe): ?>
                                            <tr>
                                                <td>
                                                    <a href="/receitas/viewReceita?id=<?= $recipe['id'] ?>"><?= htmlspecialchars($recipe['nome']) ?></a>
                                                </td>
                                                <td><?= htmlspecialchars($recipe['estilo']) ?></td>
                                                <td><?= $recipe['ingredientes'] ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($result['message']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>