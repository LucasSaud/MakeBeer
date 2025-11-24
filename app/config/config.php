<?php
// Configurações gerais do sistema
define('APP_NAME', 'MakeBeer v1.0');
define('APP_VERSION', '1.0.0');

// Configurações de ambiente
define('ENVIRONMENT', 'development'); // development, production

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de segurança
define('HASH_ALGORITHM', 'sha256');
define('SESSION_LIFETIME', 3600); // 1 hora

// Configurações de upload
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'xlsx', 'xls']);
define('UPLOAD_PATH', 'storage/uploads/');

// Configurações de paginação
define('DEFAULT_PAGE_SIZE', 20);

// Configurações de email (para futuras implementações)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');

// Configurações de alertas
define('ALERT_ESTOQUE_MINIMO_DIAS', 7); // Alertar quando restam X dias para atingir estoque mínimo
define('ALERT_VALIDADE_DIAS', 30); // Alertar quando restam X dias para vencimento

// Configurações de relatórios
define('RELATORIO_PERIODO_PADRAO', 30); // dias

// Mostrar erros apenas em desenvolvimento
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>