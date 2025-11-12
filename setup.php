<?php
/**
 * SCRIPT DE INSTALAÇÃO E ATUALIZAÇÃO DO SISTEMA ATOMOS
 *
 * Acesse via: http://localhost/setup.php
 *
 * IMPORTANTE:
 * - Por segurança, defina uma senha de acesso abaixo
 * - Após usar, renomeie ou delete este arquivo
 * - Usa as configurações de app/config/database.php e app/config/config.php
 */

// ============================================
// CONFIGURAÇÕES DE SEGURANÇA
// ============================================
define('SETUP_PASSWORD', 'atomos2025'); // ALTERE ESTA SENHA!

// ============================================
// CARREGA CONFIGURAÇÕES DO SISTEMA
// ============================================
// Carrega configurações do banco de dados
if (file_exists(__DIR__ . '/app/config/database.php')) {
    require_once __DIR__ . '/app/config/database.php';
} else {
    die('Erro: Arquivo app/config/database.php não encontrado!');
}

// Carrega configurações gerais (opcional, para pegar APP_URL)
if (file_exists(__DIR__ . '/app/config/config.php')) {
    require_once __DIR__ . '/app/config/config.php';
}

// Carrega funções auxiliares
if (file_exists(__DIR__ . '/app/helpers/functions.php')) {
    require_once __DIR__ . '/app/helpers/functions.php';
}

// ============================================
// INICIALIZAÇÃO
// ============================================
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutos timeout

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

function getDBConnection() {
    try {
        // Usa as constantes definidas em app/config/database.php
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        return ['error' => 'Erro ao conectar: ' . $e->getMessage()];
    }
}

