<?php
// admin/processa_categoria.php
require_once 'secure.php';

// --- LÓGICA PARA ADICIONAR NOVA CATEGORIA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome']);
    if (!empty($nome)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categorias (nome) VALUES (?)");
            $stmt->execute([$nome]);
            $_SESSION['admin_message'] = "Categoria adicionada com sucesso!";
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao adicionar categoria: " . $e->getMessage();
        }
    } else {
        $_SESSION['admin_message'] = "O nome da categoria não pode ser vazio.";
    }
}

// --- LÓGICA PARA DELETAR CATEGORIA ---
if (isset($_GET['deletar'])) {
    $id = (int)$_GET['deletar'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['admin_message'] = "Categoria deletada com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao deletar categoria: " . $e->getMessage();
    }
}

header("Location: gerenciar_categorias.php");
exit();
?>