<?php
// config.php (GERENCIAMENTO DE ACESSOS + ENDERECO)

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

// --- BANCO DE DADOS (MIGRAÇÕES E TRACKING) ---
try {
    // 1. Tabela de Visitas
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

    // 2. Colunas Extras (Migrações Rápidas)
    $migracoes = [
        "ALTER TABLE site_visitas ADD COLUMN dispositivo ENUM('Mobile', 'Desktop') DEFAULT 'Desktop'",
        "ALTER TABLE produtos ADD COLUMN frete_gratis TINYINT(1) DEFAULT 0",
        // Colunas de Endereço em Pedidos
        "ALTER TABLE pedidos ADD COLUMN whatsapp VARCHAR(20)",
        "ALTER TABLE pedidos ADD COLUMN cep VARCHAR(10)",
        "ALTER TABLE pedidos ADD COLUMN endereco VARCHAR(255)",
        "ALTER TABLE pedidos ADD COLUMN numero VARCHAR(20)",
        "ALTER TABLE pedidos ADD COLUMN complemento VARCHAR(100)",
        "ALTER TABLE pedidos ADD COLUMN bairro VARCHAR(100)",
        "ALTER TABLE pedidos ADD COLUMN cidade VARCHAR(100)",
        "ALTER TABLE pedidos ADD COLUMN estado VARCHAR(2)",
        // Colunas de Endereço em Usuários
        "ALTER TABLE usuarios ADD COLUMN whatsapp VARCHAR(20)",
        "ALTER TABLE usuarios ADD COLUMN cep VARCHAR(10)",
        "ALTER TABLE usuarios ADD COLUMN endereco VARCHAR(255)",
        "ALTER TABLE usuarios ADD COLUMN numero VARCHAR(20)",
        "ALTER TABLE usuarios ADD COLUMN complemento VARCHAR(100)",
        "ALTER TABLE usuarios ADD COLUMN bairro VARCHAR(100)",
        "ALTER TABLE usuarios ADD COLUMN cidade VARCHAR(100)",
        "ALTER TABLE usuarios ADD COLUMN estado VARCHAR(2)"
    ];

    foreach ($migracoes as $sql) {
        try { $pdo->exec($sql); } catch (Exception $e) {}
    }

    // 3. Tracking de Visitas
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $ip = $_SERVER['REMOTE_ADDR'];
    $hoje = date('Y-m-d');
    $mobile_regex = '/AppleWebKit.*Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|webOS|Kindle|Silk-Accelerated|(hpw|hur|juc)OS|Fennec|Minimo|Gobrowser|UCBrowser|Blazer|Tizen|MiuiBrowser|SamsungBrowser/i';
    $device = preg_match($mobile_regex, $user_agent) ? 'Mobile' : 'Desktop';

    $stmt_check = $pdo->prepare("SELECT id FROM site_visitas WHERE ip_address = ? AND data_visita = ? LIMIT 1");
    $stmt_check->execute([$ip, $hoje]);
    if (!$stmt_check->fetch()) {
        $stmt_ins = $pdo->prepare("INSERT INTO site_visitas (ip_address, data_visita, hora_visita, pagina_visitada, user_agent, dispositivo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_ins->execute([$ip, $hoje, date('H:i:s'), $_SERVER['REQUEST_URI'] ?? 'home', $user_agent, $device]);
        setcookie('vst_track', 'active', time() + 86400, "/");
    }
} catch (Exception $e) {}
?>
