<?php
// admin/deletar_produto.php
require_once 'secure.php';

if (isset($_GET['id'])) {
    $produto_id = (int)$_GET['id'];

    try {
        // Primeiro, pega o caminho da imagem para poder deletá-la do servidor
        $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            // Deleta o registro do produto no banco de dados
            $delete_stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
            $delete_stmt->execute([$produto_id]);

            // Deleta o arquivo da imagem do servidor
            $caminho_imagem = '../' . $produto['imagem'];
            if (file_exists($caminho_imagem)) {
                unlink($caminho_imagem);
            }

            $_SESSION['admin_message'] = "Produto deletado com sucesso!";
        } else {
            $_SESSION['admin_message'] = "Produto não encontrado.";
        }

    } catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao deletar o produto: " . $e->getMessage();
    }
}

header("Location: index.php");
exit();
?>
