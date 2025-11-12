            </div>
        </main>
    </div>

    <?php if (isLoggedIn()): ?>
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Todos os direitos reservados.</p>
    </footer>
    <?php endif; ?>

    <?= $additionalJS ?? '' ?>

    <?php if (isLoggedIn()): ?>
    <script>
    // ============================================================================
    // THEME TOGGLE - Alternar entre tema claro e escuro
    // ============================================================================
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

    // ============================================================================
    // USER MENU DROPDOWN
    // ============================================================================
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userDropdown = document.getElementById('userDropdown');

    // Toggle menu do usuário
    userMenuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('active');
    });

    // Fechar dropdown ao clicar fora
    document.addEventListener('click', (e) => {
        if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('active');
        }
    });

    // Fechar dropdown ao pressionar ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            userDropdown.classList.remove('active');
        }
    });
    </script>
    <?php endif; ?>
</body>
</html>
