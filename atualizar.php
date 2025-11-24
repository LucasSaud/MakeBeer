<?php
/**
 * SCRIPT DE ATUALIZAÇÃO AUTOMÁTICA DO SISTEMA ATOMOS
 *
 * Acesse via: http://localhost/atualizar.php
 *
 * Este script:
 * - Verifica tabelas faltantes no banco de dados
 * - Cria automaticamente apenas as tabelas que não existem
 * - Ideal para atualização de sistemas já em produção (Windows/Linux)
 * - Não sobrescreve dados existentes
 *
 * IMPORTANTE:
 * - Define uma senha de acesso abaixo
 * - Após usar, renomeie ou delete este arquivo
 */

// ============================================
// CONFIGURAÇÕES DE SEGURANÇA
// ============================================
define('UPDATE_PASSWORD', 'atomos2025'); // ALTERE ESTA SENHA!

// ============================================
// CARREGA CONFIGURAÇÕES DO SISTEMA
// ============================================
if (file_exists(__DIR__ . '/app/config/database.php')) {
    require_once __DIR__ . '/app/config/database.php';
} else {
    die('Erro: Arquivo app/config/database.php não encontrado!');
}

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
set_time_limit(300);

// ============================================
// DEFINIÇÃO DAS TABELAS NECESSÁRIAS
// ============================================
$requiredTables = [
    'usuarios',
    'fornecedores',
    'categorias_insumos',
    'insumos',
    'entradas_estoque',
    'receitas',
    'receita_ingredientes',
    'lotes_producao',
    'lote_consumos',
    'envases',              // TABELA DE ENVASE
    // 'barris' foi removido - pode conflitar com catálogo de barris físicos existente
    'saida_barril',         // TABELA DE SAÍDA DE BARRIL
    'estoque_barris',       // TABELA DE ESTOQUE DE BARRIS
    'produtos_finais',
    'producao_produtos',
    'camarafria_setores',
    'estoque_localizacao',
    'camarafria_movimentacoes',
    'camarafria_temperatura',
    'movimentacoes_estoque',
    'log_atividades'
];

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        return ['error' => 'Erro ao conectar: ' . $e->getMessage()];
    }
}

