<?php
session_start();
require_once 'app/config/config.php';
require_once 'app/config/database.php';
require_once 'app/helpers/functions.php';

// Verificar e criar diretórios necessários
$requiredDirs = [
    'storage',
    'storage/uploads'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Autoloader simples para classes
spl_autoload_register(function ($class) {
    $paths = [
        'app/controllers/',
        'app/models/',
        'app/middleware/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// Roteamento simples
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = trim($path, '/');

// Remover a pasta base (Atomos) da URL se existir
$path = preg_replace('#^Atomos/?#', '', $path);

// Tratamento especial para rotas de login
if (strpos($path, 'login/') === 0) {
    $loginAction = substr($path, 6); // Remove 'login/' do início
    
    $loginController = new LoginController();
    switch ($loginAction) {
        case 'forgot-password':
            $loginController->forgotPassword();
            exit;
        case 'send-reset-link':
            $loginController->sendResetLink();
            exit;
        case 'reset-password':
            $loginController->resetPassword();
            exit;
        case 'update-password':
            $loginController->updatePassword();
            exit;
        case 'authenticate':
            $loginController->authenticate();
            exit;
        case 'logout':
            $loginController->logout();
            exit;
        case '':
        case 'index':
            $loginController->index();
            exit;
        default:
            http_response_code(404);
            echo "Página não encontrada";
            exit;
    }
}

// Rotas normais
$segments = explode('/', $path);
$controller = !empty($segments[0]) ? $segments[0] : 'home';
$action = !empty($segments[1]) ? $segments[1] : 'index';

// Verificar autenticação
$publicRoutes = ['login', 'home'];
if (!in_array($controller, $publicRoutes) && !isset($_SESSION['user_id'])) {
    redirect('/login');
}

// Carregar controller
$controllerClass = ucfirst($controller) . 'Controller';
if (class_exists($controllerClass)) {
    $controllerInstance = new $controllerClass();
    if (method_exists($controllerInstance, $action)) {
        $controllerInstance->$action();
    } else {
        http_response_code(404);
        echo "Ação não encontrada";
    }
} else {
    http_response_code(404);
    echo "Página não encontrada";
}
?>