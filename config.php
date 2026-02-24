<?php
// config.php - Base Configuration & Security
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do Banco de Dados
$host = 'localhost';
$db = 'u789270650_loja';
$user = 'u789270650_loja';
$pass = 'U789270650_loja';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
}
catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Funções Utilitárias
function formatarPreco($valor)
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function setSecurityHeaders()
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
}

function salvarCarrinho($pdo)
{
    if (!isset($_SESSION['carrinho']))
        return;

    $user_id = $_SESSION['user_id'] ?? null;
    $sessao_id = session_id();
    $dados = json_encode($_SESSION['carrinho']);

    $valor_total = 0;
    foreach ($_SESSION['carrinho'] as $item) {
        $valor_total += ($item['preco'] * $item['quantidade']);
    }

    if (empty($_SESSION['carrinho'])) {
        $stmt = $pdo->prepare("DELETE FROM carrinhos_abandonados WHERE sessao_id = ? OR (usuario_id = ? AND usuario_id IS NOT NULL)");
        $stmt->execute([$sessao_id, $user_id]);
        return;
    }

    // Upsert
    $stmt = $pdo->prepare("SELECT id FROM carrinhos_abandonados WHERE sessao_id = ? OR (usuario_id = ? AND usuario_id IS NOT NULL) LIMIT 1");
    $stmt->execute([$sessao_id, $user_id]);
    $existente = $stmt->fetch();

    if ($existente) {
        $stmt = $pdo->prepare("UPDATE carrinhos_abandonados SET usuario_id = ?, dados_carrinho = ?, valor_total = ?, data_atualizacao = NOW() WHERE id = ?");
        $stmt->execute([$user_id, $dados, $valor_total, $existente['id']]);
    }
    else {
        $stmt = $pdo->prepare("INSERT INTO carrinhos_abandonados (usuario_id, sessao_id, dados_carrinho, valor_total) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $sessao_id, $dados, $valor_total]);
    }
}

// --- BANCO DE DADOS (MIGRAÇÕES E TRACKING) ---
try {
    // 1. Tabela de Visitas
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_visitas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45),
        data_visita DATE,
        hora_visita TIME,
        pagina_visitada VARCHAR(255),
        user_agent TEXT,
        dispositivo VARCHAR(50)
    )");

    // 2. Migrações de Colunas
    $migracoes = [
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
        "ALTER TABLE usuarios ADD COLUMN estado VARCHAR(2)",
        // Tabela de Carrinhos Abandonados
        "CREATE TABLE IF NOT EXISTS carrinhos_abandonados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT,
            sessao_id VARCHAR(255),
            dados_carrinho TEXT,
            valor_total DECIMAL(10,2),
            data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (usuario_id),
            INDEX (sessao_id)
        )"
    ];

    foreach ($migracoes as $sql) {
        try {
            $pdo->exec($sql);
        }
        catch (Exception $e) {
        }
    }

    // 3. Tracking de Visitas
    if (!isset($_COOKIE['vst_track'])) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
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
    }
}
catch (Exception $e) {
}
?>