function checkTableExists($pdo, $tableName) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function getTableColumns($pdo, $tableName) {
    try {
        $stmt = $pdo->query("DESCRIBE $tableName");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

function getMissingTables($pdo, $requiredTables) {
    $missing = [];
    foreach ($requiredTables as $table) {
        if (!checkTableExists($pdo, $table)) {
            $missing[] = $table;
        }
    }
    return $missing;
}

function createMissingTables($pdo, $missingTables) {
    $logs = [];
    $errors = [];

    // Lê o arquivo de criação completo
    $sqlFile = __DIR__ . '/database/migrations/001_create_all_tables.sql';

    if (!file_exists($sqlFile)) {
        return [
            'success' => false,
            'error' => 'Arquivo de migração não encontrado: ' . $sqlFile
        ];
    }

    $sql = file_get_contents($sqlFile);

    // Remove comentários
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // Desabilita verificação de chaves estrangeiras temporariamente
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Extrai CREATE TABLE de cada tabela faltante
    foreach ($missingTables as $tableName) {
        $logs[] = "Criando tabela: <strong>$tableName</strong>";

        // Tenta encontrar o CREATE TABLE dessa tabela
        $pattern = '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?' . preg_quote($tableName, '/') . '\s*\([^;]*\)[^;]*;/is';

        if (preg_match($pattern, $sql, $matches)) {
            $createStatement = $matches[0];

            try {
                $pdo->exec($createStatement);
                $logs[] = "✓ Tabela <strong>$tableName</strong> criada com sucesso";
            } catch (PDOException $e) {
                $error = "✗ Erro ao criar tabela $tableName: " . $e->getMessage();
                $errors[] = $error;
                $logs[] = "<span style='color: red;'>$error</span>";
            }
        } else {
            $error = "⚠ Definição da tabela $tableName não encontrada no arquivo de migração";
            $errors[] = $error;
            $logs[] = "<span style='color: orange;'>$error</span>";
        }
    }

    // Reabilita verificação de chaves estrangeiras
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    return [
        'success' => empty($errors),
        'logs' => $logs,
        'errors' => $errors
    ];
}

function checkColumnExists($pdo, $tableName, $columnName) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM $tableName LIKE '$columnName'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function addMissingColumns($pdo) {
    $logs = [];
    $errors = [];

    // Verifica e adiciona colunas específicas que podem estar faltando
    $columnsToCheck = [
        'lotes_producao' => [
            'envase_iniciado' => 'ADD COLUMN envase_iniciado BOOLEAN DEFAULT FALSE',
            'envase_finalizado' => 'ADD COLUMN envase_finalizado BOOLEAN DEFAULT FALSE',
            'data_envase' => 'ADD COLUMN data_envase DATE NULL'
        ]
    ];

    foreach ($columnsToCheck as $table => $columns) {
        if (checkTableExists($pdo, $table)) {
            foreach ($columns as $columnName => $alterStatement) {
                if (!checkColumnExists($pdo, $table, $columnName)) {
                    try {
                        $pdo->exec("ALTER TABLE $table $alterStatement");
                        $logs[] = "✓ Coluna <strong>$columnName</strong> adicionada à tabela <strong>$table</strong>";
                    } catch (PDOException $e) {
                        $error = "✗ Erro ao adicionar coluna $columnName: " . $e->getMessage();
                        $errors[] = $error;
                        $logs[] = "<span style='color: red;'>$error</span>";
                    }
                }
            }
        }
    }

    // NOTA: Tabela 'barris' NÃO é criada pelo sistema de envase
    // Se existir uma tabela 'barris', é um catálogo de barris físicos independente
    // O sistema de envase usa apenas 'estoque_barris'

    // Corrigir estrutura da tabela estoque_barris
    if (checkTableExists($pdo, 'estoque_barris')) {
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM estoque_barris");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $has_barril_fisico_id = in_array('barril_fisico_id', $columns);
            $has_codigo_barril = in_array('codigo_barril', $columns);
            $has_old_barril_id = in_array('barril_id', $columns);

            // Se tem barril_id (antigo) e não tem barril_fisico_id, renomear
            if ($has_old_barril_id && !$has_barril_fisico_id) {
                $pdo->exec("ALTER TABLE estoque_barris CHANGE COLUMN barril_id barril_fisico_id INT NULL COMMENT 'Referência opcional ao catálogo de barris físicos'");
                $logs[] = "✓ Coluna <strong>barril_id</strong> renomeada para <strong>barril_fisico_id</strong> na tabela <strong>estoque_barris</strong>";
            }

            // Se não tem codigo_barril, adicionar
            if (!$has_codigo_barril) {
                $pdo->exec("ALTER TABLE estoque_barris ADD COLUMN codigo_barril VARCHAR(50) NOT NULL DEFAULT '' AFTER numero_barril");
                $logs[] = "✓ Coluna <strong>codigo_barril</strong> adicionada na tabela <strong>estoque_barris</strong>";
            }
        } catch (PDOException $e) {
            $error = "⚠ Aviso ao verificar tabela estoque_barris: " . $e->getMessage();
            $logs[] = "<span style='color: orange;'>$error</span>";
        }
    }

    // Corrigir estrutura da tabela saida_barril
    if (checkTableExists($pdo, 'saida_barril')) {
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM saida_barril");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $has_estoque_barril_id = in_array('estoque_barril_id', $columns);
            $has_old_barril_id = in_array('barril_id', $columns);

            // Se tem barril_id (antigo) e não tem estoque_barril_id, renomear
            if ($has_old_barril_id && !$has_estoque_barril_id) {
                $pdo->exec("ALTER TABLE saida_barril CHANGE COLUMN barril_id estoque_barril_id INT NULL");
                $logs[] = "✓ Coluna <strong>barril_id</strong> renomeada para <strong>estoque_barril_id</strong> na tabela <strong>saida_barril</strong>";
            }
        } catch (PDOException $e) {
            $error = "⚠ Aviso ao verificar tabela saida_barril: " . $e->getMessage();
            $logs[] = "<span style='color: orange;'>$error</span>";
        }
    }

    // Aplicar migração para importação de receitas BeerXML
    $importMigrationResult = applyImportMigration($pdo);
    $logs = array_merge($logs, $importMigrationResult['logs']);
    if (!$importMigrationResult['success']) {
        $errors = array_merge($errors, $importMigrationResult['errors']);
    }

    return [
        'success' => empty($errors),
        'logs' => $logs,
        'errors' => $errors
    ];
}

