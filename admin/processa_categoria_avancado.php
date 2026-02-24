<?php
// admin/processa_categoria_avancado.php - Processador AvanÃ§ado de Categorias
require_once 'secure.php';

// --- LÃ“GICA PARA ADICIONAR NOVA CATEGORIA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao'] ?? '');
    $ordem = (int)($_POST['ordem'] ?? 0);
    $icone = trim($_POST['icone'] ?? 'fas fa-tag');
    $cor = trim($_POST['cor'] ?? '#FF3B5C');
    $ativa = isset($_POST['ativa']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    
    if (!empty($nome)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO categorias (nome, descricao, ordem, icone, cor, ativa, destaque, meta_title, meta_description, data_criacao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$nome, $descricao, $ordem, $icone, $cor, $ativa, $destaque, $meta_title, $meta_description]);
            
            $_SESSION['admin_message'] = "Categoria adicionada com sucesso!";
            $_SESSION['admin_message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao adicionar categoria: " . $e->getMessage();
            $_SESSION['admin_message_type'] = 'error';
        }
    } else {
        $_SESSION['admin_message'] = "O nome da categoria nÃ£o pode ser vazio.";
        $_SESSION['admin_message_type'] = 'error';
    }
}

// --- LÃ“GICA PARA EDITAR CATEGORIA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $categoria_id = (int)$_POST['categoria_id'];
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao'] ?? '');
    $ordem = (int)($_POST['ordem'] ?? 0);
    $icone = trim($_POST['icone'] ?? 'fas fa-tag');
    $cor = trim($_POST['cor'] ?? '#FF3B5C');
    $ativa = isset($_POST['ativa']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    
    if (!empty($nome) && $categoria_id > 0) {
        try {
            $stmt = $pdo->prepare("
                UPDATE categorias 
                SET nome = ?, descricao = ?, ordem = ?, icone = ?, cor = ?, ativa = ?, destaque = ?, meta_title = ?, meta_description = ?, data_atualizacao = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$nome, $descricao, $ordem, $icone, $cor, $ativa, $destaque, $meta_title, $meta_description, $categoria_id]);
            
            $_SESSION['admin_message'] = "Categoria atualizada com sucesso!";
            $_SESSION['admin_message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao atualizar categoria: " . $e->getMessage();
            $_SESSION['admin_message_type'] = 'error';
        }
    } else {
        $_SESSION['admin_message'] = "Dados invÃ¡lidos para atualizaÃ§Ã£o.";
        $_SESSION['admin_message_type'] = 'error';
    }
}

// --- LÃ“GICA PARA DELETAR CATEGORIA ---
if (isset($_GET['deletar'])) {
    $id = (int)$_GET['deletar'];
    
    if ($id > 0) {
        try {
            // Verificar se hÃ¡ produtos nesta categoria
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = ?");
            $stmt->execute([$id]);
            $produtos_count = $stmt->fetchColumn();
            
            if ($produtos_count > 0) {
                $_SESSION['admin_message'] = "NÃ£o Ã© possÃ­vel deletar esta categoria pois ela possui produtos associados. Remova os produtos primeiro.";
                $_SESSION['admin_message_type'] = 'warning';
            } else {
                $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['admin_message'] = "Categoria deletada com sucesso!";
                $_SESSION['admin_message_type'] = 'success';
            }
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao deletar categoria: " . $e->getMessage();
            $_SESSION['admin_message_type'] = 'error';
        }
    }
}

// --- LÃ“GICA PARA ALTERAR STATUS ---
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE categorias SET ativa = NOT ativa WHERE id = ?");
            $stmt->execute([$id]);
            
            $stmt = $pdo->prepare("SELECT ativa FROM categorias WHERE id = ?");
            $stmt->execute([$id]);
            $nova_status = $stmt->fetchColumn();
            
            $_SESSION['admin_message'] = "Status da categoria alterado para " . ($nova_status ? 'ativo' : 'inativo') . "!";
            $_SESSION['admin_message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao alterar status: " . $e->getMessage();
            $_SESSION['admin_message_type'] = 'error';
        }
    }
}

// --- LÃ“GICA PARA REORDENAR CATEGORIAS ---
if (isset($_POST['reordenar'])) {
    $categorias = $_POST['categorias'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($categorias as $index => $categoria_id) {
            $stmt = $pdo->prepare("UPDATE categorias SET ordem = ? WHERE id = ?");
            $stmt->execute([$index + 1, $categoria_id]);
        }
        
        $pdo->commit();
        $_SESSION['admin_message'] = "Ordem das categorias atualizada com sucesso!";
        $_SESSION['admin_message_type'] = 'success';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['admin_message'] = "Erro ao reordenar categorias: " . $e->getMessage();
        $_SESSION['admin_message_type'] = 'error';
    }
}

// Redirecionar de volta para a pÃ¡gina de gerenciamento
header("Location: gerenciar_categorias.php");
exit();
?>

