<?php
$appEnv = strtolower((string) (getenv('APP_ENV') ?: 'development'));
$isProduction = ($appEnv === 'production');

error_reporting(E_ALL);
ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('log_errors', '1');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'soru_bankasi');

// Dynamically determine BASE_URL
$httpsEnabled = (
    (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
);
$protocol = $httpsEnabled ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_path = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
if ($script_path === '/' || $script_path === '\\') {
    $script_path = '';
}
define('BASE_URL', $protocol . $host . $script_path);
define('UPLOAD_PATH', __DIR__ . '/public/uploads');

// Configure session security before starting the session.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $httpsEnabled,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Load Secrets if present
if (file_exists(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
}

// Load Language System
require_once __DIR__ . '/core/Language.php';

// ── AI Content Moderation ────────────────────────────────────────────────────
// Priority: OpenAI → Gemini → Keyword Fallback (automatic, no config needed)
//
// ── OpenAI (Primary) ──────────────────────────────────────────────────────────
// Get your key at: https://platform.openai.com/api-keys
// Recommended model: gpt-4o-mini (fast + cheap) or gpt-3.5-turbo (free tier users)
defined('OPENAI_API_KEY') || define('OPENAI_API_KEY', 'YOUR_OPENAI_KEY_HERE'); // e.g. 'sk-proj-...'
defined('OPENAI_MODEL')   || define('OPENAI_MODEL',   'gpt-4o-mini');           // or 'gpt-3.5-turbo'
//
// ── Google Gemini (Secondary / Free Alternative) ──────────────────────────────
// Get your free key at: https://aistudio.google.com/app/apikey
defined('GEMINI_API_KEY') || define('GEMINI_API_KEY', 'YOUR_GEMINI_KEY_HERE');  // e.g. 'AIzaSy...'
//
// ── General Settings ──────────────────────────────────────────────────────────
defined('MODERATION_ENABLED') || define('MODERATION_ENABLED', true); // Set false to bypass all moderation
require_once __DIR__ . '/core/ModerationService.php';
