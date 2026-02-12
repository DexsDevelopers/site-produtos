<?php
// admin/salvar_midia.php
require_once 'secure.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['arquivo'])) {
    $titulo = $_POST['titulo'] ?? 'Mídia sem título';
    $arquivo = $_FILES['arquivo'];

    if ($arquivo['error'] === 0) {
        $target_dir = "../assets/uploads/midias/";

        // Garante que o diretório existe
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $extension = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid('midia_', true) . '.' . $extension;
        $target_file = $target_dir . $new_filename;

        // Verifica o tipo
        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $video_extensions = ['mp4', 'mov', 'avi', 'mpeg'];

        $tipo = 'imagem';
        if (in_array($extension, $video_extensions)) {
            $tipo = 'video';
        }

        if (move_uploaded_file($arquivo['tmp_name'], $target_file)) {
            $path = "assets/uploads/midias/" . $new_filename;

            try {
                $stmt = $pdo->prepare("INSERT INTO midias (titulo, tipo, path) VALUES (?, ?, ?)");
                $stmt->execute([$titulo, $tipo, $path]);
                $_SESSION['admin_message'] = "Mídia enviada com sucesso!";
            }
            catch (PDOException $e) {
                $_SESSION['admin_message'] = "Erro no banco de dados: " . $e->getMessage();
            }
        }
        else {
            $_SESSION['admin_message'] = "Erro ao mover o arquivo para o servidor.";
        }
    }
    else {
        $_SESSION['admin_message'] = "Erro no upload do arquivo (Código: " . $arquivo['error'] . ")";
    }

    header("Location: gestao_midias.php");
    exit();
}
header("Location: gestao_midias.php");
exit();