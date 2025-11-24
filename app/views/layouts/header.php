<?php
// Carregar funções de ícones
require_once __DIR__ . '/icons.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="icon" type="image/x-icon" href="/public/images/favicon.ico">
    <?= $additionalCSS ?? '' ?>
</head>
<body>
    <div class="wrapper">
        <?php if (isLoggedIn()): ?>
            <!-- HEADER FIXO NO TOPO -->
            <header class="header">
                <div class="header-content">
                    <a href="/dashboard" class="logo">
                        <?= APP_NAME ?>
                    </a>

                    <div class="user-info">
                        <?php $user = getCurrentUser(); ?>

                        <!-- Theme Toggle -->
                        <button class="theme-toggle" id="themeToggle" title="Alternar tema">
                            <span class="theme-icon">🌙</span>
                        </button>

                        <!-- User Menu -->
                        <div class="user-menu">
                            <button class="user-menu-toggle" id="userMenuToggle">
                                <span class="user-icon">👤</span>
                                <span class="user-name"><?= htmlspecialchars($user['nome']) ?></span>
                            </button>

                            <div class="user-dropdown" id="userDropdown">
                                <div class="user-dropdown-header">
                                    <div class="user-dropdown-name"><?= htmlspecialchars($user['nome']) ?></div>
                                    <div class="user-dropdown-role"><?= ucfirst($user['perfil']) ?></div>
                                </div>
                                <ul class="user-dropdown-menu">
                                    <li class="user-dropdown-item">
                                        <a href="/usuarios/perfil" class="user-dropdown-link">
                                            <span>👤</span>
                                            <span>Meu Perfil</span>
                                        </a>
                                    </li>
                                    <?php if ($user['perfil'] === 'administrador'): ?>
                                    <li class="user-dropdown-item">
                                        <a href="/configuracoes" class="user-dropdown-link">
                                            <span>⚙️</span>
                                            <span>Configurações</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li class="user-dropdown-item">
                                        <a href="/login/logout" class="user-dropdown-link logout">
                                            <span>🚪</span>
                                            <span>Sair</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- SIDEBAR LATERAL FIXA -->
            <aside class="sidebar">
                <ul class="sidebar-menu">
                    <li class="sidebar-item">
                        <a href="/dashboard" class="sidebar-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>" data-tooltip="Dashboard">
                            <span class="sidebar-icon">📊</span>
                        </a>
                    </li>

                    <?php if (hasPermission('entrada_estoque')): ?>
                    <li class="sidebar-item">
                        <a href="/insumos" class="sidebar-link <?= $activeMenu === 'insumos' ? 'active' : '' ?>" data-tooltip="Insumos">
                            <span class="sidebar-icon">📦</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/entradas" class="sidebar-link <?= $activeMenu === 'entradas' ? 'active' : '' ?>" data-tooltip="Entradas">
                            <span class="sidebar-icon">📥</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (hasPermission('gestao_fornecedores')): ?>
                    <li class="sidebar-item">
                        <a href="/fornecedores" class="sidebar-link <?= $activeMenu === 'fornecedores' ? 'active' : '' ?>" data-tooltip="Fornecedores">
                            <span class="sidebar-icon">🏢</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (hasPermission('registro_producao')): ?>
                    <li class="sidebar-item">
                        <a href="/receitas" class="sidebar-link <?= $activeMenu === 'receitas' ? 'active' : '' ?>" data-tooltip="Receitas">
                            <span class="sidebar-icon">📝</span>
                        </a>
                    </li>
                    <?php if (getCurrentUser()['perfil'] === 'administrador'): ?>
                    <li class="sidebar-item">
                        <a href="/import" class="sidebar-link <?= $activeMenu === 'import' ? 'active' : '' ?>" data-tooltip="Importar Receitas">
                            <span class="sidebar-icon">📥</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="sidebar-item">
                        <a href="/producao" class="sidebar-link <?= $activeMenu === 'producao' ? 'active' : '' ?>" data-tooltip="Produção">
                            <span class="sidebar-icon">🍺</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/envase" class="sidebar-link <?= $activeMenu === 'envase' ? 'active' : '' ?>" data-tooltip="Envase">
                            <span class="sidebar-icon">🛢️</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/saidabarril" class="sidebar-link <?= $activeMenu === 'saidabarril' ? 'active' : '' ?>" data-tooltip="Saída de Barril">
                            <span class="sidebar-icon">📤</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/estoque" class="sidebar-link <?= $activeMenu === 'estoque' ? 'active' : '' ?>" data-tooltip="Estoque">
                            <span class="sidebar-icon">🏭</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/produtos" class="sidebar-link <?= $activeMenu === 'produtos' ? 'active' : '' ?>" data-tooltip="Produtos">
                            <span class="sidebar-icon">🍻</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/camarafria" class="sidebar-link <?= $activeMenu === 'camarafria' ? 'active' : '' ?>" data-tooltip="Câmara Fria">
                            <span class="sidebar-icon">❄️</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (hasPermission('relatorios')): ?>
                    <li class="sidebar-item">
                        <a href="/relatorios" class="sidebar-link <?= $activeMenu === 'relatorios' ? 'active' : '' ?>" data-tooltip="Relatórios">
                            <span class="sidebar-icon">📈</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($user['perfil'] === 'administrador'): ?>
                    <li class="sidebar-item">
                        <a href="/usuarios" class="sidebar-link <?= $activeMenu === 'usuarios' ? 'active' : '' ?>" data-tooltip="Usuários">
                            <span class="sidebar-icon">👥</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/configuracoes" class="sidebar-link <?= $activeMenu === 'configuracoes' ? 'active' : '' ?>" data-tooltip="Configurações">
                            <span class="sidebar-icon">⚙️</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </aside>
        <?php endif; ?>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="main-content">
            <div class="container">
                <?php
                // Exibir mensagens flash
                foreach (['success', 'error', 'warning', 'info'] as $type) {
                    $message = getFlashMessage($type);
                    if ($message) {
                        echo "<div class='alert alert-{$type}'>{$message}</div>";
                    }
                }
                ?>