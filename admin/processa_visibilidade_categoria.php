<?php
// admin/processa_visibilidade_categoria.php
require_once 'secure.php';

if (isset($_GET['id']) && isset($_GET['visivel'])) {
    $id = (int)$_GET['id'];
    $visivel = (int)$_GET['visivel'];

    try {
        $stmt = $pdo->prepare("UPDATE categorias SET exibir_home = ? WHERE id = ?");
        $stmt->execute([$visivel, $id]);
        $_SESSION['admin_message'] = "Visibilidade da categoria atualizada.";
    }
    catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao atualizar visibilidade: " . $e->getMessage();
    }
}

header("Location: gerenciar_categorias.php");
exit();
?>