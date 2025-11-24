<?php
$pageTitle = 'Importar Receitas - ' . APP_NAME;
$activeMenu = 'receitas';
$additionalCSS = '<link rel="stylesheet" href="/public/css/import.css">';
include 'app/views/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1 class="page-title">📥 Importar Receitas BeerXML</h1>
    <div class="page-actions">
        <a href="/receitas" class="btn btn-secondary">← Voltar para Receitas</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Importar Receitas BeerXML</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <p><strong>ℹ️ Instruções:</strong></p>
            <ul>
                <li>Você pode importar receitas individuais ou em lote</li>
                <li>Os arquivos devem estar no formato BeerXML (.xml)</li>
                <li>Receitas com o mesmo nome serão atualizadas</li>
                <li>Apenas administradores podem realizar importações</li>
            </ul>
        </div>

        <form method="POST" action="/import/upload" enctype="multipart/form-data" id="importForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">📁 Importar Arquivos</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Selecione os arquivos BeerXML:</label>
                                <div class="import-file-upload" id="fileDropArea">
                                    <div class="import-file-icon">📁</div>
                                    <div class="import-file-text">Arraste e solte os arquivos aqui ou clique para selecionar</div>
                                    <input type="file" name="xml_files[]" class="form-control" accept=".xml" multiple style="display: none;">
                                </div>
                                <small class="form-text text-muted">Você pode selecionar múltiplos arquivos XML</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">📂 Importar Diretório</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Caminho do diretório:</label>
                                <input type="text" name="directory_path" class="form-control" placeholder="/caminho/para/diretorios/com/arquivos/xml">
                                <small class="form-text text-muted">Informe o caminho completo para o diretório contendo arquivos XML</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="clearImportForm()">Limpar</button>
                <button type="submit" class="btn btn-primary" onclick="return confirmImport()">
                    🚀 Iniciar Importação
                </button>
            </div>
            
            <div class="import-progress-container mt-4">
                <div class="import-progress-bar">
                    <div class="import-progress-fill"></div>
                </div>
                <div class="text-center mt-2">Processando importação...</div>
            </div>
        </form>
    </div>
</div>

<script src="/public/js/import.js"></script>
<script>
function confirmImport() {
    const fileInput = document.querySelector('input[name="xml_files[]"]');
    const dirInput = document.querySelector('input[name="directory_path"]');
    
    if (!fileInput.files.length && !dirInput.value.trim()) {
        alert('Por favor, selecione arquivos para importar ou informe um diretório.');
        return false;
    }
    
    if (fileInput.files.length > 0) {
        const fileCount = fileInput.files.length;
        return confirm(`Tem certeza que deseja importar ${fileCount} arquivo(s)?`);
    }
    
    if (dirInput.value.trim()) {
        return confirm(`Tem certeza que deseja importar todos os arquivos XML do diretório:\n${dirInput.value.trim()}?`);
    }
    
    return false;
}
</script>

<?php include 'app/views/layouts/footer.php'; ?>