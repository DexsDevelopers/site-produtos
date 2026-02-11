<?php
// admin/toggle_destaque_produto.php
require_once 'secure.php';

if (isset($_GET['id']) && isset($_GET['destaque'])) {
    $id = (int)$_GET['id'];
    $destaque = (int)$_GET['destaque'];

    try {
        $stmt = $pdo->prepare("UPDATE produtos SET destaque = ? WHERE id = ?");
        $stmt->execute([$destaque, $id]);
        $_SESSION['admin_message'] = "Status de destaque atualizado.";
    }
    catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao atualizar destaque: " . $e->getMessage();
    }
}

header("Location: gerenciar_produtos.php");
exit();
?>