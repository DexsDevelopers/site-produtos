<?php
// admin/processa_banner_avancado.php - Processador AvanÃ§ado de Banners
require_once 'secure.php';

// FunÃ§Ã£o para upload de imagem
function uploadBannerImage($file, $banner_id = null) {
    $upload_dir = '../assets/uploads/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Tipo de arquivo nÃ£o permitido. Use JPG, PNG, GIF ou WebP.');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('Arquivo muito grande. MÃ¡ximo 5MB.');
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'banner_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Erro ao fazer upload da imagem.');
    }
    
    return 'assets/uploads/' . $filename;
}

// --- LÃ“GICA PARA ADICIONAR NOVO BANNER ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $texto_botao = trim($_POST['texto_botao'] ?? 'Saiba Mais');
    $tipo = trim($_POST['tipo'] ?? 'principal');
    $posicao = (int)($_POST['posicao'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $nova_aba = isset($_POST['nova_aba']) ? 1 : 0;
    
    if (empty($_FILES['imagem']['name'])) {
        $_SESSION['admin_message'] = "A imagem Ã© obrigatÃ³ria para novos banners.";
        $_SESSION['admin_message_type'] = 'error';
    } else {
        try {
            $imagem = uploadBannerImage($_FILES['imagem']);
            
            $stmt = $pdo->prepare("
                INSERT INTO banners (titulo, subtitulo, link, texto_botao, tipo, posicao, ativo, nova_aba, imagem, data_criacao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$titulo, $subtitulo, $link, $texto_botao, $tipo, $posicao, $ativo, $nova_aba, $imagem]);
            
            $_SESSION['admin_message'] = "Banner adicionado com sucesso!";
            $_SESSION['admin_message_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['admin_message'] = "Erro ao adicionar banner: " . $e->getMessage();
            $_SESSION['admin_message_type'] = 'error';
        }
    }
}

// --- LÃ“GICA PARA EDITAR BANNER ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $banner_id = (int)$_POST['banner_id'];
    $titulo = trim($_POST['titulo'] ?? '');
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $texto_botao = trim($_POST['texto_botao'] ?? 'Saiba Mais');
    $tipo = trim($_POST['tipo'] ?? 'principal');
    $posicao = (int)($_POST['posicao'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $nova_aba = isset($_POST['nova_aba']) ? 1 : 0;
    
    if ($banner_id > 0) {
        try {
            $imagem = null;
            
            // Se uma nova imagem foi enviada
            if (!empty($_FILES['imagem']['name'])) {
                $imagem = uploadBannerImage($_FILES['imagem'], $banner_id);
            }
            
            if ($imagem) {
                $stmt = $pdo->prepare("
                    UPDATE banners 
                    SET titulo = ?, subtitulo = ?, link = ?, texto_botao = ?, tipo = ?, posicao = ?, ativo = ?, nova_aba = ?, imagem = ?, data_atualizacao = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$titulo, $subtitulo, $link, $texto_botao, $tipo, $posicao, $ativo, $nova_aba, $imagem, $banner_id]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE banners 
                    SET titulo = ?, subtitulo = ?, link = ?, texto_botao = ?, tipo = ?, posicao = ?, ativo = ?, nova_aba = ?, data_atualizacao = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$titulo, $subtitulo, $link, $texto_botao, $tipo, $posicao, $ativo, $nova_aba, $banner_id]);
            }
            
            $_SESSION['admin_message'] = "Banner atualizado com sucesso!";
            $_SESSION['admin_message_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['admin_message'] = "Erro ao atualizar banner: " . $e->getMessage();
            $_SESSION['admin_message_type'] = 'error';
        }
    }
}

// --- LÃ“GICA PARA DELETAR BANNER ---
if (isset($_GET['deletar'])) {
    $id = (int)$_GET['deletar'];
    
    if ($id > 0) {
        try {
            // Buscar o banner para deletar a imagem
            $stmt = $pdo->prepare("SELECT imagem FROM banners WHERE id = ?");
            $stmt->execute([$id]);
            $banner = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($banner && file_exists('../' . $banner['imagem'])) {
                unlink('../' . $banner['imagem']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['admin_message'] = "Banner deletado com sucesso!";
            $_SESSION['admin_message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao deletar banner: " . $e->getMessage();
            $_SESSION['admin_message_type'] = 'error';
        }
    }
}

// --- LÃ“GICA PARA ALTERAR STATUS ---
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE banners SET ativo = NOT ativo WHERE id = ?");
            $stmt->execute([$id]);
            
            $stmt = $pdo->prepare("SELECT ativo FROM banners WHERE id = ?");
            $stmt->execute([$id]);
            $nova_status = $stmt->fetchColumn();
            
            $_SESSION['admin_message'] = "Status do banner alterado para " . ($nova_status ? 'ativo' : 'inativo') . "!";
            $_SESSION['admin_message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao alterar status: " . $e->getMessage();
            $_SESSION['admin_message_type'] = 'error';
        }
    }
}

// --- LÃ“GICA PARA REORDENAR BANNERS ---
if (isset($_POST['reordenar'])) {
    $banners = $_POST['banners'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($banners as $index => $banner_id) {
            $stmt = $pdo->prepare("UPDATE banners SET posicao = ? WHERE id = ?");
            $stmt->execute([$index + 1, $banner_id]);
        }
        
        $pdo->commit();
        $_SESSION['admin_message'] = "Ordem dos banners atualizada com sucesso!";
        $_SESSION['admin_message_type'] = 'success';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['admin_message'] = "Erro ao reordenar banners: " . $e->getMessage();
        $_SESSION['admin_message_type'] = 'error';
    }
}

// Redirecionar de volta para a pÃ¡gina de gerenciamento
header("Location: gerenciar_banners.php");
exit();
?>

