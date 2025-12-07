<?php
// salvar_preferencia_tema.php - Salvar preferência de tema do usuário
session_start();
require_once 'config.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

// Obter dados do JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['tema'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit();
}

$tema = $input['tema'];
$usuario_id = $_SESSION['user_id'];

// Validar tema
$temas_validos = ['light', 'dark', 'auto'];
if (!in_array($tema, $temas_validos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tema inválido']);
    exit();
}

try {
    // Verificar se a tabela existe, se não, criar
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS preferencias_usuario (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            tema_preferido ENUM('light', 'dark', 'auto') DEFAULT 'auto',
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_usuario (usuario_id),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        )
    ");
    
    // Salvar ou atualizar preferência
    $stmt = $pdo->prepare("
        INSERT INTO preferencias_usuario (usuario_id, tema_preferido) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE tema_preferido = VALUES(tema_preferido)
    ");
    
    $resultado = $stmt->execute([$usuario_id, $tema]);
    
    if ($resultado) {
        echo json_encode([
            'success' => true, 
            'message' => 'Preferência salva com sucesso',
            'tema' => $tema
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao salvar preferência'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?>
