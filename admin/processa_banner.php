<?php
// admin/processa_banner.php
require_once 'secure.php';

// --- LÓGICA PARA EDITAR BANNER ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $banner_id = (int)$_POST['banner_id'];
    $titulo = trim($_POST['titulo']);
    $subtitulo = trim($_POST['subtitulo']);
    $link = trim($_POST['link']);
    $texto_botao = trim($_POST['texto_botao'] ?? 'Saiba Mais');
    $tipo = trim($_POST['tipo']);
    $posicao = (int)($_POST['posicao'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $nova_aba = isset($_POST['nova_aba']) ? 1 : 0;
    
    if ($banner_id > 0) {
        try {
            $imagem_path = null;
            
            // Se uma nova imagem foi enviada
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
                $target_dir = "../assets/uploads/";
                $file_extension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid('banner_', true) . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file)) {
                    $imagem_path = "assets/uploads/" . $new_filename;
                }
            }
            
            if ($imagem_path) {
                // Atualizar com nova imagem
                $stmt = $pdo->prepare("
                    UPDATE banners 
                    SET titulo = ?, subtitulo = ?, link = ?, texto_botao = ?, tipo = ?, posicao = ?, ativo = ?, nova_aba = ?, imagem = ?, data_atualizacao = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$titulo, $subtitulo, $link, $texto_botao, $tipo, $posicao, $ativo, $nova_aba, $imagem_path, $banner_id]);
            } else {
                // Atualizar sem nova imagem
                $stmt = $pdo->prepare("
                    UPDATE banners 
                    SET titulo = ?, subtitulo = ?, link = ?, texto_botao = ?, tipo = ?, posicao = ?, ativo = ?, nova_aba = ?, data_atualizacao = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$titulo, $subtitulo, $link, $texto_botao, $tipo, $posicao, $ativo, $nova_aba, $banner_id]);
            }
            
            $_SESSION['admin_message'] = "Banner atualizado com sucesso!";
            $_SESSION['admin_message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao atualizar banner: " . $e->getMessage();
            $_SESSION['admin_message_type'] = 'error';
        }
    } else {
        $_SESSION['admin_message'] = "ID do banner inválido.";
        $_SESSION['admin_message_type'] = 'error';
    }
}

// --- LÓGICA PARA ADICIONAR BANNER ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar'])) {
    $titulo = trim($_POST['titulo']);
    $subtitulo = trim($_POST['subtitulo']);
    $link = trim($_POST['link']);
    $texto_botao = trim($_POST['texto_botao'] ?? 'Saiba Mais');
    $tipo = trim($_POST['tipo']);
    $posicao = (int)($_POST['posicao'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $nova_aba = isset($_POST['nova_aba']) ? 1 : 0;
    
    if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== 0) {
        $_SESSION['admin_message'] = "A imagem do banner é obrigatória.";
        $_SESSION['admin_message_type'] = 'error';
        header("Location: gerenciar_banners.php");
        exit();
    }

    $target_dir = "../assets/uploads/";
    $file_extension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('banner_', true) . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file)) {
        $imagem_path = "assets/uploads/" . $new_filename;
        try {
            $stmt = $pdo->prepare("
                INSERT INTO banners (titulo, subtitulo, link, texto_botao, tipo, posicao, ativo, nova_aba, imagem, data_criacao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$titulo, $subtitulo, $link, $texto_botao, $tipo, $posicao, $ativo, $nova_aba, $imagem_path]);
            $_SESSION['admin_message'] = "Banner adicionado com sucesso!";
            $_SESSION['admin_message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao salvar banner: " . $e->getMessage();
            $_SESSION['admin_message_type'] = 'error';
        }
    } else {
        $_SESSION['admin_message'] = "Erro ao fazer upload da imagem.";
        $_SESSION['admin_message_type'] = 'error';
    }
}

// --- LÓGICA PARA DELETAR BANNER ---
if (isset($_GET['deletar'])) {
    $id = (int)$_GET['deletar'];
    try {
        $stmt = $pdo->prepare("SELECT imagem FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($banner) {
            $delete_stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
            $delete_stmt->execute([$id]);
            $caminho_imagem = '../' . $banner['imagem'];
            if (file_exists($caminho_imagem)) {
                unlink($caminho_imagem);
            }
            $_SESSION['admin_message'] = "Banner deletado com sucesso!";
        }
    } catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao deletar banner: " . $e->getMessage();
    }
}

header("Location: gerenciar_banners.php");
exit();
?>