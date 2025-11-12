<?php
$pageTitle = 'Produtos Próximos ao Vencimento';
$activeMenu = 'camarafria';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">⚠️ Produtos Próximos ao Vencimento</h1>
    <div>
        <a href="/camarafria" class="btn btn-secondary">
            Voltar ao Dashboard
        </a>
    </div>
</div>

<!-- Filtro de Dias -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/camarafria/produtosVencimento" class="row g-3 align-items-end">
            <div class="col col-3">
                <label class="form-label">Mostrar produtos que vencem em:</label>
                <select name="dias" class="form-select" onchange="this.form.submit()">
                    <option value="7" <?= $dias == 7 ? 'selected' : '' ?>>Próximos 7 dias</option>
                    <option value="15" <?= $dias == 15 ? 'selected' : '' ?>>Próximos 15 dias</option>
                    <option value="30" <?= $dias == 30 ? 'selected' : '' ?>>Próximos 30 dias</option>
                    <option value="60" <?= $dias == 60 ? 'selected' : '' ?>>Próximos 60 dias</option>
                    <option value="90" <?= $dias == 90 ? 'selected' : '' ?>>Próximos 90 dias</option>
                </select>
            </div>
            <div class="col col-9 text-end">
                <?php if (!empty($produtos)): ?>
                    <span class="badge badge-<?= count($produtos) > 10 ? 'danger' : 'warning' ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <?= count($produtos) ?> produto(s) encontrado(s)
                    </span>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Produtos -->