function applyImportMigration($pdo) {
    $logs = [];
    $errors = [];

    $logs[] = "Aplicando migração para importação de receitas BeerXML...";

    // Verificar se o arquivo de migração existe
    $migrationFile = __DIR__ . '/database/migrations/002_add_import_columns.sql';
    if (!file_exists($migrationFile)) {
        $logs[] = "<span style='color: orange;'>⚠ Arquivo de migração 002 não encontrado, pulando...</span>";
        return ['success' => true, 'logs' => $logs, 'errors' => $errors];
    }

    // Ler o conteúdo do arquivo
    $sql = file_get_contents($migrationFile);

    // Remover comentários e linhas vazias
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $sql = preg_replace('/^\s*[\r\n]/m', '', $sql);

    // Desabilita verificação de chaves estrangeiras temporariamente
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Dividir em comandos individuais
    $commands = explode(';', $sql);

    $successCount = 0;
    $errorCount = 0;

    foreach ($commands as $command) {
        $command = trim($command);
        if (empty($command)) {
            continue;
        }

        try {
            $pdo->exec($command);
            $successCount++;
        } catch (PDOException $e) {
            // Ignorar erros de colunas já existentes
            if (strpos($e->getMessage(), 'Duplicate column name') !== false ||
                strpos($e->getMessage(), 'already exists') !== false ||
                strpos($e->getMessage(), 'Duplicate entry') !== false) {
                // Coluna ou índice já existe, ignorar
                $successCount++;
            } else {
                $error = "Erro ao executar comando: " . $e->getMessage();
                $errors[] = $error;
                $errorCount++;
            }
        }
    }

    // Reabilita verificação de chaves estrangeiras
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $logs[] = "✓ Migração 002 aplicada: $successCount comandos executados";

    return [
        'success' => empty($errors),
        'logs' => $logs,
        'errors' => $errors
    ];
}

// ============================================
// PROCESSAMENTO DE AÇÕES
// ============================================

$message = '';
$error = '';
$logs = [];
$systemInfo = null;

// Verifica autenticação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['password']) || $_POST['password'] !== UPDATE_PASSWORD) {
        $error = 'Senha incorreta!';
    } else {
        $_SESSION['authenticated'] = true;
    }
}

// Processa atualização
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    $pdo = getDBConnection();

    if (is_array($pdo) && isset($pdo['error'])) {
        $error = $pdo['error'];
    } else {
        // Obtém informações do sistema
        $existingTables = [];
        try {
            $stmt = $pdo->query("SHOW TABLES");
            $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $error = "Erro ao listar tabelas: " . $e->getMessage();
        }

        $missingTables = getMissingTables($pdo, $requiredTables);

        $systemInfo = [
            'php_version' => phpversion(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido',
            'db_name' => DB_NAME,
            'db_host' => DB_HOST,
            'total_tables' => count($existingTables),
            'required_tables' => count($requiredTables),
            'missing_tables' => count($missingTables),
            'missing_list' => $missingTables,
            'existing_tables' => $existingTables
        ];

        // Executa atualização se solicitado
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute'])) {
            if (empty($missingTables)) {
                $logs[] = "✓ Todas as tabelas já existem! Verificando colunas...";

                // Verifica colunas faltantes
                $columnResult = addMissingColumns($pdo);
                $logs = array_merge($logs, $columnResult['logs']);

                if ($columnResult['success']) {
                    $message = "Sistema atualizado com sucesso!";
                } else {
                    $error = "Algumas colunas não puderam ser adicionadas. Verifique os logs.";
                }
            } else {
                $logs[] = "Criando " . count($missingTables) . " tabela(s) faltante(s)...";

                $result = createMissingTables($pdo, $missingTables);
                $logs = array_merge($logs, $result['logs']);

                if ($result['success']) {
                    // Verifica colunas após criar tabelas
                    $columnResult = addMissingColumns($pdo);
                    $logs = array_merge($logs, $columnResult['logs']);

                    $message = "Atualização concluída com sucesso!";
                } else {
                    $error = "Algumas tabelas não puderam ser criadas. Verifique os logs.";
                }

                // Atualiza informações
                $missingTables = getMissingTables($pdo, $requiredTables);
                $systemInfo['missing_tables'] = count($missingTables);
                $systemInfo['missing_list'] = $missingTables;
            }
        }
    }
}

