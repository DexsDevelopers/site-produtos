<?php
// config_final.php - Configuração Final Otimizada

// Inicia a sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// --- FUNÇÕES GLOBAIS ---
function formatarPreco($preco) {
    if (!is_numeric($preco)) { return 'R$ 0,00'; }
    return 'R$ ' . number_format((float)$preco, 2, ',', '.');
}

function formatarPrecoPagBank($preco) {
    if (!is_numeric($preco)) { return '0.00'; }
    return number_format((float)$preco, 2, '.', '');
}

// --- FUNÇÕES DE SEGURANÇA ---
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

// --- CONFIGURAÇÕES DE SEGURANÇA ---
ini_set('session.cookie_httponly', 1);
// Só ativa cookie_secure em HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.use_strict_mode', 1);

// Headers de segurança
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// --- CONFIGURAÇÕES DE PERFORMANCE ---
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 30);
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');

// --- CONFIGURAÇÕES DE CACHE ---
if (!defined('CACHE_DIR')) {
    define('CACHE_DIR', __DIR__ . '/cache/');
}

if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

// --- CONFIGURAÇÕES DE UPLOAD ---
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/assets/uploads/');
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// --- CONFIGURAÇÕES DE LOG ---
if (!defined('LOG_DIR')) {
    define('LOG_DIR', __DIR__ . '/logs/');
}

if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}

// --- CONFIGURAÇÕES DA LOJA ---
define('LOJA_NOME', 'Minha Loja - O Mercado é dos Tubarões');
define('LOJA_DESCRICAO', 'Descubra produtos incríveis na nossa loja online. Qualidade, preços competitivos e entrega rápida.');
define('LOJA_EMAIL', 'contato@minhaloja.com');
define('LOJA_TELEFONE', '(11) 99999-9999');
define('LOJA_ENDERECO', 'São Paulo, SP - Brasil');

// --- CONFIGURAÇÕES DE PAGAMENTO ---
define('PAGBANK_ACCESS_TOKEN', 'SEU_ACCESS_TOKEN_AQUI');
define('PAGBANK_ENVIRONMENT', 'sandbox'); // ou 'production'

// --- CONFIGURAÇÕES DE EMAIL ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'seu_email@gmail.com');
define('SMTP_PASSWORD', 'sua_senha_app');

// --- CONFIGURAÇÕES DE REDE SOCIAL ---
define('INSTAGRAM_URL', 'https://www.instagram.com/minhaloja');
define('YOUTUBE_URL', 'https://www.youtube.com/minhaloja');
define('FACEBOOK_URL', 'https://www.facebook.com/minhaloja');

// --- CONFIGURAÇÕES DE SEO ---
define('SITE_URL', 'https://seudominio.com');
define('SITE_LOGO', 'https://i.ibb.co/xq66KBdr/Design-sem-nome-4.png');

// --- FUNÇÕES DE UTILIDADE ---
function gerarSlug($texto) {
    $texto = strtolower(trim($texto));
    $texto = preg_replace('/[^a-z0-9-]/', '-', $texto);
    $texto = preg_replace('/-+/', '-', $texto);
    return trim($texto, '-');
}

function formatarData($data, $formato = 'd/m/Y H:i') {
    return date($formato, strtotime($data));
}

function gerarCodigoProduto() {
    return 'PROD-' . strtoupper(uniqid());
}

function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11) return false;
    
    if (preg_match('/(\d)\1{10}/', $cpf)) return false;
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    
    return true;
}

function enviarEmail($para, $assunto, $mensagem, $de = null) {
    if (!$de) $de = LOJA_EMAIL;
    
    $headers = "From: $de\r\n";
    $headers .= "Reply-To: $de\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($para, $assunto, $mensagem, $headers);
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

echo "<!-- Configuração carregada com sucesso! -->\n";
?>
