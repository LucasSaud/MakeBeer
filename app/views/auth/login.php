<?php
$pageTitle = 'Login - ' . APP_NAME;
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
                <div class="login-container">
                    <div class="row justify-content-center">
                        <div class="col col-4">
                            <div class="card">
                                <div class="card-header text-center">
                                    <h2>Login</h2>
                                    <p class="text-muted">Acesse sua conta</p>
                                </div>
                                
                                <div class="card-body">
                                    <form method="POST" action="/login/authenticate">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                                        <div class="form-group">
                                            <label for="email" class="form-label">Email</label>
                                            <input
                                                type="email"
                                                id="email"
                                                name="email"
                                                class="form-control"
                                                placeholder="Digite seu email"
                                                required
                                                autocomplete="email"
                                                value="<?= $_POST['email'] ?? '' ?>"
                                            >
                                        </div>

                                        <div class="form-group">
                                            <label for="password" class="form-label">Senha</label>
                                            <input
                                                type="password"
                                                id="password"
                                                name="password"
                                                class="form-control"
                                                placeholder="Digite sua senha"
                                                required
                                                autocomplete="current-password"
                                            >
                                        </div>

                                        <div class="form-group">
                                            <div class="checkbox-group">
                                                <input
                                                    type="checkbox"
                                                    id="remember"
                                                    name="remember"
                                                    value="1"
                                                >
                                                <label for="remember">Lembrar-me</label>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100">
                                            Entrar
                                        </button>
                                    </form>

                                    <div class="mt-3 text-center">
                                        <a href="/login/forgot-password" class="forgot-password">
                                            Esqueceu sua senha?
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="card-footer text-center">
                                    <div class="demo-info">
                                        <strong>Dados para teste:</strong><br>
                                        Email: admin@atomos.com<br>
                                        Senha: admin123
                                    </div>
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

        // Focar no campo email ao carregar a página
        document.getElementById('email').focus();

        // Validação do formulário
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return false;
            }

            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('Por favor, digite um email válido.');
                return false;
            }
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    });
    </script>
</body>
</html>