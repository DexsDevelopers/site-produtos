<?php
// admin/processa_categoria.php
require_once 'secure.php';

// --- LÓGICA PARA ADICIONAR NOVA CATEGORIA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    
    // Campos adicionais do editor avançado (se existirem no POST)
    $descricao = trim($_POST['descricao'] ?? '');
    $ordem = isset($_POST['ordem']) ? (int)$_POST['ordem'] : null;
    $icone = trim($_POST['icone'] ?? 'fas fa-tag');
    $cor = trim($_POST['cor'] ?? '#FF3B5C');
    $ativa = isset($_POST['ativa']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $banner_categoria = null;
    if (isset($_FILES['banner_categoria']) && $_FILES['banner_categoria']['error'] === 0) {
        $target_dir = "../assets/uploads/";
        $ext = pathinfo($_FILES['banner_categoria']['name'], PATHINFO_EXTENSION);
        $fname = uniqid('cat_banner_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['banner_categoria']['tmp_name'], $target_dir . $fname)) {
            $banner_categoria = 'assets/uploads/' . $fname;
        }
    }
    $banner_categoria_mobile = null;
    if (isset($_FILES['banner_categoria_mobile']) && $_FILES['banner_categoria_mobile']['error'] === 0) {
        $target_dir = "../assets/uploads/";
        $ext = pathinfo($_FILES['banner_categoria_mobile']['name'], PATHINFO_EXTENSION);
        $fname = uniqid('cat_banner_mob_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['banner_categoria_mobile']['tmp_name'], $target_dir . $fname)) {
            $banner_categoria_mobile = 'assets/uploads/' . $fname;
        }
    }

    if (!empty($nome)) {
        try {
            if ($ordem === null) {
                // Pega a maior ordem atual para colocar a nova categoria no final
                $stmt_ordem = $pdo->query("SELECT MAX(ordem) FROM categorias");
                $max_ordem = $stmt_ordem->fetchColumn();
                $ordem = ($max_ordem !== false) ? $max_ordem + 1 : 0;
            }

            // Tenta inserir com todos os campos possíveis
            try {
                $stmt = $pdo->prepare("INSERT INTO categorias (nome, parent_id, descricao, ordem, icone, cor, ativa, destaque, meta_title, meta_description, banner_categoria, banner_categoria_mobile) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $parent_id, $descricao, $ordem, $icone, $cor, $ativa, $destaque, $meta_title, $meta_description, $banner_categoria, $banner_categoria_mobile]);
            } catch (PDOException $e) {
                // Se falhar, tenta apenas os campos básicos (compatibilidade com versões anteriores da tabela)
                $stmt = $pdo->prepare("INSERT INTO categorias (nome, ordem, parent_id) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $ordem, $parent_id]);
            }
            
            $_SESSION['admin_message'] = "Categoria adicionada com sucesso!";
        }
        catch (PDOException $e) {
            // Se a coluna parent_id não existir, tenta adicionar
            if (strpos($e->getMessage(), "Unknown column 'parent_id'") !== false) {
                $pdo->exec("ALTER TABLE categorias ADD COLUMN parent_id INT DEFAULT NULL");
                // Tenta novamente
                $stmt = $pdo->prepare("INSERT INTO categorias (nome, ordem, parent_id) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $ordem, $parent_id]);
                $_SESSION['admin_message'] = "Categoria adicionada com sucesso!";
            } else {
                $_SESSION['admin_message'] = "Erro ao adicionar categoria: " . $e->getMessage();
            }
        }
    }
    else {
        $_SESSION['admin_message'] = "O nome da categoria não pode ser vazio.";
    }
}

// --- LÓGICA PARA EDITAR CATEGORIA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $categoria_id = (int)$_POST['categoria_id'];
    $nome = trim($_POST['nome']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $descricao = trim($_POST['descricao'] ?? '');
    $ordem = (int)($_POST['ordem'] ?? 0);
    $icone = trim($_POST['icone'] ?? 'fas fa-tag');
    $cor = trim($_POST['cor'] ?? '#FF3B5C');
    $ativa = isset($_POST['ativa']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $banner_categoria = null;
    if (isset($_FILES['banner_categoria']) && $_FILES['banner_categoria']['error'] === 0) {
        $target_dir = "../assets/uploads/";
        $ext = pathinfo($_FILES['banner_categoria']['name'], PATHINFO_EXTENSION);
        $fname = uniqid('cat_banner_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['banner_categoria']['tmp_name'], $target_dir . $fname)) {
            $banner_categoria = 'assets/uploads/' . $fname;
        }
    }
    if (isset($_POST['remover_banner'])) $banner_categoria = '';

    $banner_categoria_mobile = null;
    if (isset($_FILES['banner_categoria_mobile']) && $_FILES['banner_categoria_mobile']['error'] === 0) {
        $target_dir = "../assets/uploads/";
        $ext = pathinfo($_FILES['banner_categoria_mobile']['name'], PATHINFO_EXTENSION);
        $fname = uniqid('cat_banner_mob_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['banner_categoria_mobile']['tmp_name'], $target_dir . $fname)) {
            $banner_categoria_mobile = 'assets/uploads/' . $fname;
        }
    }
    if (isset($_POST['remover_banner_mobile'])) $banner_categoria_mobile = '';
    
    // Evita que uma categoria seja pai de si mesma
    if ($parent_id == $categoria_id) {
        $parent_id = null;
    }

    if (!empty($nome) && $categoria_id > 0) {
        try {
            // Tenta atualizar com todos os campos possíveis
            try {
                $base_fields = "nome = ?, parent_id = ?, descricao = ?, ordem = ?, icone = ?, cor = ?, ativa = ?, destaque = ?, meta_title = ?, meta_description = ?";
                $base_vals  = [$nome, $parent_id, $descricao, $ordem, $icone, $cor, $ativa, $destaque, $meta_title, $meta_description];
                $extra_sql  = '';
                $extra_vals = [];
                if ($banner_categoria !== null)        { $extra_sql .= ', banner_categoria = ?';        $extra_vals[] = $banner_categoria; }
                if ($banner_categoria_mobile !== null) { $extra_sql .= ', banner_categoria_mobile = ?'; $extra_vals[] = $banner_categoria_mobile; }
                $stmt = $pdo->prepare("UPDATE categorias SET $base_fields$extra_sql WHERE id = ?");
                $stmt->execute(array_merge($base_vals, $extra_vals, [$categoria_id]));
            } catch (PDOException $e) {
                // Fallback para campos básicos
                $stmt = $pdo->prepare("UPDATE categorias SET nome = ?, parent_id = ?, ordem = ? WHERE id = ?");
                $stmt->execute([$nome, $parent_id, $ordem, $categoria_id]);
            }
            
            $_SESSION['admin_message'] = "Categoria atualizada com sucesso!";
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao atualizar categoria: " . $e->getMessage();
        }
    } else {
        $_SESSION['admin_message'] = "Dados inválidos para atualização.";
    }
}

// --- LÓGICA PARA DELETAR CATEGORIA ---
if (isset($_GET['deletar'])) {
    $id = (int)$_GET['deletar'];
    try {
        // Primeiro, remove o parent_id das subcategorias para não quebrá-las ou deletar em cascata indesejada
        $stmt_sub = $pdo->prepare("UPDATE categorias SET parent_id = NULL WHERE parent_id = ?");
        $stmt_sub->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['admin_message'] = "Categoria deletada com sucesso!";
    }
    catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao deletar categoria: " . $e->getMessage();
    }
}

header("Location: gerenciar_categorias.php");
exit();
?>