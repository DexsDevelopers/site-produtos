<?php
// admin/excluir_midia.php
require_once 'secure.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Busca o path do arquivo
    $stmt = $pdo->prepare("SELECT path FROM midias WHERE id = ?");
    $stmt->execute([$id]);
    $midia = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($midia) {
        // Deleta o arquivo físico
        $file_path = "../" . $midia['path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Deleta do banco
        $stmt = $pdo->prepare("DELETE FROM midias WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['admin_message'] = "Mídia excluída com sucesso!";
    }
    else {
        $_SESSION['admin_message'] = "Mídia não encontrada.";
    }
}

header("Location: gestao_midias.php");
exit();