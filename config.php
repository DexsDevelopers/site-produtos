<?php
// config.php - Base Configuration & Security
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// RESTAURAÇÃO DE CREDENCIAIS
$host = "localhost";
$dbname = "u853242961_lojahelmer";
$user = "u853242961_user2";
$password = "Lucastav8012@";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (\PDOException $e) {
    die("Erro de conexão.");
}

if (file_exists(__DIR__ . "/includes/file_storage.php")) {
    require_once __DIR__ . "/includes/file_storage.php";
    $fileStorage = new FileStorage();
}

function formatarPreco($preco) {
    if (!is_numeric($preco)) return "R$ 0,00";
    return "R$ " . number_format((float)$preco, 2, ",", ".");
}

function formatarPrecoPagBank($preco) {
    if (!is_numeric($preco)) return "0.00";
    return number_format((float)$preco, 2, ".", "");
}

function sanitizarEntrada($dados) {
    if (is_array($dados)) return array_map("sanitizarEntrada", $dados);
    return htmlspecialchars(trim($dados), ENT_QUOTES, "UTF-8");
}

function salvarCarrinho($pdo) {
    if (!isset($_SESSION["carrinho"])) return;
    $user_id = $_SESSION["user_id"] ?? null;
    $sessao_id = session_id();
    $dados = json_encode($_SESSION["carrinho"]);
    $valor_total = 0;
    foreach ($_SESSION["carrinho"] as $item) {
        $valor_total += ($item["preco"] * $item["quantidade"]);
    }
    if (empty($_SESSION["carrinho"])) {
        $stmt = $pdo->prepare("DELETE FROM carrinhos_abandonados WHERE sessao_id = ? OR (usuario_id = ? AND usuario_id IS NOT NULL)");
        $stmt->execute([$sessao_id, $user_id]);
        return;
    }
    $stmt = $pdo->prepare("SELECT id FROM carrinhos_abandonados WHERE sessao_id = ? OR (usuario_id = ? AND usuario_id IS NOT NULL) LIMIT 1");
    $stmt->execute([$sessao_id, $user_id]);
    $existente = $stmt->fetch();
    if ($existente) {
        $stmt = $pdo->prepare("UPDATE carrinhos_abandonados SET usuario_id = ?, dados_carrinho = ?, valor_total = ?, data_atualizacao = NOW() WHERE id = ?");
        $stmt->execute([$user_id, $dados, $valor_total, $existente["id"]]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO carrinhos_abandonados (usuario_id, sessao_id, dados_carrinho, valor_total) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $sessao_id, $dados, $valor_total]);
    }
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_visitas (id INT AUTO_INCREMENT PRIMARY KEY, ip_address VARCHAR(45), data_visita DATE, hora_visita TIME, pagina_visitada VARCHAR(255), user_agent TEXT, dispositivo VARCHAR(50))");
    $migracoes = [
        "ALTER TABLE pedidos ADD COLUMN whatsapp VARCHAR(20)", "ALTER TABLE pedidos ADD COLUMN cep VARCHAR(10)",
        "ALTER TABLE pedidos ADD COLUMN endereco VARCHAR(255)", "ALTER TABLE pedidos ADD COLUMN numero VARCHAR(20)",
        "ALTER TABLE pedidos ADD COLUMN complemento VARCHAR(100)", "ALTER TABLE pedidos ADD COLUMN bairro VARCHAR(100)",
        "ALTER TABLE pedidos ADD COLUMN cidade VARCHAR(100)", "ALTER TABLE pedidos ADD COLUMN estado VARCHAR(2)",
        "ALTER TABLE usuarios ADD COLUMN whatsapp VARCHAR(20)", "ALTER TABLE usuarios ADD COLUMN cep VARCHAR(10)",
        "ALTER TABLE usuarios ADD COLUMN endereco VARCHAR(255)", "ALTER TABLE usuarios ADD COLUMN numero VARCHAR(20)",
        "ALTER TABLE usuarios ADD COLUMN complemento VARCHAR(100)", "ALTER TABLE usuarios ADD COLUMN bairro VARCHAR(100)",
        "ALTER TABLE usuarios ADD COLUMN cidade VARCHAR(100)", "ALTER TABLE usuarios ADD COLUMN estado VARCHAR(2)",
        "CREATE TABLE IF NOT EXISTS carrinhos_abandonados (id INT AUTO_INCREMENT PRIMARY KEY, usuario_id INT, sessao_id VARCHAR(255), dados_carrinho TEXT, valor_total DECIMAL(10,2), data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX (usuario_id), INDEX (sessao_id))"
    ];
    foreach ($migracoes as $sql) { try { $pdo->exec($sql); } catch (Exception $e) {} }
} catch (Exception $e) {}
?>
