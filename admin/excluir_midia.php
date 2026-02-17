<?php
// admin/excluir_midia.php
require_once 'secure.php';

if (isset($_GET['grupo_id'])) {
    $grupo_id = $_GET['grupo_id'];

    // Busca todas as mídias do grupo para excluir os arquivos físicos
    $stmt = $pdo->prepare("SELECT path FROM midias WHERE grupo_id = ?");
    $stmt->execute([$grupo_id]);
    $midias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($midias) {
        foreach ($midias as $midia) {
            $file_path = "../" . $midia['path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Deleta todos do banco
        $stmt = $pdo->prepare("DELETE FROM midias WHERE grupo_id = ?");
        $stmt->execute([$grupo_id]);

        $_SESSION['admin_message'] = "Postagem excluída com sucesso!";
    }
    else {
        // Tenta excluir single que pode ter sido passado como grupo_id (compatibilidade)
        $id_parts = explode('_', $grupo_id);
        if (count($id_parts) > 1 && is_numeric($id_parts[1])) {
            $id = (int)$id_parts[1];
            // Redireciona para a logica de ID simples abaixo ou executa aqui mesmo
            $stmt = $pdo->prepare("SELECT path FROM midias WHERE id = ?");
            $stmt->execute([$id]);
            $midia = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($midia) {
                $file_path = "../" . $midia['path'];
                if (file_exists($file_path))
                    unlink($file_path);

                $stmt = $pdo->prepare("DELETE FROM midias WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['admin_message'] = "Mídia excluída com sucesso!";
            }
        }
        else {
            $_SESSION['admin_message'] = "Nenhuma mídia encontrada para este grupo.";
        }
    }

}
elseif (isset($_GET['id'])) {
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