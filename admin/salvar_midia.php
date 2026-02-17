<?php
// admin/salvar_midia.php
require_once 'secure.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['arquivo'])) {
    $titulo = $_POST['titulo'] ?? 'Postagem sem título';
    $arquivos = $_FILES['arquivo'];

    // Gera um ID único para o grupo de mídias
    $grupo_id = uniqid('post_', true);

    $total_arquivos = count($arquivos['name']);
    $sucesso_count = 0;
    $erros = [];

    // Garante coluna grupo_id
    try {
        $pdo->exec("ALTER TABLE midias ADD COLUMN grupo_id VARCHAR(50) DEFAULT NULL");
    }
    catch (PDOException $e) {
    }

    for ($i = 0; $i < $total_arquivos; $i++) {
        if ($arquivos['error'][$i] === 0) {
            $target_dir = "../assets/uploads/midias/";

            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $extension = strtolower(pathinfo($arquivos['name'][$i], PATHINFO_EXTENSION));
            $new_filename = uniqid('midia_', true) . '.' . $extension;
            $target_file = $target_dir . $new_filename;

            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $video_extensions = ['mp4', 'mov', 'avi', 'mpeg'];

            $tipo = 'imagem';
            if (in_array($extension, $video_extensions)) {
                $tipo = 'video';
            }

            if (move_uploaded_file($arquivos['tmp_name'][$i], $target_file)) {
                $path = "assets/uploads/midias/" . $new_filename;

                try {
                    $stmt = $pdo->prepare("INSERT INTO midias (titulo, tipo, path, grupo_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$titulo, $tipo, $path, $grupo_id]);
                    $sucesso_count++;
                }
                catch (PDOException $e) {
                    $erros[] = "Erro DB: " . $e->getMessage();
                }
            }
            else {
                $erros[] = "Erro ao mover arquivo " . $arquivos['name'][$i];
            }
        }
    }

    if ($sucesso_count > 0) {
        $_SESSION['admin_message'] = "✅ $sucesso_count arquivo(s) enviado(s) com sucesso na postagem!";
    }
    else {
        $_SESSION['admin_message'] = "❌ Falha no envio. " . implode(", ", $erros);
    }

    header("Location: gestao_midias.php");
    exit();
}
header("Location: gestao_midias.php");
exit();