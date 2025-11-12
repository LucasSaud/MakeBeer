<?php
$pageTitle = 'Relatórios - ' . APP_NAME;
$activeMenu = 'relatorios';
include 'app/views/layouts/header.php';
?>

<div class="page-header">
    <h1 class="page-title">📈 Relatórios</h1>
    <p class="page-subtitle">Selecione o tipo de relatório que deseja visualizar</p>
</div>

<div class="row">
    <!-- Relatório de Estoque -->
    <div class="col col-6 mb-4">
        <div class="card" style="cursor: pointer;" onclick="window.location.href='/relatorios/estoque'">
            <div class="card-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
                <h3 class="card-title" style="color: white; margin: 0;">Relatório de Estoque</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Visualize o status atual do estoque de todos os insumos, incluindo quantidades, valores e alertas de estoque baixo.
                </p>
                <ul style="color: #6c757d; margin-bottom: 1rem;">
                    <li>Estoque atual de insumos</li>
                    <li>Valor total em estoque</li>
                    <li>Insumos com estoque crítico</li>
                    <li>Distribuição por categoria</li>
                </ul>
                <a href="/relatorios/estoque" class="btn btn-primary">Acessar Relatório</a>
            </div>
        </div>
    </div>

    <!-- Relatório de Produção -->
    <div class="col col-6 mb-4">
        <div class="card" style="cursor: pointer;" onclick="window.location.href='/relatorios/producao'">
            <div class="card-header" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%); color: white;">
                <h3 class="card-title" style="color: white; margin: 0;">Relatório de Produção</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Acompanhe a produção de cerveja, incluindo lotes em andamento, finalizados e estatísticas de produtividade.
                </p>
                <ul style="color: #6c757d; margin-bottom: 1rem;">
                    <li>Lotes produzidos por período</li>
                    <li>Volume total produzido</li>
                    <li>Custos de produção</li>
                    <li>Eficiência por receita</li>
                </ul>
                <a href="/relatorios/producao" class="btn btn-success">Acessar Relatório</a>
            </div>
        </div>
    </div>

    <!-- Relatório de Compras -->
    <div class="col col-6 mb-4">
        <div class="card" style="cursor: pointer;" onclick="window.location.href='/relatorios/compras'">
            <div class="card-header" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white;">
                <h3 class="card-title" style="color: white; margin: 0;">Relatório de Compras</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Analise as entradas de estoque, fornecedores e gastos com aquisição de insumos.
                </p>
                <ul style="color: #6c757d; margin-bottom: 1rem;">
                    <li>Total de compras por período</li>
                    <li>Gastos por fornecedor</li>
                    <li>Gastos por categoria de insumo</li>
                    <li>Histórico de preços</li>
                </ul>
                <a href="/relatorios/compras" class="btn btn-warning">Acessar Relatório</a>
            </div>
        </div>
    </div>

    <!-- Relatório de Custos -->
    <div class="col col-6 mb-4">
        <div class="card" style="cursor: pointer;" onclick="window.location.href='/relatorios/custos'">
            <div class="card-header" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white;">
                <h3 class="card-title" style="color: white; margin: 0;">Relatório de Custos</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Analise os custos de produção, precificação e margens de lucro dos produtos.
                </p>
                <ul style="color: #6c757d; margin-bottom: 1rem;">
                    <li>Custo de produção por receita</li>
                    <li>Margem de lucro por produto</li>
                    <li>Análise de rentabilidade</li>
                    <li>Evolução de custos</li>
                </ul>
                <a href="/relatorios/custos" class="btn btn-danger">Acessar Relatório</a>
            </div>
        </div>
    </div>

    <!-- Relatório de Validades -->
    <div class="col col-6 mb-4">
        <div class="card" style="cursor: pointer;" onclick="window.location.href='/relatorios/validade'">
            <div class="card-header" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: white;">
                <h3 class="card-title" style="color: white; margin: 0;">Relatório de Validades</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Controle as datas de validade de insumos e produtos para evitar perdas.
                </p>
                <ul style="color: #6c757d; margin-bottom: 1rem;">
                    <li>Insumos próximos do vencimento</li>
                    <li>Produtos próximos do vencimento</li>
                    <li>Itens vencidos</li>
                    <li>Alertas preventivos</li>
                </ul>
                <a href="/relatorios/validade" class="btn" style="background: #9b59b6; color: white;">Acessar Relatório</a>
            </div>
        </div>
    </div>

    <!-- Exportações -->
    <div class="col col-6 mb-4">
        <div class="card">
            <div class="card-header" style="background: #34495e; color: white;">
                <h3 class="card-title" style="color: white; margin: 0;">Exportações</h3>
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1rem;">
                    Exporte dados do sistema em diversos formatos para análise externa.
                </p>
                <ul style="color: #6c757d; margin-bottom: 1rem;">
                    <li>Exportar para Excel (XLSX)</li>
                    <li>Exportar para CSV</li>
                    <li>Exportar para PDF</li>
                    <li>Agendar relatórios automáticos</li>
                </ul>
                <button class="btn btn-secondary" disabled>Em breve</button>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
