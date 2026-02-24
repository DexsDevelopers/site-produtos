<?php
// admin/baixar_midia.php
require_once 'secure.php';

if (!isset($_GET['grupo_id'])) {
    die("ID do grupo não especificado.");
}

$grupo_id = $_GET['grupo_id'];

try {
    // Busca todas as mídias do grupo
    $stmt = $pdo->prepare("SELECT * FROM midias WHERE grupo_id = ?");
    $stmt->execute([$grupo_id]);
    $midias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($midias)) {
        die("Nenhuma mídia encontrada para este grupo.");
    }

    // Se for apenas 1 arquivo, redireciona direto para ele
    if (count($midias) === 1) {
        $file_path = '../' . $midias[0]['path'];
        if (file_exists($file_path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            readfile($file_path);
            exit;
        }
        else {
            die("Arquivo não encontrado no servidor.");
        }
    }

    // Se forem múltiplos, cria um ZIP
    $zipname = 'postagem_' . $grupo_id . '.zip';
    $zip_path = sys_get_temp_dir() . '/' . $zipname;

    $zip = new ZipArchive;
    if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

        foreach ($midias as $midia) {
            $file_path = '../' . $midia['path'];
            if (file_exists($file_path)) {
                $zip->addFile($file_path, basename($file_path));
            }
        }

        $zip->close();

        // Força o download
        if (file_exists($zip_path)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipname . '"');
            header('Content-Length: ' . filesize($zip_path));
            readfile($zip_path);

            // Remove o arquivo temporário
            unlink($zip_path);
            exit;
        }
        else {
            die("Erro ao criar arquivo ZIP.");
        }
    }
    else {
        die("Não foi possível criar o arquivo ZIP.");
    }

}
catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
}
?>