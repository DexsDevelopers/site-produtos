<?php
// config.php (DETECTOR DE MOBILE MELHORADO)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CONEXÃO ---
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
    die("Erro de conexão.");
}

require_once __DIR__ . '/includes/file_storage.php';
$fileStorage = new FileStorage();

// --- FUNÇÕES ---
function formatarPreco($preco) {
    if (!is_numeric($preco)) return 'R$ 0,00';
    return 'R$ ' . number_format((float)$preco, 2, ',', '.');
}

function formatarPrecoPagBank($preco) {
    if (!is_numeric($preco)) return '0.00';
    return number_format((float)$preco, 2, '.', '');
}

function sanitizarEntrada($dados) {
    if (is_array($dados)) return array_map('sanitizarEntrada', $dados);
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
}

// --- CONTADOR DE VISITAS (IP ÚNICO + MOBILE DETECT COMPLETO) ---
try {
    // 1. Garante que a tabela existe
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

    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $ip = $_SERVER['REMOTE_ADDR'];
    $hoje = date('Y-m-d');

    // REGEX MELHORADA (MOBILE, TABLET, ANDROID, IPHONE)
    $mobile_regex = '/AppleWebKit.*Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|webOS|Kindle|Silk-Accelerated|(hpw|hur|juc)OS|Fennec|Minimo|Gobrowser|UCBrowser|Blazer|Tizen|MiuiBrowser|SamsungBrowser/i';
    
    $is_mobile = preg_match($mobile_regex, $user_agent);
    $device = $is_mobile ? 'Mobile' : 'Desktop';

    // 2. Verifica se este IP já visitou HOJE (DEDUP REAL)
    $stmt_check = $pdo->prepare("SELECT id FROM site_visitas WHERE ip_address = ? AND data_visita = ? LIMIT 1");
    $stmt_check->execute([$ip, $hoje]);
    $ja_visitou = $stmt_check->fetch();

    if (!$ja_visitou) {
        $stmt_ins = $pdo->prepare("INSERT INTO site_visitas (ip_address, data_visita, hora_visita, pagina_visitada, user_agent, dispositivo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_ins->execute([
            $ip,
            $hoje,
            date('H:i:s'),
            $_SERVER['REQUEST_URI'] ?? 'home',
            $user_agent,
            $device
        ]);
        
        // Ativamos um sinal de rastreio para redundância
        setcookie('vst_track', 'active', time() + 86400, "/");
    }
} catch (Exception $e) {}

?>