function createDatabase($pdo) {
    try {
        // Usa DB_NAME definido em app/config/database.php
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE " . DB_NAME);
        return ['success' => true, 'message' => 'Banco de dados "' . DB_NAME . '" criado/selecionado com sucesso'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function getMigrationFiles() {
    $migrationDir = __DIR__ . '/database/migrations/';
    if (!is_dir($migrationDir)) {
        return ['error' => 'Diretório de migrações não encontrado'];
    }

    $files = glob($migrationDir . '*.sql');
    sort($files);
    return $files;
}

function executeSQLFile($pdo, $filepath) {
    try {
        $sql = file_get_contents($filepath);
        if ($sql === false) {
            return ['success' => false, 'error' => 'Não foi possível ler o arquivo'];
        }

        // Remove comentários e divide por declarações
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // Divide por ponto e vírgula, mas mantém procedures/functions intactas
        $statements = [];
        $buffer = '';
        $delimiter = ';';

        foreach (explode("\n", $sql) as $line) {
            $line = trim($line);

            // Verifica mudança de delimitador
            if (preg_match('/^DELIMITER\s+(\S+)/i', $line, $matches)) {
                $delimiter = $matches[1];
                continue;
            }

            $buffer .= $line . "\n";

            if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
                $statement = trim(substr($buffer, 0, -strlen($delimiter)));
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $buffer = '';
            }
        }

        // Executa cada declaração
        $executed = 0;
        $errors = [];

        foreach ($statements as $statement) {
            if (empty(trim($statement))) continue;

            try {
                $pdo->exec($statement);
                $executed++;
            } catch (PDOException $e) {
                // Ignora erros de "tabela já existe" e "registro duplicado"
                if (strpos($e->getMessage(), 'already exists') === false &&
                    strpos($e->getMessage(), 'Duplicate entry') === false) {
                    $errors[] = substr($statement, 0, 50) . '... => ' . $e->getMessage();
                }
            }
        }

        return [
            'success' => true,
            'executed' => $executed,
            'errors' => $errors
        ];

    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function checkTableExists($pdo, $tableName) {
    try {
        $pdo->exec("USE " . DB_NAME);
        $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function getSystemInfo($pdo) {
    $info = [
        'php_version' => phpversion(),
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido',
        'db_exists' => false,
        'tables' => []
    ];

    try {
        $pdo->exec("USE " . DB_NAME);
        $info['db_exists'] = true;

        $stmt = $pdo->query("SHOW TABLES");
        $info['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $info['table_count'] = count($info['tables']);
    } catch (PDOException $e) {
        $info['db_error'] = $e->getMessage();
    }

    return $info;
}

// ============================================
// PROCESSAMENTO DE AÇÕES
// ============================================

$message = '';
$error = '';
$logs = [];

// Verifica autenticação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['password']) || $_POST['password'] !== SETUP_PASSWORD) {
        $error = 'Senha incorreta!';
    } else {
        $_SESSION['authenticated'] = true;
    }
}

// Processa ações
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute'])) {
        $action = $_POST['execute'];

        $pdo = getDBConnection();
        if (is_array($pdo) && isset($pdo['error'])) {
            $error = $pdo['error'];
        } else {
            // Cria banco se necessário
            $result = createDatabase($pdo);
            if (!$result['success']) {
                $error = $result['error'];
            } else {
                $logs[] = $result['message'];

                // Executa migrações
                $migrations = getMigrationFiles();

                if (is_array($migrations) && isset($migrations['error'])) {
                    $error = $migrations['error'];
                } else {
                    $selectedMigrations = $_POST['migrations'] ?? [];

                    if (empty($selectedMigrations)) {
                        $error = 'Selecione pelo menos uma migração para executar';
                    } else {
                        foreach ($migrations as $file) {
                            $filename = basename($file);

                            if (in_array($filename, $selectedMigrations)) {
                                $logs[] = "<strong>Executando:</strong> $filename";
                                $result = executeSQLFile($pdo, $file);

                                if ($result['success']) {
                                    $logs[] = "✓ Executadas {$result['executed']} declarações";
                                    if (!empty($result['errors'])) {
                                        foreach ($result['errors'] as $err) {
                                            $logs[] = "<span style='color: orange;'>⚠ $err</span>";
                                        }
                                    }
                                } else {
                                    $logs[] = "<span style='color: red;'>✗ Erro: {$result['error']}</span>";
                                }
                            }
                        }
                        $message = 'Migração concluída!';
                    }
                }
            }
        }
    }
}

// Obtém informações do sistema
$pdo = getDBConnection();
$systemInfo = is_array($pdo) && isset($pdo['error']) ? null : getSystemInfo($pdo);
$migrations = is_array($pdo) && isset($pdo['error']) ? [] : getMigrationFiles();

// Definir variáveis para o layout
$pageTitle = 'Setup - Sistema Atomos';
$additionalCSS = '<link rel="stylesheet" href="/public/css/style.css">';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="icon" type="image/x-icon" href="/public/images/favicon.ico">
</head>
<body>
    <div class="wrapper">
        <!-- HEADER FIXO NO TOPO -->
        <header class="header">
            <div class="header-content">
                <a href="/" class="logo">
                    <?= APP_NAME ?> - Setup
                </a>

                <div class="user-info">
                    <!-- Theme Toggle -->
                    <button class="theme-toggle" id="themeToggle" title="Alternar tema">
                        <span class="theme-icon">🌙</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- SIDEBAR LATERAL FIXA -->
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="/" class="sidebar-link" data-tooltip="Voltar ao Sistema">
                        <span class="sidebar-icon">🏠</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="main-content">
            <div class="container">
                <div class="page-header">
                    <h1 class="page-title">🍺 Instalação e Atualização do Sistema</h1>
                    <p class="page-subtitle">Setup do Sistema Atomos</p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <?php if ($systemInfo): ?>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">📊 Informações do Sistema</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>PHP:</strong> <?= $systemInfo['php_version'] ?></p>
                                            <p><strong>Servidor:</strong> <?= $systemInfo['server'] ?></p>
                                            <p><strong>Host DB:</strong> <?= DB_HOST ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p>
                                                <strong>Banco de Dados:</strong> <?= DB_NAME ?>
                                                <?php if ($systemInfo['db_exists']): ?>
                                                    <span class="badge badge-success">Existe</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Não existe</span>
                                                <?php endif; ?>
                                            </p>
                                            <?php if ($systemInfo['db_exists']): ?>
                                                <p><strong>Tabelas:</strong> <?= $systemInfo['table_count'] ?> encontradas</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true): ?>
                            <!-- FORMULÁRIO DE AUTENTICAÇÃO -->
                            <form method="POST">
                                <div class="form-group">
                                    <label class="form-label">🔐 Senha de Acesso:</label>
                                    <input type="password" name="password" required autofocus placeholder="Digite a senha de setup" class="form-control">
                                </div>
                                <input type="hidden" name="action" value="authenticate">
                                <button type="submit" class="btn btn-primary">Autenticar</button>
                            </form>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">ℹ️ Instruções</h3>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li>A senha padrão está definida no arquivo setup.php (linha 16)</li>
                                        <li>Configurações de DB vêm de app/config/database.php</li>
                                        <li>Altere a constante SETUP_PASSWORD para maior segurança</li>
                                        <li>Após o uso, delete ou renomeie este arquivo</li>
                                    </ul>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- FORMULÁRIO DE INSTALAÇÃO/ATUALIZAÇÃO -->
                            <form method="POST" id="setupForm">
                                <div class="form-group">
                                    <label class="form-label">📦 Selecione as Migrações para Executar:</label>

                                    <?php if (is_array($migrations) && !empty($migrations)): ?>
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h4 class="card-title mb-0">Arquivos de Migração</h4>
                                                    <div>
                                                        <input type="checkbox" id="selectAll" onchange="toggleAll()">
                                                        <label for="selectAll" style="display: inline; cursor: pointer;">
                                                            Selecionar Todas
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <?php foreach ($migrations as $file):
                                                        $filename = basename($file);
                                                        $filesize = filesize($file);
                                                        $filesizeKb = round($filesize / 1024, 2);
                                                    ?>
                                                        <div class="col-6 mb-2">
                                                            <div class="form-check">
                                                                <input type="checkbox"
                                                                       name="migrations[]"
                                                                       value="<?= htmlspecialchars($filename) ?>"
                                                                       id="mig_<?= md5($filename) ?>"
                                                                       class="form-check-input migration-checkbox"
                                                                       checked>
                                                                <label class="form-check-label" for="mig_<?= md5($filename) ?>">
                                                                    <?= htmlspecialchars($filename) ?>
                                                                    <small class="text-muted">(<?= $filesizeKb ?> KB)</small>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-error">
                                            Nenhum arquivo de migração encontrado em /database/migrations/
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <input type="hidden" name="password" value="<?= htmlspecialchars(SETUP_PASSWORD) ?>">
                                <input type="hidden" name="execute" value="migrate">

                                <?php if (!empty($migrations)): ?>
                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Tem certeza que deseja executar as migrações selecionadas?')">
                                        🚀 Executar Migrações
                                    </button>
                                <?php endif; ?>
                            </form>

                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-secondary" onclick="location.reload()">
                                    🔄 Atualizar Página
                                </button>

                                <form method="POST">
                                    <button type="submit" class="btn btn-secondary" name="logout" value="1">
                                        🚪 Sair
                                    </button>
                                </form>
                            </div>

                        <?php endif; ?>

                        <?php if (!empty($logs)): ?>
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">📝 Log de Execução</h3>
                                </div>
                                <div class="card-body">
                                    <div class="logs bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                                        <?php foreach ($logs as $log): ?>
                                            <div class="mb-1"><?= $log ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.querySelector('.theme-icon');
    const html = document.documentElement;

    // Carregar tema salvo do localStorage
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    // Toggle do tema
    themeToggle.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
        themeIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
    }

    function toggleAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.migration-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }

    // Auto-check all on page load
    window.addEventListener('DOMContentLoaded', () => {
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.checked = true;
            toggleAll();
        }
    });

    <?php if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true): ?>
    // Auto-refresh a cada 30 segundos quando autenticado
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 30000);
    <?php endif; ?>
    </script>
</body>
</html>

<?php
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: setup.php');
    exit;
}
?>