<?php
// processa_avaliacao.php
session_start();
require_once 'config.php';

// Segurança: usuário precisa estar logado para avaliar
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produto_id = (int)$_POST['produto_id'];
    $usuario_id = $_SESSION['user_id'];
    $nota = (int)$_POST['nota'];
    $comentario = trim($_POST['comentario']);

    // Validações
    if ($nota < 1 || $nota > 5) {
        $_SESSION['error_message'] = "Por favor, selecione uma nota de 1 a 5 estrelas.";
        header("Location: produto.php?id=" . $produto_id);
        exit();
    }

    // Verifica se o usuário já avaliou este produto para evitar spam
    $stmt_check = $pdo->prepare("SELECT id FROM avaliacoes WHERE produto_id = ? AND usuario_id = ?");
    $stmt_check->execute([$produto_id, $usuario_id]);
    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "Você já avaliou este produto.";
        header("Location: produto.php?id=" . $produto_id);
        exit();
    }

    // Se tudo estiver certo, insere a avaliação no banco
    try {
        $stmt = $pdo->prepare("INSERT INTO avaliacoes (produto_id, usuario_id, nota, comentario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$produto_id, $usuario_id, $nota, $comentario]);
        $_SESSION['success_message'] = "Sua avaliação foi enviada com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erro ao enviar sua avaliação. Tente novamente.";
    }
}

// Redireciona de volta para a página do produto
header("Location: produto.php?id=" . $produto_id);
exit();
?>