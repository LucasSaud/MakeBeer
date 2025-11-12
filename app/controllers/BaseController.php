<?php

class BaseController {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Renderiza uma view
     */
    protected function view($view, $data = []) {
        // Extrai as variáveis para serem usadas na view
        extract($data);

        // Caminho completo da view
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        // Verifica se a view existe
        if (!file_exists($viewPath)) {
            throw new Exception("View não encontrada: {$viewPath}");
        }

        // Inclui a view
        require_once $viewPath;
    }

    /**
     * Verifica se a requisição é POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Verifica se a requisição é GET
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Obtém dados do formulário POST
     */
    protected function getPost($key, $default = '') {
        if (!isset($_POST[$key])) {
            return $default;
        }
        
        // Se for um array, retorna o array diretamente
        if (is_array($_POST[$key])) {
            return $_POST[$key];
        }
        
        // Se for uma string, aplica trim
        return trim($_POST[$key]);
    }

    /**
     * Obtém dados da URL (GET)
     */
    protected function getGet($key, $default = '') {
        if (!isset($_GET[$key])) {
            return $default;
        }
        
        // Se for um array, retorna o array diretamente
        if (is_array($_GET[$key])) {
            return $_GET[$key];
        }
        
        // Se for uma string, aplica trim
        return trim($_GET[$key]);
    }

    /**
     * Redireciona para uma URL
     */
    protected function redirect($url) {
        header("Location: {$url}");
        exit();
    }

    /**
     * Define uma mensagem flash
     */
    protected function setFlashMessage($type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Obtém e limpa a mensagem flash
     */
    protected function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }

    /**
     * Verifica se usuário está logado
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Obtém usuário logado
     */
    protected function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'nome' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email']
            ];
        }
        return null;
    }

    /**
     * Requer autenticação
     */
    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', 'Você precisa estar logado para acessar esta página.');
            $this->redirect('/login');
        }
    }

    /**
     * Requer permissão específica
     */
    protected function requirePermission($permission) {
        $user = $this->getCurrentUser();
        if (!$user || !$this->userHasPermission($user['id'], $permission)) {
            $this->setFlashMessage('error', 'Você não tem permissão para acessar esta página.');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Verifica se usuário tem permissão
     */
    protected function userHasPermission($userId, $permission) {
        // Implementar lógica de permissões conforme necessário
        return true;
    }

    /**
     * Log de atividade
     */
    protected function logActivity($action, $details = '') {
        logActivity($action, $details);
    }
}
?>