// Definir variáveis para o layout
$pageTitle = 'Atualizar - Sistema Atomos';
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
                    <?= APP_NAME ?> - Atualização
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
                    <h1 class="page-title">🔄 Atualização do Sistema</h1>
                    <p class="page-subtitle">Atualização Automática de Banco de Dados</p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <?php if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true): ?>
                            <!-- FORMULÁRIO DE AUTENTICAÇÃO -->
                            <form method="POST">
                                <div class="form-group">
                                    <label class="form-label">🔐 Senha de Acesso:</label>
                                    <input type="password" name="password" required autofocus placeholder="Digite a senha de atualização" class="form-control">
                                </div>
                                <input type="hidden" name="action" value="authenticate">
                                <button type="submit" class="btn btn-primary">Autenticar</button>
                            </form>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">ℹ️ Sobre este Script</h3>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li>✅ Verifica tabelas faltantes no banco de dados</li>
                                        <li>✅ Cria apenas tabelas que não existem</li>
                                        <li>✅ Não sobrescreve dados existentes</li>
                                        <li>✅ Compatível com Windows e Linux</li>
                                        <li>✅ Ideal para atualizar sistemas em produção</li>
                                        <li>✅ Aplica migrações para funcionalidades adicionais</li>
                                    </ul>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- INFORMAÇÕES E CONTROLES -->
                            <?php if ($systemInfo): ?>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h4 class="mb-1" style="color: #667eea;"><?= $systemInfo['total_tables'] ?></h4>
                                                <p class="mb-0">Tabelas Existentes</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h4 class="mb-1" style="color: #28a745;"><?= $systemInfo['required_tables'] ?></h4>
                                                <p class="mb-0">Tabelas Necessárias</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h4 class="mb-1" style="color: <?= $systemInfo['missing_tables'] > 0 ? '#dc3545' : '#28a745' ?>">
                                                    <?= $systemInfo['missing_tables'] ?>
                                                </h4>
                                                <p class="mb-0">Tabelas Faltantes</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h3 class="card-title">📊 Informações do Sistema</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <p><strong>PHP:</strong> <?= $systemInfo['php_version'] ?></p>
                                                <p><strong>Servidor:</strong> <?= $systemInfo['server'] ?></p>
                                            </div>
                                            <div class="col-6">
                                                <p><strong>Banco de Dados:</strong> <?= $systemInfo['db_name'] ?> @ <?= $systemInfo['db_host'] ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($systemInfo['missing_tables'] > 0): ?>
                                    <div class="alert alert-warning">
                                        <strong>⚠️ Atenção:</strong> Existem <?= $systemInfo['missing_tables'] ?> tabela(s) faltante(s) no banco de dados.
                                        
                                        <div class="card mt-2">
                                            <div class="card-header">
                                                <h4 class="card-title">Tabelas Faltantes</h4>
                                            </div>
                                            <div class="card-body">
                                                <ul>
                                                    <?php foreach ($systemInfo['missing_list'] as $table): ?>
                                                        <li>❌ <?= htmlspecialchars($table) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="POST">
                                        <input type="hidden" name="password" value="<?= htmlspecialchars(UPDATE_PASSWORD) ?>">
                                        <input type="hidden" name="action" value="authenticate">
                                        <input type="hidden" name="execute" value="update">
                                        <button type="submit" class="btn btn-warning" onclick="return confirm('Confirma a criação das tabelas faltantes?')">
                                            🚀 Criar Tabelas Faltantes
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <strong>✅ Parabéns!</strong> Todas as tabelas necessárias já existem no banco de dados.
                                    </div>

                                    <form method="POST">
                                        <input type="hidden" name="password" value="<?= htmlspecialchars(UPDATE_PASSWORD) ?>">
                                        <input type="hidden" name="action" value="authenticate">
                                        <input type="hidden" name="execute" value="update">
                                        <button type="submit" class="btn btn-success">
                                            🔍 Verificar Novamente e Adicionar Colunas Faltantes
                                        </button>
                                    </form>
                                <?php endif; ?>

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

                                <?php if ($systemInfo['total_tables'] > 0): ?>
                                    <div class="card mt-3">
                                        <div class="card-header">
                                            <h3 class="card-title">📋 Tabelas Existentes</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <?php foreach ($systemInfo['existing_tables'] as $table): ?>
                                                    <div class="col-3 mb-2">
                                                        <span class="badge badge-success">✓ <?= htmlspecialchars($table) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

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
    header('Location: atualizar.php');
    exit;
}
?>