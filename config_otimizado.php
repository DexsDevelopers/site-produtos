<?php
// config_otimizado.php - Configuração Otimizada para Performance

// Inicia a sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CONFIGURAÇÕES DE PERFORMANCE ---
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 15);
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');
ini_set('max_input_vars', 1000);

// --- CONFIGURAÇÕES DE CACHE ---
ini_set('opcache.enable', 1);
ini_set('opcache.memory_consumption', 64);
ini_set('opcache.max_accelerated_files', 2000);

// --- CONFIGURAÇÕES DO BANCO DE DADOS ---
$host = 'localhost';
$dbname = 'u853242961_lojahelmer';
$user = 'u853242961_user2';
$password = 'Lucastav8012@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    error_log("Erro de conexão com banco de dados: " . $e->getMessage());
    die("Erro: Não foi possível conectar ao banco de dados.");
}

// --- FUNÇÕES GLOBAIS OTIMIZADAS ---
function formatarPreco($preco) {
    if (!is_numeric($preco)) { return 'R$ 0,00'; }
    return 'R$ ' . number_format((float)$preco, 2, ',', '.');
}

function formatarPrecoPagBank($preco) {
    if (!is_numeric($preco)) { return '0.00'; }
    return number_format((float)$preco, 2, '.', '');
}

// --- FUNÇÕES DE SEGURANÇA OTIMIZADAS ---
function sanitizarEntrada($dados) {
    if (is_array($dados)) {
        return array_map('sanitizarEntrada', $dados);
    }
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validarSenha($senha) {
    return strlen($senha) >= 6;
}

function gerarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirecionarComMensagem($url, $tipo, $mensagem) {
    $_SESSION[$tipo . '_message'] = $mensagem;
    header("Location: $url");
    exit();
}

function exibirMensagem($tipo) {
    if (isset($_SESSION[$tipo . '_message'])) {
        $mensagem = $_SESSION[$tipo . '_message'];
        unset($_SESSION[$tipo . '_message']);
        return $mensagem;
    }
    return null;
}

// --- CONFIGURAÇÕES DE SEGURANÇA OTIMIZADAS ---
ini_set('session.cookie_httponly', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.use_strict_mode', 1);

// Headers de segurança otimizados
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Cache-Control: public, max-age=3600');
}

// --- CONFIGURAÇÕES DE CACHE SIMPLES ---
if (!defined('CACHE_DIR')) {
    define('CACHE_DIR', __DIR__ . '/cache/');
}

if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

// --- CONFIGURAÇÕES DE UPLOAD OTIMIZADAS ---
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/assets/uploads/');
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// --- CONFIGURAÇÕES DA LOJA ---
define('LOJA_NOME', 'Minha Loja - O Mercado é dos Tubarões');
define('LOJA_DESCRICAO', 'Descubra produtos incríveis na nossa loja online. Qualidade, preços competitivos e entrega rápida.');
define('LOJA_EMAIL', 'contato@minhaloja.com');
define('LOJA_TELEFONE', '(11) 99999-9999');

// --- CONFIGURAÇÕES DE PERFORMANCE ---
define('ENABLE_CACHE', true);
define('CACHE_DURATION', 3600); // 1 hora
define('LAZY_LOADING', true);
define('MINIFY_CSS', true);
define('MINIFY_JS', true);

// --- FUNÇÕES DE CACHE SIMPLES ---
function getCache($key) {
    if (!ENABLE_CACHE) return false;
    
    $file = CACHE_DIR . md5($key) . '.cache';
    if (file_exists($file) && (time() - filemtime($file)) < CACHE_DURATION) {
        return unserialize(file_get_contents($file));
    }
    return false;
}

function setCache($key, $data) {
    if (!ENABLE_CACHE) return false;
    
    $file = CACHE_DIR . md5($key) . '.cache';
    return file_put_contents($file, serialize($data));
}

// --- FUNÇÕES DE OTIMIZAÇÃO ---
function otimizarImagem($caminho, $largura = 800, $qualidade = 80) {
    if (!file_exists($caminho)) return false;
    
    $info = getimagesize($caminho);
    if (!$info) return false;
    
    $tipo = $info[2];
    $largura_original = $info[0];
    $altura_original = $info[1];
    
    if ($largura_original <= $largura) return $caminho;
    
    $altura = ($altura_original * $largura) / $largura_original;
    
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $imagem = imagecreatefromjpeg($caminho);
            break;
        case IMAGETYPE_PNG:
            $imagem = imagecreatefrompng($caminho);
            break;
        case IMAGETYPE_WEBP:
            $imagem = imagecreatefromwebp($caminho);
            break;
        default:
            return $caminho;
    }
    
    $nova_imagem = imagecreatetruecolor($largura, $altura);
    imagecopyresampled($nova_imagem, $imagem, 0, 0, 0, 0, $largura, $altura, $largura_original, $altura_original);
    
    $novo_caminho = str_replace('.', '_otimizada.', $caminho);
    
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($nova_imagem, $novo_caminho, $qualidade);
            break;
        case IMAGETYPE_PNG:
            imagepng($nova_imagem, $novo_caminho, 9);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($nova_imagem, $novo_caminho, $qualidade);
            break;
    }
    
    imagedestroy($imagem);
    imagedestroy($nova_imagem);
    
    return $novo_caminho;
}

// --- CONFIGURAÇÕES DE DEBUG ---
if (defined('DEBUG') && DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// --- CONFIGURAÇÕES DE TIMEZONE ---
date_default_timezone_set('America/Sao_Paulo');

// --- CONFIGURAÇÕES DE IDIOMA ---
setlocale(LC_ALL, 'pt_BR.UTF-8', 'pt_BR', 'portuguese');

// --- COMPRESSÃO GZIP ---
if (!ob_get_level() && extension_loaded('zlib')) {
    ob_start('ob_gzhandler');
}

echo "<!-- Configuração otimizada carregada! -->\n";
?>
