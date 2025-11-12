<?php
$pageTitle = 'Redefinir Senha - ' . APP_NAME;
$activeMenu = 'login';
// Não incluir o header padrão pois estamos criando uma página especial sem sidebar
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
                <a href="/dashboard" class="logo">
                    <?= APP_NAME ?>
                </a>

                <div class="user-info">
                    <!-- Theme Toggle -->
                    <button class="theme-toggle" id="themeToggle" title="Alternar tema">
                        <span class="theme-icon">🌙</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="main-content" style="margin-left: 0; width: 100%;">
            <div class="container">
                <div class="reset-password-container">
                    <div class="row justify-content-center">
                        <div class="col col-4">
                            <div class="card">
                                <div class="card-header text-center">
                                    <h2>Redefinir Senha</h2>
                                    <p class="text-muted">Digite sua nova senha</p>
                                </div>
                                
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        Digite sua nova senha abaixo. A senha deve ter pelo menos 6 caracteres.
                                    </div>

                                    <form method="POST" action="/login/update-password">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="token" value="<?= $token ?>">

                                        <div class="form-group">
                                            <label for="password" class="form-label">Nova Senha</label>
                                            <input
                                                type="password"
                                                id="password"
                                                name="password"
                                                class="form-control"
                                                placeholder="Digite sua nova senha"
                                                required
                                                minlength="6"
                                            >
                                            <div class="form-text">
                                                A senha deve ter pelo menos 6 caracteres
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                            <input
                                                type="password"
                                                id="confirm_password"
                                                name="confirm_password"
                                                class="form-control"
                                                placeholder="Confirme sua nova senha"
                                                required
                                                minlength="6"
                                            >
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100">
                                            Redefinir Senha
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="card-footer text-center">
                                    <a href="/login" class="back-to-login">
                                        ← Voltar para o login
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    // ============================================================================
    // THEME TOGGLE - Alternar entre tema claro e escuro
    // ============================================================================
    document.addEventListener('DOMContentLoaded', function() {
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

        // Focar no campo senha ao carregar a página
        document.getElementById('password').focus();

        // Validação do formulário
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!password || !confirmPassword) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres.');
                return false;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('As senhas não conferem. Por favor, verifique.');
                return false;
            }
        });
    });
    </script>
</body>
</html>