<?php
// config_optimized.php - Configuração Otimizada para Performance
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CONFIGURAÇÕES DE PERFORMANCE ---
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 30);
ini_set('opcache.enable', 1);
ini_set('opcache.memory_consumption', 128);
ini_set('opcache.max_accelerated_files', 4000);
ini_set('opcache.revalidate_freq', 60);

// --- INFORMAÇÕES DE CONEXÃO COM O BANCO DE DADOS ---
$host = 'localhost';
$dbname = 'u853242961_lojahelmer';
$user = 'u853242961_user2';
$password = 'Lucastav8012@';

// --- CONEXÃO OTIMIZADA COM PDO ---
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_PERSISTENT => true, // Conexão persistente
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ]);
} catch (PDOException $e) {
    error_log("Erro de conexão: " . $e->getMessage());
    die("Erro de conexão com o banco de dados. Tente novamente mais tarde.");
}

// --- SISTEMA DE CACHE OTIMIZADO ---
class CacheOptimized {
    private $cache_dir;
    private $default_ttl;
    
    public function __construct($cache_dir = 'cache/', $default_ttl = 3600) {
        $this->cache_dir = $cache_dir;
        $this->default_ttl = $default_ttl;
        
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    public function get($key, $default = null) {
        $filename = $this->cache_dir . md5($key) . '.cache';
        
        if (file_exists($filename) && (time() - filemtime($filename)) < $this->default_ttl) {
            $data = file_get_contents($filename);
            return unserialize($data);
        }
        
        return $default;
    }
    
    public function set($key, $data, $ttl = null) {
        $filename = $this->cache_dir . md5($key) . '.cache';
        $ttl = $ttl ?: $this->default_ttl;
        
        $cache_data = [
            'data' => $data,
            'expires' => time() + $ttl
        ];
        
        return file_put_contents($filename, serialize($cache_data));
    }
    
    public function remember($key, $callback, $ttl = null) {
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $data = $callback();
        $this->set($key, $data, $ttl);
        
        return $data;
    }
    
    public function forget($key) {
        $filename = $this->cache_dir . md5($key) . '.cache';
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return false;
    }
    
    public function flush() {
        $files = glob($this->cache_dir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
}

// --- FUNÇÕES GLOBAIS OTIMIZADAS ---
function formatarPreco($preco) {
    if (!is_numeric($preco)) { 
        return 'R$ 0,00'; 
    }
    return 'R$ ' . number_format((float)$preco, 2, ',', '.');
}

function formatarPrecoPagBank($preco) {
    if (!is_numeric($preco)) { 
        return '0.00'; 
    }
    return number_format((float)$preco, 2, '.', '');
}

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

// --- FUNÇÕES DE CACHE OTIMIZADAS ---
function getCachedData($key, $callback, $ttl = 3600) {
    static $cache = null;
    
    if ($cache === null) {
        $cache = new CacheOptimized();
    }
    
    return $cache->remember($key, $callback, $ttl);
}

function clearCache() {
    static $cache = null;
    
    if ($cache === null) {
        $cache = new CacheOptimized();
    }
    
    return $cache->flush();
}

// --- FUNÇÕES DE PERFORMANCE ---
function optimizeImage($image_path, $width = 1200, $height = 1200, $quality = 85) {
    if (!file_exists($image_path)) {
        return $image_path;
    }
    
    $cache_dir = 'assets/cache/images/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    $path_info = pathinfo($image_path);
    $cache_filename = md5($image_path . $width . $height . $quality) . '.' . $path_info['extension'];
    $cache_path = $cache_dir . $cache_filename;
    
    if (file_exists($cache_path) && filemtime($cache_path) > filemtime($image_path)) {
        return $cache_path;
    }
    
    $image_info = getimagesize($image_path);
    if (!$image_info) {
        return $image_path;
    }
    
    $original_width = $image_info[0];
    $original_height = $image_info[1];
    $mime_type = $image_info['mime'];
    
    if ($original_width <= $width && $original_height <= $height) {
        copy($image_path, $cache_path);
        return $cache_path;
    }
    
    $ratio = min($width / $original_width, $height / $original_height);
    $new_width = intval($original_width * $ratio);
    $new_height = intval($original_height * $ratio);
    
    $source_image = null;
    switch ($mime_type) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($image_path);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($image_path);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($image_path);
            break;
        case 'image/webp':
            $source_image = imagecreatefromwebp($image_path);
            break;
    }
    
    if (!$source_image) {
        return $image_path;
    }
    
    $resized_image = imagecreatetruecolor($new_width, $new_height);
    
    if ($mime_type === 'image/png') {
        imagealphablending($resized_image, false);
        imagesavealpha($resized_image, true);
    }
    
    imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
    
    switch ($mime_type) {
        case 'image/jpeg':
            imagejpeg($resized_image, $cache_path, $quality);
            break;
        case 'image/png':
            imagepng($resized_image, $cache_path, intval((100 - $quality) / 10));
            break;
        case 'image/gif':
            imagegif($resized_image, $cache_path);
            break;
        case 'image/webp':
            imagewebp($resized_image, $cache_path, $quality);
            break;
    }
    
    imagedestroy($source_image);
    imagedestroy($resized_image);
    
    return $cache_path;
}

// --- HEADERS DE SEGURANÇA E PERFORMANCE ---
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Cache para recursos estáticos
    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
            $cache_time = 3600 * 24 * 7; // 7 dias
            header('Cache-Control: public, max-age=' . $cache_time);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
        }
    }
}

// --- INICIALIZAÇÃO DO SISTEMA DE CACHE ---
$cache = new CacheOptimized();

// --- LIMPEZA AUTOMÁTICA DE CACHE (executar periodicamente) ---
if (rand(1, 100) === 1) { // 1% de chance de limpar cache
    $cache->flush();
}
?>


