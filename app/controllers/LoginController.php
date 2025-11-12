<?php
require_once 'BaseController.php';

class LoginController extends BaseController {

    /**
     * Exibe formulário de login
     */
    public function index() {
        // Se já estiver logado, redirecionar para dashboard
        if (isLoggedIn()) {
            redirect('/dashboard');
        }

        $this->view('auth/login', [
            'csrfToken' => generateCSRFToken()
        ]);
    }

    /**
     * Processa login
     */
    public function authenticate() {
        if (!$this->isPost()) {
            redirect('/login');
        }

        // Verificar token CSRF
        $csrfToken = $this->getPost('csrf_token');
        if (!verifyCSRFToken($csrfToken)) {
            setFlashMessage('error', 'Token de segurança inválido');
            redirect('/login');
        }

        $email = $this->getPost('email');
        $password = $this->getPost('password');
        $remember = $this->getPost('remember');

        // Validação básica
        if (empty($email) || empty($password)) {
            setFlashMessage('error', 'Email e senha são obrigatórios');
            redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->authenticate($email, $password);

        if ($user) {
            // Verificar se usuário está ativo
            if (!$user['ativo']) {
                setFlashMessage('error', 'Usuário inativo. Contate o administrador.');
                redirect('/login');
            }

            // Criar sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_data'] = $user;

            // Log da atividade
            logActivity('login', "Usuário {$user['nome']} fez login");

            // Configurar cookie "lembrar-me" se solicitado
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                // TODO: Salvar token no banco para validação futura
            }

            // Redirecionar baseado no perfil
            $redirectUrl = $this->getRedirectUrl($user['perfil']);
            setFlashMessage('success', "Bem-vindo, {$user['nome']}!");
            redirect($redirectUrl);

        } else {
            // Log da tentativa de login inválida
            logActivity('login_failed', "Tentativa de login falhada para email: {$email}");

            setFlashMessage('error', 'Email ou senha inválidos');
            redirect('/login');
        }
    }

    /**
     * Logout do usuário
     */
    public function logout() {
        $user = getCurrentUser();

        if ($user) {
            logActivity('logout', "Usuário {$user['nome']} fez logout");
        }

        // Destruir sessão
        session_destroy();

        // Remover cookie "lembrar-me"
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }

        setFlashMessage('success', 'Logout realizado com sucesso');
        redirect('/login');
    }

    /**
     * Exibe formulário de recuperação de senha
     */
    public function forgotPassword() {
        $this->view('auth/forgot-password', [
            'csrfToken' => generateCSRFToken()
        ]);
    }

    /**
     * Processa solicitação de recuperação de senha
     */
    public function sendResetLink() {
        if (!$this->isPost()) {
            redirect('/login/forgot-password');
        }

        // Verificar token CSRF
        $csrfToken = $this->getPost('csrf_token');
        if (!verifyCSRFToken($csrfToken)) {
            setFlashMessage('error', 'Token de segurança inválido');
            redirect('/login/forgot-password');
        }

        $email = $this->getPost('email');

        if (empty($email) || !isValidEmail($email)) {
            setFlashMessage('error', 'Email inválido');
            redirect('/login/forgot-password');
        }

        $userModel = new User();
        $user = $userModel->whereFirst('email', $email);

        if ($user) {
            // TODO: Implementar envio de email com link de reset
            // Por enquanto, apenas simular o processo
            logActivity('password_reset_requested', "Solicitação de reset de senha para: {$email}");
            setFlashMessage('success', 'Link de recuperação enviado para seu email');
        } else {
            // Não revelar se email existe ou não
            setFlashMessage('success', 'Se o email existir, você receberá um link de recuperação');
        }

        redirect('/login');
    }

    /**
     * Exibe formulário de reset de senha
     */
    public function resetPassword() {
        $token = $this->getGet('token');

        if (empty($token)) {
            setFlashMessage('error', 'Token inválido');
            redirect('/login');
        }

        // TODO: Validar token no banco de dados

        $this->view('auth/reset-password', [
            'token' => $token,
            'csrfToken' => generateCSRFToken()
        ]);
    }

    /**
     * Processa reset de senha
     */
    public function updatePassword() {
        if (!$this->isPost()) {
            redirect('/login');
        }

        // Verificar token CSRF
        $csrfToken = $this->getPost('csrf_token');
        if (!verifyCSRFToken($csrfToken)) {
            setFlashMessage('error', 'Token de segurança inválido');
            $token = $this->getPost('token');
            redirect("/login/reset-password?token={$token}");
        }

        $token = $this->getPost('token');
        $password = $this->getPost('password');
        $confirmPassword = $this->getPost('confirm_password');

        // Validações
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            setFlashMessage('error', 'Todos os campos são obrigatórios');
            redirect("/login/reset-password?token={$token}");
        }

        if ($password !== $confirmPassword) {
            setFlashMessage('error', 'Senhas não conferem');
            redirect("/login/reset-password?token={$token}");
        }

        if (strlen($password) < 6) {
            setFlashMessage('error', 'Senha deve ter pelo menos 6 caracteres');
            redirect("/login/reset-password?token={$token}");
        }

        // TODO: Validar token e atualizar senha
        // Por enquanto, apenas simular o processo

        logActivity('password_reset_completed', "Senha alterada via reset");
        setFlashMessage('success', 'Senha alterada com sucesso. Faça login com sua nova senha.');
        redirect('/login');
    }

    /**
     * Determina URL de redirecionamento baseada no perfil
     */
    private function getRedirectUrl($perfil) {
        switch ($perfil) {
            case 'administrador':
                return '/dashboard';
            case 'producao':
                return '/producao';
            case 'comprador':
                return '/entradas';
            case 'consulta':
                return '/relatorios';
            default:
                return '/dashboard';
        }
    }
}
?>