<?php
// admin/deletar_imagem_produto.php — Remove uma imagem da galeria do produto
require_once 'secure.php';

$img_id     = (int)($_GET['id']         ?? 0);
$produto_id = (int)($_GET['produto_id'] ?? 0);

if ($img_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT imagem FROM produto_imagens WHERE id = ?");
        $stmt->execute([$img_id]);
        $path = $stmt->fetchColumn();

        if ($path) {
            $pdo->prepare("DELETE FROM produto_imagens WHERE id = ?")->execute([$img_id]);
            $file = '../' . ltrim($path, '/');
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $_SESSION['admin_message'] = "Imagem removida.";
    } catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao remover imagem: " . $e->getMessage();
    }
}

header("Location: editar_produto.php?id=$produto_id");
exit();
