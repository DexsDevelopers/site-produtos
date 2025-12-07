<?php
// admin/salvar_produto.php (VERSÃO FINAL COM CORREÇÃO DE TIPO DE DADO)
require_once 'secure.php';

// --- LÓGICA PARA ADICIONAR UM NOVO PRODUTO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar'])) {
    
    $nome = trim($_POST['nome']);
    $descricao_curta = trim($_POST['descricao_curta']);
    $descricao = trim($_POST['descricao']);
    $preco = trim($_POST['preco']);
    $preco_antigo = !empty(trim($_POST['preco_antigo'])) ? trim($_POST['preco_antigo']) : null;
    $categoria_id = (int)$_POST['categoria_id'];
    $checkout_link = trim($_POST['checkout_link']);
    
    if (empty($nome) || empty($preco) || empty($categoria_id) || !isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== 0) {
        $_SESSION['admin_message'] = "Nome, Preço, Categoria e Imagem são obrigatórios.";
        header("Location: adicionar_produto.php");
        exit();
    }

    $target_dir = "../assets/uploads/";
    $file_extension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('produto_', true) . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file)) {
        $imagem_path = "assets/uploads/" . $new_filename;
        try {
            $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao_curta, descricao, preco, preco_antigo, imagem, categoria_id, checkout_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $descricao_curta, $descricao, $preco, $preco_antigo, $imagem_path, $categoria_id, $checkout_link]);
            $_SESSION['admin_message'] = "Produto adicionado com sucesso!";
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao salvar o produto: " . $e->getMessage();
        }
    } else {
        $_SESSION['admin_message'] = "Erro ao fazer upload da imagem.";
    }
    header("Location: adicionar_produto.php");
    exit();
}

// --- LÓGICA PARA EDITAR UM PRODUTO EXISTENTE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    
    $id = (int)$_POST['id'];
    $nome = trim($_POST['nome']);
    $descricao_curta = trim($_POST['descricao_curta']);
    $descricao = trim($_POST['descricao']);
    $preco = trim($_POST['preco']);
    $preco_antigo = !empty(trim($_POST['preco_antigo'])) ? trim($_POST['preco_antigo']) : null;
    $categoria_id = (int)$_POST['categoria_id'];
    $checkout_link = trim($_POST['checkout_link']);

    if (empty($nome) || empty($preco) || empty($id) || empty($categoria_id)) {
        $_SESSION['admin_message'] = "Nome, Preço e Categoria são obrigatórios.";
        header("Location: editar_produto.php?id=$id");
        exit();
    }

    $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto_atual = $stmt->fetch(PDO::FETCH_ASSOC);
    $imagem_path = $produto_atual['imagem'];

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $target_dir = "../assets/uploads/";
        $file_extension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('produto_', true) . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file)) {
            if (!empty($imagem_path) && file_exists('../' . $imagem_path)) {
                unlink('../' . $imagem_path);
            }
            $imagem_path = "assets/uploads/" . $new_filename;
        }
    }

    try {
        $sql = "UPDATE produtos SET nome = :nome, descricao_curta = :desc_curta, descricao = :desc, preco = :preco, preco_antigo = :preco_antigo, imagem = :img, categoria_id = :cat_id, checkout_link = :link WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        // A MÁGICA ESTÁ AQUI: bindParam força o tipo de dado correto
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':desc_curta', $descricao_curta);
        $stmt->bindParam(':desc', $descricao);
        $stmt->bindParam(':preco', $preco);
        $stmt->bindParam(':preco_antigo', $preco_antigo);
        $stmt->bindParam(':img', $imagem_path);
        $stmt->bindParam(':cat_id', $categoria_id, PDO::PARAM_INT);
        $stmt->bindParam(':link', $checkout_link);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // Forçamos o ID a ser um INTEIRO
        
        $stmt->execute();
        $_SESSION['admin_message'] = "Produto atualizado com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao atualizar o produto: " . $e->getMessage();
    }
    header("Location: editar_produto.php?id=$id");
    exit();
}

header("Location: index.php");
exit();
?>