<?php if (empty($produtos)): ?>
    <div class="card">
        <div class="card-body text-center" style="padding: 3rem;">
            <div style="font-size: 4rem; color: #2ecc71; margin-bottom: 1rem;">✓</div>
            <h3 style="color: #2ecc71;">Nenhum produto próximo ao vencimento!</h3>
            <p style="color: #6c757d;">Todos os produtos estão dentro do prazo de validade.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Urgência</th>
                            <th>Produto</th>
                            <th>Lote</th>
                            <th>Setor</th>
                            <th>Quantidade</th>
                            <th>Data Validade</th>
                            <th>Dias Restantes</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $prod): ?>
                            <?php
                                $diasRestantes = $prod['dias_restantes'];
                                $urgencia = $diasRestantes <= 3 ? 'critico' : ($diasRestantes <= 7 ? 'urgente' : ($diasRestantes <= 15 ? 'atencao' : 'normal'));

                                $corUrgencia = [
                                    'critico' => '#e74c3c',
                                    'urgente' => '#e67e22',
                                    'atencao' => '#f39c12',
                                    'normal' => '#95a5a6'
                                ];

                                $textoUrgencia = [
                                    'critico' => 'CRÍTICO',
                                    'urgente' => 'URGENTE',
                                    'atencao' => 'ATENÇÃO',
                                    'normal' => 'Normal'
                                ];
                            ?>
                            <tr style="<?= $urgencia == 'critico' ? 'background-color: #ffe6e6;' : '' ?>">
                                <td>
                                    <span class="badge" style="background: <?= $corUrgencia[$urgencia] ?>; color: white; font-weight: bold;">
                                        <?= $textoUrgencia[$urgencia] ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($prod['produto_nome']) ?></strong>
                                    <br><small style="color: #6c757d;"><?= htmlspecialchars($prod['tipo_embalagem']) ?></small>
                                </td>
                                <td>
                                    <code><?= htmlspecialchars($prod['numero_lote'] ?? 'S/N') ?></code>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?= htmlspecialchars($prod['setor_nome']) ?></span>
                                </td>
                                <td>
                                    <strong style="font-size: 1.1rem;"><?= $prod['quantidade'] ?></strong> un.
                                </td>
                                <td>
                                    <strong style="color: <?= $corUrgencia[$urgencia] ?>;">
                                        <?= date('d/m/Y', strtotime($prod['data_validade'])) ?>
                                    </strong>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <?php if ($diasRestantes <= 0): ?>
                                            <span class="badge badge-danger" style="font-size: 0.95rem;">VENCIDO</span>
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; border-radius: 50%; border: 4px solid <?= $corUrgencia[$urgencia] ?>; display: flex; align-items: center; justify-content: center; font-weight: bold; color: <?= $corUrgencia[$urgencia] ?>;">
                                                <?= $diasRestantes ?>
                                            </div>
                                            <span style="color: #6c757d; font-size: 0.875rem;">dia(s)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $prod['status'] == 'disponivel' ? 'success' : ($prod['status'] == 'quarentena' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($prod['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/camarafria/verSetor?id=<?= $prod['setor_id'] ?>"
                                           class="btn btn-outline-primary"
                                           title="Ver Setor">
                                            📍
                                        </a>
                                        <?php if ($diasRestantes <= 7 && $prod['status'] == 'disponivel'): ?>
                                            <button type="button"
                                                    class="btn btn-outline-warning"
                                                    onclick="alterarStatusQuarentena(<?= $prod['id'] ?>)"
                                                    title="Mover para Quarentena">
                                                ⚠️
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Resumo por Urgência -->
    <div class="row mt-4">
        <?php
            $contadores = [
                'critico' => 0,
                'urgente' => 0,
                'atencao' => 0,
                'normal' => 0
            ];

            foreach ($produtos as $p) {
                $dias = $p['dias_restantes'];
                if ($dias <= 3) $contadores['critico']++;
                elseif ($dias <= 7) $contadores['urgente']++;
                elseif ($dias <= 15) $contadores['atencao']++;
                else $contadores['normal']++;
            }
        ?>

        <div class="col col-3">
            <div class="card" style="border-left: 4px solid #e74c3c;">
                <div class="card-body text-center">
                    <h2 style="color: #e74c3c; margin-bottom: 0.5rem;"><?= $contadores['critico'] ?></h2>
                    <p style="margin: 0; color: #6c757d;">Críticos (≤3 dias)</p>
                </div>
            </div>
        </div>
        <div class="col col-3">
            <div class="card" style="border-left: 4px solid #e67e22;">
                <div class="card-body text-center">
                    <h2 style="color: #e67e22; margin-bottom: 0.5rem;"><?= $contadores['urgente'] ?></h2>
                    <p style="margin: 0; color: #6c757d;">Urgentes (4-7 dias)</p>
                </div>
            </div>
        </div>
        <div class="col col-3">
            <div class="card" style="border-left: 4px solid #f39c12;">
                <div class="card-body text-center">
                    <h2 style="color: #f39c12; margin-bottom: 0.5rem;"><?= $contadores['atencao'] ?></h2>
                    <p style="margin: 0; color: #6c757d;">Atenção (8-15 dias)</p>
                </div>
            </div>
        </div>
        <div class="col col-3">
            <div class="card" style="border-left: 4px solid #95a5a6;">
                <div class="card-body text-center">
                    <h2 style="color: #95a5a6; margin-bottom: 0.5rem;"><?= $contadores['normal'] ?></h2>
                    <p style="margin: 0; color: #6c757d;">Normal (>15 dias)</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function alterarStatusQuarentena(localizacaoId) {
    if (confirm('Deseja mover este produto para QUARENTENA?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/camarafria/alterarStatus';

        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'localizacao_id';
        inputId.value = localizacaoId;

        const inputStatus = document.createElement('input');
        inputStatus.type = 'hidden';
        inputStatus.name = 'status';
        inputStatus.value = 'quarentena';

        const inputObs = document.createElement('input');
        inputObs.type = 'hidden';
        inputObs.name = 'observacoes';
        inputObs.value = 'Movido para quarentena - Próximo ao vencimento';

        form.appendChild(inputId);
        form.appendChild(inputStatus);
        form.appendChild(inputObs);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'app/views/layouts/footer.php'; ?>
