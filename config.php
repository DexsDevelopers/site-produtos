<?php
// config.php (VERSÃO COM BANCO DE DADOS RESTAURADA)

// Inicia a sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- INFORMAÇÕES DE CONEXÃO COM O BANCO DE DADOS ---
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
}
catch (PDOException $e) {
    die("Erro de conexão com o banco de dados. Verifique as configurações.");
}

// --- SISTEMA DE ARMAZENAMENTO PARA CHAVE PIX ---
require_once __DIR__ . '/includes/file_storage.php';
$fileStorage = new FileStorage();

// --- FUNÇÕES GLOBAIS ---
function formatarPreco($preco) {
    if (!is_numeric($preco)) return 'R$ 0,00';
    return 'R$ ' . number_format((float)$preco, 2, ',', '.');
}

function formatarPrecoPagBank($preco) {
    if (!is_numeric($preco)) return '0.00';
    return number_format((float)$preco, 2, '.', '');
}

// --- FUNÇÕES DE SEGURANÇA ---
function sanitizarEntrada($dados) {
    if (is_array($dados)) return array_map('sanitizarEntrada', $dados);
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}

function validarEmail($email) { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
function validarSenha($senha) { return strlen($senha) >= 6; }

function gerarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

// --- CONFIGURAÇÕES BÁSICAS ---
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
}

// --- CONTADOR DE VISITAS ROBUSTO (Unique IP + Device Detect) ---
try {
    // 1. Garante que a tabela e as colunas existem
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_visitas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        data_visita DATE NOT NULL,
        hora_visita TIME NOT NULL,
        pagina_visitada VARCHAR(255) DEFAULT 'home',
        user_agent TEXT,
        dispositivo ENUM('Mobile', 'Desktop') DEFAULT 'Desktop',
        INDEX (data_visita),
        INDEX (ip_address)
    )");

    try {
        $pdo->exec("ALTER TABLE site_visitas ADD COLUMN dispositivo ENUM('Mobile', 'Desktop') DEFAULT 'Desktop'");
    } catch (PDOException $e) {}

    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $ip = $_SERVER['REMOTE_ADDR'];
    $hoje = date('Y-m-d');

    // Detecção simplificada de Mobile
    $is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $user_agent);
    $device = $is_mobile ? 'Mobile' : 'Desktop';

    // 2. Verifica se este IP já visitou HOJE (Check no DB para IP único real)
    $stmt_check = $pdo->prepare("SELECT id FROM site_visitas WHERE ip_address = ? AND data_visita = ? LIMIT 1");
    $stmt_check->execute([$ip, $hoje]);
    $ja_visitou = $stmt_check->fetch();

    if (!$ja_visitou && !isset($_COOKIE['vst_track'])) {
        // Registra a visita
        $stmt_ins = $pdo->prepare("INSERT INTO site_visitas (ip_address, data_visita, hora_visita, pagina_visitada, user_agent, dispositivo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_ins->execute([
            $ip,
            $hoje,
            date('H:i:s'),
            $_SERVER['REQUEST_URI'] ?? 'home',
            $user_agent,
            $device
        ]);

        setcookie('vst_track', 'active', time() + 86400, "/");
    }
} catch (Exception $e) {}

?>
