<?php
// Funções auxiliares do sistema

/**
 * Sanitiza input do usuário
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES);
}

/**
 * Valida se email é válido
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Gera hash seguro para senhas
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifica senha com hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Formata data para exibição
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Formata data e hora para exibição
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    return date($format, strtotime($datetime));
}

/**
 * Formata valor monetário
 */
function formatMoney($value) {
    if ($value === null || $value === '') {
        $value = 0;
    }
    return 'R$ ' . number_format(floatval($value), 2, ',', '.');
}

/**
 * Formata peso/quantidade
 */

/**
 * Redireciona para uma URL
 */
function redirect($url) {
    // Se a URL começar com /, adicionar o subdiretório automaticamente
    if (substr($url, 0, 1) === '/') {
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $url = $scriptName . $url;
        }
    }
    header("Location: $url");
    exit;
}

/**
 * Define uma mensagem flash na sessão
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Obtém e remove mensagem flash da sessão
 */
function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Gera token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Calcula dias até uma data
 */
function daysUntil($date) {
    // Verificar se a data é válida
    if (!$date || empty(trim($date))) {
        return null; // ou retorne 0, false, ou outro valor padrão conforme sua necessidade
    }
    
    try {
        $now = new DateTime();
        $target = new DateTime($date);
        $diff = $now->diff($target);
        return $diff->invert ? -$diff->days : $diff->days;
    } catch (Exception $e) {
        // Log do erro se necessário
        logActivity('date_calculation_error', "Erro ao calcular dias até a data: $date - " . $e->getMessage());
        return null;
    }
}

/**
 * Verifica se usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Obtém dados do usuário logado
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return $_SESSION['user_data'] ?? null;
}

/**
 * Verifica permissão do usuário
 */
function hasPermission($permission) {
    $user = getCurrentUser();
    if (!$user) return false;

    // Administrador tem todas as permissões
    if ($user['perfil'] === 'administrador') return true;

    // Verificar permissões específicas baseadas no perfil
    $permissions = [
        'producao' => ['entrada_estoque', 'registro_producao', 'consulta_estoque'],
        'consulta' => ['consulta_estoque', 'relatorios'],
        'comprador' => ['entrada_estoque', 'gestao_fornecedores', 'consulta_estoque']
    ];

    return in_array($permission, $permissions[$user['perfil']] ?? []);
}

/**
 * Gera código único para lotes
 */
function generateLoteCode($prefix = 'LT') {
    return $prefix . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Converte unidades de medida
 */
function convertUnit($value, $fromUnit, $toUnit) {
    // Conversões básicas - expandir conforme necessário
    $conversions = [
        'kg_to_g' => 1000,
        'g_to_kg' => 0.001,
        'l_to_ml' => 1000,
        'ml_to_l' => 0.001
    ];

    $conversionKey = strtolower($fromUnit) . '_to_' . strtolower($toUnit);
    return isset($conversions[$conversionKey]) ? $value * $conversions[$conversionKey] : $value;
}

/**
 * Log de atividades do sistema
 */
function logActivity($action, $details = '') {
    $user = getCurrentUser();
    $userId = $user ? $user['id'] : null;

    $logEntry = [
        'user_id' => $userId,
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Salvar no banco ou arquivo de log
    $logFile = 'storage/logs/activity_' . date('Y-m-d') . '.log';
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

// Corrija a função formatQuantity para lidar com valores nulos
function formatQuantity($quantity, $unit = 'un') {
    if ($quantity === null || $quantity === '') {
        $quantity = 0;
    }
    
    $quantity = floatval($quantity);
    
    switch ($unit) {
        case 'kg':
        case 'l':
        case 'L':
            return number_format($quantity, 3, ',', '.') . ' ' . $unit;
        case 'g':
        case 'ml':
            return number_format($quantity, 0, ',', '.') . ' ' . $unit;
        case 'un':
        default:
            return number_format($quantity, 0, ',', '.') . ' ' . $unit;
    }
}

/**
 * Valida upload de arquivo
 */
function validateUpload($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Erro no upload do arquivo'];
    }

    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['success' => false, 'message' => 'Arquivo muito grande'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, UPLOAD_ALLOWED_TYPES)) {
        return ['success' => false, 'message' => 'Tipo de arquivo não permitido'];
    }

    return ['success' => true];
}
?>